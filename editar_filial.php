<?php
// RF08.3 - Alterar Filial
session_start();
if (!isset($_SESSION['logado_filial'])) {
    header("Location: login_filiais.php");
    exit;
}

include 'conexao.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo "<script>alert('ID inválido');window.location='listar_filiais.php';</script>";
    exit;
}

$mensagem = '';
$tipo_mensagem = '';

// Processa atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $endereco = trim($_POST['endereco'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $responsavel = trim($_POST['responsavel'] ?? '');

    if ($nome === '') {
        $mensagem = 'O nome da filial é obrigatório';
        $tipo_mensagem = 'error';
    } else {
        $stmt = $conn->prepare("UPDATE filiais SET nome = ?, endereco = ?, telefone = ?, responsavel = ? WHERE id = ?");
        $stmt->bind_param('ssssi', $nome, $endereco, $telefone, $responsavel, $id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $mensagem = 'Filial atualizada com sucesso!';
                $tipo_mensagem = 'success';
            } else {
                $mensagem = 'Nenhuma alteração foi feita';
                $tipo_mensagem = 'info';
            }
        } else {
            $mensagem = 'Erro ao atualizar filial: ' . $conn->error;
            $tipo_mensagem = 'error';
        }
        $stmt->close();
    }
}

// Busca dados da filial
$stmt = $conn->prepare("SELECT nome, endereco, telefone, responsavel FROM filiais WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$filial = $result->fetch_assoc();
$stmt->close();

if (!$filial) {
    echo "<script>alert('Filial não encontrada');window.location='listar_filiais.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Filial</title>
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
    .mensagem.info {
      background-color: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
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
      background-color: #ffc107;
      color: #333;
      padding: 12px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
      font-weight: bold;
    }
    button:hover {
      background-color: #e0a800;
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
    <h2>Editar Filial</h2>
    
    <?php if ($mensagem): ?>
      <div class="mensagem <?php echo $tipo_mensagem; ?>">
        <?php echo htmlspecialchars($mensagem); ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      <div>
        <label for="nome">Nome da Filial *</label>
        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($filial['nome']); ?>" required>
      </div>
      
      <div>
        <label for="endereco">Endereço</label>
        <input type="text" id="endereco" name="endereco" value="<?php echo htmlspecialchars($filial['endereco'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="telefone">Telefone</label>
        <input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($filial['telefone'] ?? ''); ?>">
      </div>
      
      <div>
        <label for="responsavel">Responsável</label>
        <input type="text" id="responsavel" name="responsavel" value="<?php echo htmlspecialchars($filial['responsavel'] ?? ''); ?>">
      </div>
      
      <button type="submit">Atualizar Filial</button>
    </form>

    <a href="listar_filiais.php" class="voltar">← Voltar para Lista</a>
  </div>
</body>
</html>
<?php
$conn->close();
?>