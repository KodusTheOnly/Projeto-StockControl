<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $endereco = $_POST['endereco'];
    $telefone = $_POST['telefone'];
    $responsavel = $_POST['responsavel'];

    $sql = "INSERT INTO cadastro_usuarios.filiais (nome, endereco, telefone, responsavel)
            VALUES ('$nome', '$endereco', '$telefone', '$responsavel')";

    if (mysqli_query($conn, $sql)) {
        echo "Filial cadastrada com sucesso!";
    } else {
        echo "Erro: " . mysqli_error($conn);
    }
}
?>

<form method="POST">
    <input type="text" name="nome" placeholder="Nome" required><br>
    <input type="text" name="endereco" placeholder="Endereço"><br>
    <input type="text" name="telefone" placeholder="Telefone"><br>
    <input type="text" name="responsavel" placeholder="Responsável"><br>
    <button type="submit">Cadastrar</button>
</form>