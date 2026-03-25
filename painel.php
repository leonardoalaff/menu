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
$totalComImagem = 0;

foreach ($itens as $item) {
    if (!empty($item['imagem'])) {
        $totalComImagem++;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel - CardápioOn</title>
  <link rel="stylesheet" href="style_painel3.css">
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

    <section class="dashboard-grid fade-up delay-2">
      <div class="dashboard-left">
        <section class="stats-grid">
          <div class="stat-card">
            <small>Total de itens</small>
            <strong><?= $totalItens ?></strong>
          </div>

          <div class="stat-card">
            <small>Itens com foto</small>
            <strong><?= $totalComImagem ?></strong>
          </div>

          <div class="stat-card">
            <small>Cor principal</small>
            <div class="stat-color">
              <span class="color-chip" style="background: <?= htmlspecialchars($cardapio['cor_principal']) ?>;"></span>
              <strong><?= htmlspecialchars($cardapio['cor_principal']) ?></strong>
            </div>
          </div>

          <div class="stat-card">
            <small>Status</small>
            <strong>Ativo</strong>
          </div>
        </section>

        <section class="quick-actions-card">
          <div class="section-header">
            <div>
              <small class="section-mini-title">Atalhos</small>
              <h3>Ações rápidas</h3>
            </div>
          </div>

          <div class="quick-actions-grid">
            <a href="visualizar_cardapio.php" class="quick-action-box">
              <span class="quick-icon">👁</span>
              <strong>Ver cardápio</strong>
              <small>Abrir visualização</small>
            </a>

            <a href="#form-editar" class="quick-action-box">
              <span class="quick-icon">✏</span>
              <strong>Editar dados</strong>
              <small>Atualizar negócio</small>
            </a>

            <a href="#novo-item" class="quick-action-box">
              <span class="quick-icon">➕</span>
              <strong>Novo item</strong>
              <small>Adicionar produto</small>
            </a>

            <a href="#lista-itens" class="quick-action-box">
              <span class="quick-icon">🍽</span>
              <strong>Seus itens</strong>
              <small>Gerenciar lista</small>
            </a>
          </div>
        </section>

        <section class="preview-card">
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
      </div>

      <div class="dashboard-right">
        <form id="form-editar" class="form-card fade-up delay-3" action="salvar_cardapio.php" method="POST" enctype="multipart/form-data">
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

          <div class="form-grid-2">
            <div class="field-group full">
              <label class="label-inline">Nome do negócio</label>
              <input
                type="text"
                name="nome_negocio"
                placeholder="Nome do negócio"
                value="<?= htmlspecialchars($cardapio['nome_negocio']) ?>"
                required
              >
            </div>

            <div class="field-group full">
              <label class="label-inline">Descrição</label>
              <textarea
                name="descricao"
                placeholder="Descrição do negócio"
              ><?= htmlspecialchars($cardapio['descricao']) ?></textarea>
            </div>

            <div class="field-group full">
              <label class="label-inline">Cor principal do cardápio</label>
              <div class="color-row">
                <input type="color" name="cor_principal" value="<?= htmlspecialchars($cardapio['cor_principal']) ?>">
                <div class="color-info">
                  <span class="color-chip" style="background: <?= htmlspecialchars($cardapio['cor_principal']) ?>;"></span>
                  <small>Escolha a cor que representa sua marca</small>
                </div>
              </div>
            </div>

            <div class="field-group full">
              <label class="label-inline">Imagem de fundo do cardápio</label>

              <?php if (!empty($cardapio['imagem_fundo'])): ?>
                <div class="upload-preview banner-preview">
                  <img src="<?= htmlspecialchars($cardapio['imagem_fundo']) ?>" alt="Imagem de fundo do cardápio">
                </div>

                <div class="media-actions">
                  <span class="media-help">Você pode trocar ou remover a imagem atual.</span>
                  <div>
                    <button type="submit" formaction="salvar_cardapio.php" formmethod="POST" name="acao" value="remover_fundo" class="btn-secondary btn-danger-lite" onclick="return confirm('Remover a imagem de fundo atual?');">
                      Remover imagem de fundo
                    </button>
                  </div>
                </div>
              <?php endif; ?>

              <input type="file" name="imagem_fundo" accept="image/*">
            </div>
          </div>

          <hr>

          <div id="novo-item" class="section-header inner">
            <div>
              <small class="section-mini-title">Novo produto</small>
              <h3>Adicionar item</h3>
            </div>
          </div>

          <div class="form-grid-2">
            <div class="field-group">
              <label class="label-inline">Nome do item</label>
              <input type="text" name="item_nome" placeholder="Ex: X-Burguer Especial">
            </div>

            <div class="field-group">
              <label class="label-inline">Categoria</label>
              <input type="text" name="item_categoria" placeholder="Ex: Hambúrgueres">
            </div>

            <div class="field-group full">
              <label class="label-inline">Descrição do item</label>
              <textarea name="item_descricao" placeholder="Descrição do item"></textarea>
            </div>

            <div class="field-group">
              <label class="label-inline">Preço</label>
              <input type="number" step="0.01" name="item_preco" placeholder="Ex: 29.90">
            </div>

            <div class="field-group">
              <label class="label-inline">Foto do item</label>
              <input type="file" name="item_imagem" accept="image/*">
            </div>
          </div>

          <button type="submit" class="btn-primary">Salvar alterações</button>
        </form>
      </div>
    </section>

    <section id="lista-itens" class="list-card fade-up delay-3">
      <div class="section-header">
        <div>
          <small class="section-mini-title">Seu conteúdo</small>
          <h3>Itens do cardápio</h3>
        </div>
        <span class="section-badge"><?= $totalItens ?> item(ns)</span>
      </div>

      <?php if (count($itens) > 0): ?>
  <div class="items-grid-panel compact-items-grid <?= $totalItens > 5 ? 'grid-2' : '' ?>">
    <?php foreach ($itens as $item): ?>
      <div class="ifood-item-card">
        <div class="ifood-item-top">
          <?php if (!empty($item['imagem'])): ?>
            <img class="ifood-item-thumb" src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
          <?php else: ?>
            <div class="ifood-item-thumb ifood-item-thumb-placeholder">🍽</div>
          <?php endif; ?>

          <div class="ifood-item-info">
            <div class="ifood-item-head">
              <h4><?= htmlspecialchars($item['nome']) ?></h4>
              <span class="ifood-price">R$ <?= number_format((float)$item['preco'], 2, ',', '.') ?></span>
            </div>

            <?php if (!empty($item['descricao'])): ?>
              <p><?= htmlspecialchars($item['descricao']) ?></p>
            <?php else: ?>
              <p class="item-desc-empty">Sem descrição cadastrada.</p>
            <?php endif; ?>

            <div class="ifood-tags">
              <span class="ifood-tag"><?= htmlspecialchars(!empty($item['categoria']) ? $item['categoria'] : 'Geral') ?></span>
              <span class="ifood-tag soft"><?= !empty($item['imagem']) ? 'Com foto' : 'Sem foto' ?></span>
            </div>
          </div>
        </div>

        <div class="ifood-actions">
          <button type="button" class="btn-secondary ifood-btn toggle-upload-btn" data-target="upload-<?= (int)$item['id'] ?>">
            <?= !empty($item['imagem']) ? 'Trocar foto' : 'Adicionar foto' ?>
          </button>

          <?php if (!empty($item['imagem'])): ?>
            <form action="salvar_cardapio.php" method="POST" class="inline-action-form" onsubmit="return confirm('Remover a foto deste item?');">
              <input type="hidden" name="acao" value="remover_foto_item">
              <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
              <button type="submit" class="btn-secondary btn-danger-lite ifood-btn">Remover</button>
            </form>
          <?php endif; ?>
        </div>

        <div class="compact-upload-panel" id="upload-<?= (int)$item['id'] ?>">
          <form action="salvar_cardapio.php" method="POST" enctype="multipart/form-data" class="compact-upload-form">
            <input type="hidden" name="acao" value="atualizar_foto_item">
            <input type="hidden" name="item_id" value="<?= $item['id'] ?>">

            <div class="compact-upload-row">
              <input type="file" name="nova_item_imagem" accept="image/*" required>
              <button type="submit" class="btn-secondary">Salvar foto</button>
            </div>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php else: ?>
        <div class="empty-state">
          <div class="empty-icon">🍽</div>
          <h4>Nenhum item cadastrado</h4>
          <p>Preencha o formulário acima para adicionar o primeiro item do seu cardápio.</p>
        </div>
      <?php endif; ?>
    </section>

  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.toggle-upload-btn').forEach(function (botao) {
        botao.addEventListener('click', function () {
          const id = this.getAttribute('data-target');
          const painel = document.getElementById(id);

          if (!painel) return;

          const aberto = painel.classList.contains('ativo');

          document.querySelectorAll('.compact-upload-panel').forEach(function (item) {
            item.classList.remove('ativo');
          });

          if (!aberto) {
            painel.classList.add('ativo');
          }
        });
      });
    });
  </script>
</body>
</html>