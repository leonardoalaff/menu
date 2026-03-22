<?php
session_start();
include 'db.php';

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM cardapios WHERE id=?");
$stmt->execute([$id]);
$cardapio = $stmt->fetch();

$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id=?");
$stmt->execute([$id]);
$itens = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
</head>

<body class="preview-body">

<!-- CAPA -->
<div class="capa">
    <img src="<?= !empty($cardapio['imagem']) ? $cardapio['imagem'] : 'https://via.placeholder.com/600x200' ?>">
    <div class="overlay"></div>
    <h1><?= $cardapio['nome'] ?></h1>
</div>

<!-- LISTA -->
<div class="itens">

<?php foreach ($itens as $item): 
$preco = is_numeric($item['preco']) ? (float)$item['preco'] : 0;
?>

<div class="item">

    <img src="<?= $item['imagem'] ?>" class="item-img">

    <div class="item-info">
        <h3><?= $item['nome'] ?></h3>
        <p><?= $item['descricao'] ?></p>
        <strong>R$ <?= number_format($preco, 2, ',', '.') ?></strong>
    </div>

    <button class="btn-add">+</button>

</div>

<?php endforeach; ?>

</div>

</body>
</html>