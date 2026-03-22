<?php
include 'db.php';

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id=?");
$stmt->execute([$id]);
$itens = $stmt->fetchAll();
?>

<h2>Cardápio</h2>

<?php foreach($itens as $item): ?>
    <div class="card">
        <img src="<?= $item['imagem'] ?>" width="100%">
        <h3><?= $item['nome'] ?></h3>
        <p><?= $item['descricao'] ?></p>
        <strong>R$ <?= $item['preco'] ?></strong>
    </div>
<?php endforeach; ?>