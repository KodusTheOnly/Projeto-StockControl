<?php
session_start();

// Usuário e senha fixos (pode vir do banco depois)
$usuario_correto = "admin";
$senha_correta = "1234";

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

if ($usuario === $usuario_correto && $senha === $senha_correta) {
    $_SESSION['logado_filial'] = true;
    header("Location: filiais.html");
    exit;
} else {
    echo "Usuário ou senha incorretos.";
}
?>