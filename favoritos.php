<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Meus Favoritos</title>
    
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    
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
            <a href="#"><i class="fas fa-cog"></i> <span class="txt">Configurações</span></a>
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
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Pesquisar produtos...">
                
                <button id="microphone-btn" class="mic-btn"><i class="fas fa-microphone"></i></button>
                
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
        </header>

        <section class="favoritos-container" style="padding: 20px;">
            <h1 style="border-bottom: 2px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; color: var(--text-color);"><i class="fas fa-heart"></i> Meus Produtos Favoritos</h1>
            
            <section class="produtos-favoritos" id="favoritos-list" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <p id="empty-favorites-message" style="text-align: center; color: #555; margin-top: 20px; width: 100%; display: none;">Você ainda não tem nenhum produto favorito. 💔</p>
                </section>
        </section>
        
        </main>

    <div id="cart-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="cart-title"><i class="fas fa-shopping-cart"></i> Seu Carrinho</h2>
                <span class="close-btn">&times;</span>
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
                <img src="D:\site organizado\imagems\logos\logo.png" alt="Mascote Login" class="login-mascote">
                
                <h2 class="login-title">Login</h2>

                <form>
                    <label for="email">Email ou telefone</label>
                    <input type="text" id="email" placeholder="Email ou telefone" required>
                    
                    <label for="password">Senha</label>
                    <div class="password-container">
                        <input type="password" id="password" placeholder="Digite sua senha" required>
                        <i class="fas fa-eye password-toggle"></i>
                    </div>
                    
                    <div class="login-options">
                        <label class="remember-me">
                            <input type="checkbox" id="remember-me">
                            Lembrar-me
                        </label>
                        <a href="#" class="forgot-password">Esqueceu a senha?</a>
                    </div>

                    <button type="submit" class="sign-in-btn">Entrar</button>
                </form>
                
                <button class="google-sign-in-btn">
                    <i class="fab fa-google"></i> Ou entre com Google
                </button>
            </div>
        </div>
    </div>
    
    <script src="home.js"></script> 
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Garante que a lógica de exibir os favoritos seja executada ao carregar esta página
            // A função displayFavorites() foi definida no home.js na resposta anterior.
            if (window.displayFavorites) {
                window.displayFavorites();
            }
        });
    </script>
</body>
</html>