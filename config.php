<?php
// ==========================================================
// config.php: Configurações globais e inicialização do PDO
// ==========================================================

// --- 1. Inicializa a Sessão (se ainda não estiver iniciada) ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- 2. Configurações do Banco de Dados ---
$host = 'localhost'; // Seu host do banco de dados
$db   = 'projeto';    // Nome do seu banco de dados
$user = 'root';      // Seu usuário do banco de dados
$pass = '';          // Sua senha do banco de dados
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Variável global para a conexão
$pdo = null;

/**
 * Função para obter a conexão PDO.
 * @return PDO
 * @throws PDOException
 */
function getPdoConnection() {
    global $pdo, $dsn, $user, $pass, $options;
    if ($pdo === null) {
        try {
            $pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            // Em produção, você deve logar este erro, não exibi-lo.
            // Aqui, lançamos a exceção para que o código chamador lide com ela.
            throw $e;
        }
    }
    return $pdo;
}

// --- 3. Lógica de Login e Sessão (Variáveis globais) ---
$logado = false;
$nome_usuario = '';
$nivel_usuario = 'guest';

if (isset($_SESSION['usuario_id'])) {
    $logado = true;
    $nome_usuario = $_SESSION['usuario_nome'] ?? 'Usuário'; 
    $nivel_usuario = $_SESSION['usuario_nivel'] ?? 'user'; 
}

// --- 4. Função de Formatação (Reutilizada no PHP) ---

/**
 * Formata um preço float para o padrão R$ X.XXX,XX
 * @param float $preco
 * @return string
 */
function formatarPreco($preco) {
    return number_format((float)$preco, 2, ',', '.');
}
?>