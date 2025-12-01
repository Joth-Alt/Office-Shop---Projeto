<?php
// ====================================================================
// estoque-esgotado.php: Gerenciamento de Estoque Esgotado
// ====================================================================
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

// Variáveis para mensagens de status
$success = null;
$error = null;

// Processar adição de estoque
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_estoque'])) {
    $id_produto = $_POST['id_produto'];
    $quantidade = $_POST['quantidade'];
    
    try {
        $stmt = $pdo->prepare("UPDATE produto SET quantidade = quantidade + ? WHERE id_produto = ?");
        $stmt->execute([$quantidade, $id_produto]);
        
        $success = "Estoque do produto atualizado com sucesso!";
    } catch(PDOException $e) {
        $error = "Erro ao atualizar estoque: " . $e->getMessage();
    }
}

// Buscar produtos esgotados
$stmt = $pdo->query("SELECT * FROM produto WHERE quantidade = 0 ORDER BY n_produto");
$produtos_esgotados = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produtos Esgotados - Admin</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" href="imagems/logos/logo.png" type="image/png">

    <style>
        /* --- Variáveis e Layout Principal (Coerente com o painel) --- */
        :root {
            --sidebar-width: 250px; 
            --color-primary-pink: #ff3366; 
            --color-dark-text: #4a4a4a; 
            --color-light-bg: #f5f7f9;
            --color-table-bg: #ffffff;
            --color-border-light: #eeeeee;
            --color-success: #28a745;
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
        
        .main-content-wrapper { 
            margin-left: var(--sidebar-width); 
            flex-grow: 1;
            padding: 0;
            box-sizing: border-box;
        }
        
        /* --- Header Top (Header Principal) --- */
        .admin-top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px; 
            background-color: var(--color-table-bg);
            border-bottom: 1px solid var(--color-border-light); 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 0; 
        }

        .admin-top-nav h1 { 
            margin: 0; 
            font-size: 1.5em; 
            color: var(--color-dark-text); 
            font-weight: 500;
        }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .user-info a { color: var(--color-dark-text); text-decoration: none; font-size: 0.9em; }
        .admin-avatar-icon { font-size: 2.2em; color: var(--color-primary-pink); }

        /* --- Sub-Menu (Barra com Destaque Rosa) --- */
        /* Esta página não tem sub-menu, mas usamos padding/margin para manter o alinhamento */
        .admin-sub-menu {
            display: flex;
            gap: 25px; 
            align-items: center;
            padding: 15px 30px;
            background-color: #f0f0f0; 
            margin-bottom: 30px; 
            position: relative; 
        }
        .admin-sub-menu::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px; 
            background-color: var(--color-primary-pink);
        }
        .admin-sub-menu a { color: var(--color-dark-text); text-decoration: none; padding: 5px 0; }

        /* --- Título Principal --- */
        .page-title {
            margin: 0 30px 25px 30px; /* Alinhando com o conteúdo */
            color: var(--color-dark-text);
            font-weight: 300; 
            border-left: 5px solid var(--color-primary-pink); 
            padding-left: 10px;
        }

        /* --- Alertas de Status --- */
        .alert {
            padding: 15px 30px;
            border-radius: 8px;
            margin: 0 30px 20px 30px;
            font-weight: 500;
        }

        .alert.success {
            background-color: #d4edda;
            color: var(--color-success);
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background-color: #f8d7da;
            color: var(--color-danger);
            border: 1px solid #f5c6cb;
        }

        /* --- Grid de Produtos --- */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            padding: 0 30px;
        }

        .product-card {
            background: var(--color-table-bg);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            transition: transform 0.3s ease;
            border-left: 5px solid var(--color-danger); /* Destaque lateral vermelho */
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-name {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--color-dark-text);
            margin-bottom: 5px;
        }

        .stock-status {
            display: inline-block;
            padding: 5px 10px;
            background-color: var(--color-danger);
            color: white;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .product-info {
            margin-bottom: 15px;
            color: #666;
            font-size: 0.95em;
        }

        .product-price {
            font-size: 1.1em;
            color: var(--color-primary-pink);
            font-weight: bold;
        }

        /* --- Formulário de Estoque --- */
        .add-stock-form {
            border-top: 1px solid var(--color-border-light);
            padding-top: 20px;
            margin-top: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--color-dark-text);
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 1em;
            box-sizing: border-box;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }
        
        /* Botão de Estoque (Primário) */
        .btn-primary {
            background-color: var(--color-success); /* Usando Verde para Estoque */
            color: white;
        }

        .btn-primary:hover {
            background-color: #1e7e34;
        }

        /* --- Empty State --- */
        .empty-state {
            text-align: center;
            padding: 60px;
            background: var(--color-table-bg);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            margin: 0 30px;
        }

        .empty-state i {
            font-size: 3.5em;
            color: var(--color-success);
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: var(--color-dark-text);
            margin-bottom: 10px;
            font-weight: 500;
        }

        .empty-state p {
            color: #888;
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

    <main class="main-content-wrapper">
        <header class="admin-top-nav">
            <h1>Gerenciamento de Estoque</h1>
            <div class="user-info">
                <span>Olá, Administrador!</span>
                <a href="admin.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
            </div>
        </header>

        <nav class="admin-sub-menu">
            <a href="admin.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="produtos.php"><i class="fas fa-boxes"></i> Produtos</a>
            <a href="pedidos.php"><i class="fas fa-receipt"></i> Pedidos</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Clientes</a>
            <a href="lucro.php"><i class="fas fa-chart-bar"></i> Relatório de Lucros</a>
            <a href="admin-criar-produto.php"><i class="fas fa-plus-circle"></i> Novo Produto</a>
        </nav>

        <h2 class="page-title"><i class="fas fa-exclamation-triangle"></i> Produtos Esgotados</h2>

        <?php if (isset($success)): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($produtos_esgotados)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>Nenhum produto esgotado</h3>
                <p>Todos os produtos estão com estoque disponível. Parabéns!</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($produtos_esgotados as $produto): ?>
                    <div class="product-card">
                        <div class="product-name"><?php echo htmlspecialchars($produto['n_produto']); ?></div>
                        <div class="stock-status">
                            <i class="fas fa-times-circle"></i> ESGOTADO
                        </div>
                        <div class="product-info">
                            <div><strong>Categoria:</strong> <?php echo htmlspecialchars($produto['categoria']); ?></div>
                            <div><strong>Preço:</strong> <span class="product-price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span></div>
                            <div><strong>ID:</strong> #<?php echo $produto['id_produto']; ?></div>
                        </div>
                        
                        <form method="POST" class="add-stock-form">
                            <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">
                            
                            <div class="form-group">
                                <label for="quantidade_<?php echo $produto['id_produto']; ?>">
                                    <i class="fas fa-boxes"></i> Quantidade a adicionar:
                                </label>
                                <input type="number" 
                                        id="quantidade_<?php echo $produto['id_produto']; ?>" 
                                        name="quantidade" 
                                        min="1" 
                                        max="1000" 
                                        value="10" 
                                        required>
                            </div>
                            
                            <button type="submit" name="adicionar_estoque" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Adicionar ao Estoque
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Adicionar confirmação antes de adicionar estoque
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('.add-stock-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const quantidade = this.querySelector('input[name="quantidade"]').value;
                    const produtoNome = this.closest('.product-card').querySelector('.product-name').textContent;
                    
                    if (!confirm(`Adicionar ${quantidade} unidades ao estoque de "${produtoNome}"?`)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>