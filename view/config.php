<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'controle_patrimonio');
define('DB_USER', 'root');
define('DB_PASS', '123456');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=3307", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}