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
    die("Cardápio não encontrado.");
}

$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id = ? ORDER BY categoria ASC, nome ASC");
$stmt->execute([$cardapio['id']]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$itensPorCategoria = [];

foreach ($itens as $item) {
    $categoria = trim($item['categoria']) !== '' ? $item['categoria'] : 'Geral';
    $itensPorCategoria[$categoria][] = $item;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visualização do Cardápio</title>
  <link rel="stylesheet" href="style1.css">
</head>
<body class="mobile-body">
  <div class="mobile-app">

    <div class="topbar">
      <div>
        <small>Prévia do seu</small>
        <h2>Cardápio</h2>
      </div>
      <a href="painel.php" class="btn-ghost">Voltar</a>
    </div>

    <div class="cliente-cardapio-banner" style="--cor-principal: <?= htmlspecialchars($cardapio['cor_principal']) ?>;">
      <div class="cliente-cardapio-overlay">
        <span class="cliente-badge">Cardápio Online</span>
        <h1><?= htmlspecialchars($cardapio['nome_negocio']) ?></h1>
        <p><?= nl2br(htmlspecialchars($cardapio['descricao'])) ?></p>
      </div>
    </div>

    <?php if (count($itens) > 0): ?>
      <?php foreach ($itensPorCategoria as $categoria => $lista): ?>
        <div class="categoria-card">
          <h3 class="categoria-titulo"><?= htmlspecialchars($categoria) ?></h3>

          <?php foreach ($lista as $item): ?>
            <div class="produto-card">
              <div class="produto-info">
                <h4><?= htmlspecialchars($item['nome']) ?></h4>

                <?php if (!empty($item['descricao'])): ?>
                  <p><?= htmlspecialchars($item['descricao']) ?></p>
                <?php endif; ?>
              </div>

              <div class="produto-preco">
                R$ <?= number_format($item['preco'], 2, ',', '.') ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="list-card">
        <p class="empty">Seu cardápio ainda não possui itens cadastrados.</p>
      </div>
    <?php endif; ?>

  </div>
</body>
</html>