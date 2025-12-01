<?php
session_start();
require_once 'conexao.php'; // Inclui a conexão com o banco de dados

// 1. Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_usuario_logado = $_SESSION['usuario_id'];

// 2. Verifica se a requisição é um POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: perfil.php");
    exit;
}

// 3. Coleta e Limpa os dados do formulário
$nome = trim($_POST['nome'] ?? '');
// O email não será alterado neste script, mas o mantemos para referência futura
// $email = trim($_POST['email'] ?? '');
$cpf = trim($_POST['cpf'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$endereco = trim($_POST['endereco'] ?? '');


// 4. Validação básica
if (empty($nome) || empty($endereco)) {
    echo "<script>alert('Nome e Endereço são campos obrigatórios!'); window.location.href='perfil.php';</script>";
    exit;
}

// Opcional: Adicione validações de formato para CPF/Telefone aqui.

// 5. Atualizar os dados do usuário no banco
// Nota: 'email' não está sendo atualizado no UPDATE, pois é um campo sensível.
$sql = "UPDATE usuarios SET n_usuario = ?, CPF_usuario = ?, endereco = ?, telefone = ? WHERE id_usuario = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    // Erro na preparação da query
    echo "<script>alert('Erro interno do sistema (SQL Prep).'); window.location.href='perfil.php';</script>";
    exit;
}

$stmt->bind_param("ssssi", $nome, $cpf, $endereco, $telefone, $id_usuario_logado);

if ($stmt->execute()) {
    // Atualiza a variável de sessão com o novo nome (para a barra superior)
    $_SESSION['usuario_nome'] = $nome;

    echo "<script>alert('Perfil atualizado com sucesso!'); window.location.href='perfil.php';</script>";
    exit;
} else {
    // Erro no banco de dados
    echo "<script>alert('Erro ao atualizar o perfil! Tente novamente.'); window.location.href='perfil.php';</script>";
    exit;
}
    
$stmt->close();
$conn->close();
?>