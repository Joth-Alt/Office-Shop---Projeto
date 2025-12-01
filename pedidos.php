<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Pedidos</title>
    
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
            --color-success: #28a745;
            --color-warning: #ffc107;
            --color-danger: #dc3545;
        }

        body {
            display: flex; /* CHAVE: Coloca a sidebar e o main lado a lado */
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

        /* --- Estilos do Menu Secundário e Título --- */
        .admin-sub-menu {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            margin-bottom: 30px;
            border-bottom: 2px solid var(--color-primary-pink);
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

        /* --- 1. Estilo do Contêiner da Tabela (Seção Bonitinha) --- */
        .pedidos-table-container {
            background-color: var(--color-table-bg);
            padding: 25px;
            margin: 20px 0;
            border-radius: 12px; 
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); 
            overflow-x: auto; 
        }

        /* --- 2. Estilo Geral da Tabela --- */
        .pedidos-table-container table {
            width: 100%;
            border-collapse: collapse; 
            table-layout: fixed; /* Força o navegador a respeitar as larguras */
            border: none;
        }

        /* 3. Estilo de Células e Cabeçalho */
        .pedidos-table-container th,
        .pedidos-table-container td {
            border: none; 
            border-bottom: 1px solid var(--color-border-light); /* Divisor horizontal */
            padding: 12px 10px; /* Mais espaço interno */
            vertical-align: top; /* Mantido TOP, para listas longas */
            font-size: 13px;
            color: var(--color-dark-text);
            word-wrap: break-word;
        }
        .pedidos-table-container tbody tr:last-child td { border-bottom: none; }

        .pedidos-table-container thead th {
            background-color: var(--color-table-bg); 
            font-weight: 600;
            text-align: center; 
            text-transform: uppercase;
            color: #888; 
            padding: 15px 10px;
        }
        
        /* Efeito Zebrado e Hover */
        .pedidos-table-container tbody tr:nth-child(even) { background-color: rgba(255, 51, 102, 0.03); }
        .pedidos-table-container tbody tr:hover { background-color: rgba(255, 51, 102, 0.1); transition: background-color 0.2s; }

        /* 4. DEFINIÇÃO DAS LARGURAS DAS 11 COLUNAS (Ajuste fino) */
        /* A soma das larguras deve ser 100% */
        
        .pedidos-table-container th:nth-child(1), .pedidos-table-container td:nth-child(1) { width: 5%; text-align: center; font-weight: bold; } /* ID Pedido */
        .pedidos-table-container th:nth-child(2), .pedidos-table-container td:nth-child(2) { width: 7%; text-align: center; } /* Data */
        .pedidos-table-container th:nth-child(3), .pedidos-table-container td:nth-child(3) { width: 5%; text-align: center; } /* ID Cliente */
        .pedidos-table-container th:nth-child(4), .pedidos-table-container td:nth-child(4) { width: 5%; text-align: center; } /* Avatar */
        .pedidos-table-container th:nth-child(5), .pedidos-table-container td:nth-child(5) { width: 8%; } /* Cliente */
        .pedidos-table-container th:nth-child(6), .pedidos-table-container td:nth-child(6) { width: 5%; text-align: center; } /* IDs Prod. (Reduzido) */
        .pedidos-table-container th:nth-child(7), .pedidos-table-container td:nth-child(7) { width: 15%; } /* Nomes Produtos */
        .pedidos-table-container th:nth-child(8), .pedidos-table-container td:nth-child(8) { width: 15%; text-align: center; } /* Imagens Produtos */
        .pedidos-table-container th:nth-child(9), .pedidos-table-container td:nth-child(9) { width: 10%; text-align: right; font-weight: bold; } /* Preço Total */
        .pedidos-table-container th:nth-child(10), .pedidos-table-container td:nth-child(10) { width: 10%; text-align: center; } /* Pagamento */
        .pedidos-table-container th:nth-child(11), .pedidos-table-container td:nth-child(11) { width: 10%; text-align: center; } /* Status */


        /* 5. Estilos de Imagens e Status */
        .avatar-img { 
            width: 40px; height: 40px; border-radius: 50%; object-fit: cover; 
            border: 2px solid var(--color-primary-pink);
            display: block; margin: 0 auto; 
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .produto-mini-img { 
            width: 45px; height: 45px; object-fit: cover; /* Reduzido levemente */
            margin: 2px; 
            border: 1px solid var(--color-border-light);
            border-radius: 4px;
        }
        .img-container { 
            display: flex; flex-wrap: wrap; 
            justify-content: center; 
            align-items: flex-start; 
        }

        /* Estilos para Tags de Status */
        .status-enviado, .status-completo { background-color: var(--color-success); color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600; }
        .status-pendente, .status-processando { background-color: var(--color-warning); color: #333; padding: 4px 8px; border-radius: 4px; font-weight: 600; }
        .status-cancelado, .status-falha { background-color: var(--color-danger); color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600; }

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
            <a href="editar-produto.php"><i class="fas fa-pen"></i> Editar Produto</a>x
            </nav>

        <h1>Lista de Pedidos</h1>
    
        <div class="pedidos-table-container">
            <?php
            // ATENÇÃO: Seu código de conexão e consulta SQL deve estar funcionando.
            // Para evitar o erro de variável $conn indefinida, inclua a conexão aqui:
            include 'conexao.php'; 

            // --- FIM DO BLOCO DE SIMULAÇÃO ---
            
            // Verifica se a conexão MySQLi existe antes de tentar a consulta
            if (isset($conn) && class_exists('mysqli')) {
                
                $sql = "SELECT p.id_pedido, p.data_p, u.id_usuario, u.n_usuario, u.avatar_url, p.forma_pag, p.status, SUM(ip.quantidade * ip.preco) AS preco_total, GROUP_CONCAT(prod.id_produto SEPARATOR ', ') AS ids_produtos, GROUP_CONCAT(prod.n_produto SEPARATOR ', ') AS nomes_produtos, GROUP_CONCAT(prod.imagem_url SEPARATOR '|||') AS imagens_urls_raw FROM pedidos p JOIN usuarios u ON p.id_usuario = u.id_usuario LEFT JOIN itens_pedido ip ON p.id_pedido = ip.id_pedido LEFT JOIN produto prod ON ip.id_produto = prod.id_produto GROUP BY p.id_pedido, u.id_usuario, p.forma_pag, p.status, u.n_usuario, u.avatar_url, p.data_p ORDER BY p.data_p DESC";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<thead><tr>
                            <th>ID Pedido</th>
                            <th>Data</th>
                            <th>ID Cliente</th>
                            <th>Avatar</th>
                            <th>Cliente</th>
                            <th>IDs Prod.</th>
                            <th>Nomes Produtos</th>
                            <th>Imagens</th>
                            <th>Total</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                          </tr></thead>";
                    echo "<tbody>";
                    
                    while($row = $result->fetch_assoc()) {
                        
                        // Formatando data
                        $data_formatada = (new DateTime($row["data_p"]))->format('d/m/Y');
                        
                        $avatar = !empty($row["avatar_url"])
                                  ? '<img src="' . htmlspecialchars($row["avatar_url"]) . '" alt="Avatar" class="avatar-img">'
                                  : 'N/A';
            
                        $ids_produtos = htmlspecialchars($row["ids_produtos"]);
                        $nomes_produtos = nl2br(htmlspecialchars($row["nomes_produtos"])); 
                        
                        $imagens_produtos_html = '<div class="img-container">';
                        if (!empty($row["imagens_urls_raw"])) {
                            $urls_imagens = explode('|||', $row["imagens_urls_raw"]);
                            foreach ($urls_imagens as $url) {
                                if (!empty($url)) {
                                    $imagens_produtos_html .= '<img src="' . htmlspecialchars($url) . '" alt="Produto" class="produto-mini-img">';
                                }
                            }
                        } else {
                            $imagens_produtos_html .= 'N/A';
                        }
                        $imagens_produtos_html .= '</div>';
                        
                        // Estilização do Status (usa as classes definidas no CSS)
                        $status_class = strtolower(str_replace(' ', '-', $row["status"]));
                        $status_display = "<span class='status-{$status_class}'>" . htmlspecialchars($row["status"]) . "</span>";
                        
                        echo "<tr>";
                        echo "<td>" . $row["id_pedido"] . "</td>";
                        echo "<td>" . $data_formatada . "</td>";
                        echo "<td>" . $row["id_usuario"] . "</td>";
                        echo "<td>" . $avatar . "</td>";
                        echo "<td>" . htmlspecialchars($row["n_usuario"]) . "</td>";
                        echo "<td>" . $ids_produtos . "</td>";
                        echo "<td>" . $nomes_produtos . "</td>";
                        echo "<td>" . $imagens_produtos_html . "</td>";
                        echo "<td style='text-align: right;'>R$ " . number_format($row["preco_total"], 2, ',', '.') . "</td>";
                        echo "<td>" . htmlspecialchars($row["forma_pag"]) . "</td>";
                        echo "<td>" . $status_display . "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody></table>";
                } else {
                    echo "<p>Nenhum pedido encontrado.</p>";
                }
            
                $conn->close();
            } else {
                echo "<p>Erro: Variável de conexão com o banco de dados (\$conn) não definida. Verifique seu arquivo 'conexao.php'.</p>";
            }
            ?>
        </div>
    </main>
</body>
</html>