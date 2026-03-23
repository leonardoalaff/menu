<?php
session_start();
if (isset($_SESSION['usuario_id'])) {
    header("Location: painel.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CardápioOn</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body class="mobile-body">
  <div class="mobile-app">

    <div class="hero-card">
      <div class="logo-badge"></div>
      <p>Seu cardápio online, bonito, rápido e pronto para impressionar seus clientes.</p>
    </div>

    <form class="form-card" action="login.php" method="POST">
      <h2>Entrar</h2>
      <p class="form-subtitle">Acesse seu painel e edite seu cardápio digital.</p>

      <input type="email" name="email" placeholder="Seu e-mail" required>
      <input type="password" name="senha" placeholder="Sua senha" required>

      <button type="submit" class="btn-primary">Entrar no painel</button>
      <a href="cadastrar.php" class="link-btn">Criar minha conta</a>
    </form>

    <div class="list-card">
      <h3>Vantagens do CardápioOn</h3>

      <div class="menu-item">
        <div>
          <strong>Visual profissional</strong>
          <p>Crie um cardápio moderno e bonito para seu negócio.</p>
        </div>
      </div>

      <div class="menu-item">
        <div>
          <strong>Fácil de editar</strong>
          <p>Altere nome, descrição, cor e itens em poucos segundos.</p>
        </div>
      </div>

      <div class="menu-item">
        <div>
          <strong>Feito para celular</strong>
          <p>Experiência pensada primeiro para mobile.</p>
        </div>
      </div>
    </div>

  </div>
</body>
</html>