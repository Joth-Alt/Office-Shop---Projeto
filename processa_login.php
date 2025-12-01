<?php
session_start();
include 'conexao.php'; // Certifique-se de que a conexão está incluída

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Coleta e Limpa os dados
    $email_login = trim($_POST['email']);
    $senha_digitada = $_POST['senha'];

    // ==========================================================
    // NOVO: CHECAGEM MANUAL DO ADMIN ESPECÍFICO
    // Email: admin@gmail.com | Senha: admadm
    // ==========================================================
    if ($email_login === 'admin@gmail.com' && $senha_digitada === 'admadm') {
        $_SESSION['usuario_id'] = 999; // ID fictício para admin
        $_SESSION['usuario_nome'] = 'Admin'; 
        $_SESSION['usuario_nivel'] = 'admin'; 

        header("Location: index.php");
        exit;
    }
    // ==========================================================
    
    // 2. Busca o usuário no banco (Lógica para usuários normais)
    $stmt = $conn->prepare("SELECT id_usuario, n_usuario, senha FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email_login);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        $senha_hash = $usuario['senha'];

        // 3. Verifica a senha usando password_verify()
        if (password_verify($senha_digitada, $senha_hash)) {
            // 🔑 LOGIN BEM-SUCEDIDO: Configura a sessão
            $_SESSION['usuario_id'] = $usuario['id_usuario'];
            $_SESSION['usuario_nome'] = $usuario['n_usuario']; // Salva o nome para exibição
            $_SESSION['usuario_nivel'] = 'user'; // Define o nível padrão
            
            header("Location: index.php");
            exit;
        } else {
            // 🚨 Senha incorreta
            echo "<script>alert('Senha incorreta.'); window.location.href='index.php';</script>";
        }
    } else {
        // 🚨 Usuário não encontrado
        echo "<script>alert('E-mail não cadastrado.'); window.location.href='index.php';</script>";
    }

    $stmt->close();
    $conn->close();
} else {
    // Acesso direto à página
    header("Location: index.php");
    exit;
}
?>