<?php
// Conexão dedicada ao banco estoque_produtos
if (!defined('DB_PROD_HOST')) {
    define('DB_PROD_HOST', 'localhost');
    define('DB_PROD_PORT', '3307');
    define('DB_PROD_USER', 'root');
    define('DB_PROD_PASS', '');
    define('DB_PROD_NAME', 'estoque_produtos');
}

/**
 * Retorna uma conexão ativa com o banco estoque_produtos.
 *
 * @throws RuntimeException quando a conexão falha.
 */
function conectarEstoque(): mysqli
{
    $conn = new mysqli(DB_PROD_HOST, DB_PROD_USER, DB_PROD_PASS, DB_PROD_NAME, DB_PROD_PORT);
    if ($conn->connect_error) {
        throw new RuntimeException('Falha na conexão com estoque_produtos: ' . $conn->connect_error);
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
