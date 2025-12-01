<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Configurações</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    
    <style>
        /* ------------------------------------------------ */
        /* --- VARIÁVEIS DE CORES E BASE DO LAYOUT --- */
        /* ------------------------------------------------ */
        :root {
            --cor-principal: #dc3545; /* Azul vibrante (Usado em botões e títulos) */
            --cor-secundaria: #6c757d; /* Cinza para botões secundários */
            --cor-fundo: #f4f7f9; /* Fundo claro da página */
            --cor-sidebar: #2c3e50; /* Azul escuro para sidebar (Estrutura original) */
            --cor-texto-principal: #333333;
            --cor-card-fundo: #ffffff; /* Fundo do cartão */
            --cor-borda: #e0e0e0; /* Bordas de divisórias */
            --cor-perigo: #dc3545; /* Vermelho para ações destrutivas */
            --cor-link: #dc3545;
            --sombra-card: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Dark Mode */
        .dark-mode {
            --cor-fundo: #121212;
            --cor-texto-principal: #e0e0e0;
            --cor-card-fundo: #242424;
            --cor-borda: #444444;
            --sombra-card: 0 4px 12px rgba(0, 0, 0, 0.3);
            --cor-link: #f15161ff;
        }

        /* ------------------------------------------------ */
        /* --- LAYOUT PRINCIPAL E BARRA SUPERIOR --- */
        /* ------------------------------------------------ */
        body {
            display: flex;
            min-height: 100vh;
            margin: 0;
            font-family: 'Arial', sans-serif;
            background-color: var(--cor-fundo);
            color: var(--cor-texto-principal);
            transition: background-color 0.3s, color 0.3s;
        }


        /* ------------------------------------------------ */
        /* --- LAYOUT DOS CARDS DE CONFIGURAÇÃO --- */
        /* ------------------------------------------------ */

        /* CONTAINER (settings-container) - Usa sua classe original, aplica Grid */
        .settings-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px; 
            padding-bottom: 50px;
        }

        /* SEÇÕES (settings-section) - Usa sua classe original, transforma em Card */
        .settings-section {
            background-color: var(--cor-card-fundo);
            padding: 25px;
            border-radius: 12px;
            box-shadow: var(--sombra-card);
            border: 1px solid var(--cor-borda);
            transition: background-color 0.3s, border-color 0.3s;
        }
        .settings-section h2 {
            color: var(--cor-principal);
            border-bottom: 1px solid var(--cor-borda);
            padding-bottom: 10px;
            margin-top: 0;
            margin-bottom: 20px;
            font-size: 1.4rem;
        }
        .settings-section h2 i {
            margin-right: 10px;
        }

        /* ITENS DE CONFIGURAÇÃO (setting-item) - Usa sua classe original, aplica Flexbox */
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px dashed var(--cor-borda);
        }
        .setting-item:last-child {
            border-bottom: none;
        }
        .setting-item span {
            font-weight: 500;
        }

        /* --- CONTROLES E BOTÕES (Usando suas classes originais) --- */
        
        /* Action buttons (geral) */
        .action-btn {
            padding: 10px 18px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.2s, transform 0.1s;
        }
        

        /* Botão Perigo (delete-btn) */
        .delete-btn {
            background-color: var(--cor-perigo);
            color: white;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }

        /* SELECTS */
        .setting-item select {
            padding: 10px;
            border: 1px solid var(--cor-borda);
            border-radius: 6px;
            min-width: 150px;
            background-color: var(--cor-card-fundo);
            color: var(--cor-texto-principal);
        }

        /* SWITCH (Toggle) - Mantenha o seu estilo original aqui (apenas cores ajustadas) */
        .switch { position: relative; display: inline-block; width: 45px; height: 25px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider {
            position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
            background-color: #ccc; transition: .4s;
        }
        .slider:before {
            position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px;
            background-color: white; transition: .4s;
        }
        input:checked + .slider { background-color: var(--cor-principal); }
        input:checked + .slider:before { transform: translateX(20px); }
        .slider.round { border-radius: 34px; }
        .slider.round:before { border-radius: 50%; }


        /* BARRA DE SALVAR */
        .save-settings {
            grid-column: 1 / -1; 
            padding: 20px 0;
            text-align: center;
        }
        .save-settings .login-btn {
            font-size: 1.2rem;
            padding: 12px 40px;
        }

        /* --- RESPONSIVIDADE (Telas pequenas) --- */
        @media (max-width: 768px) {
            .settings-container {
                grid-template-columns: 1fr; /* Força uma única coluna em telas pequenas */
                gap: 15px;
            }
            .content {
                /* Se a sidebar for escondida ou reduzida em mobile (como é comum), o margin-left deve ser ajustado aqui. */
                margin-left: 0; 
            }
        }
    </style>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="icon" href="imagems/logos/logo.png" type="image/png"> 
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
            <a href="configuracoes.php" class="active-config"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
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

    <main class="content">

        <header class="top-nav">

            <div class="page-title">

                <h1><i class="fas fa-cog"></i> Configurações do Site</h1>

            </div>

            <div class="user-actions">

                <a href="#" id="openLogin" class="login-btn"><i class="fas fa-sign-in-alt"></i> Entrar</a>

            </div>

        </header>

        <section class="settings-container">
            
            <div class="settings-section">
                <h2><i class="fas fa-paint-brush"></i> Aparência</h2>
                <div class="setting-item">
                    <span>Modo Escuro (Toggle)</span>
                    <label class="switch">
                        <input type="checkbox" id="theme-switch" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <span>Tamanho da Fonte</span>
                    <select id="font-size-select">
                        <option value="small">Pequena</option>
                        <option value="medium" selected>Média (Padrão)</option>
                        <option value="large">Grande</option>
                    </select>
                </div>
                <div class="setting-item">
                    <span>Mostrar Animações</span>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <div class="settings-section">
                <h2><i class="fas fa-lock"></i> Segurança da Conta</h2>
                <div class="setting-item">
                    <span>Autenticação de Dois Fatores (2FA)</span>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <span>Alterar Senha</span>
                    <button class="action-btn login-btn"><i class="fas fa-key"></i> Mudar</button>
                </div>
                <div class="setting-item">
                    <span>Dispositivos Conectados</span>
                    <button class="action-btn" style="background-color: var(--cor-secundaria); color: white;"><i class="fas fa-desktop"></i> Gerenciar</button>
                </div>
            </div>


            <div class="settings-section">
                <h2><i class="fas fa-bell"></i> Notificações</h2>
                <div class="setting-item">
                    <span>Promoções e Ofertas</span>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <span>Atualizações de Pedidos</span>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <span>Notificações por Email</span>
                    <label class="switch">
                        <input type="checkbox" checked>
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            
            <div class="settings-section">
                <h2><i class="fas fa-user-lock"></i> Privacidade e Dados</h2>
                <div class="setting-item">
                    <span>Permitir Cookies Analíticos</span>
                    <label class="switch">
                        <input type="checkbox">
                        <span class="slider round"></span>
                    </label>
                </div>
                <div class="setting-item">
                    <span>Apagar Histórico de Navegação</span>
                    <button class="action-btn delete-btn"><i class="fas fa-trash-alt"></i> Apagar Dados</button>
                </div>
                <div class="setting-item">
                    <span>Exportar Dados da Conta</span>
                    <button class="action-btn login-btn"><i class="fas fa-download"></i> Exportar</button>
                </div>
            </div>

            
            <div class="save-settings">
                <button class="login-btn save-btn">Salvar Configurações</button>
            </div>

        </section>

    </main>
    
    <div id="cart-modal" class="modal">...</div> 
    <div id="login-modal" class="modal">...</div> 
    
    <script src="home.js"></script>
    <script>
        // JS básico para demonstração do tema (só para alternar a classe)
        document.getElementById('themeToggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
        });
        document.getElementById('theme-switch').addEventListener('change', function() {
            document.body.classList.toggle('dark-mode', this.checked);
        });

        // Função para simular a mudança de idioma (para o menu dropdown)
        function setLanguage(lang) {
            console.log('Idioma alterado para: ' + lang);
        }
    </script>
</body>
</html>