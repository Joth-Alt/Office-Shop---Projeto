<?php
session_start();
include 'conexao.php'; // Inclui a conexão com o banco de dados

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Coleta e Limpa os dados
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $cpf = trim($_POST['cpf']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $senha = $_POST['senha'];

    // 2. Validação básica (Recomendado adicionar validação de CPF/Telefone no futuro)
    if (empty($nome) || empty($email) || empty($senha)) {
        echo "<script>alert('Preencha todos os campos obrigatórios!'); window.location.href='cadastro.php';</script>";
        exit;
    }

    // 3. Verificar se o email já está cadastrado
    $verifica = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $verifica->bind_param("s", $email);
    $verifica->execute();
    $verifica->store_result();

    if ($verifica->num_rows > 0) {
        echo "<script>alert('Este e-mail já está cadastrado!'); window.location.href='cadastro.php';</script>";
        exit;
    }
    $verifica->close();

    // 4. Criptografar senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // 5. Inserir novo usuário no banco
    $stmt = $conn->prepare("INSERT INTO usuarios (n_usuario, email, CPF_usuario, endereco, telefone, senha) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nome, $email, $cpf, $endereco, $telefone, $senha_hash);

    if ($stmt->execute()) {
        // 🔑 SUCESSO: LOGAR O USUÁRIO AUTOMATICAMENTE
        $_SESSION['usuario_id'] = $conn->insert_id;
        $_SESSION['usuario_nome'] = $nome; // Armazena o nome para exibição

        // Redirecionar para a página inicial logado
        header("Location: index.php");
        exit;
    } else {
        // 🚨 Erro no banco de dados
        echo "<script>alert('Erro ao cadastrar usuário! Tente novamente.'); window.location.href='cadastro.php';</script>";
    }
    
    $stmt->close();
    $conn->close();
} else {
    // Se a página for acessada diretamente sem POST
    header("Location: cadastro.php");
    exit;
}
?>