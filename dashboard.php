<?php
session_start();
if (!isset($_SESSION['user'])) exit;

include 'db.php';

$stmt = $db->prepare("
SELECT c.*, 
(SELECT COUNT(*) FROM itens i WHERE i.cardapio_id = c.id) as total_itens
FROM cardapios c
WHERE usuario_id = ?
");

$stmt->execute([$_SESSION['user']]);
$cardapios = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="style.css">
<script src="script.js"></script>
</head>

<body>

<div class="topo">
    <h2>🍔 Meu Painel</h2>
    <a href="criar_cardapio.php" class="btn-criar">+ Criar</a>
</div>

<div class="lista">

<?php foreach ($cardapios as $c): ?>
<div class="card">

    <?php if (!empty($c['imagem'])): ?>
    <img src="<?= $c['imagem'] ?>" class="card-img">
<?php else: ?>
    <img src="https://via.placeholder.com/300x120?text=Sem+Imagem" class="card-img">
<?php endif; ?>

    <div class="card-info">
        <h3><?= $c['nome'] ?></h3>
        <p><?= $c['total_itens'] ?> itens</p>
    </div>

    <div class="card-acoes">
        <a href="editar_cardapio.php?id=<?= $c['id'] ?>" class="btn editar">Editar</a>
        <a href="preview_cardapio.php?id=<?= $c['id'] ?>" class="btn ver">Preview</a>
        <button onclick="excluir(<?= $c['id'] ?>)" class="btn excluir">X</button>
    </div>

</div>
<?php endforeach; ?>

</div>

</body>
</html>