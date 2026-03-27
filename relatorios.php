<?php
session_start();
require 'db.php';
require 'config_openai.php';

date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$usuario_id = (int) $_SESSION['usuario_id'];

function formatarMoeda(float $valor): string
{
    return 'R$ ' . number_format($valor, 2, ',', '.');
}

function chamarOpenAI(string $prompt, string $contexto): array
{
    if (!defined('OPENAI_API_KEY') || trim((string) OPENAI_API_KEY) === '' || OPENAI_API_KEY === 'chaveapileonardo') {
        return [
            'ok' => false,
            'texto' => 'Configure sua chave da OpenAI no arquivo config_openai.php ou na variável de ambiente OPENAI_API_KEY para usar os relatórios com IA.'
        ];
    }

    $system = "Você é um consultor de gestão para restaurantes e lanchonetes. Responda sempre em português do Brasil. "
        . "Seja objetivo, prático e comercial. Use somente os dados enviados. Não invente números. "
        . "Estruture a resposta em tópicos curtos quando ajudar, mas sem markdown complexo.";

    $body = json_encode([
        'model' => OPENAI_MODEL,
        'messages' => [
            ['role' => 'system', 'content' => $system],
            ['role' => 'user', 'content' => "Contexto do negócio e dados:\n" . $contexto . "\n\nPedido do usuário:\n" . $prompt]
        ],
        'temperature' => 0.5,
        'max_tokens' => 900
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $ch = curl_init('https://api.openai.com/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENAI_API_KEY
        ],
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response === false || $curlError !== '') {
        return ['ok' => false, 'texto' => 'Erro ao conectar com a OpenAI: ' . $curlError];
    }

    $data = json_decode($response, true);
    $texto = trim((string) ($data['choices'][0]['message']['content'] ?? ''));

    if ($httpCode >= 400 || $texto === '') {
        return ['ok' => false, 'texto' => $data['error']['message'] ?? 'Não foi possível gerar a análise agora.'];
    }

    return ['ok' => true, 'texto' => $texto];
}

$stmt = $db->prepare('SELECT * FROM cardapios WHERE usuario_id = ? LIMIT 1');
$stmt->execute([$usuario_id]);
$cardapio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cardapio) {
    die('Cardápio não encontrado.');
}

$inicio = trim((string) ($_GET['inicio'] ?? date('Y-m-01')));
$fim = trim((string) ($_GET['fim'] ?? date('Y-m-d')));

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $inicio)) {
    $inicio = date('Y-m-01');
}

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fim)) {
    $fim = date('Y-m-d');
}

if ($inicio > $fim) {
    [$inicio, $fim] = [$fim, $inicio];
}

$inicioSql = $inicio . ' 00:00:00';
$fimSql = $fim . ' 23:59:59';

$stmt = $db->prepare(
    'SELECT 
        COUNT(*) AS total_pedidos,
        COALESCE(SUM(total), 0) AS faturamento,
        COALESCE(SUM(lucro_estimado), 0) AS lucro,
        COALESCE(AVG(total), 0) AS ticket_medio
     FROM vendas
     WHERE usuario_id = ? AND cardapio_id = ? AND criado_em BETWEEN ? AND ?'
);
$stmt->execute([$usuario_id, (int) $cardapio['id'], $inicioSql, $fimSql]);
$resumo = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$totalPedidos = (int) ($resumo['total_pedidos'] ?? 0);
$faturamento = (float) ($resumo['faturamento'] ?? 0);
$lucro = (float) ($resumo['lucro'] ?? 0);
$ticketMedio = (float) ($resumo['ticket_medio'] ?? 0);

$stmt = $db->prepare(
    'SELECT 
        vi.nome_item,
        SUM(vi.quantidade) AS qtd,
        SUM(vi.subtotal) AS faturamento_item,
        SUM(vi.lucro_item) AS lucro_item
     FROM venda_itens vi
     INNER JOIN vendas v ON v.id = vi.venda_id
     WHERE v.usuario_id = ? AND v.cardapio_id = ? AND v.criado_em BETWEEN ? AND ?
     GROUP BY vi.nome_item
     ORDER BY qtd DESC, faturamento_item DESC
     LIMIT 5'
);
$stmt->execute([$usuario_id, (int) $cardapio['id'], $inicioSql, $fimSql]);
$topItens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare(
    'SELECT 
        date(criado_em) AS dia,
        COUNT(*) AS pedidos,
        COALESCE(SUM(total), 0) AS faturamento,
        COALESCE(SUM(lucro_estimado), 0) AS lucro
     FROM vendas
     WHERE usuario_id = ? AND cardapio_id = ? AND criado_em BETWEEN ? AND ?
     GROUP BY date(criado_em)
     ORDER BY dia DESC
     LIMIT 10'
);
$stmt->execute([$usuario_id, (int) $cardapio['id'], $inicioSql, $fimSql]);
$vendasPorDia = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare(
    'SELECT 
        cliente_nome,
        total,
        lucro_estimado,
        criado_em
     FROM vendas
     WHERE usuario_id = ? AND cardapio_id = ? AND criado_em BETWEEN ? AND ?
     ORDER BY datetime(criado_em) DESC
     LIMIT 8'
);
$stmt->execute([$usuario_id, (int) $cardapio['id'], $inicioSql, $fimSql]);
$ultimasVendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $db->prepare('SELECT COUNT(*) FROM itens WHERE cardapio_id = ? AND COALESCE(custo, 0) > 0');
$stmt->execute([(int) $cardapio['id']]);
$itensComCusto = (int) $stmt->fetchColumn();

$stmt = $db->prepare('SELECT COUNT(*) FROM itens WHERE cardapio_id = ?');
$stmt->execute([(int) $cardapio['id']]);
$totalItensCadastro = (int) $stmt->fetchColumn();

$percentualCustos = $totalItensCadastro > 0 ? ($itensComCusto / $totalItensCadastro) * 100 : 0;

$contextoDados = [];
$contextoDados[] = 'Negócio: ' . trim((string) ($cardapio['nome_negocio'] ?? ''));
$contextoDados[] = 'Período analisado: ' . $inicio . ' até ' . $fim;
$contextoDados[] = 'Total de pedidos: ' . $totalPedidos;
$contextoDados[] = 'Faturamento: ' . number_format($faturamento, 2, '.', '');
$contextoDados[] = 'Lucro estimado: ' . number_format($lucro, 2, '.', '');
$contextoDados[] = 'Ticket médio: ' . number_format($ticketMedio, 2, '.', '');
$contextoDados[] = 'Itens com custo cadastrado: ' . $itensComCusto . ' de ' . $totalItensCadastro;
$contextoDados[] = 'Top itens:';
foreach ($topItens as $item) {
    $contextoDados[] = '- ' . ($item['nome_item'] ?? '') . ' | qtd=' . (int) ($item['qtd'] ?? 0) . ' | faturamento=' . number_format((float) ($item['faturamento_item'] ?? 0), 2, '.', '') . ' | lucro=' . number_format((float) ($item['lucro_item'] ?? 0), 2, '.', '');
}
$contextoDados[] = 'Resumo diário:';
foreach ($vendasPorDia as $dia) {
    $contextoDados[] = '- ' . ($dia['dia'] ?? '') . ' | pedidos=' . (int) ($dia['pedidos'] ?? 0) . ' | faturamento=' . number_format((float) ($dia['faturamento'] ?? 0), 2, '.', '') . ' | lucro=' . number_format((float) ($dia['lucro'] ?? 0), 2, '.', '');
}
$contextoConsolidado = implode("\n", $contextoDados);

$acaoIa = trim((string) ($_POST['acao_ia'] ?? ''));
$saidaIa = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $acaoIa !== '') {
    $prompts = [
        'resumo' => 'Faça um resumo executivo do desempenho deste período. Aponte o que está bom, o que preocupa e quais 3 ações práticas devem ser feitas primeiro.',
        'promocoes' => 'Crie 5 ideias de promoções realistas para aumentar pedidos e ticket médio com base nos itens vendidos. Priorize ideias simples de executar.',
        'lucro' => 'Analise os dados e diga como aumentar o lucro. Considere mix de produtos, ticket médio, frequência de pedidos e cadastro de custos. Traga um plano prático para os próximos 7 dias.',
        'marketing' => 'Escreva 3 textos curtos de campanha para WhatsApp e Instagram, em tom vendedor, com CTA forte, usando os dados do negócio. Evite inventar números específicos não presentes nos dados.',
    ];

    if (isset($prompts[$acaoIa])) {
        $respostaIa = chamarOpenAI($prompts[$acaoIa], $contextoConsolidado);
        $saidaIa = $respostaIa['texto'] ?? '';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios Inteligentes - CardápioOn</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="style_relatorios.css">
</head>
<body>
    <div class="relatorios-shell">
        <header class="page-top">
            <div>
                <a href="painel.php" class="back-link"><i class="ri-arrow-left-line"></i> Voltar ao painel</a>
                <p class="eyebrow">Relatórios com IA</p>
                <h1><?= htmlspecialchars($cardapio['nome_negocio'] ?? 'Seu negócio') ?></h1>
                <p class="subtitle">Acompanhe vendas, ticket médio, lucro estimado e use ferramentas com a API do ChatGPT para gerar ações rápidas.</p>
            </div>
            <div class="top-actions">
                <a href="visualizar_cardapio.php" class="btn secondary">Ver cardápio</a>
            </div>
        </header>

        <section class="filter-card">
            <form method="GET" class="filter-form">
                <div class="field">
                    <label>Data inicial</label>
                    <input type="date" name="inicio" value="<?= htmlspecialchars($inicio) ?>">
                </div>
                <div class="field">
                    <label>Data final</label>
                    <input type="date" name="fim" value="<?= htmlspecialchars($fim) ?>">
                </div>
                <button type="submit" class="btn primary">Atualizar relatório</button>
            </form>
        </section>

        <section class="stats-grid">
            <article class="stat-card">
                <span class="stat-label">Pedidos</span>
                <strong><?= $totalPedidos ?></strong>
                <small>Total no período</small>
            </article>
            <article class="stat-card">
                <span class="stat-label">Faturamento</span>
                <strong><?= formatarMoeda($faturamento) ?></strong>
                <small>Valor bruto vendido</small>
            </article>
            <article class="stat-card">
                <span class="stat-label">Lucro estimado</span>
                <strong><?= formatarMoeda($lucro) ?></strong>
                <small>Baseado no custo cadastrado</small>
            </article>
            <article class="stat-card">
                <span class="stat-label">Ticket médio</span>
                <strong><?= formatarMoeda($ticketMedio) ?></strong>
                <small>Média por pedido</small>
            </article>
        </section>

        <section class="two-col-grid">
            <article class="panel-card">
                <div class="panel-head">
                    <div>
                        <p class="eyebrow">Produtos</p>
                        <h2>Itens mais vendidos</h2>
                    </div>
                </div>

                <?php if (!empty($topItens)): ?>
                    <div class="rank-list">
                        <?php foreach ($topItens as $index => $item): ?>
                            <div class="rank-item">
                                <div class="rank-number">#<?= $index + 1 ?></div>
                                <div class="rank-body">
                                    <strong><?= htmlspecialchars($item['nome_item'] ?? '') ?></strong>
                                    <span><?= (int) ($item['qtd'] ?? 0) ?> unidade(s)</span>
                                </div>
                                <div class="rank-values">
                                    <span><?= formatarMoeda((float) ($item['faturamento_item'] ?? 0)) ?></span>
                                    <small>Lucro: <?= formatarMoeda((float) ($item['lucro_item'] ?? 0)) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="empty-box">Ainda não há vendas registradas neste período.</p>
                <?php endif; ?>
            </article>

            <article class="panel-card">
                <div class="panel-head">
                    <div>
                        <p class="eyebrow">Base de custos</p>
                        <h2>Qualidade do cadastro</h2>
                    </div>
                </div>

                <div class="coverage-card">
                    <strong><?= number_format($percentualCustos, 0, ',', '.') ?>%</strong>
                    <p><?= $itensComCusto ?> de <?= $totalItensCadastro ?> item(ns) com custo cadastrado.</p>
                    <small>Quanto mais custos você cadastrar, mais confiável fica o lucro estimado.</small>
                </div>
            </article>
        </section>

        <section class="panel-card">
            <div class="panel-head">
                <div>
                    <p class="eyebrow">Movimento</p>
                    <h2>Últimas vendas</h2>
                </div>
            </div>

            <?php if (!empty($ultimasVendas)): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Lucro estimado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ultimasVendas as $venda): ?>
                                <tr>
                                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime((string) $venda['criado_em']))) ?></td>
                                    <td><?= htmlspecialchars($venda['cliente_nome'] ?? '') ?></td>
                                    <td><?= formatarMoeda((float) ($venda['total'] ?? 0)) ?></td>
                                    <td><?= formatarMoeda((float) ($venda['lucro_estimado'] ?? 0)) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="empty-box">Nenhuma venda encontrada para este intervalo.</p>
            <?php endif; ?>
        </section>

        <section class="panel-card">
            <div class="panel-head">
                <div>
                    <p class="eyebrow">Ferramentas com ChatGPT</p>
                    <h2>Assistentes prontos para gestão</h2>
                </div>
            </div>

            <div class="ai-tools-grid">
                <form method="POST" class="ai-tool-card">
                    <input type="hidden" name="acao_ia" value="resumo">
                    <h3>Resumo executivo</h3>
                    <p>Receba uma leitura rápida do período com prioridades de ação.</p>
                    <button type="submit" class="btn primary">Gerar resumo</button>
                </form>

                <form method="POST" class="ai-tool-card">
                    <input type="hidden" name="acao_ia" value="promocoes">
                    <h3>Ideias de promoções</h3>
                    <p>Promoções simples para vender mais e aumentar ticket médio.</p>
                    <button type="submit" class="btn primary">Gerar promoções</button>
                </form>

                <form method="POST" class="ai-tool-card">
                    <input type="hidden" name="acao_ia" value="lucro">
                    <h3>Plano para aumentar lucro</h3>
                    <p>Sugestões práticas usando seus números de vendas e custos.</p>
                    <button type="submit" class="btn primary">Gerar plano</button>
                </form>

                <form method="POST" class="ai-tool-card">
                    <input type="hidden" name="acao_ia" value="marketing">
                    <h3>Textos de campanha</h3>
                    <p>Copys prontas para WhatsApp e Instagram.</p>
                    <button type="submit" class="btn primary">Gerar textos</button>
                </form>
            </div>

            <?php if ($saidaIa !== ''): ?>
                <div class="ai-output">
                    <div class="panel-head compact">
                        <div>
                            <p class="eyebrow">Resultado</p>
                            <h2>Resposta da IA</h2>
                        </div>
                    </div>
                    <div class="ai-output-text"><?= nl2br(htmlspecialchars($saidaIa)) ?></div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
