<?php
session_start();
require 'db.php';

header('Content-Type: application/json; charset=utf-8');

date_default_timezone_set('America/Sao_Paulo');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Método não permitido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
$cardapioId = (int) ($payload['cardapio_id'] ?? 0);
$clienteNome = trim((string) ($payload['cliente_nome'] ?? ''));
$clienteEndereco = trim((string) ($payload['cliente_endereco'] ?? ''));
$itens = is_array($payload['itens'] ?? null) ? $payload['itens'] : [];
$origem = trim((string) ($payload['origem'] ?? 'cardapio'));

if ($cardapioId <= 0) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erro' => 'Cardápio inválido.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($clienteNome === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erro' => 'Informe o nome do cliente.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (empty($itens)) {
    http_response_code(422);
    echo json_encode(['ok' => false, 'erro' => 'Nenhum item informado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$stmt = $db->prepare('SELECT c.id, c.usuario_id FROM cardapios c WHERE c.id = ? LIMIT 1');
$stmt->execute([$cardapioId]);
$cardapio = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cardapio) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'erro' => 'Cardápio não encontrado.'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db->beginTransaction();

    $totalVenda = 0;
    $lucroVenda = 0;
    $itensSalvos = [];

    foreach ($itens as $item) {
        $itemId = (int) ($item['id'] ?? 0);
        $nomeInformado = trim((string) ($item['nome'] ?? ''));
        $precoInformado = (float) ($item['preco'] ?? 0);
        $quantidade = max(1, (int) ($item['quantidade'] ?? 1));

        if ($itemId <= 0) {
            continue;
        }

        $stmt = $db->prepare('SELECT id, nome, preco, COALESCE(custo, 0) AS custo FROM itens WHERE id = ? AND cardapio_id = ? LIMIT 1');
        $stmt->execute([$itemId, $cardapioId]);
        $itemDb = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$itemDb) {
            continue;
        }

        $nome = trim((string) ($itemDb['nome'] ?? $nomeInformado));
        $preco = (float) ($itemDb['preco'] ?? $precoInformado);
        $custo = (float) ($itemDb['custo'] ?? 0);
        $subtotal = $preco * $quantidade;
        $lucroItem = ($preco - $custo) * $quantidade;

        $totalVenda += $subtotal;
        $lucroVenda += $lucroItem;

        $itensSalvos[] = [
            'item_id' => $itemId,
            'nome' => $nome,
            'preco' => $preco,
            'custo' => $custo,
            'quantidade' => $quantidade,
            'subtotal' => $subtotal,
            'lucro_item' => $lucroItem,
        ];
    }

    if (empty($itensSalvos)) {
        throw new RuntimeException('Nenhum item válido foi encontrado para registrar a venda.');
    }

    $stmt = $db->prepare('INSERT INTO vendas (cardapio_id, usuario_id, cliente_nome, cliente_endereco, total, lucro_estimado, origem) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([
        $cardapioId,
        (int) $cardapio['usuario_id'],
        $clienteNome,
        $clienteEndereco,
        $totalVenda,
        $lucroVenda,
        $origem !== '' ? $origem : 'cardapio'
    ]);

    $vendaId = (int) $db->lastInsertId();

    $stmtItem = $db->prepare('INSERT INTO venda_itens (venda_id, item_id, nome_item, preco_unitario, custo_unitario, quantidade, subtotal, lucro_item) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');

    foreach ($itensSalvos as $itemSalvo) {
        $stmtItem->execute([
            $vendaId,
            $itemSalvo['item_id'],
            $itemSalvo['nome'],
            $itemSalvo['preco'],
            $itemSalvo['custo'],
            $itemSalvo['quantidade'],
            $itemSalvo['subtotal'],
            $itemSalvo['lucro_item']
        ]);
    }

    $db->commit();

    echo json_encode([
        'ok' => true,
        'venda_id' => $vendaId,
        'total' => $totalVenda,
        'lucro_estimado' => $lucroVenda
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'erro' => 'Erro ao registrar a venda: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
