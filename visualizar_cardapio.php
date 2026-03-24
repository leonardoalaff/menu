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

$totalItens = count($itens);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visualização do Cardápio</title>
  <link rel="stylesheet" href="style_ver_cardapio.css">
</head>
<body class="mobile-body">

  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>
  <div class="bg-shape shape-3"></div>

  <div class="mobile-app">

    <div class="topbar fade-up delay-1">
      <div>
        <small>Prévia do seu</small>
        <h2>Cardápio</h2>
      </div>
      <a href="painel.php" class="btn-ghost">Voltar</a>
    </div>

    <div class="cliente-cardapio-banner fade-up delay-2" style="--cor-principal: <?= htmlspecialchars($cardapio['cor_principal']) ?>; <?= !empty($cardapio['imagem_fundo']) ? "background-image: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.45)), url('" . htmlspecialchars($cardapio['imagem_fundo']) . "'); background-size: cover; background-position: center;" : "" ?>">
      <div class="cliente-cardapio-overlay">
        <span class="cliente-badge">Cardápio Online</span>
        <h1><?= htmlspecialchars($cardapio['nome_negocio']) ?></h1>
        <p><?= nl2br(htmlspecialchars($cardapio['descricao'])) ?></p>

        <div class="hero-mini-list">
          <span><?= $totalItens ?> item(ns)</span>
          <span>Digital</span>
          <span>Mobile</span>
        </div>
      </div>
    </div>

    <?php if (count($itens) > 0): ?>
      <?php foreach ($itensPorCategoria as $categoria => $lista): ?>
        <div class="categoria-card fade-up delay-3">
          <div class="categoria-header">
            <h3 class="categoria-titulo"><?= htmlspecialchars($categoria) ?></h3>
            <span class="categoria-count"><?= count($lista) ?> item(ns)</span>
          </div>

          <?php foreach ($lista as $item): ?>
            <div class="produto-card<?= !empty($item['imagem']) ? ' has-image' : '' ?>">
              <?php if (!empty($item['imagem'])): ?>
                <img class="produto-imagem" src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
              <?php endif; ?>

              <div class="produto-info">
                <div class="produto-top">
                  <h4><?= htmlspecialchars($item['nome']) ?></h4>
                </div>

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
      <div class="empty-card fade-up delay-3">
        <div class="empty-icon">🍽</div>
        <h3>Seu cardápio está vazio</h3>
        <p>Adicione itens no painel para visualizar aqui como seus clientes verão.</p>
      </div>
    <?php endif; ?>

  </div>
</body>
</html>