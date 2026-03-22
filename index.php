<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="script.js" defer></script>
</head>

<body class="login-body">

<div class="login-container">

    <div class="login-box">

        <h1>🍔 MeuCardápio</h1>
        <p class="sub">Acesse sua conta</p>

        <?php if (isset($_GET['erro'])): ?>
            <div class="erro">Email ou senha inválidos</div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="formLogin">

            <!-- EMAIL -->
            <div class="input-group">
                <input type="email" name="email" required>
                <label>Email</label>
            </div>

            <!-- SENHA -->
            <div class="input-group senha-group">
                <input type="password" name="senha" id="senha" required>
                <label>Senha</label>
                <span class="toggle-senha" onclick="toggleSenha()">👁️</span>
            </div>

            <!-- LEMBRAR -->
            <div class="lembrar">
                <input type="checkbox" name="lembrar" id="lembrar">
                <label for="lembrar">Lembrar de mim</label>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                Entrar
            </button>

        </form>

        <a href="register.php" class="link">Criar conta</a>

    </div>

</div>

</body>
</html>