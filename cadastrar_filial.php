<?php
// RF08.1 - Cadastrar Filial
session_start();
if (!isset($_SESSION['logado_filial'])) {
    header("Location: login_filiais.php");
    exit;
}

include 'conexao.php';

$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $responsavel = trim($_POST['responsavel'] ?? '');

    // Validação
    if ($nome === '') {
        $mensagem = 'O nome da filial é obrigatório';
        $tipo_mensagem = 'error';
    } else {
        // Usa prepared statement para evitar SQL injection
        $stmt = $conn->prepare("INSERT INTO filiais (nome, endereco, telefone, responsavel) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $nome, $endereco, $telefone, $responsavel);

        if ($stmt->execute()) {
            $mensagem = 'Filial cadastrada com sucesso!';
            $tipo_mensagem = 'success';
        } else {
            $mensagem = 'Erro ao cadastrar filial: ' . $conn->error;
            $tipo_mensagem = 'error';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastrar Filial</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h2 {
      color: #0077cc;
      margin-bottom: 20px;
    }
    .mensagem {
      padding: 12px;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    .mensagem.success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .mensagem.error {
      background-color: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    label {
      font-weight: bold;
      color: #333;
    }
    input {
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 14px;
    }
    button {
      background-color: #0077cc;
      color: white;
      padding: 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
    }
    button:hover {
      background-color: #005fa3;
    }
    .voltar {
      display: inline-block;
      margin-top: 15px;
      color: #0077cc;
      text-decoration: none;
    }
    .voltar:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Cadastrar Nova Filial</h2>
    
    <?php if ($mensagem): ?>
      <div class="mensagem <?php echo $tipo_mensagem; ?>">
        <?php echo htmlspecialchars($mensagem); ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div>
        <label for="nome">Nome da Filial *</label>
        <input type="text" id="nome" name="nome" required>
      </div>
      
      <div>
        <label for="endereco">Endereço</label>
        <input type="text" id="endereco" name="endereco">
      </div>
      
      <div>
        <label for="telefone">Telefone</label>
        <input type="text" id="telefone" name="telefone" placeholder="(00) 0000-0000">
      </div>
      
      <div>
        <label for="responsavel">Responsável</label>
        <input type="text" id="responsavel" name="responsavel">
      </div>
      
      <button type="submit">Cadastrar Filial</button>
    </form>

    <a href="filiais.php" class="voltar">← Voltar ao Menu</a>
  </div>
</body>
</html>