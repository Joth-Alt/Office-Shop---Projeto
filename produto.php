<?php 
// ====================================================================
// produto.php: Detalhes do Produto para o Cliente
// =ÇÃO: Busca o produto pelo ID e exibe opções (ex: Tamanho)
// ====================================================================
session_start();

// ------------------------------------
// 1. Configuração e Conexão com o Banco de Dados
// ------------------------------------
$host = 'localhost'; 
$db = 'projeto'; 
$user = 'root'; 
$pass = ''; 
$charset = 'utf8mb4';

$dsn="mysql:host=$host;dbname=$db;charset=$charset";
$options=[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try{
    $pdo=new PDO($dsn,$user,$pass,$options);
}catch(\PDOException $e){
    die("Erro de conexão com o banco de dados: ".$e->getMessage());
}

// ------------------------------------
// 2. Processamento do ID do Produto
// ------------------------------------
$id_produto = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Se não houver ID válido na URL, redireciona ou exibe erro
if (!$id_produto) {
    // Redireciona para a lista de produtos, por exemplo
    header('Location: index.php'); 
    exit;
}

// ------------------------------------
// 3. Busca dos Detalhes do Produto
// ------------------------------------
try {
    $stmt = $pdo->prepare("SELECT * FROM produto WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch();

    if (!$produto) {
        die("Produto não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro ao buscar produto: " . $e->getMessage());
}

// ------------------------------------
// 4. Lógica de Tamanhos e Opções
// ------------------------------------
$is_vestuario = ($produto['categoria'] === 'Roupas' || $produto['categoria'] === 'Calçados');

// Extrai tamanhos disponíveis da descrição (se houver)
$tamanhos_disponiveis = [];
if ($is_vestuario) {
    // Tenta encontrar a linha "Tamanhos disponíveis: P, M, G..." na descrição
    if (preg_match('/Tamanhos disponíveis: (.*)/', $produto['descricao'], $matches)) {
        $tamanhos_str = trim($matches[1]);
        $tamanhos_disponiveis = array_map('trim', explode(',', $tamanhos_str));
    }
    // Caso não encontre na descrição, use um array padrão ou vazio.
}

// Formatação do Preço
$preco_formatado = 'R$ ' . number_format($produto['preco'], 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?php echo htmlspecialchars($produto['n_produto']); ?> - Detalhes</title>
    <link rel="stylesheet" href="css/basic.css" />
    <link rel="stylesheet" href="css/produto.css" /> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        /* Estilos específicos para a página do produto */
        .product-page { max-width: 1200px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; display: flex; gap: 40px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .product-image-area { flex: 1; }
        .product-image-area img { max-width: 100%; height: auto; border-radius: 6px; }
        .product-details-area { flex: 1.2; }
        h1 { font-size: 2.5em; margin-top: 0; }
        .price { font-size: 2em; color: #f7316b; font-weight: bold; margin: 15px 0; }
        .stock { color: <?php echo $produto['quantidade'] > 0 ? 'green' : 'red'; ?>; margin-bottom: 20px; font-weight: bold; }
        .options-group { margin-bottom: 25px; }
        .options-group label { display: block; font-weight: bold; margin-bottom: 10px; }
        .size-selector select { padding: 10px; border: 1px solid #ccc; border-radius: 4px; width: 150px; }
        .quantity-selector input { width: 60px; padding: 10px; border: 1px solid #ccc; border-radius: 4px; text-align: center; }
        .add-to-cart-btn { background-color: #f7316b; color: white; padding: 15px 30px; border: none; border-radius: 4px; cursor: pointer; font-size: 1.1em; transition: background-color 0.3s; width: 100%; max-width: 300px; }
        .add-to-cart-btn:hover { background-color: #d82b5f; }
        .description-box h3 { border-bottom: 1px solid #eee; padding-bottom: 10px; margin-top: 30px; }
        .description-box p { line-height: 1.6; color: #555; }
    </style>
</head>
<body> 
    <main class="content">
        <div class="product-page">
            
            <div class="product-image-area">
                <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                     alt="<?php echo htmlspecialchars($produto['n_produto']); ?>" />
            </div>

            <div class="product-details-area">
                <p class="category-tag"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($produto['categoria']); ?></p>
                <h1><?php echo htmlspecialchars($produto['n_produto']); ?></h1>
                
                <div class="price"><?php echo $preco_formatado; ?></div>
                <div class="stock">
                    <?php 
                    if ($produto['quantidade'] > 0) {
                        echo '<i class="fas fa-check-circle"></i> Em estoque (' . $produto['quantidade'] . ' unidades)';
                    } else {
                        echo '<i class="fas fa-times-circle"></i> Esgotado';
                    }
                    ?>
                </div>

                <form action="carrinho.php" method="POST">
                    <input type="hidden" name="id_produto" value="<?php echo $id_produto; ?>">
                    
                    <?php if ($is_vestuario && !empty($tamanhos_disponiveis)): ?>
                        <div class="options-group size-selector">
                            <label for="tamanho_selecionado">Selecione o Tamanho:</label>
                            <select id="tamanho_selecionado" name="tamanho" required>
                                <option value="">-- Escolha --</option>
                                <?php foreach ($tamanhos_disponiveis as $tamanho): ?>
                                    <option value="<?php echo htmlspecialchars($tamanho); ?>">
                                        <?php echo htmlspecialchars($tamanho); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="options-group quantity-selector">
                        <label for="quantidade_adicionar">Quantidade:</label>
                        <input type="number" id="quantidade_adicionar" name="quantidade" value="1" min="1" 
                               max="<?php echo max(1, $produto['quantidade']); ?>" required 
                               <?php echo $produto['quantidade'] === 0 ? 'disabled' : ''; ?>>
                    </div>
                    
                    <button type="submit" class="add-to-cart-btn" 
                            <?php echo $produto['quantidade'] === 0 ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
                    </button>
                    
                    <a href="favoritos.php?acao=adicionar&id=<?php echo $id_produto; ?>" style="margin-top: 10px; display: block; text-align: center;">
                        <i class="fas fa-heart"></i> Adicionar aos Favoritos
                    </a>
                </form>

                <div class="description-box">
                    <h3>Descrição do Produto</h3>
                    <p><?php echo nl2br(htmlspecialchars($produto['descricao'])); ?></p> 
                </div>

            </div>
        </div>
    </main>
</body>
</html>