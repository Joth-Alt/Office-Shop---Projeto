<?php
session_start();
include('conexao.php');

// Recebe os dados enviados pelo JavaScript
$data = json_decode(file_get_contents("php://input"), true);

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(["success" => false, "message" => "Usuário não logado."]);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$forma_pag = $data['forma_pag'] ?? 'pix';
$endereco_entrega = $data['endereco_entrega'] ?? '';
$carrinho = $_SESSION['carrinho'] ?? [];

// Validações
if (empty($carrinho)) {
    echo json_encode(["success" => false, "message" => "Carrinho vazio."]);
    exit;
}

if (empty($endereco_entrega)) {
    echo json_encode(["success" => false, "message" => "Endereço de entrega não informado."]);
    exit;
}

/* ============================================================
   🧾 1️⃣ CRIA O PEDIDO
============================================================ */
$sql_pedido = "INSERT INTO pedidos (id_usuario, forma_pag, endereco_entrega, status, inicio_entrega)
               VALUES (?, ?, ?, 'Preparando', NOW())";
$stmt = $conn->prepare($sql_pedido);
$stmt->bind_param("iss", $id_usuario, $forma_pag, $endereco_entrega);

if ($stmt->execute()) {
    $id_pedido = $stmt->insert_id;

    /* ============================================================
       🛒 2️⃣ INSERE OS ITENS DO PEDIDO
    ============================================================ */
    $sql_item = "INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco)
                 VALUES (?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);

    foreach ($carrinho as $item) {
        $id_produto = $item['id'];
        $quantidade = $item['quantidade'];
        $preco = $item['preco'];
        $stmt_item->bind_param("iiid", $id_pedido, $id_produto, $quantidade, $preco);
        $stmt_item->execute();
    }

    // Limpa o carrinho
    unset($_SESSION['carrinho']);

    echo json_encode(["success" => true, "id_pedido" => $id_pedido]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao registrar pedido: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
