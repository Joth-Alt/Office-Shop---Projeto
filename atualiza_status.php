<?php
session_start();
include('conexao.php');

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    exit(json_encode(['success' => false, 'message' => 'Não autorizado']));
}

$id_usuario = $_SESSION['usuario_id'];

// Tempo fixo para preparação (10 segundos)
$TEMPO_PREPARANDO = 10;

try {
    // 1. Buscar pedidos ativos do usuário
    $sql_pedidos = "SELECT * FROM pedidos 
                    WHERE id_usuario = ? 
                    AND status IN ('Preparando', 'A caminho')
                    ORDER BY data_p ASC";
    $stmt_pedidos = $conn->prepare($sql_pedidos);
    $stmt_pedidos->bind_param("i", $id_usuario);
    $stmt_pedidos->execute();
    $pedidos = $stmt_pedidos->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_pedidos->close();

    foreach ($pedidos as $pedido) {
        $id_pedido = $pedido['id_pedido'];
        $status_atual = $pedido['status'];
        $data_pedido = strtotime($pedido['data_p']);
        $agora = time();
        $tempo_decorrido = $agora - $data_pedido;

        if ($status_atual === 'Preparando') {
            // Mudar para "A caminho" após 10 segundos
            if ($tempo_decorrido >= $TEMPO_PREPARANDO) {
                $novo_status = 'A caminho';
                $sql_update = "UPDATE pedidos 
                              SET status = ?, inicio_entrega = NOW() 
                              WHERE id_pedido = ? AND status = 'Preparando'";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $novo_status, $id_pedido);
                $stmt_update->execute();
                $stmt_update->close();
            }
        } 
        elseif ($status_atual === 'A caminho') {
            // Calcular tempo restante baseado no tempo estimado
            $tempo_estimado = $pedido['tempo_estimado'];
            $tempo_restante = $tempo_estimado - $tempo_decorrido;
            
            // Atualizar tempo restante
            $sql_tempo = "UPDATE pedidos SET tempo_restante = ? WHERE id_pedido = ?";
            $stmt_tempo = $conn->prepare($sql_tempo);
            $stmt_tempo->bind_param("ii", $tempo_restante, $id_pedido);
            $stmt_tempo->execute();
            $stmt_tempo->close();
            
            // Mudar para "Entregue" quando o tempo acabar
            if ($tempo_restante <= 0) {
                $novo_status = 'Entregue';
                $sql_entregue = "UPDATE pedidos 
                                SET status = ?, final_entrega = NOW(), tempo_restante = 0
                                WHERE id_pedido = ?";
                $stmt_entregue = $conn->prepare($sql_entregue);
                $stmt_entregue->bind_param("si", $novo_status, $id_pedido);
                $stmt_entregue->execute();
                $stmt_entregue->close();
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Status atualizados']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>