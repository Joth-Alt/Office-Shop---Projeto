<?php
// ====================================================================
// pagamento.php: Lógica PHP atualizada para verificar a sessão do usuário
// ====================================================================
session_start();

// --------------------------------------------------------------------
// 1. Verificação de Sessão do Usuário (CRÍTICO para o erro 1452)
// --------------------------------------------------------------------
$usuario_id = $_SESSION['usuario_id'] ?? null;
$logado = ($usuario_id !== null);

// Simulação de Carrinho/Totais (SUBSTITUA PELOS SEUS DADOS REAIS)
// Estes valores são apenas para preencher o HTML enquanto a lógica real não está conectada.
$subtotal_simulado = 129.90;
$taxa_entrega = 20.00;
$desconto_percentual = 0.05; // 5% de desconto para PIX
$metodo_pagamento = 'PIX/Boleto (padrão)'; 

$desconto_aplicado = $subtotal_simulado * $desconto_percentual;
$total_com_desconto = $subtotal_simulado - $desconto_aplicado;
$total_final = $total_com_desconto + $taxa_entrega;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Pagamento</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/dark-mode-vibrante.css" /> 
    <link rel="stylesheet" href="css/pagamento.css" /> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
    
    <style>
        /* --- Variáveis e Layout Principal (Coerente com Admin) --- */
        :root {
            --sidebar-width: 250px; 
            --color-primary-pink: #ff3366; 
            --color-dark-text: #4a4a4a; 
            --color-light-bg: #f5f7f9;
            --color-card-bg: #ffffff;
            --color-border-light: #eeeeee;
            --color-accent-dark: #1a1a1a; /* Fundo escuro coeso */
            --color-success: #28a745;
            --color-danger: #dc3545;
        }

        body {
            display: flex; /* CHAVE: Alinha Sidebar e Main */
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: var(--color-light-bg);
        }

        .sidebar {
            width: var(--sidebar-width);
            flex-shrink: 0; 
            position: fixed; 
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
        }
        
        /* Contêiner principal após a sidebar */
        .checkout-container {
            margin-left: var(--sidebar-width); 
            flex-grow: 1;
            padding: 40px;
            box-sizing: border-box;
            display: grid;
            grid-template-columns: 2fr 1.2fr; /* Layout principal: Resumo | Detalhes */
            gap: 40px;
            max-width: 1400px;
            width: 100%;
        }

        /* --- 1. Resumo do Pedido (Lado Esquerdo - Card) --- */
        .order-summary {
            background-color: var(--color-card-bg);
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            height: fit-content;
        }
        .order-summary h2 {
            font-size: 1.5em;
            color: var(--color-dark-text);
            border-bottom: 1px solid var(--color-border-light);
            padding-bottom: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .back-link {
            color: var(--color-primary-pink);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9em;
            display: block;
        }

        /* --- 2. Detalhes do Pagamento (Lado Direito - Card Escuro) --- */
        .payment-details {
            background-color: var(--color-accent-dark); 
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            color: white;
            height: fit-content;
        }
        .payment-details h2 {
            font-size: 1.6em;
            color: var(--color-primary-pink);
            text-transform: uppercase;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Campos e Botões */
        .payment-details label {
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
            font-weight: 500;
            color: #ddd;
            text-transform: uppercase;
            font-size: 0.85em;
        }
        .payment-details input[type="text"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 6px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            box-sizing: border-box;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        .form-row > div {
            flex: 1;
        }
        .cep-input-group {
            display: flex;
        }
        .cep-search-btn {
            background-color: var(--color-primary-pink);
            color: white;
            border: none;
            padding: 0 15px;
            border-radius: 6px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            cursor: pointer;
        }
        
        .checkout-btn {
            width: 100%;
            padding: 15px;
            background-color: var(--color-primary-pink);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            margin-top: 25px;
        }

        /* --- Estilo dos Alertas de Diagnóstico --- */
        .diagnostic-alert {
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-weight: 500;
        }
        .diagnostic-alert.error-check {
            background-color: #f8d7da; /* Vermelho claro */
            color: var(--color-danger);
            border: 1px solid #f5c6cb;
        }
        .diagnostic-alert.success-check {
            background-color: #d4edda; /* Verde claro */
            color: var(--color-success);
            border: 1px solid #c3e6cb;
        }

        /* --- Estilos para o Resumo de Itens (Simulação da Imagem) --- */
        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed var(--color-border-light);
        }
        .product-item:last-child {
            border-bottom: none;
        }
        .product-item img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        .product-details {
            flex-grow: 1;
        }
        .product-details h4 {
            margin: 0;
            font-size: 1em;
            color: var(--color-dark-text);
        }
        .product-details small {
            color: #888;
            font-size: 0.85em;
        }
        .item-price {
            font-weight: bold;
            color: var(--color-primary-pink);
            font-size: 1.1em;
        }

    </style>
</head>
<body> 
    
    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
        </nav>
        <nav class="menu-config">
            <a href="#"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
            <a href="#" id="themeToggle"><i class="fas fa-adjust"></i> <span class="txt">Tema</span></a>
            <div class="dropdown">
                <a href="#" class="dropbtn"><i class="fas fa-language"></i> <span class="txt">Tradução</span></a>
                <div class="dropdown-content">
                    <button onclick="setLanguage('pt')">🇧🇷 Português</button>
                    <button onclick="setLanguage('en')">🇺🇸 English</button>
                    <button onclick="setLanguage('es')">🇪🇦 Español</button>
                </div>
            </div>
            <a href="#" id="openCart"><i class="fas fa-shopping-cart"></i> <span class="txt">Carrinho</span> <span class="cart-count">. 0</span></a>
        </nav>
        </aside>

    <main class="checkout-container">
        
        <section class="order-summary">
            <a href="index.php" class="back-link"><i class="fas fa-chevron-left"></i> voltar para a home</a>
            
            <h2 style="margin-top: 20px;">📦 Resumo do Pedido</h2>

            <?php if (!$logado): ?>
                <div class="diagnostic-alert error-check">
                    <i class="fas fa-exclamation-triangle"></i> **ALERTA PHP:** Você não está logado (`$_SESSION['usuario_id']` está faltando). Se tentar finalizar o pedido assim, o erro **1452** ocorrerá.
                </div>
            <?php else: ?>
                 <div class="diagnostic-alert success-check">
                    <i class="fas fa-check-circle"></i> Logado com sucesso. ID de Usuário: **<?php echo htmlspecialchars($usuario_id); ?>**.
                </div>
            <?php endif; ?>
            <div id="cart-items-summary">
                
                <div class="product-item">
                    <img src="imagems/avatars/teste.png" alt="Produto Teste">
                    <div class="product-details">
                        <h4>Teste</h4>
                        <small>Produto padrão</small><br>
                        <small style="color: var(--color-primary-pink);">Qtd: 1</small>
                    </div>
                    <span class="item-price">R$ 40,00</span>
                </div>

                <div class="product-item">
                    <img src="imagems/avatars/default.png" alt="Produto 2">
                    <div class="product-details">
                        <h4>Outro Produto</h4>
                        <small>Acessório</small><br>
                        <small style="color: var(--color-primary-pink);">Qtd: 2</small>
                    </div>
                    <span class="item-price">R$ 60,00</span>
                </div>
            </div>
            
        </section>

        <aside class="payment-details">
            <h2>detalhes da compra</h2>
            
            <p style="font-size: 0.9em; color: #aaa; margin-bottom: 15px;">método de pagamento: <span style="font-weight: bold; color: #ffcccc;"><?php echo htmlspecialchars($metodo_pagamento); ?></span></p>
            
            <form id="checkout-form">
                
                <div class="form-row">
                    <div>
                        <label for="cupom">cupom</label>
                        <input type="text" id="cupom" placeholder="CUPOM">
                    </div>
                    
                    <div class="cep-group">
                        <label for="cep">CEP</label>
                        <div class="cep-input-group">
                            <input type="text" id="cep" name="cep" placeholder="00000000" maxlength="8" required>
                            <button type="button" id="button" class="cep-search-btn"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>

                <h3 style="margin-top: 30px; margin-bottom: 10px; font-size: 1.1em; color: white;">📍 Endereço de Entrega</h3>
                
                <div class="address-result-container">
                    <p>Rua/Logradouro: <span id="logradouro" class="address-data">Aguardando CEP...</span></p>
                    <p>Bairro: <span id="bairro" class="address-data">-</span></p>
                    <p>Cidade/UF: <span id="localidade" class="address-data">-</span>/<span id="uf" class="address-data">-</span></p>
                    
                    <label for="numero" style="margin-top: 10px;">Número</label>
                    <input type="text" id="numero" placeholder="Número da casa/apartamento" required>
                </div>
                <div class="total-summary">
                    <div class="total-row">
                        <span>Subtotal do pedido</span>
                        <span id="subtotal-value">R$ <?php echo number_format($subtotal_simulado, 2, ',', '.'); ?></span>
                    </div>
                    <div class="total-row">
                    
                    </div>
                    <div class="total-row">
                        <span>taxa de entrega</span>
                        <span id="delivery-tax">R$ <?php echo number_format($taxa_entrega, 2, ',', '.'); ?></span> </div>
                    <div class="total-row final">
                        <span>Total Final</span>
                        <span id="final-total-value">R$ <?php echo number_format($total_final, 2, ',', '.'); ?></span>
                    </div>
                </div>
                
                <button type="submit" class="checkout-btn">FINALIZAR COMPRA <i class="fas fa-arrow-right"></i></button>
            </form>
        </aside>
</main>

<div id="paymentModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" id="closePaymentModal">&times;</span>
        <div class="modal-header">
            <h2 style="color: #333; margin-bottom: 20px;">Escolha a Forma de Pagamento</h2>
        </div>
        
        <form id="payment-selection-form">
            
            <div class="payment-option-group">
                <input type="radio" id="paymentPix" name="paymentMethod" value="pix" checked>
                <label for="paymentPix">
                    <i class="fas fa-qrcode"></i>
                    <span>PIX</span>
                    <small>Pagamento instantâneo, 5% de desconto.</small>
                </label>
            </div>
            
            <div class="payment-option-group">
                <input type="radio" id="paymentBoleto" name="paymentMethod" value="boleto">
                <label for="paymentBoleto">
                    <i class="fas fa-barcode"></i>
                    <span>Boleto Bancário</span>
                    <small>Compensação em até 3 dias úteis.</small>
                </label>
            </div>
            
            <div class="payment-option-group">
                <input type="radio" id="paymentCard" name="paymentMethod" value="card">
                <label for="paymentCard">
                    <i class="fas fa-credit-card"></i>
                    <span>Cartão de Crédito</span>
                    <small>Pague em até 12x.</small>
                </label>
            </div>

            <div id="contentPix" class="payment-dynamic-content" style="display: none;">
                <h4 style="color: #333; margin-top: 15px;">Escaneie para Pagar</h4>
                <div style="text-align: center; padding: 20px; border: 1px dashed #df2356; border-radius: 8px;">
                    
                    <p style="font-size: 0.85em; color: #555; margin-top: 10px;">Válido por 30 minutos.</p>
                </div>
            </div>

            <div id="contentBoleto" class="payment-dynamic-content" style="display: none;">
                <h4 style="color: #333; margin-top: 15px;">Detalhes do Boleto</h4>
                <div style="background-color: #f5f5f5; padding: 15px; border-radius: 8px;">
                    <label for="codigoBoleto" style="color: #555; margin-top: 0;">Código de Barras (Copia e Cola)</label>
                    <input type="text" id="codigoBoleto" value="34191.09003 00000.345674 58000.000000 7 87650000001000" readonly 
                            style="background-color: white; color: #1a1a1a; font-size: 0.9em; cursor: copy; padding: 8px;"/>
                    <p style="font-size: 0.85em; color: #777; margin-top: 10px;">O boleto será enviado para seu e-mail.</p>
                </div>
            </div>

            <div id="contentCard" class="payment-dynamic-content" style="display: none;">
                <h4 style="color: #333; margin-top: 15px;">Dados do Cartão</h4>
                <label for="cardNumber">Número do Cartão</label>
                <input type="text" id="cardNumber" placeholder="0000 0000 0000 0000">
                
                <label for="cardName">Nome Impresso no Cartão</label>
                <input type="text" id="cardName" placeholder="Seu Nome Completo">
                
                <div class="form-row" style="margin-top: 10px; gap: 20px;">
                    <div style="flex: 2;">
                        <label for="cardExpiry">Validade (MM/AA)</label>
                        <input type="text" id="cardExpiry" placeholder="MM/AA">
                    </div>
                    <div style="flex: 1;">
                        <label for="cardCVC">CVC</label>
                        <input type="text" id="cardCVC" placeholder="***">
                    </div>
                </div>
            </div>

            <button type="submit" id="confirmPaymentBtn" class="confirm-payment-btn">CONFIRMAR E GERAR PAGAMENTO</button>
        </form>
    </div>
</div>
<script src="home.js"></script>
<script src="pagamento.js"></script>
</body>
</html>