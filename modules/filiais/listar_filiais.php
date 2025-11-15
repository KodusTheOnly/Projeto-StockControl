<?php
// RF08.2 - Consultar Filiais
session_start();
if (!isset($_SESSION['logado_filial'])) {
    header("Location: login_filiais.php");
    exit;
}

include '../../config/conexao.php';

// Busca todas as filiais ordenadas por nome
$stmt = $conn->prepare("SELECT id, nome, endereco, telefone, responsavel, criado_em FROM filiais ORDER BY nome ASC");
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listar Filiais</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    .container {
      max-width: 1200px;
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
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #0077cc;
      color: white;
      font-weight: bold;
    }
    tr:hover {
      background-color: #f5f5f5;
    }
    .acoes {
      display: flex;
      gap: 8px;
    }
    .acoes a {
      padding: 6px 12px;
      border-radius: 4px;
      text-decoration: none;
      font-size: 14px;
      color: white;
    }
    .btn-editar {
      background-color: #ffc107;
    }
    .btn-editar:hover {
      background-color: #e0a800;
    }
    .btn-excluir {
      background-color: #dc3545;
    }
    .btn-excluir:hover {
      background-color: #c82333;
    }
    .voltar {
      display: inline-block;
      margin-top: 15px;
      color: #0077cc;
      text-decoration: none;
      font-weight: bold;
    }
    .voltar:hover {
      text-decoration: underline;
    }
    .empty-state {
      text-align: center;
      padding: 40px;
      color: #666;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Lista de Filiais</h2>

    <?php if ($result->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Endereço</th>
            <th>Telefone</th>
            <th>Responsável</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?php echo htmlspecialchars($row['id']); ?></td>
              <td><?php echo htmlspecialchars($row['nome']); ?></td>
              <td><?php echo htmlspecialchars($row['endereco'] ?? '-'); ?></td>
              <td><?php echo htmlspecialchars($row['telefone'] ?? '-'); ?></td>
              <td><?php echo htmlspecialchars($row['responsavel'] ?? '-'); ?></td>
              <td>
                <div class="acoes">
                  <a href="editar_filial.php?id=<?php echo $row['id']; ?>" class="btn-editar">Editar</a>
                  <a href="excluir_filial.php?id=<?php echo $row['id']; ?>" class="btn-excluir" onclick="return confirm('Tem certeza que deseja excluir esta filial?')">Excluir</a>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="empty-state">
        <p>Nenhuma filial cadastrada ainda.</p>
        <p><a href="cadastrar_filial.php">Cadastrar primeira filial</a></p>
      </div>
    <?php endif; ?>

    <a href="filiais.php" class="voltar">← Voltar ao Menu</a>
  </div>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>
