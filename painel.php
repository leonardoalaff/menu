<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

$stmt = $db->prepare("SELECT * FROM cardapios WHERE usuario_id = ?");
$stmt->execute([$usuario_id]);
$cardapio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cardapio) {
    $stmt = $db->prepare("INSERT INTO cardapios (usuario_id, nome_negocio, cor_principal, descricao) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario_id, "Meu Negócio", "#FF5A1F", "Seu cardápio digital"]);

    $stmt = $db->prepare("SELECT * FROM cardapios WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $cardapio = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id = ? ORDER BY categoria ASC, nome ASC");
$stmt->execute([$cardapio['id']]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel - CardápioOn</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body class="mobile-body">
  <div class="mobile-app">

    <div class="topbar">
      <div>
        <small>Bem-vindo(a)</small>
        <h2><?= htmlspecialchars($_SESSION['usuario_nome']) ?></h2>
      </div>
      <a href="logout.php" class="btn-ghost">Sair</a>
    </div>

    <div class="hero-card">
      <div class="logo-badge"></div>
      <p>Gerencie seu cardápio digital e veja como ele está aparecendo para seus clientes.</p>
    </div>

    <div class="preview-card" style="border-left-color: <?= htmlspecialchars($cardapio['cor_principal']) ?>;">
      <h3><?= htmlspecialchars($cardapio['nome_negocio']) ?></h3>
      <p><?= htmlspecialchars($cardapio['descricao']) ?></p>
      <span class="color-chip" style="background: <?= htmlspecialchars($cardapio['cor_principal']) ?>;"></span>
    </div>

    <a href="visualizar_cardapio.php" class="btn-primary">Ver como o cliente vê</a>

    <form class="form-card" action="salvar_cardapio.php" method="POST">
      <h3>Personalizar cardápio</h3>
      <p class="form-subtitle">Atualize os dados principais e adicione novos itens.</p>

      <input type="hidden" name="cardapio_id" value="<?= $cardapio['id'] ?>">

      <input
        type="text"
        name="nome_negocio"
        placeholder="Nome do negócio"
        value="<?= htmlspecialchars($cardapio['nome_negocio']) ?>"
        required
      >

      <textarea
        name="descricao"
        placeholder="Descrição do negócio"
      ><?= htmlspecialchars($cardapio['descricao']) ?></textarea>

      <label class="label-inline">Cor principal do cardápio</label>
      <input type="color" name="cor_principal" value="<?= htmlspecialchars($cardapio['cor_principal']) ?>">

      <hr>

      <h3>Adicionar novo item</h3>

      <input type="text" name="item_nome" placeholder="Nome do item">
      <input type="text" name="item_categoria" placeholder="Categoria">
      <textarea name="item_descricao" placeholder="Descrição do item"></textarea>
      <input type="number" step="0.01" name="item_preco" placeholder="Preço">

      <button type="submit" class="btn-primary">Salvar alterações</button>
    </form>

    <div class="list-card">
      <h3>Itens do cardápio</h3>

      <?php if (count($itens) > 0): ?>
        <?php foreach ($itens as $item): ?>
          <div class="menu-item">
            <div>
              <strong><?= htmlspecialchars($item['nome']) ?></strong>
              <p><?= htmlspecialchars($item['descricao']) ?></p>
              <small><?= htmlspecialchars($item['categoria'] ?: 'Geral') ?></small>
            </div>
            <span class="produto-preco">R$ <?= number_format($item['preco'], 2, ',', '.') ?></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="empty">Nenhum item cadastrado ainda.</p>
      <?php endif; ?>
    </div>

  </div>
</body>
</html>