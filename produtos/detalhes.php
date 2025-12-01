<?php 
// ==========================================================
// produtos/detalhes.php: Exibe um produto específico do BD
// ==========================================================

// --- 1. Inclui o arquivo de configuração ---
require_once '../config.php'; 

// Garante que a sessão é iniciada APENAS se ainda não estiver ativa.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 2. Variáveis de Sessão ---
$logado = isset($_SESSION['usuario_id']);
$nome_usuario = $_SESSION['usuario_nome'] ?? '';
$nivel_usuario = $_SESSION['usuario_nivel'] ?? 'cliente'; 

// --- 3. Conexão e Busca do Produto ---
$id_produto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Redireciona se não houver ID válido
if (!$id_produto) {
    header('Location: ../index.php'); 
    exit;
}

try {
    $pdo = getPdoConnection(); // Assume que esta função está no seu config.php
    
    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch();

    if (!$produto) {
        die("Produto não encontrado.");
    }
} catch (\PDOException $e) {
    die("Erro ao buscar produto: " . $e->getMessage());
}

// ------------------------------------
// 4. Formatação e Extração de Dados
// ------------------------------------
// Formata o preço para o data-price (sem separador de milhar, com ponto decimal)
$data_price = number_format($produto['preco'] ?? 0, 2, '.', '');
// Formata o preço para exibição (com ponto de milhar opcional e vírgula decimal)
$display_price = formatarPreco($produto['preco'] ?? 0); 
$titulo_pagina = htmlspecialchars($produto['n_produto'] ?? 'Produto Desconhecido');
$imagem_url_bd = htmlspecialchars($produto['imagem_url'] ?? 'placeholder.png'); 

$categoria = htmlspecialchars($produto['categoria'] ?? 'Não classificada');
$quantidade_estoque = htmlspecialchars($produto['quantidade'] ?? '0');

$info_material = 'Não especificado';
$info_dimensoes = 'Não especificadas';
$descricao_principal = '';

if (isset($produto['descricao'])) {
    $linhas_descricao = explode("\n", $produto['descricao']);
    $descricao_formatada = [];

    foreach ($linhas_descricao as $linha) {
        $linha = trim($linha);
        // Busca informações específicas (Material/Dimensões) e as separa da descrição principal
        if (stripos($linha, 'Material:') !== false) {
            $info_material = trim(str_replace('Material:', '', $linha));
        } elseif (stripos($linha, 'Dimensões:') !== false) {
            $info_dimensoes = trim(str_replace('Dimensões:', '', $linha));
        } else {
            $descricao_formatada[] = $linha;
        }
    }
    // Remove linhas vazias e junta o restante para a descrição principal
    $descricao_principal = htmlspecialchars(implode(' ', array_filter($descricao_formatada)));
}

// ------------------------------------
// 5. Busca de Produtos Semelhantes
// ------------------------------------
$produtos_semelhantes = [];
// Garante que a categoria não está vazia e faz a busca
if ($categoria !== 'Não classificada') {
    try {
        $pdo = getPdoConnection();
        $stmt_semelhantes = $pdo->prepare("
            SELECT 
                id_produto, n_produto, preco, imagem_url 
            FROM 
                produto 
            WHERE 
                categoria = ? AND id_produto != ? 
            LIMIT 4 
        ");
        $stmt_semelhantes->execute([$produto['categoria'], $id_produto]);
        $produtos_semelhantes = $stmt_semelhantes->fetchAll();
    } catch (\PDOException $e) {
        error_log("Erro ao buscar produtos semelhantes: " . $e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - <?php echo $titulo_pagina; ?></title>
    
    <link rel="stylesheet" href="../css/basic.css" /> 
    <link rel="stylesheet" href="../css/background.css" />
    <link rel="stylesheet" href="../css/modal.css" /> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="../imagems/logos/logo.png" type="image/png"> 

    <style>
        /* VARIÁVEIS DE COR */
        :root {
            --cor-principal: #df2356; 
            --cor-fundo-info: #222222; 
            --cor-texto-claro: #f0f0f0;
            --cor-borda-acao: #555;
            --cor-texto-secundario: #aaa;
        }
        
        /* LAYOUT PRINCIPAL */
        .pagina-produto { 
            display: flex; 
            gap: 40px; 
            padding: 40px; 
            max-width: 1200px; 
            margin: 0 auto; 
            align-items: stretch; 
        }
        
        /* GALERIA DE IMAGEM (MAIS RETANGULAR) */
        .galeria-produto { 
            flex: 1.5; /* Aumenta um pouco a proporção da imagem */
            display: flex; 
            flex-direction: column; 
            gap: 15px; 
        }

        .imagem-principal { 
            background-color: white; 
            border-radius: 10px; 
            padding: 20px; 
            position: relative; 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); 
            overflow: hidden; 
            aspect-ratio: 4 / 3; /* Proporção de tela mais retangular */
            height: auto; 
            min-height: 250px; 
            cursor: grab; 
        }
        .imagem-principal.dragging {
            cursor: grabbing;
        }

        .imagem-principal img { 
            width: 100%; 
            height: 100%; 
            max-height: none; 
            display: block; 
            object-fit: contain; 
            border-radius: 8px; 
            transition: transform 0.3s ease, left 0s, top 0s; 
            position: relative;
        }

        /* CORREÇÃO DO POSICIONAMENTO DOS BOTÕES DE ZOOM/FULLSCREEN */
        .zoom-button {
            position: absolute;
            top: 20px;
            background-color: rgba(0, 0, 0, 0.6);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1em;
            transition: background-color 0.2s;
            z-index: 10;
        }
        .zoom-button:hover { background-color: rgba(0, 0, 0, 0.8); }

        #zoom-btn { right: 70px; } 
        #fullscreen-btn { right: 20px; } 

        /* REMOÇÃO DA SEÇÃO DE MINIATURAS */
        .miniaturas {
            display: none; 
        }

        /* CAIXA DE INFORMAÇÕES DO PRODUTO */
        .info-produto { 
            flex: 1; /* Proporção reduzida para dar mais destaque à imagem */
            padding: 30px; 
            background-color: var(--cor-fundo-info); 
            color: var(--cor-texto-claro); 
            border-radius: 10px; 
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
        }

        .info-produto header {
            margin-bottom: 25px;
        }
        .titulo-produto { 
            font-size: 2.2em; 
            margin: 0; 
            color: var(--cor-principal); 
        }
        .descricao-produto-label {
            color: var(--cor-texto-secundario);
            font-size: 1.1em;
            margin-bottom: 10px;
            display: block;
        }
        .descricao-produto-texto { 
            color: #ccc; 
            line-height: 1.6; 
            font-size: 1.05em; 
            margin-bottom: 0;
        }

        /* SEÇÃO DE DETALHES TÉCNICOS/TAMANHO (corpo central) */
        .detalhes-tecnicos {
            padding: 20px 0;
            border-top: 1px solid #333;
            border-bottom: 1px solid #333;
            margin-bottom: 25px;
        }
        .info-material, .info-dimensoes { 
            margin-bottom: 15px; 
            font-size: 1.1em; 
            color: #ddd; 
        }
        .detalhes-tecnicos strong {
            color: var(--cor-texto-claro);
        }
        /* Ajuste de margem se as opções de tamanho estiverem visíveis */
        .size-selector {
            margin-top: 15px;
        }

        /* SELEÇÃO DE TAMANHO/OPÇÃO */
        .size-selector strong {
            display: block;
            margin-bottom: 10px;
            font-size: 1.1em;
            color: var(--cor-texto-claro);
        }
        .tamanho-opcoes {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .tamanho-opcoes input[type="radio"] { display: none; }
        .tamanho-opcoes label {
            padding: 8px 15px;
            border: 2px solid #555;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: bold;
            color: #ccc;
        }
        .tamanho-opcoes input[type="radio"]:checked + label {
            background-color: var(--cor-principal);
            border-color: var(--cor-principal);
            color: white;
        }

        /* BOTÕES DE AÇÃO (fundo) */
        .acoes-fundo {
            margin-top: auto; 
            padding-top: 25px;
        }

        /* PREÇO */
        .preco-produto { 
            font-size: 3em; 
            font-weight: bold; 
            color: var(--cor-principal); 
            margin: 10px 0 30px 0; 
            text-align: right; 
            padding-right: 10px;
        }

        .btn-comprar { 
            background-color: var(--cor-principal); 
            color: white; 
            border: none; 
            width: 100%; 
            padding: 18px 0; 
            font-size: 1.2em; 
            font-weight: bold; 
            border-radius: 8px; 
            cursor: pointer; 
            transition: background-color 0.3s, transform 0.1s; 
            margin-top: 0; 
        }
        .btn-comprar:hover { 
            background-color: #c71e4d; 
            transform: translateY(-1px); 
        }
        
        /* AÇÕES ADICIONAIS - FAVORITAR/COMPARTILHAR/CARRINHO RÁPIDO */
        .acoes-produto { 
            display: flex; 
            justify-content: flex-end; 
            gap: 15px; 
            margin-top: 15px; 
        }
        .acoes-produto button {
            flex: 0 0 auto;
            width: 50px;
            height: 50px;
            padding: 0;
            border-radius: 50%;
            background: none;
            border: 2px solid var(--cor-borda-acao);
            color: var(--cor-texto-claro);
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .acoes-produto button:hover {
            background-color: #333;
            border-color: var(--cor-principal);
        }
        .favoritar.active { 
            background-color: var(--cor-principal); 
            border-color: var(--cor-principal); 
        }
        
        /* SEÇÃO DE RECOMENDAÇÕES (Produtos Semelhantes Lado a Lado) */
        .produtos-recomendados {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 40px;
        }
        .produtos-recomendados h3 {
            font-size: 1.8em;
            color: #333;
            margin-bottom: 20px;
            border-left: 5px solid var(--cor-principal);
            padding-left: 15px;
            font-weight: 700;
        }
        /* ESTILO PARA BARRA DE ROLAGEM HORIZONTAL */
        .recomendacoes-lista {
            display: flex; /* Alinha os cards lado a lado */
            overflow-x: auto; /* Adiciona scroll horizontal */
            gap: 20px;
            padding-bottom: 10px; 
            scroll-snap-type: x mandatory; 
        }
        
        .produto-card {
            flex: 0 0 220px; /* Define largura fixa e impede que encolha */
            scroll-snap-align: start; 
            background-color: white;
            border-radius: 8px;
            padding: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: #333;
        }
        
        /* MELHORIA: Esconde a barra de rolagem em navegadores Webkit (Chrome, Safari) */
        .recomendacoes-lista::-webkit-scrollbar {
            height: 6px;
        }

        .recomendacoes-lista::-webkit-scrollbar-thumb {
            background-color: #ccc;
            border-radius: 3px;
        }

        .produto-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        .produto-card img {
            width: 100%;
            height: 150px;
            object-fit: contain;
            border-radius: 6px;
            margin-bottom: 10px;
        }
        .produto-card p {
            margin: 5px 0;
            font-size: 0.95em;
            height: 40px; 
            overflow: hidden;
        }
        .preco-card {
            font-weight: bold;
            color: var(--cor-principal);
            font-size: 1.1em;
        }

        @media (max-width: 768px) {
            .pagina-produto { 
                flex-direction: column; 
                padding: 20px;
            }
            .produtos-recomendados {
                padding: 0 20px;
            }
            .titulo-produto { font-size: 1.8em; }
            .preco-produto { font-size: 2.5em; text-align: left; padding-right: 0; }
            .acoes-produto { justify-content: flex-start; }
            .acoes-produto button { width: 40px; height: 40px; }
        }
    </style>

</head>
<body>
    <div class="bg-pattern"></div>

    <?php if ($logado && $nivel_usuario === 'admin'): ?>
        <aside class="sidebar">
            <nav class="menu-principal">
                <a href="../index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
                <a href="../favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
                <a href="../perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
                <a href="../contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
            </nav>

            <nav class="menu-config">
                <a href="../admin.php" class="active-config"><i class="fas fa-user-shield"></i> <span class="txt">Painel Admin</span></a> 
                
                <a href="../configuracoes.php"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
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

    <?php else: ?>
        <aside class="sidebar">
            <nav class="menu-principal">
                <a href="../index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
                <a href="../favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
                <a href="../perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
                <a href="../contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
                <a href="../minhas_compras.php"><i class="fas fa-envelope"></i> <span class="txt">Minhas Compras</span></a>
            </nav>

            <nav class="menu-config">
                <a href="../configuracoes.php"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
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

    <?php endif; ?>

    <main class="content">
        <header class="top-nav">
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Pesquisar produtos...">
                <button class="search-btn" aria-label="Pesquisar"><i class="fas fa-search"></i></button>
                <div id="search-results" class="search-results"></div> 
            </div>
            <div class="user-actions">
                <?php if ($logado): ?>
                    <a href="../perfil.php" class="login-btn">
                        <i class="fas fa-user"></i> Olá, <?php echo htmlspecialchars($nome_usuario); ?>
                    </a>
                    <a href="../logout.php" class="login-btn" style="margin-left: 10px;">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                <?php else: ?>
                    <a href="#" id="openLogin" class="login-btn"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                <?php endif; ?>
            </div>
        </header>

        <section class="pagina-produto">
            <div class="produto-detalhes">
                <div class="galeria-produto">
                    <div class="imagem-principal">
                        <img id="imagem-produto" 
                            src="../<?php echo $imagem_url_bd; ?>" 
                            alt="<?php echo $titulo_pagina; ?>">
                        
                        <button id="zoom-btn" class="zoom-button" aria-label="Alternar Zoom da Imagem"><i class="fas fa-search-plus"></i></button>
                        <button id="fullscreen-btn" class="zoom-button fullscreen-button" aria-label="Ver em Tela Cheia"><i class="fas fa-expand"></i></button>
                    </div>

                    <div class="miniaturas">
                    </div>
                </div>

                <div class="info-produto" 
                    data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>"
                    data-name="<?php echo htmlspecialchars($titulo_pagina); ?>"
                    data-price="<?php echo $data_price; ?>"
                    data-img="../<?php echo $imagem_url_bd; ?>">
                    
                    <header>
                        <h2 class="titulo-produto"><?php echo $titulo_pagina; ?></h2>
                        <span class="descricao-produto-label">descrição do produto</span> 
                        <p class="descricao-produto-texto"><?php echo $descricao_principal; ?></p>
                    </header>
                    
                    <div class="detalhes-tecnicos">
                        <p class="info-material"><strong>Material:</strong> <?php echo htmlspecialchars($info_material); ?></p>
                        <p class="info-dimensoes"><strong>Dimensões:</strong> <?php echo htmlspecialchars($info_dimensoes); ?></p>
                        
                        <?php 
                        // Lógica: Verifica se a categoria é Camisetas ou Moletons
                        $categorias_com_tamanho = ['Camisetas', 'Moletons'];
                        if (in_array($categoria, $categorias_com_tamanho)): 
                        ?>
                            <div class="size-selector">
                                <strong>Selecione o Tamanho:</strong>
                                <div class="tamanho-opcoes">
                                    <input type="radio" id="size-pp" name="tamanho" value="PP" checked>
                                    <label for="size-pp">PP</label>
                                    <input type="radio" id="size-p" name="tamanho" value="P">
                                    <label for="size-p">P</label>
                                    <input type="radio" id="size-m" name="tamanho" value="M">
                                    <label for="size-m">M</label>
                                    <input type="radio" id="size-g" name="tamanho" value="G">
                                    <label for="size-g">G</label>
                                    <input type="radio" id="size-gg" name="tamanho" value="GG">
                                    <label for="size-gg">GG</label>
                                    <input type="radio" id="size-xgg" name="tamanho" value="XGG">
                                    <label for="size-xgg">XGG</label>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="size-selector">
                                <strong>Opção de Produto:</strong>
                                <div class="tamanho-opcoes">
                                    <input type="radio" id="size-na" name="tamanho" value="N/A" checked>
                                    <label for="size-na">Único / Padrão</label>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="acoes-fundo">
                        <p class="preco-produto">R$ <?php echo $display_price; ?></p> 

                        <form onsubmit="return false;">
                            <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">
                            
                            <button type="button" class="btn-comprar add-to-cart-btn" 
                                        onclick="window.addToCart(this)"
                                        data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>"
                                        data-name="<?php echo htmlspecialchars($titulo_pagina); ?>"
                                        data-price="<?php echo $data_price; ?>"
                                        data-img="../<?php echo $imagem_url_bd; ?>"
                                        aria-label="Comprar Agora">
                                Comprar Agora 
                            </button>
                        </form>

                        <div class="acoes-produto">
                            <button class="favoritar" 
                                        onclick="window.toggleFavorite(this)" 
                                        data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>"
                                        aria-label="Adicionar aos Favoritos">
                                <i class="fas fa-heart"></i>
                            </button>
                            <button class="share-btn" onclick="shareCurrentProduct()" aria-label="Compartilhar Produto">
                                <i class="fas fa-share-alt"></i>
                            </button>
                            <button class="add-to-cart-quick" 
                                        onclick="window.addToCart(this)"
                                        data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>"
                                        data-name="<?php echo htmlspecialchars($titulo_pagina); ?>"
                                        data-price="<?php echo $data_price; ?>"
                                        data-img="../<?php echo $imagem_url_bd; ?>"
                                        aria-label="Adicionar ao carrinho Rápido">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div> 
                </div> 
            </div> 
        </section> 

        <section class="produtos-recomendados">
            <h3><i class="fas fa-bolt" style="color: var(--cor-principal);"></i> Produtos Semelhantes</h3>
            <div class="recomendacoes-lista">
                <?php if (!empty($produtos_semelhantes)): ?>
                    <?php foreach ($produtos_semelhantes as $similar_produto): ?>
                        <a href="detalhes.php?id=<?php echo $similar_produto['id_produto']; ?>" class="produto-card">
                            <img src="../<?php echo htmlspecialchars($similar_produto['imagem_url'] ?? 'placeholder.png'); ?>" 
                                        alt="<?php echo htmlspecialchars($similar_produto['n_produto']); ?>">
                            <p><?php echo htmlspecialchars($similar_produto['n_produto']); ?></p>
                            <p class="preco-card">R$ <?php echo formatarPreco($similar_produto['preco'] ?? 0); ?></p>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; color: #777;">Não foram encontrados outros produtos nesta categoria.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div id="cart-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="cart-title"><i class="fas fa-shopping-cart"></i> Seu Carrinho</h2>
                <span class="close-btn">&times;</span>
            </div>
            
            <div id="cart-items" class="cart-items-list">
                <p id="empty-cart-message" style="text-align: center; color: #555; margin-top: 20px;">Seu carrinho está vazio. Adicione alguns produtos!</p>
            </div>

            <div class="cart-footer">
                <div class="cart-total">
                    <span>Total:</span>
                    <span id="cart-total-value">R$ 0,00</span>
                </div>
                <button class="checkout-btn">Finalizar Compra</button>
            </div>
        </div>
    </div>
    
    <div id="login-modal" class="modal">
        <div class="modal-content login-content">
            <span class="close-login-btn">&times;</span>
            <div class="login-form-container">
                <img src="../imagems/logos/login.png" alt="Mascote Login" class="login-mascote">
                
                <h2 class="login-title">Login</h2>
    
                <form action="../processa_login.php" method="POST"> 
                    <label for="email">Email ou telefone</label>
                    <input type="text" id="email_login" name="email" placeholder="Email ou telefone" required> 
                    
                    <label for="password">Senha</label>
                    <div class="password-container">
                        <input type="password" id="password_login" name="senha" placeholder="Digite sua senha" required> 
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    
                    <div class="login-options">
                    </div>
                    <button type="submit" class="sign-in-btn">Entrar</button>
                </form>
                
                <button class="google-sign-in-btn">
                    <i class="fab fa-google"></i> Ou entre com Google
                </button>
    
                <p style="text-align: center; margin-top: 20px; font-size: 0.9em;">
                    Não tem conta?
                    <a href="../cadastro.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">Cadastre-se aqui</a>
                </p>
            </div>
        </div>
    </div>
    
    <div id="detalhes-modal" class="modal" style="display: none;">
        <div class="modal-content detalhes-content">
            <span class="close-btn">&times;</span>
            <div class="modal-body-detalhes">
                <div class="detalhes-galeria">
                    <img id="detalhes-img" src="" alt="" class="detalhes-img-principal">
                </div>
                <div class="detalhes-info">
                    <h2 id="detalhes-nome"></h2>
                    <p id="detalhes-preco" class="detalhes-preco"></p>
                    <p id="detalhes-descricao" class="detalhes-descricao"></p>
                    
                    <div id="detalhes-size-container" style="margin-bottom: 20px;">
                        <label for="detalhes-size-select">Tamanho:</label>
                        <select id="detalhes-size-select"></select>
                    </div>

                    <button id="detalhes-add-to-cart" class="btn-comprar" data-product-id="0" data-name="" data-price="0" data-img="">
                        <i class="fas fa-cart-plus"></i> Adicionar ao Carrinho
                    </button>
                    <button id="detalhes-favoritar" class="favoritar" data-product-id="0"><i class="fas fa-heart"></i></button>

                    <div class="detalhes-extras">
                        <p>ID: <span id="detalhes-id"></span></p>
                        <p>Categoria: <span id="detalhes-categoria"></span></p>
                        <p>Estoque: <span id="detalhes-quantidade"></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        
// ==========================================================
// FUNÇÕES DE CARRINHO E FAVORITOS (IMPLEMENTAÇÃO BÁSICA)
// ==========================================================

function updateCartCount() {
    // Simula a contagem de itens no carrinho
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    const cartCountElement = document.querySelector('.cart-count');
    if (cartCountElement) {
        cartCountElement.textContent = '. ' + totalCount;
    }
}

window.addToCart = function(element) {
    const productId = element.dataset.productId;
    const productName = element.dataset.name;
    const productPrice = parseFloat(element.dataset.price);
    const productImg = element.dataset.img;
    
    // Obter o tamanho selecionado
    const sizeSelectorContainer = document.querySelector('.size-selector');
    let selectedSize = 'N/A';

    if (sizeSelectorContainer) {
        const selectedSizeElement = sizeSelectorContainer.querySelector('input[name="tamanho"]:checked');
        if (selectedSizeElement) {
            selectedSize = selectedSizeElement.value;
        }
    }


    let cart = JSON.parse(localStorage.getItem('cart') || '[]');

    const existingItem = cart.find(item => item.id === productId && item.size === selectedSize);

    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: productId,
            name: productName,
            price: productPrice,
            img: productImg,
            size: selectedSize,
            quantity: 1
        });
    }

    localStorage.setItem('cart', JSON.stringify(cart));
    updateCartCount();
    alert(`"${productName} (${selectedSize})" adicionado ao carrinho!`);
}


window.toggleFavorite = function(element) {
    const productId = element.dataset.productId;
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    
    const index = favorites.indexOf(productId);

    if (index > -1) {
        // Remover dos favoritos
        favorites.splice(index, 1);
        element.classList.remove('active');
        alert('Produto removido dos favoritos!');
    } else {
        // Adicionar aos favoritos
        favorites.push(productId);
        element.classList.add('active');
        alert('Produto adicionado aos favoritos!');
    }

    localStorage.setItem('favorites', JSON.stringify(favorites));
}

window.updateFavoriteVisuals = function() {
    const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    const favoriteButtons = document.querySelectorAll('.favoritar');

    favoriteButtons.forEach(button => {
        const productId = button.dataset.productId;
        if (favorites.includes(productId)) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }
    });
}
// ==========================================================
// FIM: FUNÇÕES DE CARRINHO E FAVORITOS
// ==========================================================

// Função para compartilhar o produto atual (mantida)
function shareCurrentProduct() {
    const productName = document.querySelector('.titulo-produto').textContent;
    const currentUrl = window.location.href;

    if (navigator.share) {
        navigator.share({
            title: productName,
            text: 'Confira este produto incrível na Office Shop!',
            url: currentUrl
        }).then(() => {
            showShareFeedback();
        }).catch((error) => {
            fallbackShare(currentUrl);
        });
    } else {
        fallbackShare(currentUrl);
    }
}

function fallbackShare(url) {
    navigator.clipboard.writeText(url).then(() => {
        showShareFeedback(true); 
    }).catch(() => {
        prompt('Compartilhe este link:', url);
    });
}

function showShareFeedback(copied = false) {
    const shareButton = document.querySelector('.acoes-produto .share-btn');
    const originalHTML = shareButton.innerHTML;
    
    shareButton.style.backgroundColor = '#4CAF50';
    shareButton.style.borderColor = '#4CAF50';
    shareButton.innerHTML = '<i class="fas fa-check"></i>';
    
    setTimeout(() => {
        shareButton.style.backgroundColor = 'transparent';
        shareButton.style.borderColor = 'var(--cor-borda-acao)';
        shareButton.innerHTML = originalHTML;
    }, 2000);
}

// LÓGICA DE ZOOM E DRAG (Arrastar)
document.addEventListener('DOMContentLoaded', function() {
    const imagemPrincipalContainer = document.querySelector('.imagem-principal');
    const imagemPrincipal = document.getElementById('imagem-produto');
    const zoomBtn = document.getElementById('zoom-btn');
    const fullscreenBtn = document.getElementById('fullscreen-btn');
    
    let isZoomed = false;
    let isDragging = false;
    let startX, startY, scrollLeft, scrollTop;
    
    // 1. Funcionalidade de Zoom
    if (zoomBtn && imagemPrincipal) {
        zoomBtn.addEventListener('click', function() {
            if (isZoomed) {
                imagemPrincipal.style.transform = 'scale(1)';
                imagemPrincipal.style.left = '0';
                imagemPrincipal.style.top = '0';
                zoomBtn.innerHTML = '<i class="fas fa-search-plus"></i>';
                imagemPrincipalContainer.style.overflow = 'hidden';
            } else {
                imagemPrincipal.style.transform = 'scale(2)'; 
                zoomBtn.innerHTML = '<i class="fas fa-search-minus"></i>';
                imagemPrincipalContainer.style.overflow = 'scroll'; 
            }
            isZoomed = !isZoomed;
        });
    }

    // 2. Funcionalidade de Arrastar (Drag)
    imagemPrincipalContainer.addEventListener('mousedown', (e) => {
        if (!isZoomed) return;
        isDragging = true;
        imagemPrincipalContainer.classList.add('dragging');
        startX = e.pageX - imagemPrincipalContainer.offsetLeft;
        startY = e.pageY - imagemPrincipalContainer.offsetTop;
        scrollLeft = imagemPrincipalContainer.scrollLeft;
        scrollTop = imagemPrincipalContainer.scrollTop;
    });

    imagemPrincipalContainer.addEventListener('mouseleave', () => {
        isDragging = false;
        imagemPrincipalContainer.classList.remove('dragging');
    });

    imagemPrincipalContainer.addEventListener('mouseup', () => {
        isDragging = false;
        imagemPrincipalContainer.classList.remove('dragging');
    });

    imagemPrincipalContainer.addEventListener('mousemove', (e) => {
        if (!isDragging || !isZoomed) return;
        e.preventDefault();

        const x = e.pageX - imagemPrincipalContainer.offsetLeft;
        const y = e.pageY - imagemPrincipalContainer.offsetTop;
        const walkX = (x - startX) * 1.5; 
        const walkY = (y - startY) * 1.5;

        imagemPrincipalContainer.scrollLeft = scrollLeft - walkX;
        imagemPrincipalContainer.scrollTop = scrollTop - walkY;
    });

    // 3. Funcionalidade de Tela Cheia (Mantida)
    if (fullscreenBtn && imagemPrincipal) {
        fullscreenBtn.addEventListener('click', function() {
            if (document.fullscreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
                return; 
            }

            const container = imagemPrincipal.closest('.imagem-principal');
            
            if (container.requestFullscreen) {
                container.requestFullscreen();
            } else if (container.webkitRequestFullscreen) {
                container.webkitRequestFullscreen();
            } else if (container.msRequestFullscreen) {
                container.msRequestFullscreen();
            }
        });
    }
});

// Inicialização e atualização no carregamento da página
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount(); 
    updateFavoriteVisuals(); 
});
    </script>
    
    <script src="../home.js"></script>
</body>
</html>