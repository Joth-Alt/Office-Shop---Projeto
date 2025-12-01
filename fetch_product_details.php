<?php
// ====================================================================
// fetch_product_details.php: Lógica para retornar detalhes do produto via AJAX
// ====================================================================

// Cabeçalhos para evitar problemas de CORS e garantir JSON
header('Content-Type: application/json');

// --- CONFIGURAÇÃO DA CONEXÃO DO BANCO DE DADOS ---
// SUBSTITUA PELAS SUAS CREDENCIAIS REAIS
$host = 'localhost';
$dbname = 'sua_base_de_dados'; // <--- MUDAR
$user = 'seu_usuario';         // <--- MUDAR
$pass = 'sua_senha';           // <--- MUDAR
// ------------------------------------------------

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Retorna erro se a conexão falhar
    echo json_encode(['error' => 'Erro de conexão com o banco de dados.', 'details' => $e->getMessage()]);
    exit();
}


if (!isset($_GET['id']) || empty($_GET['id'])) {
    // Retorna erro se o ID não for fornecido
    echo json_encode(['error' => 'ID do produto não fornecido.']);
    exit();
}

// Limpa o ID, garantindo que não há caracteres estranhos, mas mantendo strings e números.
$product_id = trim($_GET['id']);
if (empty($product_id)) {
    echo json_encode(['error' => 'ID do produto vazio após limpeza.']);
    exit();
}


try {
    // 1. Consulta SQL: Seleciona todos os campos necessários para o modal
    // Ajuste o nome da tabela (ex: 'produtos') e das colunas conforme seu BD
    $stmt = $db->prepare("SELECT 
                            id_produto, 
                            n_produto, 
                            descricao, 
                            preco, 
                            imagem_url, 
                            material, 
                            dimensoes, 
                            quantidade 
                          FROM produtos 
                          WHERE id_produto = :id");

    // 2. Usar PDO::PARAM_STR para o ID para suportar tanto IDs numéricos quanto de string.
    $stmt->bindParam(':id', $product_id, PDO::PARAM_STR); 
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Formata o preço para garantir que seja um float (19.99) antes de enviar ao JS
        $product['preco'] = (float) $product['preco'];
        
        // Retorna os dados do produto em formato JSON
        echo json_encode($product);
    } else {
        // Retorna erro se o produto não for encontrado
        echo json_encode(['error' => 'Produto não encontrado no banco de dados com o ID: ' . htmlspecialchars($product_id)]);
    }

} catch (PDOException $e) {
    // Em caso de erro na consulta, retorna uma mensagem de erro
    echo json_encode(['error' => 'Erro na consulta ao banco de dados.', 'details' => $e->getMessage()]);
}

?>