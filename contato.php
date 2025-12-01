<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Office Shop - Contato | Chat Bot</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/dark-mode-vibrante.css" /> 
    <link rel="stylesheet" href="css/modal.css" /> 
    
    <link rel="stylesheet" href="contato.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
    
    <style>
        /* * ESTILOS DO BODY E SIDEBAR PARA REPETIR O LAYOUT DE 240PX
         * Estes estilos deveriam estar no seu CSS base, mas são mantidos aqui 
         * para garantir que o layout de 240px funcione.
         */
        body {
            margin: 0;
            padding: 0;
            display: flex; 
            min-height: 100vh;
            /* ESPAÇO PARA A SIDEBAR COMPLETA (240px) */
            padding-left: 240px; 
            overflow-x: hidden; 
            /* Estilo de fundo do seu site (mantido do exemplo) */
            background: linear-gradient(to top, #FF0059, #D40F54); 
            background-image: url('imagems/background/patternpretto.webp'); 
            background-size: 80px 80px;
        }

        .sidebar {
            position: fixed; 
            z-index: 1000; 
            left: 0;
            top: 0;
            width: 240px; /* Sua largura padrão */
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

    <main id="main-content" class="chat-page-content"> 
        <div class="chat-main-container">
            <div class="chat-header">
                <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i></a>
                <div class="header-info">
                    <h4>atendente</h4>
                    <p class="online-status"><span class="status-dot"></span> Online</p>
                </div>
            </div>

            <div class="chat-messages" id="chat-messages">
                <div class="message bot-message">
                    Conectando ao chat bot...
                </div>
            </div>

            <div class="chat-input-area">
                <input type="text" id="user-input" placeholder="escreva sua Mensagem">
                <button id="send-btn" title="Enviar Mensagem"><i class="fas fa-paper-plane"></i></button>
                <button id="mic-btn" title="Falar (Voz)"><i class="fas fa-microphone"></i></button>
            </div>
        </div>
    </main>

    <div id="login-modal" class="modal">
        <div class="modal-content">
            <span class="close-btn close-login-btn">&times;</span>
            </div>
    </div>

    <div id="cart-modal" class="modal">
        <div class="modal-content">
            </div>
    </div>

    <script src="home.js"></script> 
    <script src="contato.js"></script> 
</body>
</html>