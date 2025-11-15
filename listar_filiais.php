<?php
include 'conexao.php';

$result = mysqli_query($conn, "SELECT * FROM cadastro_usuarios.filiais");

echo "<table border='1'>
<tr><th>ID</th><th>Nome</th><th>Endereço</th><th>Telefone</th><th>Responsável</th><th>Ações</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>
        <td>{$row['id']}</td>
        <td>{$row['nome']}</td>
        <td>{$row['endereco']}</td>
        <td>{$row['telefone']}</td>
        <td>{$row['responsavel']}</td>
        <td>
            <a href='editar_filial.php?id={$row['id']}'>Editar</a> |
            <a href='excluir_filial.php?id={$row['id']}'>Excluir</a>
        </td>
    </tr>";
}
echo "</table>";
?>