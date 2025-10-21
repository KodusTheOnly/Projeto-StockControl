<?php
include_once 'conexao.php';

$email = trim($_POST['email'] ?? '');
if ($email === '') { echo "<script>alert('Informe o e-mail');history.back();</script>"; exit; }

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
  // NÃO VAI REVELAR SE EXISTE O EMAIL OU NÃO
  echo "<script>alert('Se este e-mail existir, você receberá instruções.');window.location='login.html';</script>";
  exit;
}

$token = bin2hex(random_bytes(32));
$expira = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

$ins = $conn->prepare("INSERT INTO senha_tokens (usuario_id, token, expira_em) VALUES (?, ?, ?)");
$ins->bind_param('iss', $user['id'], $token, $expira);
$ins->execute();

// ENVIO DE EMAIL PARA OS PRÓXIMOS RFs
$link = "redefinir-senha.php?token=".$token;
echo "<script>alert('Enviamos instruções de recuperação.');window.location='{$link}';</script>";