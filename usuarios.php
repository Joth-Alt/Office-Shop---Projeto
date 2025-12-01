<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Usuários</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/admin.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="icon" href="imagems/logos/logo.png" type="image/png">
    
<style>
    /* --- Variáveis e Layout Principal (Mantenha o layout de duas colunas) --- */
    :root {
        --sidebar-width: 250px; 
        --color-primary-pink: #ff3366; /* Rosa vibrante para destaque */
        --color-accent-blue: #3498db; /* Azul suave para contraste (opcional) */
        --color-dark-text: #4a4a4a; 
        --color-light-bg: #f5f7f9; /* Fundo principal mais suave */
        --color-table-bg: #ffffff; /* Fundo da tabela branco */
        --color-border-light: #eeeeee;
    }

    body {
        display: flex;
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
        font-weight: 300; /* Fonte mais fina para um visual moderno */
        border-left: 5px solid var(--color-primary-pink); /* Detalhe lateral */
        padding-left: 10px;
    }

    /* --- 1. Estilo do Contêiner da Tabela (A Seção Bonitinha) --- */
    .usuarios-table-container {
        background-color: var(--color-table-bg);
        padding: 25px;
        margin: 20px 0;
        border-radius: 12px; /* Bordas mais suaves */
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); /* Sombra mais profunda e suave */
        overflow-x: auto; 
    }

    /* --- 2. Estilo Geral da Tabela --- */
    .usuarios-table-container table {
        width: 100%;
        border-collapse: collapse; 
        table-layout: fixed; 
        border: none;
    }

    /* 3. Estilo de Células e Cabeçalho */
    .usuarios-table-container th,
    .usuarios-table-container td {
        border: none; /* Remove as bordas feias */
        border-bottom: 1px solid var(--color-border-light); /* Adiciona divisores horizontais leves */
        padding: 15px 10px; /* Mais espaço interno */
        vertical-align: middle; 
        font-size: 14px;
        color: var(--color-dark-text);
        height: 70px;
    }
    .usuarios-table-container tbody tr:last-child td {
        border-bottom: none; /* Remove a linha da última célula */
    }

    .usuarios-table-container thead th {
        background-color: var(--color-table-bg); /* Cabeçalho transparente/branco */
        font-weight: 600;
        text-align: center; 
        text-transform: uppercase;
        color: #888; /* Texto cinza claro no cabeçalho */
    }

    /* Efeito Zebrado (Opção 1: Linhas Pares com cor de fundo) */
    .usuarios-table-container tbody tr:nth-child(even) {
        background-color: rgba(255, 51, 102, 0.03); /* Rosa muito claro */
    }
    /* Efeito Hover */
    .usuarios-table-container tbody tr:hover {
        background-color: rgba(255, 51, 102, 0.1); /* Rosa mais forte ao passar o mouse */
        transition: background-color 0.2s;
    }

    /* 4. DEFINIÇÃO DAS LARGURAS E ALINHAMENTO POR COLUNA */
    .usuarios-table-container th:nth-child(1), .usuarios-table-container td:nth-child(1) { width: 5%; text-align: center; font-weight: bold; } /* ID */
    .usuarios-table-container th:nth-child(2), .usuarios-table-container td:nth-child(2) { width: 8%; text-align: center; } /* Avatar */
    .usuarios-table-container th:nth-child(3), .usuarios-table-container td:nth-child(3) { width: 12%; text-align: left; } /* Nome */
    .usuarios-table-container th:nth-child(4), .usuarios-table-container td:nth-child(4) { width: 25%; text-align: left; } /* Email */
    .usuarios-table-container th:nth-child(5), .usuarios-table-container td:nth-child(5) { width: 10%; text-align: center; } /* CPF */
    .usuarios-table-container th:nth-child(6), .usuarios-table-container td:nth-child(6) { width: 10%; text-align: center; } /* Telefone */
    .usuarios-table-container th:nth-child(7), .usuarios-table-container td:nth-child(7) { width: 30%; text-align: left; } /* Endereço */


    /* 5. Estilo do Avatar (Usando sua classe original ".avatar") */
    .avatar { 
        width: 50px; height: 50px; border-radius: 50%; object-fit: cover; 
        border: 2px solid var(--color-primary-pink); /* Borda de destaque */
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        display: block; margin: 0 auto; 
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
    
    <h1>Lista de Usuários</h1>

    <div class="usuarios-table-container">
        <?php
        // 🚨 CORREÇÃO: Esta linha DEVE ser descomentada para criar a variável $conn
        include 'conexao.php'; 

        // Linha 175 no seu erro original (agora ajustada)
        $sql = "SELECT id_usuario, n_usuario, email, CPF_usuario, telefone, endereco, avatar_url FROM usuarios";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<table>";
            echo "<thead><tr><th>ID</th><th>Avatar</th><th>Nome</th><th>Email</th><th>CPF</th><th>Telefone</th><th>Endereço</th></tr></thead>";
            echo "<tbody>";
            
            while($row = $result->fetch_assoc()) {
                $avatar = !empty($row["avatar_url"])
                            ? '<img src="' . htmlspecialchars($row["avatar_url"]) . '" alt="Avatar de ' . htmlspecialchars($row["n_usuario"]) . '" class="avatar">'
                            : 'N/A';
                            
                echo "<tr>";
                echo "<td>" . $row["id_usuario"] . "</td>";
                echo "<td>" . $avatar . "</td>";
                echo "<td>" . htmlspecialchars($row["n_usuario"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["CPF_usuario"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["telefone"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["endereco"]) . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
        } else {
            echo "<p>Nenhum usuário encontrado.</p>";
        }

        // 4. Fecha a conexão
        $conn->close();
        ?>
    </div>
</main>
</body>
</html>