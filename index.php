<?php
// ==========================================================
// index.php: Inicia a sessão, conecta ao BD e exibe produtos
// ==========================================================
require_once 'config.php'; 

// --- 1. Lógica de Login e Sessão ---
// Variáveis $logado, $nome_usuario, $nivel_usuario já carregadas do config.php

// --- 2. Exibe Mensagem de Sucesso (após criação de produto) ---
if (isset($_GET['status']) && $_GET['status'] === 'produto_criado') {
    $produto_nome_sucesso = htmlspecialchars($_GET['nome'] ?? 'Novo Produto');
    $message = '<div class="success-alert">✅ Produto **' . $produto_nome_sucesso . '** cadastrado com sucesso!</div>';
    // Remove os parâmetros GET da URL para evitar reenvio ao recarregar
    echo '<script>history.replaceState({}, document.title, "index.php");</script>';
} else {
    $message = '';
}


// --- 3. Conexão com o Banco de Dados e Busca de Produtos ---
$produtos_bd = []; // Array que armazenará todos os produtos
$produtos_destaque = []; // Array para os 2 produtos em destaque na promoção

try {
    $pdo = getPdoConnection(); // Usa a função do config.php
    
    // Busca TODOS os produtos ativos (ou com quantidade > 0)
    $stmt_produtos = $pdo->query("SELECT * FROM produto WHERE quantidade > 0 ORDER BY id_produto DESC");
    $produtos_bd = $stmt_produtos->fetchAll();

    // Busca os 2 produtos mais recentes para o destaque da promoção
    $stmt_destaque = $pdo->query("SELECT * FROM produto WHERE quantidade > 0 ORDER BY id_produto DESC LIMIT 2");
    $produtos_destaque = $stmt_destaque->fetchAll();

} catch (\PDOException $e) {
    // Em um ambiente de produção, logar o erro.
    // die("Erro ao carregar produtos do banco de dados: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Loja Online</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    <link rel="stylesheet" href="index.css" /> 
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
    <style>
        /* ESTILOS EXISTENTES */
        :root {
            --cor-principal: #df2356;
            --cor-secundaria: #c71e4d;
        }

        .success-alert {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin: 20px auto 0 auto;
            max-width: 90%;
            text-align: center;
        }

        /* ---------------------------------------------------- */
        /* NOVOS ESTILOS PARA PROMOÇÃO E DESTAQUE */
        /* ---------------------------------------------------- */

        .promocao-destaque {
            max-width: 95%;
            margin: 20px auto;
            display: flex;
            gap: 15px;
            align-items: stretch;
        }

        .promocao-destaque .banner-principal {
            flex: 2; /* Ocupa 50% ou mais */
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            /* Garantir que o banner principal tenha uma altura definida ou que os itens laterais o definam */
            min-height: 250px;
        }
        .promocao-destaque .banner-principal img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .promocao-destaque .produtos-laterais {
            flex: 1; /* Ocupa o espaço restante */
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Card Principal da Promoção - ESTILOS APERFEIÇOADOS */
        .promocao-card {
            flex: 1;
            background: linear-gradient(135deg, #ffffff, #f0f0f0); /* Fundo sutil gradiente */
            border: 1px solid #eee;
            border-radius: 12px; /* Cantos mais arredondados */
            padding: 15px; /* Mais espaço interno */
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); /* Sombra mais destacada */
            text-decoration: none;
            color: #333;
            display: flex;
            align-items: center;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .promocao-card:hover {
            transform: translateY(-5px); /* Efeito de elevação mais notável */
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            background-color: #fff;
        }
        .promocao-card:hover::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 3px solid var(--cor-principal);
            border-radius: 12px;
            opacity: 0.5;
            pointer-events: none;
        }
        
        /* Imagem do Produto */
        .promocao-card .card-imagem {
            width: 100px; /* Aumenta a imagem */
            height: 100px; /* Aumenta a imagem */
            flex-shrink: 0;
            margin-right: 15px;
            overflow: hidden;
            border-radius: 8px;
            background-color: #f7f7f7;
            border: 1px solid #ddd;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .promocao-card .card-imagem img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s;
        }
        .promocao-card:hover .card-imagem img {
            transform: scale(1.05); /* Zoom sutil na imagem no hover */
        }

        /* Informações */
        .promocao-card .card-info {
            display: flex;
            flex-direction: column;
            justify-content: center;
            flex-grow: 1;
        }
        .promocao-card .card-nome {
            font-size: 1.1em; /* Nome maior */
            font-weight: 700;
            color: #222;
            max-height: 2.8em;
            overflow: hidden;
            margin-bottom: 5px;
            line-height: 1.2;
        }
        /* Preço */
        .promocao-card .card-preco {
            font-size: 1.5em; /* Preço muito maior para destaque */
            font-weight: 900;
            color: var(--cor-principal);
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Tag de Desconto (o maior destaque visual) */
        .promocao-card .tag-desconto {
            position: absolute;
            top: 0; /* Coloca no canto superior */
            right: 0;
            background-color: var(--cor-principal);
            color: white;
            font-size: 0.85em;
            font-weight: bold;
            padding: 8px 15px 8px 10px; /* Padding ajustado */
            border-radius: 0 12px 0 8px; /* Ajusta o arredondamento para o canto */
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        /* Responsividade */
        @media (max-width: 900px) {
            .promocao-destaque {
                flex-direction: column;
            }
            .promocao-destaque .produtos-laterais {
                flex-direction: row;
            }
            .promocao-card {
                flex: 1; /* Permite que os cards laterais se dividam em linha */
                padding: 10px;
                border-radius: 8px;
            }
            .promocao-card .card-imagem {
                width: 60px;
                height: 60px;
            }
            .promocao-card .card-nome {
                font-size: 0.9em;
            }
            .promocao-card .card-preco {
                font-size: 1.2em;
            }
            .promocao-card .tag-desconto {
                padding: 5px 10px 5px 8px;
                font-size: 0.75em;
            }
        }
        @media (max-width: 600px) {
            .promocao-destaque .produtos-laterais {
                flex-direction: column;
            }
        }
    </style>
</head>

<body> 
<div class="bg-pattern"></div>

<?php 
// ==========================================================
// LÓGICA CONDICIONAL DA SIDEBAR (MANTIDA)
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
        
        <?php echo $message; // Exibe a mensagem de sucesso aqui ?>

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

        <section class="banner">
            <img src="imagem_fundo\banner2.png" alt="Banner Principal">
        </section> 

        <div class="promocao-destaque">
            <div class="banner-principal">
                <img src="imagems\banner\Black f.gif" alt="Promoção Secundária: Destaque de Produtos">
                </div>

            <div class="produtos-laterais">
                <?php if (count($produtos_destaque) >= 2): ?>
                    <a href="produtos/detalhes.php?id=<?php echo $produtos_destaque[0]['id_produto']; ?>" class="promocao-card">
                        <span class="tag-desconto">-50%</span>
                        <div class="card-imagem">
                            <img src="<?php echo htmlspecialchars($produtos_destaque[0]['imagem_url']); ?>" alt="<?php echo htmlspecialchars($produtos_destaque[0]['n_produto']); ?>">
                        </div>
                        <div class="card-info">
                            <span class="card-nome"><?php echo htmlspecialchars($produtos_destaque[0]['n_produto']); ?></span>
                            <span class="card-preco">R$ <?php echo formatarPreco($produtos_destaque[0]['preco'] / 2); ?></span>
                        </div>
                    </a>

                    <a href="produtos/detalhes.php?id=<?php echo $produtos_destaque[1]['id_produto']; ?>" class="promocao-card">
                        <span class="tag-desconto">-30%</span>
                        <div class="card-imagem">
                            <img src="<?php echo htmlspecialchars($produtos_destaque[1]['imagem_url']); ?>" alt="<?php echo htmlspecialchars($produtos_destaque[1]['n_produto']); ?>">
                        </div>
                        <div class="card-info">
                            <span class="card-nome"><?php echo htmlspecialchars($produtos_destaque[1]['n_produto']); ?></span>
                            <span class="card-preco">R$ <?php echo formatarPreco($produtos_destaque[1]['preco'] * 0.7); ?></span>
                        </div>
                    </a>
                <?php else: ?>
                    <p style="text-align: center; color: #777; padding: 20px; border: 1px dashed #ccc; border-radius: 8px;">Adicione mais produtos para preencher a área de destaque!</p>
                <?php endif; ?>
            </div>
        </div>
        <section class="produtos">
            <?php foreach ($produtos_bd as $produto): 
                // Formata o preço para o data-price (sem separador de milhar, com ponto decimal)
                $data_price = number_format($produto['preco'], 2, '.', '');
                // Formata o preço para exibição (com ponto de milhar opcional e vírgula decimal)
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
                   </section>
        </section>
    </main>

<div id="cart-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="cart-title"><i class="fas fa-shopping-cart"></i> Seu Carrinho</h2>
            
            <button class="close-btn" aria-label="Fechar Modal">&times;</button>
        </div>
        
        <div id="cart-items" class="cart-items-list">
            <p id="empty-cart-message" style="text-align: center; color: #555; margin-top: 20px;">Seu carrinho está vazio. Adicione alguns produtos!</p>
        </div>

        <div class="cart-footer">
            <div class="cart-total">
                <span>Total:</span>
                <span id="cart-total-value">R$ 0,00</span>
            </div>
            <button class="checkout-btn">Finalizar Compra</button>
        </div>
    </div>
</div>
    
    <div id="login-modal" class="modal">
        <div class="modal-content login-content">
            <span class="close-login-btn">&times;</span>
            <div class="login-form-container">
                <img src="imagems/logos/login.png" alt="Mascote Login" class="login-mascote">
                
                <h2 class="login-title">Login</h2>
    
                <form action="processa_login.php" method="POST"> 
                    <label for="email">Email ou telefone</label>
                    <input type="text" id="email_login" name="email" placeholder="Email ou telefone" required> 
                    
                    <label for="password">Senha</label>
                    <div class="password-container">
                        <input type="password" id="password_login" name="senha" placeholder="Digite sua senha" required> 
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    
                    <div class="login-options">
                    </div>
                    <button type="submit" class="sign-in-btn">Entrar</button>
                </form>
                
                <button class="google-sign-in-btn">
                    <i class="fab fa-google"></i> Ou entre com Google
                </button>
    
                <p style="text-align: center; margin-top: 20px; font-size: 0.9em;">
                    Não tem conta?
                    <a href="cadastro.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">Cadastre-se aqui</a>
                </p>
            </div>
        </div>
    </div>

    <div id="detalhes-modal" class="modal">
    <div class="modal-content detalhes-content">
        <span class="close-btn">&times;</span>
        <div class="detalhes-container">
            <div class="detalhes-left">
                <img id="detalhes-img" src="" alt="Imagem do Produto">
                <div class="detalhes-acoes">
                    <button class="favoritar" id="detalhes-favoritar" onclick="toggleFavorite(this)"><i class="fas fa-heart"></i> Favoritar</button>
                    <button class="comprar" onclick="shareProduct()"><i class="fas fa-share-alt"></i> Compartilhar</button>
                </div>
            </div>
            <div class="detalhes-right">
                <h2 id="detalhes-nome">Nome do Produto</h2>
                <span class="detalhes-preco" id="detalhes-preco">R$ 0,00</span>
                
                <p class="detalhes-descricao-titulo">Descrição:</p>
                <p id="detalhes-descricao">Descrição detalhada do produto...</p>

                <div class="meta-info">
                    <p><strong>Categoria:</strong> <span id="detalhes-categoria">N/D</span></p>
                    <p><strong>Material:</strong> <span id="detalhes-material">N/D</span></p>
                    <p><strong>Dimensões:</strong> <span id="detalhes-dimensoes">N/D</span></p>
                    <p style="font-size: 0.8em; opacity: 0.6;"><strong>ID:</strong> <span id="detalhes-id"></span></p>
                    <p style="font-size: 0.8em; opacity: 0.6;"><strong>Estoque:</strong> <span id="detalhes-quantidade"></span></p>
                </div>

                <div class="detalhes-add">
                    <button id="detalhes-add-to-cart" class="add-to-cart-btn"><i class="fas fa-cart-plus"></i> Adicionar ao Carrinho</button>
                </div>
            </div>
        </div>
    </div>
</div>
    
    <script src="home.js"></script>
</body>
</html>