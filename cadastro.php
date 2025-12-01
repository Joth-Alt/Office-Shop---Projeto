<?php
session_start();

$logado = false;
$nome_usuario = '';

// Se o usuário já estiver logado, redireciona pra index.php
if (isset($_SESSION['usuario_id'])) {
    $logado = true;
    $nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário';
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Cadastro</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <nav class="menu-principal">
            <a href="index.php"><i class="fas fa-home"></i> <span class="txt">Home</span></a>
            <a href="favoritos.php"><i class="fas fa-heart"></i> <span class="txt">Favoritos</span></a>
            <a href="perfil.php"><i class="fas fa-user"></i> <span class="txt">Perfil</span></a>
            <a href="contato.php"><i class="fas fa-envelope"></i> <span class="txt">Contato</span></a>
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

    <!-- Conteúdo principal -->
    <main class="content">
        <header class="top-nav">
            <div class="search-container">
                <input type="text" id="search-input" placeholder="Pesquisar produtos...">
                <div id="search-results" class="search-dropdown"></div>
                <button id="microphone-btn" class="mic-btn"><i class="fas fa-microphone"></i></button>
                <button class="search-btn"><i class="fas fa-search"></i></button>
            </div>
            <div class="user-actions">
                <?php if ($logado): ?>
                    <a href="perfil.php" class="perfil-btn">
                        <i class="fas fa-user"></i> Olá, <?php echo htmlspecialchars($nome_usuario); ?>
                    </a>
                    <a href="logout.php" class="logout-btn" style="margin-left: 10px;">
                        <i class="fas fa-sign-out-alt"></i> Sair
                    </a>
                <?php else: ?>
                    <a href="index.php" class="login-btn"><i class="fas fa-sign-in-alt"></i> Entrar</a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Seção de Cadastro -->
        <section class="login-wall-main">
            <div class="login-wall-content-wrapper">

                <!-- Card de Cadastro -->
                <div class="login-wall-card">
                    <i class="fas fa-user-plus lock-icon"></i>
                    <h2>Crie sua Conta</h2>
                    <p>Preencha as informações abaixo para se cadastrar na Office Shop.</p>

                    <div class="login-form-container">
                        <form action="processa_cadastro.php" method="POST" class="login-wall-form" onsubmit="return validarSenha()">

                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" placeholder="Seu nome completo" required>

                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Seu email" required>

                            <label for="cpf">CPF</label>
                            <input type="text" id="cpf" name="cpf" maxlength="14" placeholder="000.000.000-00" required>

                            <label for="telefone">Telefone</label>
                            <input type="tel" id="telefone" name="telefone" maxlength="15" placeholder="(00) 00000-0000" required>

                            <label for="endereco">Endereço</label>
                            <input type="text" id="endereco" name="endereco" placeholder="Rua, número, bairro, cidade" required>

                            <label for="senha">Senha</label>
                            <div class="password-container">
                                <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
                                <i class="fas fa-eye password-toggle"></i>
                            </div>

                            <label for="confirma">Confirmar Senha</label>
                            <div class="password-container">
                                <input type="password" id="confirma" placeholder="Repita sua senha" required>
                                <i class="fas fa-eye password-toggle"></i>
                            </div>

                            <button type="submit" class="sign-in-btn full-width-btn">Cadastrar</button>
                        </form>

                        <p style="text-align: center; margin-top: 20px; font-size: 0.9em;">
                            Já tem conta?
                            <a href="index.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">Entre aqui</a>
                        </p>
                    </div>
                </div>

                <!-- Imagem decorativa à direita -->
            </div>
        </section>
    </main>

    <script>
        function validarSenha() {
            const senha = document.getElementById("senha").value;
            const confirma = document.getElementById("confirma").value;

            if (senha.length < 6) {
                alert("A senha deve ter pelo menos 6 caracteres.");
                return false;
            }
            if (senha !== confirma) {
                alert("As senhas não coincidem!");
                return false;
            }
            return true;
        }
    </script>
</body>
</html>
