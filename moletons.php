<?php
// ==========================================================
// index.php: Inicia a sessão, conecta ao BD e exibe produtos
// ==========================================================
require_once 'config.php'; 

// --- 1. Lógica de Login e Sessão ---
// Variáveis $logado, $nome_usuario, $nivel_usuario já carregadas do config.php

// --- 2. Conexão com o Banco de Dados ---
$produtos_bd = []; // Array que armazenará os produtos do BD

try {
     $pdo = getPdoConnection();
     
     // --- 3. Busca de Produtos no Banco de Dados ---
     // Busca todos os produtos ativos (ou com quantidade > 0)
     $stmt_produtos = $pdo->query("SELECT * FROM produto WHERE quantidade > 0 ORDER BY id_produto DESC");
     $produtos_bd = $stmt_produtos->fetchAll();

} catch (\PDOException $e) {
    // Em um ambiente de produção, logar o erro.
    // die("Erro ao carregar produtos do banco de dados: " . $e->getMessage());
    // Por enquanto, apenas garante que $produtos_bd está vazio se houver erro.
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Loja Online</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
    <style>
        /* Estilos para o novo banner de categoria */
        .banner-categoria {
            max-width: 95%;
            margin: 20px auto 0 auto; /* Ajusta margem para ficar alinhado */
            display: block; /* Garante que o container ocupe a largura total */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .banner-categoria a {
            display: block;
        }
        .banner-categoria img {
            width: 100%;
            height: 150px; /* Altura fixa para o banner */
            object-fit: cover; /* Garante que a imagem cubra a área sem distorcer muito */
            display: block;
            transition: transform 0.3s;
        }
        .banner-categoria a:hover img {
            transform: scale(1.02);
        }

        /* Ajuste na seção de categorias para centralizar melhor os links restantes */
        .categorias {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* Centraliza os links */
            gap: 10px;
            padding: 15px 0;
            max-width: 95%;
            margin: 10px auto;
        }
        .categorias a {
            padding: 8px 15px;
            border-radius: 20px;
            background-color: #f0f0f0;
            color: #333;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.2s, color 0.2s;
        }
        .categorias a:hover {
            background-color: var(--cor-principal);
            color: white;
        }

        /* Correção para o loop PHP que estava incorreto */
        /* A listagem principal de produtos deve vir DEPOIS do banner de categoria */
        .produtos {
            /* Adicione aqui os estilos de grid/flex que você usa para listar produtos */
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            max-width: 95%;
            margin: 20px auto;
        }
    </style>
</head>

<body> 
<div class="bg-pattern"></div>

<?php 
if ($logado && $nivel_usuario === 'admin'): 
?>
    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
        </nav>

        <nav class="menu-config">
            <a href="admin.php" class="active-config"><i class="fas fa-user-shield"></i> <span class="txt">Painel Admin</span></a> 
            
            <a href="configuracoes.php"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
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

<?php 
else: 
?>
    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
            <a href="minhas_compras.php"><i class="fas fa-box"></i> <span class="txt">Minhas Compras</span></a>
           </nav>

        <nav class="menu-config">
            <a href="configuracoes.php"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
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
                
                <div id="search-results" class="search-dropdown">
                </div>

                <button id="microphone-btn" class="mic-btn"><i class="fas fa-microphone"></i></button>
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <?php if ($logado): ?>
                    <a href="perfil.php" class="perfil-btn">
                        <i class="fas fa-user"></i> Olá, <?php echo htmlspecialchars($nome_usuario); ?>
                    </a>
                    <a href="logout.php" class="logout-btn" style="margin-left: 10px;">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                <?php else: ?>
                    <a href="#" id="openLogin" class="login-btn"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                <?php endif; ?>
            </div>
        </header>


        <section class="loja">
                        <section class="categorias">
                                <a href="categoria.php?nome=Camisetas">Camisetas</a>
                <a href="categoria.php?nome=Chaveiros">Chaveiros</a>
                <a href="categoria.php?nome=Bottons">Bottons</a>
                <a href="categoria.php?nome=Mousepads">Mouse Pads</a>
                <a href="categoria.php?nome=Posters">Posters</a>
                <a href="categoria.php?nome=Pelucías">Pelucías</a>
                <a href="categoria.php?nome=OST">OST</a>
            </section>
            
            <section class="banner-categoria">
                <a href="categoria.php?nome=Moletons">
                    <img src="imagems/banners/moletons_banner.jpg" alt="Banner de Destaque: Moletons">
                </a>
            </section>

                        <section class="produtos">

                <?php 
                // CORREÇÃO: O loop deve filtrar os produtos AQUI se necessário, ou listar todos.
                // Vou listar todos, já que a busca por categoria é feita na página 'categoria.php'
                foreach ($produtos_bd as $produto): 
                    // Formata o preço para o data-price (sem separador de milhar, com ponto decimal)
                    $data_price = number_format($produto['preco'], 2, '.', '');
                    // Formata o preço para exibição (com ponto de milhar opcional e vírgula decimal)
                    $display_price = formatarPreco($produto['preco']);
                ?>
                <div class="produto" 
                    data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>" 
                    data-name="<?php echo htmlspecialchars($produto['n_produto']); ?>" 
                    data-price="<?php echo $data_price; ?>" 
                    data-img="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                    data-category="<?php echo htmlspecialchars($produto['categoria']); ?>">
                    <div class="imagem-container">
                        <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="<?php echo htmlspecialchars($produto['n_produto']); ?>">
                    </div>
                    <div class="produto-info">
                        <span class="nome-produto"><?php echo htmlspecialchars($produto['n_produto']); ?></span>
                        <span class="preco-produto">R$ <?php echo $display_price; ?></span>
                    </div>
                    <div class="acoes">
                        <a href="produtos/detalhes.php?id=<?php echo htmlspecialchars($produto['id_produto']); ?>" class="comprar">Ver Detalhes</a>
                        <button class="add-to-cart-btn" onclick="addToCart(this)">
                            <i class="fas fa-cart-plus"></i> 
                        </button>
                        <button class="favoritar" onclick="toggleFavorite(this)"><i class="fas fa-heart"></i></button>
                    </div>
                </div>
                <?php endforeach; ?>
                 </section>
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
                <img src="imagems/logos/login.png" alt="Mascote Login" class="login-mascote">
                
                <h2 class="login-title">Login</h2>
    
                <form action="processa_login.php" method="POST"> 
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
                    <a href="cadastro.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">Cadastre-se aqui</a>
                </p>
            </div>
        </div>
    </div>


    
    <script src="home.js"></script>
</body>
</html>