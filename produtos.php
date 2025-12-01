<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Produtos - Painel Admin</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" href="imagems/logos/logo.png" type="image/png">
    
    <style>
        /* --- Variáveis e Layout Principal (Sidebar & Main) --- */
        :root {
            --sidebar-width: 250px; 
            --color-primary-pink: #ff3366; /* Rosa vibrante para destaque */
            --color-dark-text: #4a4a4a; 
            --color-light-bg: #f5f7f9; /* Fundo principal mais suave */
            --color-table-bg: #ffffff; /* Fundo da tabela branco */
            --color-border-light: #eeeeee;
        }

        body {
            display: flex; /* CHAVE: Coloca a sidebar e o main lado a lado */
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background-color: var(--color-light-bg); /* Fundo suave */
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

        /* --- Estilo da Barra Secundária e Título --- */
        .admin-sub-menu {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--color-primary-pink); /* Linha forte como destaque */
            align-items: center;
        }
        .admin-sub-menu a {
            color: var(--color-dark-text);
            text-decoration: none;
            transition: color 0.3s;
        }
        .admin-sub-menu a:hover {
            color: var(--color-primary-pink);
        }
        .admin-sub-menu .active-admin-sub-link {
            color: var(--color-primary-pink);
            font-weight: bold;
        }

        h1 {
            margin-top: 0;
            margin-bottom: 25px;
            color: var(--color-dark-text);
            font-weight: 300; 
            border-left: 5px solid var(--color-primary-pink); 
            padding-left: 10px;
        }

        /* --- 1. Estilo do Contêiner da Tabela (A Seção Bonitinha) --- */
        .produtos-table-container {
            background-color: var(--color-table-bg);
            padding: 25px;
            margin: 20px 0;
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); 
            overflow-x: auto; 
        }

        /* --- 2. Estilo Geral da Tabela --- */
        .produtos-table-container table {
            width: 100%;
            border-collapse: collapse; 
            table-layout: fixed; /* Fixa as larguras */
            border: none;
        }

        /* 3. Estilo de Células e Cabeçalho */
        .produtos-table-container th,
        .produtos-table-container td {
            border: none; 
            border-bottom: 1px solid var(--color-border-light); 
            padding: 15px 10px; 
            vertical-align: middle; 
            font-size: 14px;
            color: var(--color-dark-text);
            min-height: 70px;
        }
        .produtos-table-container tbody tr:last-child td {
            border-bottom: none; 
        }

        .produtos-table-container thead th {
            background-color: var(--color-table-bg); 
            font-weight: 600;
            text-align: left; /* Alinhamento esquerdo para os cabeçalhos de produtos */
            text-transform: uppercase;
            color: #888; 
        }

        /* Efeito Zebrado */
        .produtos-table-container tbody tr:nth-child(even) {
            background-color: rgba(255, 51, 102, 0.03); 
        }
        /* Efeito Hover */
        .produtos-table-container tbody tr:hover {
            background-color: rgba(255, 51, 102, 0.1); 
            transition: background-color 0.2s;
        }

        /* 4. DEFINIÇÃO DAS LARGURAS POR COLUNA (7 Colunas) */
        
        .produtos-table-container th:nth-child(1), .produtos-table-container td:nth-child(1) { width: 5%; text-align: center; font-weight: bold; } /* ID */
        .produtos-table-container th:nth-child(2), .produtos-table-container td:nth-child(2) { width: 10%; text-align: center; } /* Imagem */
        .produtos-table-container th:nth-child(3), .produtos-table-container td:nth-child(3) { width: 20%; } /* Nome */
        .produtos-table-container th:nth-child(4), .produtos-table-container td:nth-child(4) { width: 10%; text-align: right; } /* Preço */
        .produtos-table-container th:nth-child(5), .produtos-table-container td:nth-child(5) { width: 8%; text-align: center; } /* Qtde */
        .produtos-table-container th:nth-child(6), .produtos-table-container td:nth-child(6) { width: 12%; } /* Categoria */
        .produtos-table-container th:nth-child(7), .produtos-table-container td:nth-child(7) { width: 35%; } /* Descrição */


        /* 5. Estilo da Imagem do Produto */
        .produto-img { 
            width: 70px; 
            height: 70px;
            object-fit: cover;
            border-radius: 8px; /* Cantos levemente arredondados */
            border: 1px solid var(--color-border-light);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
    <nav class="admin-sub-menu">
                    <a href="admin.php" class="active-admin-sub-link"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="produtos.php"><i class="fas fa-boxes"></i> Produtos</a>
            <a href="pedidos.php"><i class="fas fa-receipt"></i> Pedidos</a>
            <a href="usuarios.php"><i class="fas fa-users"></i> Clientes</a>
            <a href="lucro.php"><i class="fas fa-chart-bar"></i> Relatório de Lucros</a>
            <a href="admin-criar-produto.php"><i class="fas fa-plus-circle"></i> Novo Produto</a>
            <a href="editar-produto.php"><i class="fas fa-pen"></i> Editar Produto</a>
    </nav>

    <h1>Lista de Produtos</h1>

    <div class="produtos-table-container">
        <?php
        // 🚨 CORREÇÃO: Esta linha deve estar ativa para que $conn exista.
        include 'conexao.php';

        // 🚨 CORREÇÃO SQL: Usando 'id_produto' em vez de 'id' (Linha 207 no seu erro original)
        $sql = "SELECT id_produto, n_produto, preco, quantidade, categoria, descricao, imagem_url FROM produto";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr><th>ID</th><th>Imagem</th><th>Nome</th><th>Preço</th><th>Qtde</th><th>Categoria</th><th>Descrição</th></tr></thead>";
            echo "<tbody>";
            
            while($row = $result->fetch_assoc()) {
                
                $imagem = !empty($row["imagem_url"])
                            ? '<img src="' . htmlspecialchars($row["imagem_url"]) . '" alt="Imagem de ' . htmlspecialchars($row["n_produto"]) . '" class="produto-img">'
                            : 'Sem Imagem';

                echo "<tr>";
                echo "<td>" . $row["id_produto"] . "</td>";
                echo "<td>" . $imagem . "</td>";
                echo "<td>" . htmlspecialchars($row["n_produto"]) . "</td>";
                echo "<td style='text-align: right;'>R$ " . number_format($row["preco"], 2, ',', '.') . "</td>";
                echo "<td>" . htmlspecialchars($row["quantidade"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["categoria"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["descricao"]) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>Nenhum produto encontrado.</p>";
        }

        // 4. Fecha a conexão
        $conn->close();
        ?>
    </div>
</main>
</body>
</html>