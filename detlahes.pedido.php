<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Detalhes do Pedido</title>
    <style>
        table, th, td { border: 1px solid black; border-collapse: collapse; padding: 8px; }
        .produto-img-detalhe { width: 60px; height: 60px; object-fit: cover; }
    </style>
</head>
<body>
    <?php
    include 'conexao.php';

    // 1. Pega o ID do pedido da URL e verifica se é um número válido
    $id_pedido = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id_pedido > 0) {
        // --- Informações Principais do Pedido ---
        $sql_pedido = "SELECT p.*, u.n_usuario, u.avatar_url 
                       FROM pedidos p 
                       JOIN usuarios u ON p.id_usuario = u.id_usuario 
                       WHERE p.id_pedido = $id_pedido";
        $result_pedido = $conn->query($sql_pedido);
        $pedido = $result_pedido->fetch_assoc();

        if ($pedido) {
            echo "<h1>Detalhes do Pedido #" . $pedido['id_pedido'] . "</h1>";
            echo "<p><strong>Cliente:</strong> " . htmlspecialchars($pedido['n_usuario']) . "</p>";
            echo "<p><strong>Data:</strong> " . $pedido['data_p'] . "</p>";
            echo "<p><strong>Status:</strong> " . $pedido['status'] . "</p>";
            echo "<hr>";

            // --- Itens do Pedido ---
            echo "<h2>Produtos no Pedido</h2>";
            
            // 2. Consulta para TODOS os itens da tabela ITENS_PEDIDO
            $sql_itens = "SELECT 
                              ip.quantidade, 
                              ip.preco AS preco_unitario, 
                              p.n_produto, 
                              p.imagem_url 
                          FROM itens_pedido ip
                          JOIN produto p ON ip.id_produto = p.id_produto
                          WHERE ip.id_pedido = $id_pedido";
            $result_itens = $conn->query($sql_itens);

            if ($result_itens->num_rows > 0) {
                $total_pedido = 0;
                echo "<table>";
                echo "<tr>
                        <th>Imagem</th>
                        <th>Produto</th>
                        <th>Preço Unitário</th>
                        <th>Quantidade</th>
                        <th>Subtotal</th>
                      </tr>";
                
                while($item = $result_itens->fetch_assoc()) {
                    $subtotal = $item['quantidade'] * $item['preco_unitario'];
                    $total_pedido += $subtotal;
                    
                    $imagem_produto = !empty($item["imagem_url"]) 
                                      ? '<img src="' . htmlspecialchars($item["imagem_url"]) . '" alt="Produto" class="produto-img-detalhe">' 
                                      : 'Sem Img';

                    echo "<tr>";
                    echo "<td>" . $imagem_produto . "</td>";
                    echo "<td>" . htmlspecialchars($item["n_produto"]) . "</td>";
                    echo "<td>R$ " . number_format($item["preco_unitario"], 2, ',', '.') . "</td>";
                    echo "<td>" . $item["quantidade"] . "</td>";
                    echo "<td>R$ " . number_format($subtotal, 2, ',', '.') . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
                
                // 3. Exibe o Total Final na página de detalhes
                echo "<h3>Total do Pedido: R$ " . number_format($total_pedido, 2, ',', '.') . "</h3>";

            } else {
                echo "<p>Nenhum item encontrado para este pedido.</p>";
            }

        } else {
            echo "<p>Pedido não encontrado.</p>";
        }
    } else {
        echo "<p>ID do Pedido inválido.</p>";
    }

    $conn->close();
    ?>
    <p><a href="pedidos.php">Voltar para a Lista de Pedidos</a></p>
</body>
</html>