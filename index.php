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
  <link rel="stylesheet" href="style_index3.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="mobile-body">

  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>
  <div class="bg-shape shape-3"></div>

  <main class="mobile-app">

    <section class="hero-card fade-up delay-1">
      <span class="hero-badge">Cardápio digital moderno</span>

      <div class="logo-badge"></div>

      <h1>Seu cardápio online com aparência profissional</h1>
      <p>
        Crie, edite e compartilhe seu cardápio digital de forma simples,
        bonita e otimizada para celular.
      </p>

      <div class="hero-mini-list">
        <span><i class="ri-flashlight-line"></i> Rápido</span>
        <span><i class="ri-smartphone-line"></i> Mobile</span>
        <span><i class="ri-magic-line"></i> Bonito</span>
      </div>
    </section>

    <form class="form-card fade-up delay-2" action="login.php" method="POST">
      <div class="form-header">
        <h2>Entrar</h2>
        <p class="form-subtitle">Acesse seu painel e atualize seu cardápio quando quiser.</p>
      </div>

      <div class="input-group">
        <label for="email">E-mail</label>
        <div class="input-wrap">
          <span class="input-icon"><i class="ri-mail-line"></i></span>
          <input type="email" id="email" name="email" placeholder="Digite seu e-mail" required>
        </div>
      </div>

      <div class="input-group">
        <label for="senha">Senha</label>
        <div class="input-wrap">
          <span class="input-icon"><i class="ri-lock-2-line"></i></span>
          <input type="password" id="senha" name="senha" placeholder="Digite sua senha" required>
          <button type="button" class="toggle-password" onclick="toggleSenha()">
            <i class="ri-eye-line"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-primary" id="btnLogin">
        Entrar
      </button>

      <a href="cadastrar.php" class="link-btn">Criar minha conta</a>
    </form>

    <section class="list-card fade-up delay-3">
      <div class="list-header">
        <h3>Por que usar o CardápioOn?</h3>
        <p>Deixe seu negócio com presença mais profissional no digital.</p>
      </div>

      <div class="benefit-item">
        <div class="benefit-icon">
          <i class="ri-layout-4-line"></i>
        </div>
        <div>
          <strong>Visual profissional</strong>
          <p>Deixe seu cardápio mais bonito, organizado e atrativo para seus clientes.</p>
        </div>
      </div>

      <div class="benefit-item">
        <div class="benefit-icon">
          <i class="ri-edit-2-line"></i>
        </div>
        <div>
          <strong>Fácil de editar</strong>
          <p>Altere itens, descrições, categorias e preços em poucos segundos.</p>
        </div>
      </div>

      <div class="benefit-item">
        <div class="benefit-icon">
          <i class="ri-smartphone-line"></i>
        </div>
        <div>
          <strong>Feito para celular</strong>
          <p>Uma experiência pensada primeiro para mobile, mais fluida e agradável.</p>
        </div>
      </div>
    </section>

  </main>

  <script>
    function toggleSenha() {
      const senhaInput = document.getElementById('senha');
      const icone = document.querySelector('.toggle-password i');

      if (senhaInput.type === 'password') {
        senhaInput.type = 'text';
        icone.className = 'ri-eye-off-line';
      } else {
        senhaInput.type = 'password';
        icone.className = 'ri-eye-line';
      }
    }
  </script>

  <script>
    document.querySelector("form").addEventListener("submit", function() {
      const btn = document.getElementById("btnLogin");
      btn.textContent = "Entrando...";
      btn.disabled = true;
    });
  </script>
</body>
</html>