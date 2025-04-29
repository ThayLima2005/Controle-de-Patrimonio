<?php
session_start();

// Limpa todos os dados da sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();

// Redireciona para login
header("Location: login.php");
exit();
?>