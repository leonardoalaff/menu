<?php
include 'db.php';

if ($_POST) {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO usuarios (nome,email,senha) VALUES (?,?,?)");
    $stmt->execute([$nome,$email,$senha]);

    header("Location: index.php");
}
?>

<form method="POST">
    <input name="nome" placeholder="Nome">
    <input name="email" placeholder="Email">
    <input name="senha" type="password" placeholder="Senha">
    <button>Cadastrar</button>
</form>