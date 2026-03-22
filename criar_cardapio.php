<?php
session_start();
include 'db.php';

// buscar itens
$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id=?");
$itens = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="script.js" defer></script>
</head>

<body class="bg">

<div class="form-container">

    <div class="form-box">

        <h2>🍔 Itens do Cardápio</h2>

        <!-- LISTA -->
        <?php foreach($itens as $i): ?>
        <div class="item-card">

            <img src="<?= $i['imagem'] ?>" class="item-mini">

            <div>
                <strong><?= $i['nome'] ?></strong>
                <p><?= $i['descricao'] ?></p>
                <span>R$ <?= number_format((float)$i['preco'],2,',','.') ?></span>
            </div>

        </div>
        <?php endforeach; ?>

        <hr>

        <!-- FORM -->
        <form action="salvar_item.php" method="POST" enctype="multipart/form-data">

            <input type="hidden" name="cardapio_id" value="<?= $id ?>">

            <!-- NOME -->
            <div class="input-group">
                <input name="nome" required>
                <label>Nome do item</label>
            </div>

            <!-- DESCRIÇÃO -->
            <div class="input-group">
                <input name="descricao">
                <label>Descrição</label>
            </div>

            <!-- PREÇO -->
            <div class="input-group">
                <input name="preco" required>
                <label>Preço (ex: 10.50)</label>
            </div>

            <!-- UPLOAD -->
            <label class="upload-label">Imagem do item</label>

            <div class="upload-area" onclick="document.getElementById('file').click()">
                <p>Clique para adicionar imagem</p>
                <img id="preview">
            </div>

            <input type="file" id="file" name="imagem" accept="image/*" hidden>

            <button class="btn-main">Adicionar Item</button>

        </form>

    </div>

</div>

</body>
</html>