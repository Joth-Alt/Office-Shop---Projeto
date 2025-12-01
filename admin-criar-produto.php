<?php
// ====================================================================
// admin-criar-produto.php: Adicionar Novo Produto no Banco de Dados com Upload de Imagem
// ====================================================================
session_start();
 
// ------------------------------------
// 1. Configuração e Conexão com o Banco de Dados
// ------------------------------------
$host = 'localhost';
$db = 'projeto';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';
 
$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
 
try{
    $pdo=new PDO($dsn,$user,$pass,$options);
}catch(\PDOException $e){
    die("Erro de conexão com o banco de dados: ".$e->getMessage());
}
 
// ------------------------------------
// 2. Variáveis de Configuração
// ------------------------------------
$categorias_fixas = [
    'Moletons', 
    'Camisetas', 
    'Chaveiros',
    'Bottons',
    'Mousepads',
    'Posters',
    'Pelucías'
];
 
$tamanhos_disponiveis = ['P', 'M', 'G', 'GG', 'XG'];
 
// ------------------------------------
// 3. Garante que APENAS admin pode acessar
// ------------------------------------
if(!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel']!=='admin'){
    header('Location: index.php');
    exit;
}
 
$message='';
$upload_dir='imagens-produtos/';
$nome = $preco_str = $categoria = $descricao = '';
$quantidade = 0;

// ------------------------------------
// 4. Processamento do Formulário (POST com Upload)
// ------------------------------------
if($_SERVER['REQUEST_METHOD']==='POST'){
    $nome=trim($_POST['n_produto']??'');
    $preco_str=trim($_POST['preco']??'');
    $quantidade=filter_var($_POST['quantidade']??0,FILTER_VALIDATE_INT);
    $categoria=trim($_POST['categoria']??'');
    $descricao=trim($_POST['descricao']??'');
    $tamanhos_selecionados = $_POST['tamanhos_roupa'] ?? [];
    $imagem_url=null;
 
    // Tratamento do Preço (BR para float)
    $preco_limpo=str_replace('.','',$preco_str);
    $preco_final=str_replace(',','.',$preco_limpo);
    $preco=filter_var($preco_final,FILTER_VALIDATE_FLOAT);
 
    // Validação
    if(empty($nome) || $preco===false || $preco<=0 || $quantidade===false || $quantidade<0 || empty($categoria)){
        $message='<p class="error-msg">❌ Erro: Preencha todos os campos obrigatórios (Nome, Preço e Categoria).</p>';
    } else {
        // --- Processamento do Upload da Imagem ---
        if(isset($_FILES['imagem']) && $_FILES['imagem']['error']===UPLOAD_ERR_OK){
            $file_tmp_path=$_FILES['imagem']['tmp_name'];
            $file_name=$_FILES['imagem']['name'];
            $file_extension=strtolower(pathinfo($file_name,PATHINFO_EXTENSION));
            $allowed_ext=['jpg','jpeg','png','webp'];
 
            if(in_array($file_extension,$allowed_ext)){
                $new_file_name=uniqid('prod_', true).'.'.$file_extension;
                $dest_path=$upload_dir.$new_file_name;
 
                if(move_uploaded_file($file_tmp_path,$dest_path)){
                    $imagem_url=$upload_dir.$new_file_name;
                }else{
                    $message='<p class="error-msg">❌ Erro ao mover o arquivo de imagem. Verifique as permissões da pasta '.$upload_dir.'</p>';
                }
            }else{
                $message='<p class="error-msg">❌ Tipo de arquivo de imagem não permitido. Use JPG, PNG ou WEBP.</p>';
            }
        }else{
             $message='<p class="error-msg">❌ Erro no upload da imagem ou nenhuma imagem selecionada.</p>';
        }
 
        // Se o upload falhou ou a mensagem de erro já foi definida, não insere no BD
        if($imagem_url!==null && empty($message)){
            try{
                $final_descricao = $descricao;
                if ($categoria === 'Moletons' || $categoria === 'Camisetas') {
                    if (!empty($tamanhos_selecionados)) {
                        $tamanhos_str = implode(', ', array_map('htmlspecialchars', $tamanhos_selecionados));
                        $final_descricao .= "\n\nTamanhos disponíveis: " . $tamanhos_str;
                    } else {
                        $message .= '<p class="warning-msg">⚠️ Atenção: Categoria "Roupas" selecionada, mas nenhum tamanho foi marcado.</p>';
                    }
                }
 
                $stmt=$pdo->prepare("INSERT INTO produto (n_produto, preco, quantidade, imagem_url, categoria, descricao)
                                     VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome,$preco,$quantidade,$imagem_url,$categoria,$final_descricao]);
                $lastId = $pdo->lastInsertId();
                
                $message='<p class="success-msg">✅ Produto "'.htmlspecialchars($nome).'" cadastrado com sucesso! ID Automático: '.$lastId.'</p>';
                
            }catch(\PDOException $e){
                $message='<p class="error-msg">❌ Erro ao cadastrar no BD: '.$e->getMessage().'</p>';
                if(file_exists($dest_path??'')) unlink($dest_path??'');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Criar Produto</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* --- Variáveis e Layout Principal (Coerente com Listas) --- */
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

        /* --- Botão de Escolher Arquivo (Estilizado) --- */
        .custom-file-upload {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
            width: 100%;
            border: 1px solid var(--color-border-light);
            border-radius: 6px;
            background-color: var(--color-table-bg);
            padding: 5px;
        }

        /* Esconde o input original */
        .custom-file-upload input[type="file"] {
            display: none; 
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
        
        /* --- Header Top (Header Principal) --- */
        .admin-top-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px; 
            background-color: var(--color-table-bg);
            border-bottom: 1px solid var(--color-border-light); /* Linha divisória coesa */
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
        .admin-sub-menu {
            display: flex;
            gap: 25px; 
            align-items: center;
            padding: 15px 30px;
            background-color: #f0f0f0; /* Fundo mais claro para coesão */
            margin-bottom: 30px; 
            position: relative; 
        }

        /* Linha de destaque rosa */
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

        /* --- 2. ESTILO DO FORMULÁRIO CONTAINER (Card Centralizado) --- */
        .form-container{
            max-width: 800px;
            margin: 0 auto 50px auto; 
            padding: 40px; 
            background: var(--color-table-bg);
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); 
        }
        
        .form-container h2 {
            font-size: 1.5em;
            color: var(--color-primary-pink);
            border-bottom: 1px solid var(--color-border-light);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 30px;
        }

        /* Estilo dos Grupos e Inputs */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--color-dark-text);
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea,
        .form-group select,
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            border-color: var(--color-primary-pink);
            box-shadow: 0 0 5px rgba(255, 51, 102, 0.3);
            outline: none;
        }

        /* Botão de Envio */
        .submit-btn {
            background-color: var(--color-primary-pink);
            color: white;
            padding: 14px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        
        .submit-btn:hover {
            background-color: #d92452;
        }
        
        /* Mensagens */
        .error-msg{color:var(--color-danger);background-color:#f8d7da;border:1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px;}
        .success-msg{color:var(--color-success);background-color:#d4edda;border:1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px;}
        .warning-msg { color: #8a6d3b; background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 8px; margin-bottom: 20px;}

        /* Checkboxes de Tamanho */
        .tamanho-checkboxes { 
            display: flex; flex-wrap: wrap; 
            gap: 20px; 
            margin-top: 10px;
        }
        .tamanho-checkboxes label { 
            display: inline-flex; 
            align-items: center; 
            cursor: pointer; 
            font-weight: normal;
        }
        .tamanho-checkboxes input[type="checkbox"] {
            margin-right: 8px;
            width: auto;
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
 
    <main class="content admin-content-wrapper">
        <header class="admin-top-nav">
            <h1>Criar Produto</h1>
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
 
        <div class="form-container">
            <h2><i class="fas fa-box-open"></i> Detalhes do Novo Produto</h2>
            <?php echo $message; // Exibe a mensagem de status ?>
            
            <form action="admin-criar-produto.php" method="POST" enctype="multipart/form-data">
 
                <div class="form-group">
                    <label for="n_produto">Nome do Produto:</label>
                    <input type="text" id="n_produto" name="n_produto" required value="<?php echo htmlspecialchars($nome ?? ''); ?>">
                </div>
 
                <div class="form-group">
                    <label for="categoria">Categoria: <span style="color:red">*</span></label>
                    <select id="categoria" name="categoria" required>
                        <option value="">-- Selecione a Categoria --</option>
                        <?php foreach ($categorias_fixas as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>"
                                <?php echo (isset($categoria) && $categoria === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
 
                <div class="form-group" id="tamanhos-roupa-group" style="display: none;">
                    <label>Tamanhos Disponíveis:</label>
                    <div class="tamanho-checkboxes">
                        <?php foreach ($tamanhos_disponiveis as $tamanho): ?>
                            <label>
                                <input type="checkbox" name="tamanhos_roupa[]" value="<?php echo $tamanho; ?>">
                                <?php echo $tamanho; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small>Selecione os tamanhos que este produto de roupa/calçado possui.</small>
                </div>
                <div class="form-group">
                    <label for="preco">Preço (R$ 00,00):</label>
                    <input type="text" id="preco" name="preco" placeholder="Ex: 49,90" required value="<?php echo htmlspecialchars($preco_str ?? ''); ?>">
                </div>
 
                <div class="form-group">
                    <label for="quantidade">Quantidade em Estoque:</label>
                    <input type="number" id="quantidade" name="quantidade" min="0" required value="<?php echo htmlspecialchars($quantidade ?? 0); ?>">
                </div>
                
                <div class="form-group">
                    <label for="descricao">Descrição Detalhada do Produto:</label>
                    <textarea id="descricao" name="descricao" rows="5" placeholder="Digite uma descrição completa do produto."><?php echo htmlspecialchars($descricao ?? ''); ?></textarea>
                </div>
 
                <div class="form-group">
                    <label for="imagem">Imagem do Produto (PNG/JPG): <span style="color:red">*</span></label>
                    <input type="file" id="imagem" name="imagem" accept=".jpg, .jpeg, .png, .webp" required>
                    <small>A imagem será salva na pasta `<?php echo $upload_dir; ?>` com um nome único.</small>
                </div>
 
                <button type="submit" class="submit-btn"><i class="fas fa-save"></i> Salvar Produto</button>
            </form>
        </div>
    </main>
    
    <div id="cart-modal" class="modal">...</div>
    <div id="login-modal" class="modal">...</div>
 
    <script>
        // LÓGICA JAVASCRIPT PARA MOSTRAR/ESCONDER O CAMPO DE TAMANHO
        document.addEventListener('DOMContentLoaded', function() {
            const categoriaSelect = document.getElementById('categoria');
            const tamanhosGroup = document.getElementById('tamanhos-roupa-group');
            
            const categoriasComTamanho = ['Moletons', 'Camisetas'];
 
            function toggleTamanhos() {
                const selectedCategory = categoriaSelect.value;
                if (categoriasComTamanho.includes(selectedCategory)) {
                    tamanhosGroup.style.display = 'block';
                } else {
                    tamanhosGroup.style.display = 'none';
                }
            }
 
            categoriaSelect.addEventListener('change', toggleTamanhos);
            toggleTamanhos();
        });
    </script>
 
    <script src="home.js"></script>
    <script src="admin.js"></script>
</body>
</html>