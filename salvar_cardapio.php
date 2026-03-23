<?php
session_start();
require 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$cardapio_id    = $_POST['cardapio_id'] ?? null;
$nome_negocio   = trim($_POST['nome_negocio'] ?? '');
$descricao      = trim($_POST['descricao'] ?? '');
$cor_principal  = $_POST['cor_principal'] ?? '#FF5A1F';

$item_nome       = trim($_POST['item_nome'] ?? '');
$item_categoria  = trim($_POST['item_categoria'] ?? '');
$item_descricao  = trim($_POST['item_descricao'] ?? '');
$item_preco      = trim($_POST['item_preco'] ?? '');

if ($cardapio_id) {
    $stmt = $db->prepare("UPDATE cardapios SET nome_negocio = ?, descricao = ?, cor_principal = ? WHERE id = ?");
    $stmt->execute([$nome_negocio, $descricao, $cor_principal, $cardapio_id]);

    if ($item_nome !== '' && $item_preco !== '') {
        $stmt = $db->prepare("INSERT INTO itens (cardapio_id, nome, descricao, preco, categoria) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$cardapio_id, $item_nome, $item_descricao, $item_preco, $item_categoria]);
    }
}

header("Location: painel.php");
exit;