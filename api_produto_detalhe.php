<?php
// ====================================================================
// api_produto_detalhe.php: Endpoint que retorna detalhes de um produto em JSON
// ====================================================================
require_once 'config.php'; // Use seu arquivo de configuração/conexão, se tiver

header('Content-Type: application/json'); // Garante que a resposta é JSON

// --- 1. Conexão com o Banco de Dados (Adapte se o seu config.php for diferente) ---
try {
    $pdo = getPdoConnection(); // Ou use sua lógica de conexão: new PDO(...)
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro de conexão com o banco de dados.']);
    exit;
}

// --- 2. Recebe o ID do Produto ---
$id_produto = $_GET['id'] ?? null;

if (empty($id_produto)) {
    http_response_code(400); // Bad Request
    echo json_encode(['erro' => 'ID do produto não fornecido.']);
    exit;
}

// --- 3. Busca o Produto no Banco de Dados ---
try {
    // Busca TODOS os campos, incluindo a 'descricao'
    $stmt = $pdo->prepare("SELECT id_produto, n_produto, preco, quantidade, imagem_url, categoria, descricao FROM produto WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch();

    if ($produto) {
        // Formata o preço para exibição no JSON
        $produto['preco_display'] = 'R$ ' . number_format($produto['preco'], 2, ',', '.');
        
        // Retorna o produto encontrado em formato JSON
        echo json_encode(['sucesso' => true, 'produto' => $produto]);
    } else {
        http_response_code(404); // Not Found
        echo json_encode(['erro' => 'Produto não encontrado.']);
    }

} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar dados: ' . $e->getMessage()]);
}
?>