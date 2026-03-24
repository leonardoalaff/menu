<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

function salvarUpload(array $arquivo, string $prefixo): ?string {
    if (!isset($arquivo['error']) || $arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($arquivo['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $tiposPermitidos = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif'
    ];

    $mime = mime_content_type($arquivo['tmp_name']);
    if (!isset($tiposPermitidos[$mime])) {
        return null;
    }

    $diretorio = __DIR__ . '/uploads';
    if (!is_dir($diretorio)) {
        mkdir($diretorio, 0777, true);
    }

    $nomeArquivo = $prefixo . '_' . uniqid('', true) . '.' . $tiposPermitidos[$mime];
    $destino = $diretorio . '/' . $nomeArquivo;

    if (!move_uploaded_file($arquivo['tmp_name'], $destino)) {
        return null;
    }

    return 'uploads/' . $nomeArquivo;
}

function removerArquivoLocal(?string $caminho): void {
    if (!$caminho) {
        return;
    }

    if (strpos($caminho, 'uploads/') !== 0) {
        return;
    }

    $arquivo = __DIR__ . '/' . $caminho;
    if (is_file($arquivo)) {
        @unlink($arquivo);
    }
}

$acao = $_POST['acao'] ?? 'salvar_cardapio';
$usuario_id = $_SESSION['usuario_id'];

if ($acao === 'remover_fundo') {
    $cardapio_id = (int)($_POST['cardapio_id'] ?? 0);

    $stmt = $db->prepare("SELECT c.imagem_fundo FROM cardapios c WHERE c.id = ? AND c.usuario_id = ?");
    $stmt->execute([$cardapio_id, $usuario_id]);
    $cardapio = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cardapio) {
        removerArquivoLocal($cardapio['imagem_fundo'] ?? null);
        $stmt = $db->prepare("UPDATE cardapios SET imagem_fundo = NULL WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$cardapio_id, $usuario_id]);
    }

    header("Location: painel.php");
    exit;
}

if ($acao === 'atualizar_foto_item' || $acao === 'remover_foto_item') {
    $item_id = (int)($_POST['item_id'] ?? 0);

    $stmt = $db->prepare("SELECT i.imagem, i.cardapio_id FROM itens i INNER JOIN cardapios c ON c.id = i.cardapio_id WHERE i.id = ? AND c.usuario_id = ?");
    $stmt->execute([$item_id, $usuario_id]);
    $itemAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($itemAtual) {
        if ($acao === 'remover_foto_item') {
            removerArquivoLocal($itemAtual['imagem'] ?? null);
            $stmt = $db->prepare("UPDATE itens SET imagem = NULL WHERE id = ?");
            $stmt->execute([$item_id]);
        } else {
            $novaImagem = salvarUpload($_FILES['nova_item_imagem'] ?? [], 'item_cardapio');
            if ($novaImagem) {
                removerArquivoLocal($itemAtual['imagem'] ?? null);
                $stmt = $db->prepare("UPDATE itens SET imagem = ? WHERE id = ?");
                $stmt->execute([$novaImagem, $item_id]);
            }
        }
    }

    header("Location: painel.php");
    exit;
}

$cardapio_id    = (int)($_POST['cardapio_id'] ?? 0);
$nome_negocio   = trim($_POST['nome_negocio'] ?? '');
$descricao      = trim($_POST['descricao'] ?? '');
$cor_principal  = $_POST['cor_principal'] ?? '#7c3aed';

$item_nome       = trim($_POST['item_nome'] ?? '');
$item_categoria  = trim($_POST['item_categoria'] ?? '');
$item_descricao  = trim($_POST['item_descricao'] ?? '');
$item_preco      = trim($_POST['item_preco'] ?? '');

if ($cardapio_id) {
    $stmt = $db->prepare("SELECT imagem_fundo FROM cardapios WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$cardapio_id, $usuario_id]);
    $cardapioAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cardapioAtual) {
        $imagemFundo = $cardapioAtual['imagem_fundo'] ?? null;
        $novoFundo = salvarUpload($_FILES['imagem_fundo'] ?? [], 'fundo_cardapio');
        if ($novoFundo) {
            removerArquivoLocal($imagemFundo);
            $imagemFundo = $novoFundo;
        }

        $stmt = $db->prepare("UPDATE cardapios SET nome_negocio = ?, descricao = ?, cor_principal = ?, imagem_fundo = ? WHERE id = ? AND usuario_id = ?");
        $stmt->execute([$nome_negocio, $descricao, $cor_principal, $imagemFundo, $cardapio_id, $usuario_id]);

        if ($item_nome !== '' && $item_preco !== '') {
            $imagemItem = salvarUpload($_FILES['item_imagem'] ?? [], 'item_cardapio');

            $stmt = $db->prepare("INSERT INTO itens (cardapio_id, nome, descricao, preco, categoria, imagem) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$cardapio_id, $item_nome, $item_descricao, $item_preco, $item_categoria, $imagemItem]);
        }
    }
}

header("Location: painel.php");
exit;
