<?php
// ==========================================================
// processa_pedido.php: Insere Pedido e Itens no BD e zera carrinho.
// Versão Atualizada: Com sistema de status e endereço de partida
// ==========================================================
session_start();

header('Content-Type: application/json');

// --- Validação de Login ---
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não logado.']);
    exit;
}

// --- Configuração e Conexão com o BD ---
$host = 'localhost'; 
$db   = 'projeto';  
$user = 'root';     
$pass = '';         
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE              => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE   => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES     => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Erro de conexão com o BD. Verifique o MySQL: ' . $e->getMessage()
    ]);
    exit;
}

// --- Recebe os dados do POST (JSON) ---
$data = json_decode(file_get_contents('php://input'), true);

$forma_pagamento = $data['forma_pagamento'] ?? 'pix';
$endereco_completo = $data['endereco_completo'] ?? 'Endereço não informado';

// Validação dos dados essenciais
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    echo json_encode(['success' => false, 'message' => 'O carrinho está vazio.']);
    exit;
}

$carrinho = $_SESSION['carrinho'];
$id_usuario = $_SESSION['usuario_id'];
$success = false;
$id_pedido = null;

// Endereço fixo de partida
$endereco_partida = "Av. Monsenhor Theodomiro Lobo, 100 - Parque Res. Maria Elmira, Caçapava - SP, 12285-050";

// Calcular tempo estimado de entrega
function calcularTempoEntrega($partida, $chegada) {
    $base_time = 300; // 5 minutos base
    $length_factor = strlen($chegada) * 2;
    $tempo_total = min(max($base_time + $length_factor, 600), 3600); // 10min a 60min
    return $tempo_total;
}

$tempo_estimado = calcularTempoEntrega($endereco_partida, $endereco_completo);

try {
    $pdo->beginTransaction();

    // 1. Inserir na tabela 'pedidos' com todos os campos de status
    $stmt_pedido = $pdo->prepare("INSERT INTO pedidos 
        (id_usuario, forma_pag, endereco_entrega, endereco_partida, endereco_chegada, 
         tempo_estimado, tempo_restante, status, data_p, inicio_entrega) 
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Preparando', NOW(), NOW())");
    
    $stmt_pedido->execute([
        $id_usuario, 
        $forma_pagamento, 
        $endereco_completo,
        $endereco_partida,
        $endereco_completo,
        $tempo_estimado,
        $tempo_estimado
    ]);
    
    $id_pedido = $pdo->lastInsertId();

    // 2. Inserir na tabela 'itens_pedido' e atualizar o estoque
    $stmt_item = $pdo->prepare("INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco) VALUES (?, ?, ?, ?)");
    $stmt_estoque = $pdo->prepare("UPDATE produto SET quantidade = quantidade - ? WHERE id_produto = ? AND quantidade >= ?");

    foreach ($carrinho as $item) {
        $id_produto = $item['id'];
        $quantidade = $item['quantity'];
        $preco_unitario = $item['price'];
        
        // Insere o item do pedido
        $stmt_item->execute([$id_pedido, $id_produto, $quantidade, $preco_unitario]);
        
        // Atualiza o estoque
        $stmt_estoque->execute([$quantidade, $id_produto, $quantidade]);
        if ($stmt_estoque->rowCount() == 0) {
            throw new Exception("Estoque insuficiente para o produto ID: {$id_produto}.");
        }
    }

    // 3. Confirma a transação
    $pdo->commit();
    
    // 4. Limpa o carrinho de sessão
    unset($_SESSION['carrinho']);
    
    $success = true;

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro ao processar pedido: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'Erro ao finalizar pedido: ' . $e->getMessage()]);
    exit;
} catch (\PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Erro no PDO durante o processamento: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do BD. Detalhes: ' . $e->getMessage()]);
    exit;
}

if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Compra finalizada com sucesso!',
        'id_pedido' => $id_pedido,
        'tempo_estimado' => $tempo_estimado
    ]);
}
?>