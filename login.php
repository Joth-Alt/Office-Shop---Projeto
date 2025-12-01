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
</head>

<body> 
<div class="bg-pattern"></div>

<?php 
// ==========================================================
// LÓGICA CONDICIONAL DA SIDEBAR (Permanece a mesma)
// ==========================================================

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
            
            <div id="search-results" class="search-dropdown"></div>

            <button id="microphone-btn" class="mic-btn"><i class="fas fa-microphone"></i></button>
            <button class="search-btn"><i class="fas fa-search"></i></button>
        </div>
        <div class="user-actions">
            <?php if ($logado): ?>
                <a href="perfil.php">
                    <i class="fas fa-user"></i> Olá, <?php echo htmlspecialchars($nome_usuario); ?>
                </a>
                <a href="logout.php" class="login-btn" style="margin-left: 10px;">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            <?php else: ?>
                <a href="#" id="openLogin" class="login-btn"><i class="fas fa-sign-in-alt"></i> Entrar</a>
            <?php endif; ?>
        </div>
    </header>

    <?php if ($logado): ?>
        <section class="banner">
            <img src="imagens-produtos/banner.png" alt="Banner Principal">
        </section> 

        <section class="produtos">
            <?php foreach ($produtos_bd as $produto): 
                $data_price = number_format($produto['preco'], 2, '.', '');
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
                    <a href="#" class="comprar open-detalhes-modal" 
                        data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>">
                        Ver Detalhes
                    </a>
                    <button class="add-to-cart-btn" onclick="addToCart(this)">
                        <i class="fas fa-cart-plus"></i> 
                    </button>
                    <button class="favoritar" onclick="toggleFavorite(this)"><i class="fas fa-heart"></i></button>
                </div>
            </div>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <section class="login-wall-main">
            <div class="login-wall-content-wrapper">
                
                <div class="login-wall-card">
                    <i class="fas fa-lock lock-icon"></i>
                    <h2>Acesso Restrito</h2>
                    <p>Você precisa fazer login para visualizar o conteúdo da loja. Faça seu acesso agora:</p>
                    
                    <div class="login-form-container">
                        <form action="processa_login.php" method="POST" class="login-wall-form">
                            <label for="email_wall">Email ou telefone</label>
                            <input type="text" id="email_wall" name="email" placeholder="Email ou telefone" required>
                            
                            <label for="password_wall">Senha</label>
                            <div class="password-container">
                                <input type="password" id="password_wall" name="senha" placeholder="Digite sua senha" required>
                                <i class="fas fa-eye password-toggle"></i>
                            </div>

                            <button type="submit" class="sign-in-btn full-width-btn">Entrar</button>
                        </form>

                        <button class="google-sign-in-btn" style="width: 100%; margin-top: 10px;">
                            <i class="fab fa-google"></i> Ou entre com Google
                        </button>

                        <p style="text-align: center; margin-top: 20px; font-size: 0.9em;">
                            Não tem conta?
                            <a href="cadastro.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">Cadastre-se aqui</a>
                        </p>
                    </div>
                </div>

                <!-- IMAGEM DECORATIVA À DIREITA -->
                <div class="login-wall-image-container">
                    <img src="imagens-produtos\Poster.jpg" alt="Arte ilustrativa de login" class="login-wall-image">
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<div id="cart-modal" class="modal">...</div>
<div id="login-modal" class="modal">...</div>
<div id="detalhes-modal" class="modal">...</div>

<script src="home.js"></script>
</body>
</html>
