<?php
// RF01.2 - Cadastro de novo usuário
include_once("conexao.php");

// Captura dados do formulário
$nome  = isset($_POST["nome"])  ? trim($_POST["nome"])  : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";
$senha = isset($_POST["senha"]) ? $_POST["senha"] : "";

// Validação: campos obrigatórios
if ($nome === "" || $email === "" || $senha === "") {
    echo "<script>alert('Preencha todos os campos');history.back();</script>";
    exit;
}

// Validação: formato de e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('E-mail inválido');history.back();</script>";
    exit;
}

// Validação: senha mínima de 6 caracteres
if (strlen($senha) < 6) {
    echo "<script>alert('A senha precisa ter pelo menos 6 caracteres');history.back();</script>";
    exit;
}

// Criptografa a senha antes de salvar
$senha_hash = password_hash($senha, PASSWORD_DEFAULT);

// Insere usuário no banco
$sql = "INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if(!$stmt){
    echo "<h3>Erro ao preparar a query: " . htmlspecialchars($conn->error) . "</h3>";
    exit;
}
$stmt->bind_param("sss", $nome, $email, $senha_hash);

if ($stmt->execute()) {
    echo "<script>alert('Conta criada com sucesso!');window.location.href='login.html';</script>";
} else {
    // Verifica se o e-mail já existe (código de erro 1062)
    if ($conn->errno === 1062) {
        echo "<script>alert('Este e-mail já está cadastrado');history.back();</script>";
    } else {
        echo "<h3>Erro ao salvar: " . htmlspecialchars($conn->error) . "</h3>";
    }
}
$stmt->close();
$conn->close();
?>