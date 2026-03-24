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

$cardapio_id    = $_POST['cardapio_id'] ?? null;
$nome_negocio   = trim($_POST['nome_negocio'] ?? '');
$descricao      = trim($_POST['descricao'] ?? '');
$cor_principal  = $_POST['cor_principal'] ?? '#7c3aed';

$item_nome       = trim($_POST['item_nome'] ?? '');
$item_categoria  = trim($_POST['item_categoria'] ?? '');
$item_descricao  = trim($_POST['item_descricao'] ?? '');
$item_preco      = trim($_POST['item_preco'] ?? '');

if ($cardapio_id) {
    $stmt = $db->prepare("SELECT imagem_fundo FROM cardapios WHERE id = ?");
    $stmt->execute([$cardapio_id]);
    $cardapioAtual = $stmt->fetch(PDO::FETCH_ASSOC);

    $imagemFundo = $cardapioAtual['imagem_fundo'] ?? null;
    $novoFundo = salvarUpload($_FILES['imagem_fundo'] ?? [], 'fundo_cardapio');
    if ($novoFundo) {
        $imagemFundo = $novoFundo;
    }

    $stmt = $db->prepare("UPDATE cardapios SET nome_negocio = ?, descricao = ?, cor_principal = ?, imagem_fundo = ? WHERE id = ?");
    $stmt->execute([$nome_negocio, $descricao, $cor_principal, $imagemFundo, $cardapio_id]);

    if ($item_nome !== '' && $item_preco !== '') {
        $imagemItem = salvarUpload($_FILES['item_imagem'] ?? [], 'item_cardapio');

        $stmt = $db->prepare("INSERT INTO itens (cardapio_id, nome, descricao, preco, categoria, imagem) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$cardapio_id, $item_nome, $item_descricao, $item_preco, $item_categoria, $imagemItem]);
    }
}

header("Location: painel.php");
exit;
