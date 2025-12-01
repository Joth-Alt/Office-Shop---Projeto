<?php
// ====================================================================
// admin.php: Painel Admin - Inicia a sessão e conecta ao BD (PDO)
// ====================================================================
session_start();

// --------------------------------------------------------------------
// 1. Conexão com o Banco de Dados (PDO)
// --------------------------------------------------------------------
$host = 'localhost';
$db = 'projeto'; 
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Em caso de erro de conexão, loga o erro e o script continua.
    error_log("Erro de conexão PDO: " . $e->getMessage());
}

// --------------------------------------------------------------------
// 2. Verifica se o usuário logado é ADMIN
// --------------------------------------------------------------------
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Inicializa variáveis (Valores Padrão)
$total_clientes = 0;
$pedidos_novos = 0;
$receita_total = "0,00";
$produtos_esgotados = 0;
$ultimos_pedidos = [];
$top_produtos = [];

// --------------------------------------------------------------------
// 3. Coleta de Métricas do Banco de Dados
// --------------------------------------------------------------------
if (isset($pdo)) {
    try {
        // Total de clientes
        $total_clientes = $pdo->query("SELECT COUNT(id_usuario) FROM usuarios")->fetchColumn();

        // Receita Total Geral
        $stmt_receita = $pdo->query("
            SELECT SUM(ip.quantidade * ip.preco) as receita_total 
            FROM pedidos p 
            JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido 
        ");
        $receita_result = $stmt_receita->fetchColumn();
        $receita_total = number_format($receita_result ?? 0, 2, ',', '.');

        // Pedidos feitos hoje
        $pedidos_novos = $pdo->query("
            SELECT COUNT(id_pedido)
            FROM pedidos
            WHERE DATE(data_p) = CURDATE()
        ")->fetchColumn();

        // Produtos esgotados
        $produtos_esgotados = $pdo->query("SELECT COUNT(id_produto) FROM produto WHERE quantidade = 0")->fetchColumn();

        // Últimos pedidos
        $stmt_ultimos = $pdo->query("
            SELECT p.id_pedido, p.data_p, SUM(ip.preco * ip.quantidade) AS total_pedido, u.n_usuario
            FROM pedidos p
            JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            GROUP BY p.id_pedido, p.data_p, u.n_usuario 
            ORDER BY p.data_p DESC
            LIMIT 5
        ");
        $ultimos_pedidos = $stmt_ultimos->fetchAll();

        // Top 5 produtos mais vendidos
        $stmt_top = $pdo->query("
            SELECT p.n_produto, SUM(ip.quantidade) as total_vendido
            FROM itens_pedido ip
            JOIN produto p ON ip.id_produto = p.id_produto
            GROUP BY p.id_produto, p.n_produto
            ORDER BY total_vendido DESC
            LIMIT 5
        ");
        $top_produtos = $stmt_top->fetchAll();


    } catch (PDOException $e) {
        error_log("Erro de consulta no Dashboard: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Office Shop - Painel Admin</title>

    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" href="imagems/logos/logo.png" type="image/png">
    
    <style>
        /* --- Variáveis e Layout Principal (Coerente com Lucros/Produtos) --- */
        :root {
            --sidebar-width: 250px; 
            --color-primary-pink: #ff3366; 
            --color-dark-text: #4a4a4a; 
            --color-light-bg: #f5f7f9;
            --color-table-bg: #ffffff;
            --color-border-light: #eeeeee;
            --color-success: #28a745;
            --color-warning: #ffc107;
            --color-danger: #dc3545;
        }

        body {
            display: flex; 
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
        
        .admin-content-wrapper { 
            margin-left: var(--sidebar-width); 
            flex-grow: 1;
            padding: 0;
            box-sizing: border-box;
        }

        /* --- Header Top e Sub-Menu (AJUSTADO PARA A IMAGEM) --- */
        .admin-top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px; 
            background-color: var(--color-table-bg);
            /* Borda sutil abaixo do header principal */
            border-bottom: 1px solid var(--color-border-light); 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 0; 
        }

        .admin-top-nav h1 { 
            margin: 0; 
            font-size: 1.5em; 
            color: var(--color-dark-text); 
            font-weight: 500;
            border-left: none; /* Remove o destaque lateral que estava no h1 */
            padding-left: 0;
        }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info a { color: var(--color-dark-text); text-decoration: none; font-size: 0.9em; }
        .admin-avatar-icon { font-size: 2.2em; color: var(--color-primary-pink); } /* Icone grande */

        .admin-sub-menu {
            /* Esta é a barra de navegação secundária (abaixo do header) */
            display: flex;
            gap: 25px; /* Aumenta a separação entre os links */
            align-items: center;
            padding: 15px 30px; /* Alinhamento lateral com o header */
            background-color: #f0f0f0; /* Fundo cinza/bege claro da imagem */
            margin-bottom: 30px; 
            position: relative; 
        }

        /* Linha de destaque rosa (pseudo-elemento para replicar a imagem) */
        .admin-sub-menu::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px; /* Espessura da linha rosa */
            background-color: var(--color-primary-pink);
        }

        .admin-sub-menu a { color: var(--color-dark-text); text-decoration: none; padding: 5px 0; }
        .admin-sub-menu a:hover { color: var(--color-primary-pink); }
        .admin-sub-menu a.active-admin-sub-link { color: var(--color-dark-text); font-weight: bold; } /* Mantido escuro, mas negrito */
        
        /* --- 1. CARDS DE MÉTRICAS (METRICS-CARDS) --- */
        .metrics-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 25px;
            padding: 0 30px;
            margin-bottom: 40px;
        }

        .metric-card {
            background-color: var(--color-table-bg);
            border-radius: 12px;
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            border-left: 5px solid var(--color-border-light);
            transition: transform 0.3s;
        }

        .metric-card:hover {
            transform: translateY(-5px);
            border-left-color: var(--color-primary-pink);
        }
        
        .metric-card i {
            font-size: 2.2em;
            color: var(--color-dark-text); 
            float: right; 
        }

        .metric-title {
            font-size: 0.9em;
            color: #888;
            text-transform: uppercase;
        }

        .metric-value {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--color-dark-text);
            margin: 5px 0 10px 0;
            line-height: 1.2;
        }
        
        /* Cores de Ícones de Destaque */
        .metric-card .fas.fa-dollar-sign { color: var(--color-success); }
        .metric-card .fas.fa-shopping-basket { color: var(--color-primary-pink); }
        .metric-card .fas.fa-users { color: #3498db; }
        .metric-card .fas.fa-exclamation-triangle { color: var(--color-danger); }


        .metric-trend {
            font-size: 0.85em;
            color: #aaa;
            border-top: 1px solid var(--color-border-light);
            padding-top: 10px;
            margin-top: 10px;
        }

        /* Cores de Destaque */
        .green-text { color: var(--color-success) !important; }
        .red-text { color: var(--color-danger) !important; }
        .action-link { color: var(--color-primary-pink); text-decoration: none; font-weight: 600; }
        
        /* --- 2. CARDS DE DADOS (LISTAS) --- */
        .data-sections {
            display: flex;
            gap: 25px;
            padding: 0 30px;
        }
        
        .data-card {
            background-color: var(--color-table-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            flex: 1; 
            min-width: 300px;
        }

        .data-card h2 {
            font-size: 1.3em;
            color: var(--color-primary-pink);
            border-bottom: 1px solid var(--color-border-light);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 15px;
        }

        .data-card h2 i { margin-right: 10px; }

        .order-list, .product-rank-list { list-style: none; padding: 0; margin: 0; }
        
        .order-list li, .product-rank-list li {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0; 
            color: var(--color-dark-text);
            font-size: 0.95em;
        }
        
        .order-list li:last-child, .product-rank-list li:last-child { border-bottom: none; }

        .product-rank-list { list-style-type: decimal; margin-left: 20px; }
        .product-rank-list li { font-weight: 500; padding-left: 5px; }

        .view-all {
            display: block;
            margin-top: 25px;
            color: var(--color-primary-pink);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9em;
        }

    </style>
</head>

<body>
    <div class="bg-pattern"></div>

    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
            <a href="minhas_compras.php"><i class="fas fa-box"></i> <span class="txt">Minhas Compras</span></a>
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
                    <button onclick="setLanguage('es')">🇪🇸 Español</button>
                </div>
            </div>

            <a href="#" id="openCart"><i class="fas fa-shopping-cart"></i> <span class="txt">Carrinho</span> <span class="cart-count">0</span></a>
        </nav>
    </aside>

    <main class="content admin-content-wrapper">
        <header class="admin-top-nav">
            <h1>Painel Admin</h1>
            <div class="user-info">
                <span>Olá, Administrador!</span>
                <a href="#" class="logout-link"><i class="fas fa-sign-out-alt"></i> Sair</a>
                <i class="fas fa-user-circle admin-avatar-icon"></i>
            </div>
        </header>

        <nav class="admin-sub-menu">
            <a href="admin.php" class="active-admin-sub-link"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="produtos.php"><i class="fas fa-boxes"></i> Produtos</a>
            <a href="pedidos.php"><i class="fas fa-receipt"></i> Pedidos</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Clientes</a>
            <a href="lucro.php"><i class="fas fa-chart-bar"></i> Relatório de Lucros</a>
            <a href="admin-criar-produto.php"><i class="fas fa-plus-circle"></i> Novo Produto</a>
            <a href="editar-produto.php"><i class="fas fa-pen"></i> Editar Produto</a>
        </nav>

        <section class="metrics-cards">
            <div class="card metric-card">
                <i class="fas fa-dollar-sign"></i>
                <span class="metric-title">Receita Total Geral</span>
                <span class="metric-value green-text">R$ <?php echo $receita_total; ?></span>
                <span class="metric-trend"><i class="fas fa-chart-line"></i> Histórico Completo</span>
            </div>

            <div class="card metric-card">
                <i class="fas fa-shopping-basket"></i>
                <span class="metric-title">Pedidos Novos (Hoje)</span>
                <span class="metric-value"><?php echo $pedidos_novos; ?></span>
                <span class="metric-trend"><i class="fas fa-calendar-day"></i> Novos Hoje</span>
            </div>

            <div class="card metric-card">
                <i class="fas fa-users"></i>
                <span class="metric-title">Total de Clientes</span>
                <span class="metric-value"><?php echo $total_clientes; ?></span>
                <span class="metric-trend"><i class="fas fa-database"></i> Total Cadastrados</span>
            </div>

            <div class="card metric-card">
                <i class="fas fa-exclamation-triangle"></i>
                <span class="metric-title">Produtos Esgotados</span>
                <span class="metric-value red-text"><?php echo $produtos_esgotados; ?></span>
                <span class="metric-trend">
                    <a href="estoque-esgotado.php" class="action-link">
                        Editar Estoque
                    </a>
                </span>
            </div>
        </section>

        <section class="data-sections">
            <div class="card data-card recent-orders">
                <h2><i class="fas fa-history"></i> Últimos Pedidos</h2>
                <ul class="order-list">
                    <?php if (!empty($ultimos_pedidos)): ?>
                        <?php foreach ($ultimos_pedidos as $pedido): ?>
                            <?php $valor = number_format($pedido['total_pedido'], 2, ',', '.'); ?>
                            <li>
                                **#<?php echo htmlspecialchars($pedido['id_pedido']); ?>** &mdash;
                                R$ **<?php echo $valor; ?>** &mdash;
                                Cliente: <?php echo htmlspecialchars($pedido['n_usuario']); ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Nenhum pedido encontrado.</li>
                    <?php endif; ?>
                </ul>
                <a href="pedidos.php" class="view-all">Ver todos os pedidos &rarr;</a>
            </div>

            <div class="card data-card top-products">
                <h2><i class="fas fa-star"></i> Top 5 Produtos Vendidos</h2>
                <ol class="product-rank-list">
                    <?php if (!empty($top_produtos)): ?>
                        <?php foreach ($top_produtos as $produto): ?>
                            <li>
                                **<?php echo htmlspecialchars($produto['n_produto']); ?>** &mdash; <?php echo $produto['total_vendido']; ?> Vendas
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li>Nenhum produto vendido ainda.</li>
                    <?php endif; ?>
                </ol>
            </div>
        </section>
    </main>

    <div id="cart-modal" class="modal">...</div>
    <div id="login-modal" class="modal">...</div>

    <script src="home.js"></script>
    <script src="admin.js"></script>
</body>
</html>