<?php
// ====================================================================
// produtos/detalhes.php: Exibe a página de detalhes de um único produto
// ====================================================================
require_once '../config.php'; // Ajuste o caminho se necessário

$produto = null;
$id_produto = $_GET['id'] ?? '';

if (empty($id_produto)) {
    // Redireciona ou mostra uma mensagem de erro se o ID não for fornecido
    header('Location: ../index.php');
    exit;
}

try {
    $pdo = getPdoConnection();
    
    // Busca o produto no banco de dados
    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch();

    if (!$produto) {
        // Redireciona ou mostra uma mensagem de erro se o produto não for encontrado
        header('Location: ../index.php');
        exit;
    }

    $data_price = number_format($produto['preco'], 2, '.', '');
    $display_price = formatarPreco($produto['preco']); // Assume que formatarPreco está no config.php

} catch (\PDOException $e) {
    // Em produção, registre o erro. Aqui, apenas evita que a página quebre.
    die("Erro ao carregar detalhes do produto: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Detalhes - <?php echo htmlspecialchars($produto['n_produto']); ?></title>
    <link rel="stylesheet" href="../css/basic.css" />
    <link rel="stylesheet" href="../css/background.css" />
    <link rel="stylesheet" href="../css/modal.css" />
    <link rel="stylesheet" href="../css/detalhes.css" /> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>

<body>
<div class="bg-pattern"></div>
<main class="content" style="padding-top: 20px;">
    <div class="detalhes-container">
        <div class="detalhes-imagem">
            <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" alt="<?php echo htmlspecialchars($produto['n_produto']); ?>">
        </div>
        <div class="detalhes-info">
            <h1><?php echo htmlspecialchars($produto['n_produto']); ?></h1>
            <p class="preco-detalhe">R$ <?php echo $display_price; ?></p>
            
            <div class="detalhes-texto">
                <p><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p>
            </div>
            
            <div class="detalhes-meta">
                <p><strong>Categoria:</strong> <?php echo htmlspecialchars($produto['categoria']); ?></p>
                <p><strong>Em Estoque:</strong> <?php echo (int)$produto['quantidade']; ?></p>
                </div>

            <div class="detalhes-acoes">
                <button class="add-to-cart-btn grande" 
                        data-product-id="<?php echo htmlspecialchars($produto['id_produto']); ?>" 
                        data-name="<?php echo htmlspecialchars($produto['n_produto']); ?>" 
                        data-price="<?php echo $data_price; ?>" 
                        data-img="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                        data-category="<?php echo htmlspecialchars($produto['categoria']); ?>"
                        onclick="addToCart(this)">
                    <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                </button>
                <div class="icones-acoes">
                    <button class="favoritar-detalhe" onclick="toggleFavorite(this)"><i class="fas fa-heart"></i></button>
                    <button class="compartilhar-detalhe" onclick="shareProduct()"><i class="fas fa-share-alt"></i></button>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../home.js"></script> <script>
    function shareProduct() {
        if (navigator.share) {
            navigator.share({
                title: '<?php echo htmlspecialchars($produto['n_produto']); ?>',
                text: 'Confira este produto incrível na Office Shop!',
                url: window.location.href
            }).then(() => {
                console.log('Compartilhado com sucesso!');
            }).catch((error) => {
                console.error('Erro ao compartilhar:', error);
            });
        } else {
            alert("Compartilhe este link: " + window.location.href);
        }
    }
    // No ambiente de produção, esta página também pode ser renderizada em um modal,
    // mas o modo mais simples é criar uma página de detalhes dedicada, como feito aqui.
</script>
</body>
</html>