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

date_default_timezone_set('America/Sao_Paulo');

$horarioAbertura = trim((string) ($cardapio['horario_abertura'] ?? ''));
$horarioFechamento = trim((string) ($cardapio['horario_fechamento'] ?? ''));
$agoraMinutos = (int) date('H') * 60 + (int) date('i');
$statusFuncionamento = null;

if ($horarioAbertura !== '' && $horarioFechamento !== '') {
    [$aberturaHora, $aberturaMinuto] = array_pad(array_map('intval', explode(':', $horarioAbertura)), 2, 0);
    [$fechamentoHora, $fechamentoMinuto] = array_pad(array_map('intval', explode(':', $horarioFechamento)), 2, 0);

    $aberturaTotal = ($aberturaHora * 60) + $aberturaMinuto;
    $fechamentoTotal = ($fechamentoHora * 60) + $fechamentoMinuto;

    if ($aberturaTotal === $fechamentoTotal) {
        $statusFuncionamento = 'aberto';
    } elseif ($aberturaTotal < $fechamentoTotal) {
        $statusFuncionamento = ($agoraMinutos >= $aberturaTotal && $agoraMinutos < $fechamentoTotal) ? 'aberto' : 'fechado';
    } else {
        $statusFuncionamento = ($agoraMinutos >= $aberturaTotal || $agoraMinutos < $fechamentoTotal) ? 'aberto' : 'fechado';
    }
}


$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id = ? ORDER BY nome ASC");
$stmt->execute([$cardapio['id']]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalItens = count($itens);

$cardapioIdAtual = (int) ($cardapio['id'] ?? 0);
$dadosChatbot = [
    'id' => $cardapioIdAtual,
    'nome_negocio' => (string) ($cardapio['nome_negocio'] ?? 'Restaurante'),
    'descricao' => (string) ($cardapio['descricao'] ?? ''),
    'endereco_estabelecimento' => (string) ($cardapio['endereco_estabelecimento'] ?? ''),
    'horario_abertura' => $horarioAbertura,
    'horario_fechamento' => $horarioFechamento,
    'status_funcionamento' => (string) ($statusFuncionamento ?? ''),
    'itens' => array_map(function ($item) {
        return [
            'nome' => (string) ($item['nome'] ?? ''),
            'descricao' => (string) ($item['descricao'] ?? ''),
            'preco' => (float) ($item['preco'] ?? 0)
        ];
    }, $itens)
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Visualização do Cardápio</title>
  <link rel="stylesheet" href="style_ver_cardapio5.css">
</head>
<body class="mobile-body" style="background: <?= htmlspecialchars($cardapio['cor_fundo_cardapio'] ?? '#f3f4f6') ?>;">

  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>
  <div class="bg-shape shape-3"></div>

  <div
    class="mobile-app"
    style="
      --cor-preco: <?= htmlspecialchars($cardapio['cor_preco'] ?? '#f97316') ?>;
      --cor-botao-adicionar: <?= htmlspecialchars($cardapio['cor_botao_adicionar'] ?? '#ef4444') ?>;
      --cor-botao-ver-carrinho: <?= htmlspecialchars($cardapio['cor_botao_ver_carrinho'] ?? '#ef4444') ?>;
      --cor-botao-finalizar-pedido: <?= htmlspecialchars($cardapio['cor_botao_finalizar_pedido'] ?? '#ef4444') ?>;
      --cor-titulo-cabecalho: <?= htmlspecialchars($cardapio['cor_titulo_cabecalho'] ?? '#2f2f2f') ?>;
      --cor-descricao-cabecalho: <?= htmlspecialchars($cardapio['cor_descricao_cabecalho'] ?? '#4b5563') ?>;
      --cor-fundo-cardapio: <?= htmlspecialchars($cardapio['cor_fundo_cardapio'] ?? '#f3f4f6') ?>;
    "
  >

    <div class="topbar fade-up delay-1">
      <div>
        <small>Prévia do seu</small>
        <h2>Cardápio</h2>
      </div>
      <a href="painel.php" class="btn-ghost">Voltar</a>
    </div>

    <div
      class="cliente-cardapio-banner fade-up delay-2"
      style="
        --cor-principal: <?= htmlspecialchars($cardapio['cor_principal'] ?? '#333') ?>;
        <?= !empty($cardapio['imagem_fundo'])
          ? "background-image: linear-gradient(rgba(0,0,0,0.35), rgba(0,0,0,0.45)), url('" . htmlspecialchars($cardapio['imagem_fundo']) . "'); background-size: cover; background-position: center;"
          : ""
        ?>
      "
    >
      <div class="cliente-cardapio-overlay">
        <span class="cliente-badge">Cardápio Online</span>
        <h1 style="color: <?= htmlspecialchars($cardapio['cor_titulo_cabecalho'] ?? '#2f2f2f') ?>;"><?= htmlspecialchars($cardapio['nome_negocio'] ?? '') ?></h1>
        <p style="color: <?= htmlspecialchars($cardapio['cor_descricao_cabecalho'] ?? '#4b5563') ?>;"><?= nl2br(htmlspecialchars($cardapio['descricao'] ?? '')) ?></p>

        <div class="hero-mini-list">
          <span><?= $totalItens ?> item(ns)</span>
          <?php if ($statusFuncionamento !== null): ?>
            <span class="status-loja <?= $statusFuncionamento === 'aberto' ? 'aberto' : 'fechado' ?>"><?= $statusFuncionamento === 'aberto' ? 'Aberto' : 'Fechado' ?></span>
          <?php endif; ?>
          <span>Entrega</span>
          <span>Retirada</span>
        </div>

        <?php if (!empty($cardapio['endereco_estabelecimento']) || ($horarioAbertura !== '' && $horarioFechamento !== '')): ?>
          <div class="hero-store-info">
            <?php if (!empty($cardapio['endereco_estabelecimento'])): ?>
              <div class="store-info-item">
                <span class="store-info-icon">📍</span>
                <span><?= htmlspecialchars($cardapio['endereco_estabelecimento']) ?></span>
              </div>
            <?php endif; ?>

            <?php if ($horarioAbertura !== '' && $horarioFechamento !== ''): ?>
              <div class="store-info-item">
                <span class="store-info-icon">🕒</span>
                <span>Funcionamento: <?= htmlspecialchars($horarioAbertura) ?> às <?= htmlspecialchars($horarioFechamento) ?></span>
              </div>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if ($totalItens > 0): ?>
      <div class="categoria-card fade-up delay-3">
        <div class="categoria-header">
          <h3 class="categoria-titulo">Produtos</h3>
          <span class="categoria-count"><?= $totalItens ?> item(ns)</span>
        </div>

        <div class="produtos-grid">
          <?php foreach ($itens as $item): ?>
            <div
              class="produto-card"
              data-id="<?= (int)($item['id'] ?? 0) ?>"
              data-nome="<?= htmlspecialchars($item['nome'] ?? '') ?>"
              data-preco="<?= number_format((float)($item['preco'] ?? 0), 2, '.', '') ?>"
            >
              <div class="produto-imagem-wrap">
                <?php if (!empty($item['imagem'])): ?>
                  <img
                    class="produto-imagem"
                    src="<?= htmlspecialchars($item['imagem']) ?>"
                    alt="<?= htmlspecialchars($item['nome'] ?? '') ?>"
                  >
                <?php else: ?>
                  <div class="produto-imagem produto-sem-imagem">🍽</div>
                <?php endif; ?>
              </div>

              <div class="produto-conteudo">
                <div class="produto-top">
                  <h4><?= htmlspecialchars($item['nome'] ?? '') ?></h4>
                  <div class="produto-preco">
                    <span class="produto-preco-label"></span>
                    <strong>R$ <?= number_format((float)($item['preco'] ?? 0), 2, ',', '.') ?></strong>
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

                  <button type="button" class="btn-add-carrinho" style="background: <?= htmlspecialchars($cardapio['cor_botao_adicionar'] ?? '#ef4444') ?>;">
                    Adicionar
                  </button>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
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

    <button type="button" class="btn-carrinho-finalizar" id="abrirCarrinho" style="background: <?= htmlspecialchars($cardapio['cor_botao_ver_carrinho'] ?? '#ef4444') ?>;">
      Ver carrinho
    </button>
  </div>


  <button type="button" class="chatbot-fab" id="abrirChatbot" aria-label="Abrir assistente do cardápio">
    <span class="chatbot-fab-icon">💬</span>
    <span class="chatbot-fab-texto">Ajuda</span>
  </button>

  <div class="chatbot-overlay" id="chatbotOverlay">
    <div class="chatbot-janela">
      <div class="chatbot-topo">
        <div>
          <small>Assistente virtual</small>
          <h3>Tire dúvidas sobre o cardápio</h3>
        </div>
        <button type="button" class="chatbot-fechar" id="fecharChatbot" aria-label="Fechar chatbot">✕</button>
      </div>

      <div class="chatbot-mensagens" id="chatbotMensagens">
        <div class="chatbot-msg bot">
          <div class="chatbot-bolha">
            Olá! Posso ajudar com itens, preços, horários de funcionamento e sugestões do cardápio.
          </div>
        </div>
      </div>

      <form class="chatbot-form" id="chatbotForm">
        <input type="text" id="chatbotInput" placeholder="Ex.: O que você recomenda hoje?" maxlength="300" autocomplete="off">
        <button type="submit" id="chatbotEnviar">Enviar</button>
      </form>
    </div>
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

        <button type="button" class="btn-finalizar-pedido" id="finalizarPedido" style="background: <?= htmlspecialchars($cardapio['cor_botao_finalizar_pedido'] ?? '#ef4444') ?>;">
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

      const chatbotConfig = <?= json_encode($dadosChatbot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
      const chatbotOverlay = document.getElementById('chatbotOverlay');
      const abrirChatbotBtn = document.getElementById('abrirChatbot');
      const fecharChatbotBtn = document.getElementById('fecharChatbot');
      const chatbotMensagensEl = document.getElementById('chatbotMensagens');
      const chatbotForm = document.getElementById('chatbotForm');
      const chatbotInput = document.getElementById('chatbotInput');
      const chatbotEnviar = document.getElementById('chatbotEnviar');

      function formatarMoeda(valor) {
        return Number(valor).toLocaleString('pt-BR', {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2
        });
      }

      function escaparHtml(texto) {
        return String(texto || '')
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      function adicionarMensagemChatbot(tipo, texto) {
        if (!chatbotMensagensEl) return;

        const bloco = document.createElement('div');
        bloco.className = 'chatbot-msg ' + tipo;
        bloco.innerHTML = '<div class="chatbot-bolha">' + escaparHtml(texto).replace(/\n/g, '<br>') + '</div>';
        chatbotMensagensEl.appendChild(bloco);
        chatbotMensagensEl.scrollTop = chatbotMensagensEl.scrollHeight;
      }

      function abrirChatbot() {
        if (!chatbotOverlay) return;
        chatbotOverlay.classList.add('ativo');
        chatbotOverlay.style.display = 'flex';
        setTimeout(() => {
          if (chatbotInput) chatbotInput.focus();
        }, 120);
      }

      function fecharChatbot() {
        if (!chatbotOverlay) return;
        chatbotOverlay.classList.remove('ativo');
        setTimeout(() => {
          if (!chatbotOverlay.classList.contains('ativo')) {
            chatbotOverlay.style.display = 'none';
          }
        }, 200);
      }

      async function enviarMensagemChatbot(mensagem) {
        adicionarMensagemChatbot('user', mensagem);
        adicionarMensagemChatbot('bot', 'Digitando...');

        const placeholders = chatbotMensagensEl ? chatbotMensagensEl.querySelectorAll('.chatbot-msg.bot .chatbot-bolha') : [];
        const placeholder = placeholders.length ? placeholders[placeholders.length - 1] : null;

        try {
          const resposta = await fetch('chat_cliente.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({
              mensagem: mensagem,
              cardapio_id: chatbotConfig.id
            })
          });

          const dados = await resposta.json();

          if (!resposta.ok || !dados.ok) {
            throw new Error(dados.erro || 'Não foi possível obter uma resposta agora.');
          }

          if (placeholder) {
            placeholder.innerHTML = escaparHtml(dados.resposta || 'Desculpe, não consegui responder agora.').replace(/\n/g, '<br>');
          } else {
            adicionarMensagemChatbot('bot', dados.resposta || 'Desculpe, não consegui responder agora.');
          }

          if (dados.acao === 'adicionar_ao_carrinho' && Array.isArray(dados.acoes_carrinho) && dados.acoes_carrinho.length > 0) {
            adicionarItensViaChatbot(dados.acoes_carrinho);
          }
        } catch (erro) {
          if (placeholder) {
            placeholder.innerHTML = escaparHtml(erro.message || 'Erro ao chamar o assistente.');
          } else {
            adicionarMensagemChatbot('bot', erro.message || 'Erro ao chamar o assistente.');
          }
        }

        if (chatbotMensagensEl) {
          chatbotMensagensEl.scrollTop = chatbotMensagensEl.scrollHeight;
        }
      }

      function adicionarItensViaChatbot(acoes) {
        if (!Array.isArray(acoes) || acoes.length === 0) {
          return;
        }

        const itensAdicionados = [];

        acoes.forEach(function (acao) {
          const itemId = String(acao.item_id || '').trim();
          const quantidade = Math.max(1, parseInt(acao.quantidade, 10) || 1);

          if (itemId === '') {
            return;
          }

          const card = document.querySelector('.produto-card[data-id="' + itemId + '"]');
          if (!card) {
            return;
          }

          const qtdEl = card.querySelector('.quantidade');
          if (!qtdEl) {
            return;
          }

          const qtdAtual = parseInt(qtdEl.dataset.qtd, 10) || 0;
          alterarQuantidadeVisual(card, qtdAtual + quantidade);

          const nomeItem = (acao.nome || card.dataset.nome || 'Item').trim();
          itensAdicionados.push(quantidade + 'x ' + nomeItem);
        });

        atualizarCarrinho();

        if (itensAdicionados.length > 0) {
          adicionarMensagemChatbot('bot', 'Adicionei ao carrinho: ' + itensAdicionados.join(', ') + '.');
        }
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


      if (chatbotOverlay) {
        chatbotOverlay.style.display = 'none';
        chatbotOverlay.addEventListener('click', function (e) {
          if (e.target === chatbotOverlay) {
            fecharChatbot();
          }
        });
      }

      if (abrirChatbotBtn) {
        abrirChatbotBtn.addEventListener('click', abrirChatbot);
      }

      if (fecharChatbotBtn) {
        fecharChatbotBtn.addEventListener('click', fecharChatbot);
      }

      if (chatbotForm) {
        chatbotForm.addEventListener('submit', async function (e) {
          e.preventDefault();

          const mensagem = chatbotInput ? chatbotInput.value.trim() : '';
          if (mensagem === '') return;

          if (chatbotInput) chatbotInput.value = '';
          if (chatbotEnviar) chatbotEnviar.disabled = true;

          await enviarMensagemChatbot(mensagem);

          if (chatbotEnviar) chatbotEnviar.disabled = false;
          if (chatbotInput) chatbotInput.focus();
        });
      }

      if (finalizarPedidoBtn) {
        finalizarPedidoBtn.addEventListener('click', async function () {
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

          const textoOriginalBotao = finalizarPedidoBtn.textContent;
          finalizarPedidoBtn.disabled = true;
          finalizarPedidoBtn.textContent = 'Registrando...';

          try {
            const respostaVenda = await fetch('registrar_venda.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({
                cardapio_id: chatbotConfig.id,
                cliente_nome: nomeCliente,
                cliente_endereco: enderecoCliente,
                origem: 'cardapio_web',
                itens: itens
              })
            });

            const dadosVenda = await respostaVenda.json();

            if (!respostaVenda.ok || !dadosVenda.ok) {
              throw new Error(dadosVenda.erro || 'Não foi possível registrar a venda.');
            }

            alert('Pedido registrado com sucesso!\n\n' + decodeURIComponent(mensagem));

            document.querySelectorAll('.produto-card').forEach(card => {
              alterarQuantidadeVisual(card, 0);
            });
            atualizarCarrinho();
            if (nomeClienteEl) nomeClienteEl.value = '';
            if (enderecoClienteEl) enderecoClienteEl.value = '';
            fecharModalCarrinho();
          } catch (erro) {
            alert(erro.message || 'Erro ao registrar a venda.');
          } finally {
            finalizarPedidoBtn.disabled = false;
            finalizarPedidoBtn.textContent = textoOriginalBotao;
          }
        });
      }

      atualizarCarrinho();
    });
  </script>
</body>
</html>