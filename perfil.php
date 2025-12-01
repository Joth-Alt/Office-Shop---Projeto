<?php
session_start();
require_once 'conexao.php'; // Conexão com o banco

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php"); 
    exit;
}

// 2. Pega o ID do usuário logado e nome para barra superior
$id_usuario_logado = $_SESSION['usuario_id'];
$nome_usuario_logado = $_SESSION['usuario_nome'] ?? 'Usuário'; // Para a barra superior

// 3. Busca os dados do usuário no banco, incluindo as URLs das imagens
// CERTIFIQUE-SE DE QUE AS COLUNAS avatar_url E capa_url EXISTEM NO SEU BD!
$sql = "SELECT n_usuario, email, CPF_usuario, endereco, telefone, avatar_url, capa_url FROM usuarios WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario_logado);
$stmt->execute();
$resultado = $stmt->get_result();
$dados_usuario = $resultado->fetch_assoc();

if (!$dados_usuario) {
    // Usuário não encontrado, encerra sessão e redireciona
    session_destroy();
    header("Location: login.php");
    exit;
}

// Atribui os dados buscados às variáveis
$nome_usuario = $dados_usuario['n_usuario'];
$email_usuario = $dados_usuario['email'];
$cpf_usuario = $dados_usuario['CPF_usuario'];
$endereco_usuario = $dados_usuario['endereco'];
$telefone_usuario = $dados_usuario['telefone'];

// Define as URLs das imagens com um valor padrão, caso não existam no BD (porém o DEFAULT SQL já ajuda)
$avatar_url = $dados_usuario['avatar_url'] ?? 'avatar-placeholder.png'; 
$capa_url = $dados_usuario['capa_url'] ?? 'capa-placeholder.jpg';

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Office Shop - Meu Perfil</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/background.css" />
    <link rel="stylesheet" href="css/modal.css" /> 
    <link rel="stylesheet" href="perfil.css" /> 
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

<main class="content perfil-main"> 

    <section class="perfil-central-container">
        <div class="perfil-header-area">
            <div class="capa" id="capa" style="background-image: url('<?php echo htmlspecialchars($capa_url); ?>');">
                <button class="editar-btn editar-capa" id="btnTrocarCapa"><i class="fas fa-camera"></i> Editar Capa</button>
            </div>

            <div class="avatar-info">
                <div class="avatar-container">
                    <img class="avatar" id="avatar" src="<?php echo htmlspecialchars($avatar_url); ?>" alt="Avatar" />
                    <button class="editar-btn editar-avatar" id="btnTrocarAvatar"><i class="fas fa-pen"></i></button>
                </div>
            </div>
            
            <input type="file" accept="image/*" id="inputAvatar" class="upload-input" />
            <input type="file" accept="image/*" id="inputCapa" class="upload-input" />
        </div>
        
        <div class="perfil-content-wrapper">
            <div class="perfil-info card">
                <h2>Meus Dados</h2>
                <form action="processa_edicao_perfil.php" method="POST" id="formEdicaoPerfil">
                    
                    <label for="nome">Nome Completo:</label>
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($nome_usuario); ?>" required>

                    <label for="email">Email:</label>
                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($email_usuario); ?>" disabled>
                    <small>O e-mail é a chave da conta e não pode ser alterado por aqui.</small>

                    <label for="cpf">CPF:</label>
                    <input type="text" name="cpf" id="cpf" value="<?php echo htmlspecialchars($cpf_usuario); ?>" maxlength="14">

                    <label for="telefone">Telefone:</label>
                    <input type="text" name="telefone" id="telefone" value="<?php echo htmlspecialchars($telefone_usuario); ?>" maxlength="15">

                    <label for="endereco">Endereço:</label>
                    <input type="text" name="endereco" id="endereco" value="<?php echo htmlspecialchars($endereco_usuario); ?>" required>
                    
                    <div class="botoes-edicao">
                        <button type="submit" class="btn-perfil-acao"><i class="fas fa-save"></i> Salvar Alterações</button>
                        <button type="button" id="btnMudarSenha" class="btn-perfil-acao secundario"><i class="fas fa-key"></i> Mudar Senha</button>
                    </div>

                </form>
            </div>

            <aside class="perfil-sidebar">
                <div class="card card-status">
                    <h4>Status da Conta</h4>
                    <div class="status-info">
                        <p><i class="fas fa-user-tag"></i> ID Usuário: <?php echo $id_usuario_logado; ?></p>
                        <div class="nome-container-sidebar">
                            <p><strong>Usuário:</strong> <?php echo htmlspecialchars($nome_usuario); ?></p>
                        </div>
                        <p><i class="fas fa-calendar-alt"></i> Cliente desde: 06/06/2025</p>
                        <hr style="border-color: #eee; margin: 15px 0;">
                        <p><i class="fas fa-star"></i> Nível: Gold</p>
                        <p><i class="fas fa-box-open"></i> Pedidos Realizados: 15</p>
                        <p><i class="fas fa-coins"></i> Pontos de Fidelidade: 850</p>
                    </div>

                </div>
            </aside>
        </div>
    </section>
</main>

<script>
    // Referências aos inputs de arquivo
    const inputAvatar = document.getElementById('inputAvatar');
    const inputCapa = document.getElementById('inputCapa');
    const avatarImg = document.getElementById('avatar');
    const capaDiv = document.getElementById('capa');

    // Funções para simular clique no input invisível
    document.getElementById('btnTrocarAvatar').addEventListener('click', () => {
        inputAvatar.click();
    });
    document.getElementById('btnTrocarCapa').addEventListener('click', () => {
        inputCapa.click();
    });

    // Função de upload genérica via AJAX
    function uploadImagem(inputElement, tipo) {
        if (inputElement.files.length === 0) return;

        const file = inputElement.files[0];
        const formData = new FormData();
        formData.append('imagem', file);
        formData.append('tipo', tipo); // 'avatar' ou 'capa'

        // Feedback visual de carregamento (opcional, mas recomendado)
        // Por exemplo, desabilitar botões e mostrar um spinner

        fetch('processa_upload_imagem.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                // Tratar erros HTTP
                throw new Error('Erro na requisição: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Atualiza o DOM com a nova URL
                if (tipo === 'avatar') {
                    // Adiciona timestamp para forçar o navegador a recarregar a imagem (evitar cache)
                    avatarImg.src = data.url + '?' + new Date().getTime(); 
                } else if (tipo === 'capa') {
                    capaDiv.style.backgroundImage = `url('${data.url}')`;
                }
            } else {
                alert('Erro no upload: ' + (data.message || 'Ocorreu um erro desconhecido.'));
            }
        })
        .catch(error => {
            console.error('Erro de rede ou JSON:', error);
            alert('Erro ao comunicar com o servidor. Verifique o console.');
        })
        .finally(() => {
            // Remover feedback visual de carregamento
        });
    }

    // Event Listeners para os inputs de arquivo
    inputAvatar.addEventListener('change', () => {
        uploadImagem(inputAvatar, 'avatar');
    });

    inputCapa.addEventListener('change', () => {
        uploadImagem(inputCapa, 'capa');
    });

    // Event Listener para o botão Mudar Senha
    document.getElementById('btnMudarSenha').addEventListener('click', () => {
        alert('Redirecionando para a página de Mudança de Senha (ainda não implementada).');
        // window.location.href = 'mudar_senha.php';
    });
</script>

</body>
</html>