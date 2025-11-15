<?php
include 'conexao.php';

$id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $responsavel = $_POST['responsavel'];

    $sql = "UPDATE cadastro_usuarios.filiais SET 
            nome='$nome', endereco='$endereco', telefone='$telefone', responsavel='$responsavel'
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        echo "Filial atualizada!";
    } else {
        echo "Erro: " . mysqli_error($conn);
    }
}

$result = mysqli_query($conn, "SELECT * FROM cadastro_usuarios.filiais WHERE id=$id");
$row = mysqli_fetch_assoc($result);
?>

<form method="POST">
    <input type="text" name="nome" value="<?= $row['nome'] ?>" required><br>
    <input type="text" name="endereco" value="<?= $row['endereco'] ?>"><br>
    <input type="text" name="telefone" value="<?= $row['telefone'] ?>"><br>
    <input type="text" name="responsavel" value="<?= $row['responsavel'] ?>"><br>
    <button type="submit">Atualizar</button>
</form>