<?php
// ==========================================================
// busca.php: Exibe o catálogo COMPLETO de produtos e usa filtro JS
// ==========================================================
require_once 'config.php'; 

// O termo de busca é capturado para PREENCHER o campo de input e iniciar o filtro JS,
// mas NÃO é usado aqui para o SELECT SQL.
$termo_busca = isset($_GET['q']) ? trim($_GET['q']) : '';

$produtos_encontrados = [];

// A função formatarPreco deve estar definida no seu config.php, ou ocorrerá um erro de função indefinida.
// OBS: Deixei a função aqui para garantir que a página funcione se ela não estiver em config.php.
function formatarPreco($preco) {
    return number_format($preco, 2, ',', '.');
}

try {
    // Obtém a conexão PDO (definida em config.php)
    $pdo = getPdoConnection(); 
    
    // --- Busca TODOS os Produtos no Banco de Dados (sem filtro WHERE de termo) ---
    $sql = "SELECT * FROM produto 
            WHERE quantidade > 0 
            ORDER BY n_produto ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $produtos_encontrados = $stmt->fetchAll();
    
} catch (\PDOException $e) {
    // Loga o erro, mas não exibe para o usuário
    // error_log("Erro no BD em busca.php: " . $e->getMessage());
}

// Obtém informações de login (garantindo que $logado, $nivel_usuario, $nome_usuario existam)
if (!isset($logado)) $logado = false;
if (!isset($nivel_usuario)) $nivel_usuario = 'user';
if (!isset($nome_usuario)) $nome_usuario = 'Visitante';

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Catálogo Completo | Office Shop</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
    <style>
        .busca-container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .busca-header {
            margin-bottom: 30px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 15px;
        }
        .resultados-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 20px;
        }
        /* Estilos para produtos escondidos pelo JS */
        .produto[style*="display: none"] { 
            /* Para animações futuras, se necessário */ 
        }
    </style>
</head>

<body> 
<div class="bg-pattern"></div>

<?php 
// ==========================================================
// SIDEBAR (mantida a lógica de admin/user)
// ==========================================================
if ($logado && $nivel_usuario === 'admin'): 
?>
    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="admin.php" class="active-config"><i class="fas fa-user-shield"></i> <span class="txt">Painel Admin</span></a> 
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
        </nav>
        <nav class="menu-config">
            <a href="configuracoes.php"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
            <a href="#" id="themeToggle"><i class="fas fa-adjust"></i> <span class="txt">Tema</span></a>
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
                </div>
            </div>
            <a href="#" id="openCart"><i class="fas fa-shopping-cart"></i> <span class="txt">Carrinho</span> <span class="cart-count">. 0</span></a>
        </nav>
    </aside>
<?php endif; ?>
    
    <main class="content">
        <header class="top-nav">
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Pesquisar produtos..." value="<?php echo htmlspecialchars($termo_busca); ?>">
                <div id="search-results" class="search-dropdown"></div>
                <button id="microphone-btn" class="mic-btn"><i class="fas fa-microphone"></i></button>
                <button class="search-btn" id="search-local-btn"><i class="fas fa-search"></i></button>
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

        <section class="busca-container">
            <h2 class="busca-header">
                Catálogo de Produtos Office Shop 🛍️
            </h2>

            <section class="resultados-grid" id="products-grid"> 
                <?php foreach ($produtos_encontrados as $produto): 
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

    <div id="cart-modal" class="modal">...</div> 
    <div id="login-modal" class="modal">...</div> 
    
    <script src="home.js"></script>
</body>
</html>