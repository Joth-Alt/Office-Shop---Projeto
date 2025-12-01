<?php
// ==========================================================
// carrinho_api.php: Lógica de CRUD (Create, Read, Update, Delete) do Carrinho na Sessão
// ==========================================================
require_once 'config.php'; 

header('Content-Type: application/json');

// Garante que o array de carrinho exista na sessão
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

$carrinho = &$_SESSION['carrinho'];

$action = $_REQUEST['action'] ?? '';
$product_id = $_REQUEST['product_id'] ?? null;
$response = ['success' => false, 'message' => 'Ação inválida'];

try {
    switch ($action) {
        case 'add':
            $name = $_POST['name'] ?? 'Produto Desconhecido';
            $price = floatval($_POST['price'] ?? 0); // O JS envia como float
            $img = $_POST['img'] ?? '';
            $quantity = 1;

            if (empty($product_id) || $price <= 0) {
                throw new Exception("Dados do produto incompletos ou inválidos.");
            }

            $encontrado = false;
            foreach ($carrinho as $key => $item) {
                if ($item['id'] == $product_id) {
                    $carrinho[$key]['quantity'] += $quantity;
                    $encontrado = true;
                    break;
                }
            }

            if (!$encontrado) {
                $carrinho[] = [
                    'id' => $product_id,
                    'name' => $name,
                    'price' => $price,
                    'img' => $img,
                    'quantity' => $quantity,
                ];
            }
            
            $response = ['success' => true, 'message' => 'Produto adicionado com sucesso.'];
            break;

        case 'remove':
            if (empty($product_id)) {
                throw new Exception("ID do produto é obrigatório para remover.");
            }

            $carrinho = array_filter($carrinho, function($item) use ($product_id) {
                return $item['id'] != $product_id;
            });
            // Reindexa o array após a remoção
            $carrinho = array_values($carrinho); 

            $response = ['success' => true, 'message' => 'Produto removido com sucesso.'];
            break;

        case 'update_quantity':
            $quantity = intval($_POST['quantity'] ?? 0);

            if (empty($product_id) || $quantity <= 0) {
                throw new Exception("ID do produto ou quantidade inválida.");
            }

            $encontrado = false;
            foreach ($carrinho as $key => $item) {
                if ($item['id'] == $product_id) {
                    $carrinho[$key]['quantity'] = $quantity;
                    $encontrado = true;
                    break;
                }
            }

            if (!$encontrado) {
                throw new Exception("Produto não encontrado no carrinho.");
            }

            $response = ['success' => true, 'message' => 'Quantidade atualizada.'];
            break;

        case 'get':
            // Calcula o total para garantir que os dados de retorno estejam completos
            $total = array_reduce($carrinho, function($sum, $item) {
                return $sum + ($item['price'] * $item['quantity']);
            }, 0);
            
            $response = [
                'success' => true, 
                'carrinho' => $carrinho, 
                'total' => $total, 
                'total_formatado' => formatarPreco($total)
            ];
            break;

        default:
            $response['message'] = 'Ação desconhecida.';
            break;
    }

} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>