<?php
session_start();
// Define o tipo de conteúdo como JSON para o JavaScript entender a resposta
header('Content-Type: application/json');

// Inclua sua conexão. Se 'conexao.php' não existir, vai falhar.
require_once 'conexao.php'; 

// 1. Verifica login
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$tipo_imagem = $_POST['tipo'] ?? ''; // 'avatar' ou 'capa'

// 2. Verifica se o arquivo foi enviado e o tipo é válido
if (!isset($_FILES['imagem']) || empty($tipo_imagem) || !in_array($tipo_imagem, ['avatar', 'capa'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Dados de upload inválidos ou tipo não especificado.']);
    exit;
}

$arquivo = $_FILES['imagem'];

// Verificação de erro de upload (Ex: arquivo muito grande)
if ($arquivo['error'] !== UPLOAD_ERR_OK) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no upload do arquivo. Código: ' . $arquivo['error']]);
    exit;
}

$upload_dir = 'uploads/usuarios/'; 
$extensao = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));

// Geração de um nome único e seguro para o arquivo
$nome_arquivo_unico = $tipo_imagem . '_' . $id_usuario . '_' . time() . '_' . rand(100, 999) . '.' . $extensao;
$caminho_completo = $upload_dir . $nome_arquivo_unico;

// Cria o diretório se não existir (garantindo que a pasta exista antes de salvar)
if (!is_dir($upload_dir)) {
    // Tenta criar o diretório recursivamente
    if (!mkdir($upload_dir, 0777, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Falha ao criar o diretório de upload. Verifique as permissões.']);
        exit;
    }
}

// 3. Move o arquivo e salva no BD
if (move_uploaded_file($arquivo['tmp_name'], $caminho_completo)) {
    
    $coluna_bd = $tipo_imagem . '_url';
    // Remove uploads anteriores para manter o BD limpo (opcional, mas recomendado)
    // Primeiro, busca a URL antiga
    $verifica_old = $conn->prepare("SELECT {$coluna_bd} FROM usuarios WHERE id_usuario = ?");
    $verifica_old->bind_param("i", $id_usuario);
    $verifica_old->execute();
    $verifica_old->bind_result($url_antiga);
    $verifica_old->fetch();
    $verifica_old->close();

    // Se a URL antiga existir e não for o placeholder, tenta deletar o arquivo
    if ($url_antiga && !str_contains($url_antiga, 'placeholder')) {
        @unlink($url_antiga); // @ para suprimir erros caso o arquivo não exista
    }


    // Atualiza o banco de dados com o novo caminho
    $sql = "UPDATE usuarios SET {$coluna_bd} = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $caminho_completo, $id_usuario);

    if ($stmt->execute()) {
        // Sucesso: Retorna o novo caminho
        echo json_encode([
            'success' => true,
            'url' => $caminho_completo,
            'message' => ucfirst($tipo_imagem) . ' atualizado com sucesso!'
        ]);
        exit;
    } else {
        // Se falhar o BD, remove o arquivo que subiu
        @unlink($caminho_completo); 
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar o caminho no banco de dados.']);
        exit;
    }
    
    $stmt->close();
} else {
    // Erro ao mover o arquivo
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar o arquivo no servidor. Verifique as permissões da pasta ' . $upload_dir]);
    exit;
}
$conn->close();
?>