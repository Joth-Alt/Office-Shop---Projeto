<?php
session_start();

// Verifica se é admin
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Conexão com o banco (PDO)
$host = 'localhost';
$dbname = 'projeto';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// Filtro padrão
$filtro = $_GET['filtro'] ?? 'dia';

// Consulta base
$sql = "
    SELECT 
        DATE(p.data_p) as data,
        SUM(ip.quantidade * ip.preco) as lucro_total,
        COUNT(DISTINCT p.id_pedido) as total_pedidos,
        SUM(ip.quantidade) as total_itens
    FROM pedidos p 
    JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido 
    WHERE 1=1
";

// Aplica filtros
switch($filtro) {
    case 'dia':
        $sql .= " AND DATE(p.data_p) = CURDATE()";
        $group_by = "DATE(p.data_p)";
        break;
    case 'semana':
        $sql .= " AND YEARWEEK(p.data_p) = YEARWEEK(CURDATE())";
        $group_by = "YEARWEEK(p.data_p)";
        break;
    case 'mes':
        $sql .= " AND MONTH(p.data_p) = MONTH(CURRENT_DATE()) AND YEAR(p.data_p) = YEAR(CURRENT_DATE())";
        $group_by = "MONTH(p.data_p), YEAR(p.data_p)";
        break;
    case 'ano':
        $sql .= " AND YEAR(p.data_p) = YEAR(CURRENT_DATE())";
        $group_by = "YEAR(p.data_p)";
        break;
    case 'todos':
        $group_by = "DATE(p.data_p)";
        $sql .= " AND p.data_p >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)"; // Últimos 30 dias como exemplo
        break;
    default:
        $sql .= " AND DATE(p.data_p) = CURDATE()";
        $group_by = "DATE(p.data_p)";
}

$sql .= " GROUP BY $group_by ORDER BY data DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$lucros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lucro total no período atual
$total_geral_periodo = array_sum(array_column($lucros, 'lucro_total'));

// Lucro total completo (Histórico)
$sql_total = "SELECT SUM(ip.quantidade * ip.preco) as total_geral 
             FROM pedidos p 
             JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido";
$stmt_total = $pdo->query($sql_total);
$total_geral_completo = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_geral'] ?? 0;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Office Shop - Relatório de Lucros</title>

    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" href="imagems/logos/logo.png" type="image/png">
    
    <style>
        /* --- Variáveis e Layout Principal (Estilo Produtos/Usuários) --- */
        :root {
            --sidebar-width: 250px; 
            --color-primary-pink: #ff3366; 
            --color-dark-text: #4a4a4a; 
            --color-light-bg: #f5f7f9;
            --color-table-bg: #ffffff;
            --color-border-light: #eeeeee;
            --color-success: #28a745; 
            --color-secondary-pink: #ff3366;
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
        
        .main-content-wrapper { 
            margin-left: var(--sidebar-width); 
            flex-grow: 1;
            padding: 20px;
            box-sizing: border-box;
            width: calc(100% - var(--sidebar-width));
        }

        /* --- Estilo Título e Sub-Menu --- */
        .admin-top-nav {
             /* Não existe nesta página, mas mantendo a classe do header para referência futura */
        }
        
        .admin-sub-menu {
            display: flex; gap: 15px; padding: 15px 0; margin-bottom: 30px;
            border-bottom: 2px solid var(--color-primary-pink); 
            align-items: center;
        }
        .admin-sub-menu a { color: var(--color-dark-text); text-decoration: none; transition: color 0.3s; }
        .admin-sub-menu a:hover { color: var(--color-primary-pink); }
        .admin-sub-menu .active-admin-sub-link { color: var(--color-primary-pink); font-weight: bold; }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
            color: var(--color-dark-text);
            font-weight: 300; 
            border-left: 5px solid var(--color-primary-pink); 
            padding-left: 10px;
        }

        /* --- 1. FILTROS (Como um contêiner Section/Card) --- */
        .filtros-lucros {
            background: var(--color-table-bg);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08); /* Sombra suave */
        }
        .filtros-lucros h2 {
            font-size: 1.3em;
            color: var(--color-dark-text);
            border-bottom: 1px solid var(--color-border-light);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .filtro-buttons {
            gap: 15px;
        }
        
        .filtro-btn {
            padding: 10px 18px;
            border: 1px solid var(--color-primary-pink);
            background: var(--color-table-bg);
            color: var(--color-primary-pink);
            border-radius: 8px;
            font-weight: 500;
        }
        
        .filtro-btn.active, .filtro-btn:hover {
            background: var(--color-primary-pink);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 51, 102, 0.3);
        }

        /* --- 2. RESUMO (CARDS - Mantendo o estilo dos cards) --- */
        .resumo-lucros {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card-resumo {
            background: var(--color-table-bg);
            padding: 25px;
            border-radius: 12px;
            text-align: left;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border-left: 5px solid var(--color-secondary-pink);
            transition: transform 0.3s;
        }
        .card-resumo:hover {
            transform: translateY(-5px);
        }

        .card-resumo i {
            color: var(--color-secondary-pink);
            float: right;
        }
        
        .card-resumo .valor {
            font-size: 2.2em;
            font-weight: 700;
            color: var(--color-dark-text);
        }
        
        .card-resumo .descricao {
            font-size: 0.9em;
            color: #888;
        }
        
        .card-resumo.lucro .valor { color: var(--color-success); }
        .card-resumo.pedidos .valor { color: var(--color-primary-pink); }

        /* --- 3. TABELA DE LUCROS (Estilo da Tabela de Produtos) --- */
        .tabela-lucros {
            background: var(--color-table-bg);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }
        
        .tabela-lucros table {
            width: 100%;
            border-collapse: collapse;
            table-layout: auto; /* Mantendo auto, pois o conteúdo varia */
        }
        
        .tabela-lucros th,
        .tabela-lucros td {
            padding: 15px;
            text-align: left;
            border: none;
            border-bottom: 1px solid var(--color-border-light); /* Divisor suave */
            font-size: 14px;
        }
        
        .tabela-lucros thead th {
            background: var(--color-table-bg);
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
        }
        
        .tabela-lucros tbody tr:nth-child(even) {
            background-color: rgba(255, 51, 102, 0.03); /* Zebrado suave */
        }
        
        .tabela-lucros tbody tr:hover {
            background: rgba(255, 51, 102, 0.1); /* Hover rosa */
        }
        
        /* Alinhamentos Específicos */
        .tabela-lucros td:nth-child(2), /* Lucro Total */
        .tabela-lucros td:nth-child(5) { /* Ticket Médio */
            text-align: right;
            font-weight: 600;
        }
        
        .tabela-lucros td:nth-child(3), /* Total Pedidos */
        .tabela-lucros td:nth-child(4) { /* Itens Vendidos */
            text-align: center;
        }

        .sem-dados {
            text-align: center;
            padding: 40px;
            color: #6c757d;
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
            <a href="admin.php"><i class="fas fa-user-shield"></i> <span class="txt">Painel Admin</span></a>
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
            <h1>Relatório de Lucros</h1>
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

        <section class="filtros-lucros">
            <h2><i class="fas fa-filter"></i> Filtrar por Período</h2>
            <div class="filtro-buttons">
                <a href="?filtro=dia" class="filtro-btn <?php echo $filtro == 'dia' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-day"></i> Hoje
                </a>
                <a href="?filtro=semana" class="filtro-btn <?php echo $filtro == 'semana' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-week"></i> Esta Semana
                </a>
                <a href="?filtro=mes" class="filtro-btn <?php echo $filtro == 'mes' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i> Este Mês
                </a>
                <a href="?filtro=ano" class="filtro-btn <?php echo $filtro == 'ano' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar"></i> Este Ano
                </a>
                <a href="?filtro=todos" class="filtro-btn <?php echo $filtro == 'todos' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Últimos 30 Dias
                </a>
            </div>
        </section>

        <section class="resumo-lucros">
            <div class="card-resumo lucro">
                <i class="fas fa-dollar-sign fa-2x"></i>
                <div class="valor">R$ <?php echo number_format($total_geral_periodo, 2, ',', '.'); ?></div>
                <div class="descricao">Lucro Total no Período</div>
            </div>
            
            <div class="card-resumo pedidos">
                <i class="fas fa-shopping-basket fa-2x"></i>
                <div class="valor"><?php echo array_sum(array_column($lucros, 'total_pedidos')); ?></div>
                <div class="descricao">Total de Pedidos</div>
            </div>
            
            <div class="card-resumo itens">
                <i class="fas fa-boxes fa-2x"></i>
                <div class="valor"><?php echo array_sum(array_column($lucros, 'total_itens')); ?></div>
                <div class="descricao">Total de Itens Vendidos</div>
            </div>

            <div class="card-resumo itens">
                <i class="fas fa-money-bill-wave fa-2x"></i>
                <div class="valor">R$ <?php echo number_format($total_geral_completo, 2, ',', '.'); ?></div>
                <div class="descricao">Lucro Histórico Total</div>
            </div>
        </section>

        <section class="tabela-lucros">
            <?php if (!empty($lucros)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data/Período</th>
                            <th style="text-align: right;">Lucro Total</th>
                            <th style="text-align: center;">Total de Pedidos</th>
                            <th style="text-align: center;">Itens Vendidos</th>
                            <th style="text-align: right;">Ticket Médio</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($lucros as $lucro): 
                            $lucro_total = $lucro['lucro_total'];
                            $total_pedidos = $lucro['total_pedidos'];
                            $ticket_medio = $total_pedidos > 0 ? $lucro_total / $total_pedidos : 0;
                            
                            // Formatação do nome da data/período
                            $data_display = date('d/m/Y', strtotime($lucro['data']));
                            if ($filtro == 'semana') {
                                $data_display = "Semana " . date('W', strtotime($lucro['data']));
                            } elseif ($filtro == 'mes') {
                                $data_display = date('M/Y', strtotime($lucro['data']));
                            } elseif ($filtro == 'ano') {
                                $data_display = date('Y', strtotime($lucro['data']));
                            }
                        ?>
                            <tr>
                                <td><?php echo $data_display; ?></td>
                                <td style="color: var(--color-success); font-weight: bold;">
                                    R$ <?php echo number_format($lucro_total, 2, ',', '.'); ?>
                                </td>
                                <td><?php echo $total_pedidos; ?></td>
                                <td><?php echo $lucro['total_itens']; ?></td>
                                <td style="color: var(--color-primary-pink); font-weight: bold;">
                                    R$ <?php echo number_format($ticket_medio, 2, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="sem-dados">
                    <i class="fas fa-chart-line fa-3x" style="margin-bottom: 20px;"></i>
                    <h3>Nenhum dado encontrado para o período selecionado</h3>
                    <p>Tente selecionar outro filtro ou período.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script src="home.js"></script>
</body>
</html>