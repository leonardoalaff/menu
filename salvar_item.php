<?php
ob_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $img = $_FILES['imagem'] ?? null;

    if ($img && $img['name']) {
        $ext = strtolower(pathinfo($img['name'], PATHINFO_EXTENSION));
        $nome = uniqid() . "." . $ext;
        $caminho = "uploads/" . $nome;
        move_uploaded_file($img['tmp_name'], $caminho);
    } else {
        $caminho = null;
    }

    $preco = str_replace(',', '.', $_POST['preco']);

    $stmt = $db->prepare("
        INSERT INTO itens (cardapio_id,nome,descricao,preco,imagem)
        VALUES (?,?,?,?,?)
    ");

    $stmt->execute([
        $_POST['cardapio_id'],
        $_POST['nome'],
        $_POST['descricao'],
        $preco,
        $caminho
    ]);

    header("Location: editar_cardapio.php?id=" . $_POST['cardapio_id']);
    exit;
}