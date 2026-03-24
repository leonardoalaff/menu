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
  <link rel="stylesheet" href="style_ver_cardapio2.css">
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

    <div class="cliente-cardapio-banner fade-up delay-2" style="--cor-principal: <?= htmlspecialchars($cardapio['cor_principal']) ?>; <?= !empty($cardapio['imagem_fundo']) ? "background-image: linear-gradient(rgba(0,0,0,0.40), rgba(0,0,0,0.52)), url('" . htmlspecialchars($cardapio['imagem_fundo']) . "'); background-size: cover; background-position: center;" : "" ?>">
      <div class="cliente-cardapio-overlay">
        <span class="cliente-badge">Cardápio Online</span>
        <h1><?= htmlspecialchars($cardapio['nome_negocio']) ?></h1>
        <p><?= nl2br(htmlspecialchars($cardapio['descricao'])) ?></p>

        <div class="hero-mini-list">
          <span><?= $totalItens ?> item(ns)</span>
          <span>Entrega</span>
          <span>Retirada</span>
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

          <div class="produtos-grid">
            <?php foreach ($lista as $item): ?>
              <div 
                class="produto-card"
                data-id="<?= (int)$item['id'] ?>"
                data-nome="<?= htmlspecialchars($item['nome']) ?>"
                data-preco="<?= number_format((float)$item['preco'], 2, '.', '') ?>"
              >
                <div class="produto-imagem-wrap">
                  <?php if (!empty($item['imagem'])): ?>
                    <img class="produto-imagem" src="<?= htmlspecialchars($item['imagem']) ?>" alt="<?= htmlspecialchars($item['nome']) ?>">
                  <?php else: ?>
                    <div class="produto-imagem produto-sem-imagem">🍽</div>
                  <?php endif; ?>
                </div>

                <div class="produto-conteudo">
                  <div class="produto-top">
                    <h4><?= htmlspecialchars($item['nome']) ?></h4>
                    <div class="produto-preco">
                      R$ <?= number_format($item['preco'], 2, ',', '.') ?>
                    </div>
                  </div>

                  <?php if (!empty($item['descricao'])): ?>
                    <p><?= htmlspecialchars($item['descricao']) ?></p>
                  <?php else: ?>
                    <p class="produto-desc-vazia">Delicioso item do cardápio.</p>
                  <?php endif; ?>

                  <div class="produto-acoes">
                    <div class="quantidade-box">
                      <button type="button" class="qtd-btn diminuir">−</button>
                      <span class="quantidade" data-qtd="0">0</span>
                      <button type="button" class="qtd-btn aumentar">+</button>
                    </div>

                    <button type="button" class="btn-add-carrinho">
                      Adicionar
                    </button>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
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

  <div class="carrinho-bar" id="carrinhoBar">
    <div class="carrinho-info">
      <small>Seu carrinho</small>
      <strong><span id="carrinhoQtd">0</span> item(ns)</strong>
    </div>

    <div class="carrinho-total">
      R$ <span id="carrinhoTotal">0,00</span>
    </div>

    <button type="button" class="btn-carrinho-finalizar" id="abrirCarrinho">
      Ver carrinho
    </button>
  </div>

  <div class="modal-carrinho-overlay" id="modalCarrinho">
    <div class="modal-carrinho">
      <div class="modal-carrinho-topo">
        <div>
          <small>Resumo do pedido</small>
          <h3>Seu carrinho</h3>
        </div>
        <button type="button" class="fechar-modal" id="fecharCarrinho">✕</button>
      </div>

      <div class="modal-carrinho-conteudo" id="listaCarrinho">
        <div class="carrinho-vazio">
          <div class="carrinho-vazio-icone">🛒</div>
          <h4>Seu carrinho está vazio</h4>
          <p>Adicione itens para visualizar seu pedido aqui.</p>
        </div>
      </div>

            <div class="modal-carrinho-rodape">
        <div class="cliente-pedido-form">
          <div class="campo-pedido">
            <label for="nomeCliente">Seu nome</label>
            <input type="text" id="nomeCliente" placeholder="Digite seu nome">
          </div>

          <div class="campo-pedido">
            <label for="enderecoCliente">Endereço de entrega</label>
            <input type="text" id="enderecoCliente" placeholder="Rua, número, bairro...">
          </div>
        </div>

        <div class="modal-total-box">
          <span>Total</span>
          <strong>R$ <span id="modalCarrinhoTotal">0,00</span></strong>
        </div>

        <button type="button" class="btn-finalizar-pedido" id="finalizarPedido">
          Finalizar pedido
        </button>
      </div>
    </div>
  </div>

  <script>
document.addEventListener('DOMContentLoaded', function () {
  const carrinho = {};

  const carrinhoQtdEl = document.getElementById('carrinhoQtd');
  const carrinhoTotalEl = document.getElementById('carrinhoTotal');
  const modalCarrinhoTotalEl = document.getElementById('modalCarrinhoTotal');
  const listaCarrinhoEl = document.getElementById('listaCarrinho');

  const modalCarrinho = document.getElementById('modalCarrinho');
  const abrirCarrinhoBtn = document.getElementById('abrirCarrinho');
  const fecharCarrinhoBtn = document.getElementById('fecharCarrinho');
  const finalizarPedidoBtn = document.getElementById('finalizarPedido');
  const nomeClienteEl = document.getElementById('nomeCliente');
  const enderecoClienteEl = document.getElementById('enderecoCliente');

  function formatarMoeda(valor) {
    return Number(valor).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function abrirModalCarrinho() {
    if (!modalCarrinho) return;
    modalCarrinho.style.display = 'flex';
    modalCarrinho.classList.add('ativo');
    document.body.classList.add('modal-open');
  }

  function fecharModalCarrinho() {
    if (!modalCarrinho) return;
    modalCarrinho.classList.remove('ativo');
    document.body.classList.remove('modal-open');

    setTimeout(() => {
      if (!modalCarrinho.classList.contains('ativo')) {
        modalCarrinho.style.display = 'none';
      }
    }, 250);
  }

  function alterarQuantidadeVisual(card, quantidade) {
    const qtdEl = card.querySelector('.quantidade');
    const addBtn = card.querySelector('.btn-add-carrinho');
    const qtdFinal = Math.max(0, quantidade);

    qtdEl.dataset.qtd = qtdFinal;
    qtdEl.textContent = qtdFinal;

    if (qtdFinal > 0) {
      addBtn.classList.add('ativo');
      addBtn.textContent = 'Adicionado';
    } else {
      addBtn.classList.remove('ativo');
      addBtn.textContent = 'Adicionar';
    }
  }

  function renderizarModalCarrinho() {
    if (!listaCarrinhoEl) return;

    const itens = Object.values(carrinho);

    if (itens.length === 0) {
      listaCarrinhoEl.innerHTML = `
        <div class="carrinho-vazio">
          <div class="carrinho-vazio-icone">🛒</div>
          <h4>Seu carrinho está vazio</h4>
          <p>Adicione itens para visualizar seu pedido aqui.</p>
        </div>
      `;
      return;
    }

    let html = '';

    itens.forEach(item => {
      const subtotal = item.preco * item.quantidade;

      html += `
        <div class="item-carrinho-modal">
          <div class="item-carrinho-info">
            <h4>${item.nome}</h4>
            <p>Quantidade: ${item.quantidade}</p>
          </div>

          <div class="item-carrinho-lado">
            <span class="item-carrinho-subtotal">R$ ${formatarMoeda(subtotal)}</span>

            <div class="item-carrinho-acoes">
              <button type="button" class="mini-qtd-btn" data-id="${item.id}" data-delta="-1">−</button>
              <span>${item.quantidade}</span>
              <button type="button" class="mini-qtd-btn" data-id="${item.id}" data-delta="1">+</button>
            </div>
          </div>
        </div>
      `;
    });

    listaCarrinhoEl.innerHTML = html;

    listaCarrinhoEl.querySelectorAll('.mini-qtd-btn').forEach(btn => {
      btn.addEventListener('click', function () {
        const id = this.dataset.id;
        const delta = parseInt(this.dataset.delta, 10);

        const card = document.querySelector('.produto-card[data-id="' + id + '"]');
        if (!card) return;

        const qtdEl = card.querySelector('.quantidade');
        const qtdAtual = parseInt(qtdEl.dataset.qtd, 10) || 0;

        alterarQuantidadeVisual(card, qtdAtual + delta);
        atualizarCarrinho();
      });
    });
  }

  function atualizarCarrinho() {
    let totalItens = 0;
    let totalValor = 0;

    document.querySelectorAll('.produto-card').forEach(card => {
      const id = card.dataset.id;
      const nome = card.dataset.nome;
      const preco = parseFloat(card.dataset.preco) || 0;
      const qtdEl = card.querySelector('.quantidade');
      const qtd = parseInt(qtdEl.dataset.qtd, 10) || 0;

      if (qtd > 0) {
        carrinho[id] = {
          id: id,
          nome: nome,
          preco: preco,
          quantidade: qtd
        };
      } else {
        delete carrinho[id];
      }

      totalItens += qtd;
      totalValor += qtd * preco;
    });

    if (carrinhoQtdEl) carrinhoQtdEl.textContent = totalItens;
    if (carrinhoTotalEl) carrinhoTotalEl.textContent = formatarMoeda(totalValor);
    if (modalCarrinhoTotalEl) modalCarrinhoTotalEl.textContent = formatarMoeda(totalValor);

    renderizarModalCarrinho();
  }

  document.querySelectorAll('.produto-card').forEach(card => {
    const diminuirBtn = card.querySelector('.diminuir');
    const aumentarBtn = card.querySelector('.aumentar');
    const addBtn = card.querySelector('.btn-add-carrinho');
    const qtdEl = card.querySelector('.quantidade');

    function setQuantidade(valor) {
      alterarQuantidadeVisual(card, valor);
      atualizarCarrinho();
    }

    if (diminuirBtn) {
      diminuirBtn.addEventListener('click', () => {
        setQuantidade((parseInt(qtdEl.dataset.qtd, 10) || 0) - 1);
      });
    }

    if (aumentarBtn) {
      aumentarBtn.addEventListener('click', () => {
        setQuantidade((parseInt(qtdEl.dataset.qtd, 10) || 0) + 1);
      });
    }

    if (addBtn) {
      addBtn.addEventListener('click', () => {
        const atual = parseInt(qtdEl.dataset.qtd, 10) || 0;
        setQuantidade(atual === 0 ? 1 : atual);
      });
    }
  });

  if (abrirCarrinhoBtn) {
    abrirCarrinhoBtn.addEventListener('click', function () {
      abrirModalCarrinho();
    });
  }

  if (fecharCarrinhoBtn) {
    fecharCarrinhoBtn.addEventListener('click', function () {
      fecharModalCarrinho();
    });
  }

  if (modalCarrinho) {
    modalCarrinho.style.display = 'none';

    modalCarrinho.addEventListener('click', function (e) {
      if (e.target === modalCarrinho) {
        fecharModalCarrinho();
      }
    });
  }

    if (finalizarPedidoBtn) {
    finalizarPedidoBtn.addEventListener('click', function () {
      const itens = Object.values(carrinho);

      if (itens.length === 0) {
        alert('Seu carrinho está vazio.');
        return;
      }

      const nomeCliente = nomeClienteEl ? nomeClienteEl.value.trim() : '';
      const enderecoCliente = enderecoClienteEl ? enderecoClienteEl.value.trim() : '';

      if (nomeCliente === '') {
        alert('Por favor, informe seu nome.');
        if (nomeClienteEl) nomeClienteEl.focus();
        return;
      }

      if (enderecoCliente === '') {
        alert('Por favor, informe seu endereço.');
        if (enderecoClienteEl) enderecoClienteEl.focus();
        return;
      }

      let mensagem = 'Novo pedido:%0A%0A';
      mensagem += 'Nome: ' + nomeCliente + '%0A';
      mensagem += 'Endereço: ' + enderecoCliente + '%0A%0A';
      mensagem += 'Itens do pedido:%0A';

      let total = 0;

      itens.forEach(item => {
        const subtotal = item.preco * item.quantidade;
        total += subtotal;
        mensagem += '- ' + item.nome + ' | ' + item.quantidade + 'x | R$ ' + formatarMoeda(subtotal) + '%0A';
      });

      mensagem += '%0ATotal: R$ ' + formatarMoeda(total);

      alert(decodeURIComponent(mensagem));
    });
  }

  atualizarCarrinho();
});
</script>
</body>
</html>
