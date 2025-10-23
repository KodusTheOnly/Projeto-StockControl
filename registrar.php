<?php
// conexao banco
include_once("conexao.php");

// leitura do formulário
$nome  = isset($_POST["nome"])  ? trim($_POST["nome"])  : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";
// validação
if ($nome === "" || $email === "" || $senha === "") {
    echo "<script>alert('Preencha todos os campos');history.back();</script>";
    exit;
}
// msg erro validação
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('E-mail inválido');history.back();</script>";
    exit;
}
// msg erro minimo 6 caracteres
if (strlen($senha) < 6) {
    echo "<script>alert('A senha precisa ter pelo menos 6 caracteres');history.back();</script>";
    exit;
}
// criptografa senha
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// insere no banco de dados
$sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if(!$stmt){
    echo "<h3>Erro ao preparar a query: " . htmlspecialchars($conn->error) . "</h3>";
    exit;
}
$stmt->bind_param("sss", $nome, $email, $senha_hash);
// Executa
if ($stmt->execute()) {
    echo "<script>alert('Conta criada com sucesso!');window.location.href='login.html';</script>";

// msg de erro
} else {
    
    if ($conn->errno === 1062) {
        echo "<script>alert('Este e-mail já está cadastrado');history.back();</script>";
    } else {
        echo "<h3>Erro ao salvar: " . htmlspecialchars($conn->error) . "</h3>";
    }
}
$stmt->close();
$conn->close();
?>
