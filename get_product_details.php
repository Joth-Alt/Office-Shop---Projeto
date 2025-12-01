<?php
// ====================================================================
// fetch_product_details.php: Retorna os detalhes de um produto em JSON
// ====================================================================
require_once 'config.php'; 

header('Content-Type: application/json');

$id_produto = $_GET['id'] ?? '';

if (empty($id_produto)) {
    echo json_encode(['error' => 'ID do produto não fornecido.']);
    exit;
}

// Se formatarPreco() não estiver em config.php, adicione aqui (exemplo simples):
if (!function_exists('formatarPreco')) {
    function formatarPreco($valor) {
        return number_format($valor, 2, ',', '.');
    }
}

try {
    $pdo = getPdoConnection(); // Presumindo que esta função existe
    
    // Busca o produto no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        // Adiciona o preço formatado para exibição
        $produto['display_price'] = formatarPreco($produto['preco']);
        // Retorna o produto em formato JSON
        echo json_encode($produto);
    } else {
        echo json_encode(['error' => 'Produto não encontrado.']);
    }

} catch (\PDOException $e) {
    echo json_encode(['error' => 'Erro de banco de dados: ' . $e->getMessage()]);
}

?>