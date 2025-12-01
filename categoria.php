<?php
// ==========================================================
// categoria.php: Exibe produtos de uma categoria específica
// ==========================================================
require_once 'config.php'; 

// --- 1. Lógica de Login e Sessão (Reutilizada) ---
// Variáveis $logado, $nome_usuario, $nivel_usuario já carregadas do config.php

// --- 2. Obter a Categoria da URL e Validar ---
$categoria_selecionada = filter_input(INPUT_GET, 'nome', FILTER_SANITIZE_STRING);

// Se nenhuma categoria for fornecida, redireciona para a home
if (empty($categoria_selecionada)) {
    header('Location: index.php');
    exit;
}

// --- 3. Conexão e Busca de Produtos (FILTRADO) ---
$produtos_bd = []; 
$titulo_pagina = htmlspecialchars($categoria_selecionada);

try {
    $pdo = getPdoConnection();
    
    // ATENÇÃO: A busca agora FILTRA pela categoria fornecida na URL
    $stmt_produtos = $pdo->prepare("SELECT * FROM produto WHERE quantidade > 0 AND categoria = ? ORDER BY id_produto DESC");
    $stmt_produtos->execute([$categoria_selecionada]);
    $produtos_bd = $stmt_produtos->fetchAll();

} catch (\PDOException $e) {
    $titulo_pagina = "Erro na Busca";
}

// ==========================================================
// 🚀 LÓGICA PARA BANNER DINÂMICO COM IMAGENS
// CAMINHOS CORRIGIDOS PARA O PADRÃO WEB (USANDO / E ESPAÇO)
// ==========================================================

// Mapeamento de categorias para URLs de Imagem
$estilos_banners = [
    'Moletons' => [
        'imagem_url' => 'imagems/banner/banner1.png', 
    ],
    'Camisetas' => [
        'imagem_url' => 'imagems/banner/banner2.png',
    ],
    'Chaveiros' => [
        'imagem_url' => 'imagems/banner/banner3.png',
    ],
    'Bottons' => [
        'imagem_url' => 'imagems/banner/banner4.png',
    ],
    'Mousepads' => [
        'imagem_url' => 'imagems/banner/banner5.png',
    ],
    'Posters' => [
        'imagem_url' => 'imagems/banner/banner7.png',
    ],
    'Pelucías' => [
        'imagem_url' => 'imagems/banner/banner6.png', 
    ],
    // Padrão/Fallback
    'Padrao' => [
        'imagem_url' => 'imagems/banner/Black f.gif', 
    ],
];

// Seleciona o estilo.
$estilo_categoria = $estilos_banners[$categoria_selecionada] ?? $estilos_banners['Padrao'];
$banner_url = htmlspecialchars($estilo_categoria['imagem_url']);

// REMOVIDO: $content_style
// O CSS do .banner (width: 95%; margin: 20px auto;) fará a centralização.
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - <?php echo $titulo_pagina; ?></title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    <link rel="stylesheet" href="index.css"/> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
</head>

<body> 
<div class="bg-pattern"></div>

<?php 
// ==========================================================
// LÓGICA CONDICIONAL DA SIDEBAR
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

<?php else: ?>
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
                    <a href="perfil.php" class="login-btn">
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

        <section class="banner">
            <img src="<?php echo $banner_url; ?>" alt="Banner da Categoria <?php echo $titulo_pagina; ?>">
        </section>

        <section class="loja">
            <section class="categorias">
                <a href="categoria.php?nome=Moletons">Moletons</a>
                <a href="categoria.php?nome=Camisetas">Camisetas</a>
                <a href="categoria.php?nome=Chaveiros">Chaveiros</a>
                <a href="categoria.php?nome=Bottons">Bottons</a>
                <a href="categoria.php?nome=Mousepads">Mouse Pads</a>
                <a href="categoria.php?nome=Posters">Posters</a>
                <a href="categoria.php?nome=Pelucías">Pelucías</a>
            </section>

        <section class="produtos">
            <?php if (empty($produtos_bd)): ?>
                <div style="text-align: center; width: 100%; color: white; padding: 40px;">
                    <i class="fas fa-search" style="font-size: 3em; margin-bottom: 20px; opacity: 0.5;"></i>
                    <h3>Nenhum produto encontrado</h3>
                    <p>Não há produtos disponíveis na categoria "<?php echo $titulo_pagina; ?>" no momento.</p>
                    <a href="index.php" style="color: #4FC3F7; text-decoration: none; font-weight: bold;">
                        <i class="fas fa-arrow-left"></i> Voltar para a página inicial
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($produtos_bd as $produto): 
                    $data_price = number_format($produto['preco'], 2, '.', '');
                    // Assumindo que formatarPreco() está definido em config.php ou em outro arquivo
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
                            <a href="produtos/detalhes.php?id=<?php echo htmlspecialchars($produto['id_produto']); ?>" class="comprar">
                                Ver Detalhes
                            </a>
                            <button class="add-to-cart-btn" onclick="addToCart(this)">
                                <i class="fas fa-cart-plus"></i> 
                            </button>
                            <button class="favoritar" onclick="toggleFavorite(this)"><i class="fas fa-heart"></i></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>

    <script src="home.js"></script>
</body>
</html>