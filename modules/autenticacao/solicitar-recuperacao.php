<?php
// RF02.3 - Solicitação de recuperação de senha
include_once '../../config/conexao.php';

// Captura e-mail do formulário
$email = trim($_POST['email'] ?? '');
if ($email === '') { 
    echo "<script>alert('Informe o e-mail');history.back();</script>"; 
    exit; 
}

// Verifica se e-mail existe na base
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
  // Não revela se o e-mail existe (segurança)
  echo "<script>alert('Se este e-mail existir, você receberá instruções.');window.location='login.html';</script>";
  exit;
}

// Gera token único válido por 1 hora
$token = bin2hex(random_bytes(32));
$expira = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

// Salva token no banco
$ins = $conn->prepare("INSERT INTO senha_tokens (usuario_id, token, expira_em) VALUES (?, ?, ?)");
$ins->bind_param('iss', $user['id'], $token, $expira);
$ins->execute();

// Gera link de recuperação (TODO: implementar envio de e-mail)
$link = "redefinir-senha.php?token=".$token;
echo "<script>alert('Enviamos instruções de recuperação.');window.location='{$link}';</script>";
