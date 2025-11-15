<?php
// RF08 - Validação de login para sistema de filiais
session_start();

// Credenciais fixas para acesso ao sistema de filiais
$usuario_correto = "admin";
$senha_correta = "1234";

$usuario = trim($_POST['usuario'] ?? '');
$senha = $_POST['senha'] ?? '';

// Validação de campos obrigatórios
if ($usuario === '' || $senha === '') {
    echo "<script>alert('Informe usuário e senha');history.back();</script>";
    exit;
}

// Verifica credenciais
if ($usuario === $usuario_correto && $senha === $senha_correta) {
    session_regenerate_id(true);
    $_SESSION['logado_filial'] = true;
    $_SESSION['usuario_filial'] = $usuario;
    header("Location: filiais.php");
    exit;
}

echo "<script>alert('Usuário ou senha incorretos');history.back();</script>";
?>