<?php
session_start();
require 'db.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome  = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($nome !== "" && $email !== "" && $senha !== "") {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        try {
            $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            $stmt->execute([$nome, $email, $senhaHash]);

            header("Location: index.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $erro = "Este e-mail já está cadastrado.";
            } else {
                $erro = "Erro no cadastro: " . $e->getMessage();
            }
        }
    } else {
        $erro = "Preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Criar conta</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body class="mobile-body">
  <div class="mobile-app">
    <form class="form-card" method="POST">
      <h2>Criar conta</h2>

      <?php if ($erro): ?>
        <div class="alert"><?= htmlspecialchars($erro) ?></div>
      <?php endif; ?>

      <input type="text" name="nome" placeholder="Seu nome" required>
      <input type="email" name="email" placeholder="Seu e-mail" required>
      <input type="password" name="senha" placeholder="Crie uma senha" required>

      <button type="submit" class="btn-primary">Cadastrar</button>
      <a class="link-btn" href="index.php">Voltar para login</a>
    </form>
  </div>
</body>
</html>