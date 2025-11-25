<?php
// 1. INICIA A SESSÃO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. LIMPA TODAS AS VARIÁVEIS DE SESSÃO
$_SESSION = array();

// 3. SE USAR COOKIES DE SESSÃO, DESTROI O COOKIE
// Isso irá invalidar a sessão e a senha do usuário
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. DESTRÓI A SESSÃO
session_destroy();

// 5. REDIRECIONA PARA A PÁGINA DE LOGIN
header("Location: login.php");
exit();
?>