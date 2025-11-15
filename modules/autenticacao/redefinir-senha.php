<?php
// RF02.3 - Página de redefinição de senha
include_once '../../config/conexao.php';

// Valida presença do token na URL
$token = $_GET['token'] ?? '';
if ($token === '') { 
    http_response_code(400); 
    echo 'Token ausente.'; 
    exit; 
}

// Busca token no banco com dados do usuário
$stmt = $conn->prepare("
  SELECT t.id, t.usuario_id, t.expira_em, t.usado_em, u.email
  FROM senha_tokens t
  JOIN usuarios u ON u.id = t.usuario_id
  WHERE t.token = ?
");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$tk = $res->fetch_assoc();

// Verifica se o token é válido e não expirou
$agora = new DateTime();
$expira = $tk ? new DateTime($tk['expira_em']) : null;
$valido = $tk && !$tk['usado_em'] && $expira && $agora <= $expira;

if (!$valido) { 
    echo 'Link inválido ou expirado.'; 
    exit; 
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Redefinir Senha</title>
  <link rel="stylesheet" href="../../assets/css/login.css" />
</head>
<body>
<div class="caixa-formulario">
  <form class="formulario" action="atualizar-senha.php" method="post">
    <span class="titulo">Recuperar senha</span>
    <span class="subtitulo">Digite sua nova senha</span>
    <div class="container-formulario">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
    <input type="password" class="entrada" name="senha" placeholder="Nova senha" required>
    </div>
    <button type="submit">Atualizar senha</button>
  </form>
</div>
</body>
</html>
