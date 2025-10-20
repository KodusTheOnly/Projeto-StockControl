<?php
include_once 'conexao.php';

$token = $_GET['token'] ?? '';
if ($token === '') { http_response_code(400); echo 'Token ausente.'; exit; }

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

$agora = new DateTime();
$expira = $tk ? new DateTime($tk['expira_em']) : null;

$valido = $tk && !$tk['usado_em'] && $expira && $agora <= $expira;

if (!$valido) { echo 'Link inválido ou expirado.'; exit; }
?>
<!doctype html>
<html lang="pt-BR"><head><meta charset="utf-8"><title>Definir nova senha</title></head>
<body>
  <form action="atualizar-senha.php" method="post">
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES); ?>">
    <input type="password" name="senha" placeholder="Nova senha" required>
    <button type="submit">Atualizar senha</button>
  </form>
</body></html>
