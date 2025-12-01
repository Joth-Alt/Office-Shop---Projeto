<?php
session_start();

// Destroi todas as variáveis de sessão
$_SESSION = array();

// Se for preciso apagar os cookies de sessão também, o código abaixo deve ser usado
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroi a sessão em si
session_destroy();

// Redireciona para a página inicial
header("Location: index.php");
exit;
?>