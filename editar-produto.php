<?php
session_start();

// Garante que APENAS admin pode acessar
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Conexão com o banco de dados
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

$success = null;
$error = null;

// Processar edição do produto
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['editar_produto'])) {
        $id_produto = $_POST['id_produto'];
        $n_produto = $_POST['n_produto'];
        // Ajuste de preço para formato float
        $preco_str = str_replace('.', '', $_POST['preco']);
        $preco = str_replace(',', '.', $preco_str);
        
        $quantidade = $_POST['quantidade'];
        $categoria = $_POST['categoria'];
        $descricao = $_POST['descricao'];
        
        try {
            $update_image = false;
            $imagem_destino = null;

            // Verificar se foi enviada uma nova imagem
            if (isset($_FILES['imagem_url']) && $_FILES['imagem_url']['error'] === UPLOAD_ERR_OK) {
                $imagem_temp = $_FILES['imagem_url']['tmp_name'];
                $imagem_nome = uniqid('edit_', true) . '_' . $_FILES['imagem_url']['name'];
                $imagem_destino = 'imagens-produtos/' . $imagem_nome;
                
                if (move_uploaded_file($imagem_temp, $imagem_destino)) {
                    $update_image = true;
                } else {
                    throw new Exception("Erro ao fazer upload da imagem");
                }
            }
            
            // Montar a query de atualização
            if ($update_image) {
                $stmt = $pdo->prepare("UPDATE produto SET n_produto = ?, preco = ?, quantidade = ?, categoria = ?, descricao = ?, imagem_url = ? WHERE id_produto = ?");
                $stmt->execute([$n_produto, $preco, $quantidade, $categoria, $descricao, $imagem_destino, $id_produto]);
            } else {
                $stmt = $pdo->prepare("UPDATE produto SET n_produto = ?, preco = ?, quantidade = ?, categoria = ?, descricao = ? WHERE id_produto = ?");
                $stmt->execute([$n_produto, $preco, $quantidade, $categoria, $descricao, $id_produto]);
            }

            $success = "Produto atualizado com sucesso!";
            
            // Nota: Recarregamos todos os produtos após o sucesso, para que a lista reflita a mudança.
            
        } catch(PDOException $e) {
            $error = "Erro ao atualizar produto: " . $e->getMessage();
        } catch(Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Buscar produtos (todos)
$stmt = $pdo->query("SELECT * FROM produto ORDER BY quantidade ASC, n_produto");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Categorias disponíveis (mantendo o array do código original)
$categorias = ['Moletons', 'Camisetas', 'Chaveiros', 'Bottons', 'Mousepads', 'Posters', 'Pelucías', 'OST'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - Admin</title>

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
            --color-success: #dc3545;
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
            padding: 0; /* Padding total será dado nos elementos internos */
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
        .admin-top-nav .back-link {
            font-size: 1em;
            color: var(--color-primary-pink);
            font-weight: 600;
        }

        /* --- Sub-Menu (Barra com Destaque Rosa) --- */
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
        .admin-sub-menu a:hover { color: var(--color-primary-pink); }
        .admin-sub-menu a.active-admin-sub-link { color: var(--color-dark-text); font-weight: bold; }

        /* --- Título Principal --- */
        .page-title {
            margin: 0 30px 25px 30px; 
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
        .alert i { margin-right: 10px; }

        /* --- Estatísticas (Stats Bar) --- */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 25px;
            padding: 0 30px;
            margin-bottom: 35px;
        }
        .stat-card {
            background: var(--color-table-bg);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
            border-bottom: 4px solid;
        }
        .stat-number { font-size: 2.2em; font-weight: 700; margin-bottom: 5px; color: var(--color-dark-text); }
        .stat-label { font-size: 0.9em; color: #888; text-transform: uppercase; }

        .total-products { border-bottom-color: #dc3545; }
        .out-of-stock-count { border-bottom-color: var(--color-danger); }
        .in-stock-count { border-bottom-color: var(--color-success); }
        
        .out-of-stock-count .stat-number { color: var(--color-danger); }
        .in-stock-count .stat-number { color: var(--color-success); }


        /* --- Grid de Edição (Cards de Produtos) --- */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr)); /* Mais largo para o form */
            gap: 30px;
            padding: 0 30px 40px 30px;
        }

        .product-card {
            background: var(--color-table-bg);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            padding: 25px;
            transition: transform 0.3s ease;
            border-left: 5px solid; 
        }

        .product-card:hover { transform: translateY(-5px); }

        .product-card.out-of-stock { border-left-color: var(--color-danger); }
        .product-card.in-stock { border-left-color: var(--color-success); }

        .product-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--color-border-light);
        }

        .product-name {
            font-size: 1.3em;
            font-weight: 600;
            color: var(--color-primary-pink);
            flex: 1;
        }

        .stock-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
            flex-shrink: 0;
        }

        .stock-status.out-of-stock {
            background-color: var(--color-danger);
            color: white;
        }
        .stock-status.in-stock {
            background-color: var(--color-success);
            color: white;
        }

        .product-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
        }

        /* --- Informações Resumidas --- */
        .product-info {
            margin-bottom: 20px;
            color: var(--color-dark-text);
            font-size: 0.9em;
        }
        .info-row {
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #f0f0f0;
        }
        .info-label {
            font-weight: 600;
            color: #555;
        }

        /* --- Formulário de Edição --- */
        .edit-form {
            padding-top: 20px;
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
        .form-row {
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            border-color: var(--color-primary-pink);
            outline: none;
        }
        
        /* Botões */
        .btn-primary {
            background-color: var(--color-primary-pink);
            color: white;
        }
        .btn-primary:hover {
            background-color: #d92452;
        }
        
        /* Estilo Customizado para Upload */
        .file-input-wrapper { display: block; }
        .file-input-label {
            padding: 12px 15px;
            background: #f8f8f8;
            border: 2px dashed #ddd;
            border-radius: 6px;
            color: #777;
            text-align: center;
        }
        .file-input-label small { display: block; font-weight: normal; margin-top: 5px; }

        .empty-state {
            padding: 60px 40px;
            background: var(--color-table-bg);
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
            margin: 0 30px;
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
            <h1>Gerenciar Produtos</h1>
            <div class="user-info">
                <span>Olá, Administrador!</span>
                <a href="admin.php" class="back-link"><i class="fas fa-arrow-left"></i> Voltar ao Dashboard</a>
            </div>
        </header>

        <nav class="admin-sub-menu">
            <a href="admin.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="produtos.php" class="active-admin-sub-link"><i class="fas fa-boxes"></i> Produtos</a>
            <a href="pedidos.php"><i class="fas fa-receipt"></i> Pedidos</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Clientes</a>
            <a href="lucro.php"><i class="fas fa-chart-bar"></i> Relatório de Lucros</a>
            <a href="admin-criar-produto.php"><i class="fas fa-plus-circle"></i> Novo Produto</a>
        </nav>

        <h2 class="page-title"><i class="fas fa-edit"></i> Edição de Produtos</h2>

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

        <?php
        $total_produtos = count($produtos);
        $produtos_esgotados = array_filter($produtos, function($produto) {
            return $produto['quantidade'] == 0;
        });
        $produtos_estoque = array_filter($produtos, function($produto) {
            return $produto['quantidade'] > 0;
        });
        ?>
        <div class="stats-bar">
            <div class="stat-card total-products">
                <div class="stat-number"><?php echo $total_produtos; ?></div>
                <div class="stat-label">Total de Produtos</div>
            </div>
            <div class="stat-card in-stock-count">
                <div class="stat-number"><?php echo count($produtos_estoque); ?></div>
                <div class="stat-label">Em Estoque</div>
            </div>
            <div class="stat-card out-of-stock-count">
                <div class="stat-number"><?php echo count($produtos_esgotados); ?></div>
                <div class="stat-label">Esgotados</div>
            </div>
        </div>

        <?php if (empty($produtos)): ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>Nenhum produto cadastrado</h3>
                <p>Comece adicionando produtos ao seu catálogo.</p>
            </div>
        <?php else: ?>
            <div class="products-grid">
                <?php foreach ($produtos as $produto): ?>
                    <div class="product-card <?php echo $produto['quantidade'] == 0 ? 'out-of-stock' : 'in-stock'; ?>">
                        <div class="product-header">
                            <div class="product-name"><?php echo htmlspecialchars($produto['n_produto']); ?></div>
                            <div class="stock-status <?php echo $produto['quantidade'] == 0 ? 'out-of-stock' : 'in-stock'; ?>">
                                <i class="fas <?php echo $produto['quantidade'] == 0 ? 'fa-times-circle' : 'fa-check-circle'; ?>"></i>
                                <?php echo $produto['quantidade'] == 0 ? 'ESGOTADO' : 'ESTOQUE: ' . $produto['quantidade']; ?>
                            </div>
                        </div>
                        
                        <form method="POST" class="edit-form" enctype="multipart/form-data">
                            <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">

                            <div class="current-image">
                                <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                                    alt="<?php echo htmlspecialchars($produto['n_produto']); ?>" 
                                    class="product-image"
                                    onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0jOTk5IiB0ZXh0LWFuY2hvcj0ibWlkZGxlIiBkeT0iLjNlbSI+SW1hZ2VtIG7Do28gZW5jb250cmFkYTwvdGV4dD48L3N2Zz4='">
                            </div>
                            
                            <div class="form-group">
                                <label for="n_produto_<?php echo $produto['id_produto']; ?>">
                                    <i class="fas fa-tag"></i> Nome do Produto:
                                </label>
                                <input type="text" 
                                        id="n_produto_<?php echo $produto['id_produto']; ?>" 
                                        name="n_produto" 
                                        value="<?php echo htmlspecialchars($produto['n_produto']); ?>" 
                                        required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="preco_<?php echo $produto['id_produto']; ?>">
                                        <i class="fas fa-dollar-sign"></i> Preço:
                                    </label>
                                    <input type="text" 
                                            id="preco_<?php echo $produto['id_produto']; ?>" 
                                            name="preco" 
                                            placeholder="Ex: 49,90"
                                            value="<?php echo number_format($produto['preco'], 2, ',', '.'); ?>" 
                                            required>
                                </div>

                                <div class="form-group">
                                    <label for="quantidade_<?php echo $produto['id_produto']; ?>">
                                        <i class="fas fa-boxes"></i> Estoque:
                                    </label>
                                    <input type="number" 
                                            id="quantidade_<?php echo $produto['id_produto']; ?>" 
                                            name="quantidade" 
                                            min="0" 
                                            value="<?php echo $produto['quantidade']; ?>" 
                                            required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="categoria_<?php echo $produto['id_produto']; ?>">
                                    <i class="fas fa-folder"></i> Categoria:
                                </label>
                                <select id="categoria_<?php echo $produto['id_produto']; ?>" name="categoria" required>
                                    <?php foreach ($categorias as $cat_option): ?>
                                        <option value="<?php echo $cat_option; ?>" <?php echo $produto['categoria'] == $cat_option ? 'selected' : ''; ?>>
                                            <?php echo $cat_option; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="descricao_<?php echo $produto['id_produto']; ?>">
                                    <i class="fas fa-align-left"></i> Descrição:
                                </label>
                                <textarea id="descricao_<?php echo $produto['id_produto']; ?>" 
                                            name="descricao" 
                                            rows="4"
                                            placeholder="Descrição detalhada do produto..."><?php echo htmlspecialchars($produto['descricao'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>
                                    <i class="fas fa-image"></i> Alterar Imagem:
                                </label>
                                <div class="file-input-wrapper">
                                    <div class="file-input-label">
                                        <i class="fas fa-upload"></i> Clique para selecionar nova imagem
                                        <br>
                                        <small>Deixe em branco para manter a imagem atual</small>
                                    </div>
                                    <input type="file" 
                                            name="imagem_url" 
                                            accept="image/*">
                                </div>
                            </div>
                            
                            <button type="submit" name="editar_produto" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Função para formatar o preço em Real Brasileiro
        function formatToReal(number) {
            return number.toFixed(2).replace('.', ',');
        }

        // Script para garantir que o input de preço lide com o formato BR (vírgula)
        document.addEventListener('DOMContentLoaded', function() {
            const priceInputs = document.querySelectorAll('input[name="preco"]');
            
            priceInputs.forEach(input => {
                // Ao perder o foco, formata para BR
                input.addEventListener('blur', function() {
                    let value = this.value.replace('.', '').replace(',', '.');
                    if (value && !isNaN(parseFloat(value))) {
                        this.value = formatToReal(parseFloat(value));
                    }
                });
                // Ao receber foco, remove a formatação para facilitar a edição
                input.addEventListener('focus', function() {
                     let value = this.value.replace('.', '').replace(',', '.');
                     if (value && !isNaN(parseFloat(value))) {
                        this.value = parseFloat(value).toFixed(2); // Retorna a notação numérica
                    }
                });
            });

            // Adicionar confirmação antes de salvar alterações
            const forms = document.querySelectorAll('.edit-form');
            
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const produtoNome = this.querySelector('input[name="n_produto"]').value;
                    
                    // Pré-processa o preço para o padrão float antes de enviar (caso o JS não tenha feito)
                    const precoInput = this.querySelector('input[name="preco"]');
                    if (precoInput) {
                        precoInput.value = precoInput.value.replace('.', '').replace(',', '.');
                    }
                    
                    if (!confirm(`Salvar alterações no produto "${produtoNome}"?`)) {
                        e.preventDefault();
                    }
                });

                // Preview da imagem selecionada
                const fileInput = form.querySelector('input[type="file"]');
                const imagePreview = form.parentElement.querySelector('.product-image');
                const fileLabel = form.querySelector('.file-input-label');
                
                fileInput.addEventListener('change', function(e) {
                    if (this.files && this.files[0]) {
                        const reader = new FileReader();
                        const fileName = this.files[0].name;

                        reader.onload = function(e) {
                            if (imagePreview) {
                                imagePreview.src = e.target.result;
                            }
                        }
                        
                        reader.readAsDataURL(this.files[0]);
                        fileLabel.innerHTML = `<i class="fas fa-check"></i> Nova imagem selecionada: <b>${fileName}</b>`;
                    } else {
                        fileLabel.innerHTML = `<i class="fas fa-upload"></i> Clique para selecionar nova imagem <br><small>Deixe em branco para manter a imagem atual</small>`;
                    }
                });
            });
        });
    </script>
</body>
</html>