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
    $stmt->execute([$usuario_id, "Meu Negócio", "#3b8edb", "Seu cardápio digital"]);

    $stmt = $db->prepare("SELECT * FROM cardapios WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $cardapio = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id = ? ORDER BY categoria ASC, nome ASC");
$stmt->execute([$cardapio['id']]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalItens = count($itens);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel - CardápioOn</title>
  <link rel="stylesheet" href="style_painel2.css">
</head>
<body class="mobile-body">

  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>
  <div class="bg-shape shape-3"></div>

  <div class="mobile-app">

    <header class="topbar fade-up delay-1">
      <div class="topbar-user">
        <small>Bem-vindo(a)</small>
        <h2><?= htmlspecialchars($_SESSION['usuario_nome']) ?></h2>
      </div>
      <a href="logout.php" class="btn-ghost">Sair</a>
    </header>

    <section class="hero-card fade-up delay-2">
      <span class="hero-badge">Painel do seu cardápio</span>
      <div class="logo-badge"></div>
      <h1><?= htmlspecialchars($cardapio['nome_negocio']) ?></h1>
      <p>Gerencie seu cardápio digital, edite os dados do seu negócio e acompanhe como ele aparece para seus clientes.</p>

      <div class="hero-mini-list">
        <span><?= $totalItens ?> item(ns)</span>
        <span>Mobile</span>
        <span>Online</span>
      </div>
    </section>

    <section class="stats-grid fade-up delay-2">
      <div class="stat-card">
        <small>Total de itens</small>
        <strong><?= $totalItens ?></strong>
      </div>

      <div class="stat-card">
        <small>Cor principal</small>
        <div class="stat-color">
          <span class="color-chip" style="background: <?= htmlspecialchars($cardapio['cor_principal']) ?>;"></span>
          <strong><?= htmlspecialchars($cardapio['cor_principal']) ?></strong>
        </div>
      </div>
    </section>

    <section class="preview-card fade-up delay-2">
      <div class="preview-top">
        <div>
          <small class="section-mini-title">Prévia do cardápio</small>
          <h3><?= htmlspecialchars($cardapio['nome_negocio']) ?></h3>
        </div>
        <span class="preview-badge">Ao vivo</span>
      </div>

      <p><?= htmlspecialchars($cardapio['descricao']) ?></p>

      <div class="preview-footer">
        <div class="preview-color">
          <span class="color-chip large" style="background: <?= htmlspecialchars($cardapio['cor_principal']) ?>;"></span>
          <span>Identidade visual</span>
        </div>

        <a href="visualizar_cardapio.php" class="btn-primary btn-inline">Ver cardápio</a>
      </div>
    </section>

    <form class="form-card fade-up delay-3" action="salvar_cardapio.php" method="POST" enctype="multipart/form-data">
      <div class="section-header">
        <div>
          <small class="section-mini-title">Personalização</small>
          <h3>Editar cardápio</h3>
        </div>
        <span class="section-badge">Principal</span>
      </div>

      <p class="form-subtitle">Atualize os dados do seu negócio e adicione novos itens ao cardápio.</p>

      <input type="hidden" name="acao" value="salvar_cardapio">
      <input type="hidden" name="cardapio_id" value="<?= $cardapio['id'] ?>">

      <label class="label-inline">Nome do negócio</label>
      <input
        type="text"
        name="nome_negocio"
        placeholder="Nome do negócio"
        value="<?= htmlspecialchars($cardapio['nome_negocio']) ?>"
        required
      >

      <label class="label-inline">Descrição</label>
      <textarea
        name="descricao"
        placeholder="Descrição do negócio"
      ><?= htmlspecialchars($cardapio['descricao']) ?></textarea>

      <label class="label-inline">Cor principal do cardápio</label>
      <div class="color-row">
        <input type="color" name="cor_principal" value="<?= htmlspecialchars($cardapio['cor_principal']) ?>">
        <div class="color-info">
          <span class="color-chip" style="background: <?= htmlspecialchars($cardapio['cor_principal']) ?>;"></span>
          <small>Escolha a cor que representa sua marca</small>
        </div>
      </div>

      <label class="label-inline">Imagem de fundo do cardápio</label>
      <?php if (!empty($cardapio['imagem_fundo'])): ?>
        <div class="upload-preview banner-preview">
          <img src="<?= htmlspecialchars($cardapio['imagem_fundo']) ?>" alt="Imagem de fundo do cardápio">
        </div>
        <div class="media-actions">
          <span class="media-help">Você pode trocar ou remover a imagem atual.</span>
          <form action="salvar_cardapio.php" method="POST" class="inline-action-form" onsubmit="return confirm('Remover a imagem de fundo atual?');">
            <input type="hidden" name="acao" value="remover_fundo">
            <input type="hidden" name="cardapio_id" value="<?= $cardapio['id'] ?>">
            <button type="submit" class="btn-secondary btn-danger-lite">Remover imagem de fundo</button>
          </form>
        </div>
      <?php endif; ?>
      <input type="file" name="imagem_fundo" accept="image/*">

      <hr>

      <div class="section-header inner">
        <div>
          <small class="section-mini-title">Novo produto</small>
          <h3>Adicionar item</h3>
        </div>
      </div>

      <label class="label-inline">Nome do item</label>
      <input type="text" name="item_nome" placeholder="Ex: X-Burguer Especial">

      <label class="label-inline">Categoria</label>
      <input type="text" name="item_categoria" placeholder="Ex: Hambúrgueres">

      <label class="label-inline">Descrição do item</label>
      <textarea name="item_descricao" placeholder="Descrição do item"></textarea>

      <label class="label-inline">Preço</label>
      <input type="number" step="0.01" name="item_preco" placeholder="Ex: 29.90">

      <label class="label-inline">Foto do item</label>
      <input type="file" name="item_imagem" accept="image/*">

      <button type="submit" class="btn-primary">Salvar alterações</button>
    </form>

    <section class="list-card fade-up delay-3">
      <div class="section-header">
        <div>
          <small class="section-mini-title">Seu conteúdo</small>
          <h3>Itens do cardápio</h3>
        </div>
        <span class="section-badge"><?= $totalItens ?> item(ns)</span>
      </div>

      <?php if (count($itens) > 0): ?>
        <?php foreach ($itens as $item): ?>
          <div class="menu-item item-card-extended">
            <div class="menu-item-top">
              <div class="menu-item-left">
                <?php if (!empty($item['imagem'])): ?>
                  <img class="item-thumb" src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                <?php else: ?>
                  <div class="item-category-dot"></div>
                <?php endif; ?>
                <div>
                  <strong><?= htmlspecialchars($item['nome']) ?></strong>
                  <p><?= htmlspecialchars($item['descricao']) ?></p>
                  <small><?= htmlspecialchars($item['categoria'] ?: 'Geral') ?></small>
                </div>
              </div>

              <span class="produto-preco">R$ <?= number_format($item['preco'], 2, ',', '.') ?></span>
            </div>

            <div class="item-photo-tools">
              <form action="salvar_cardapio.php" method="POST" enctype="multipart/form-data" class="item-photo-form">
                <input type="hidden" name="acao" value="atualizar_foto_item">
                <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                <label class="label-inline small"><?= !empty($item['imagem']) ? 'Trocar foto do item' : 'Adicionar foto ao item' ?></label>
                <div class="item-photo-row">
                  <input type="file" name="nova_item_imagem" accept="image/*" required>
                  <button type="submit" class="btn-secondary">Salvar foto</button>
                </div>
              </form>

              <?php if (!empty($item['imagem'])): ?>
                <form action="salvar_cardapio.php" method="POST" class="inline-action-form" onsubmit="return confirm('Remover a foto deste item?');">
                  <input type="hidden" name="acao" value="remover_foto_item">
                  <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                  <button type="submit" class="btn-secondary btn-danger-lite">Remover foto</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">🍽</div>
          <h4>Nenhum item cadastrado</h4>
          <p>Preencha o formulário acima para adicionar o primeiro item do seu cardápio.</p>
        </div>
      <?php endif; ?>
    </section>

  </div>
</body>
</html>