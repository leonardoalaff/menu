<?php
include 'db.php';

$id = $_GET['id'];

$stmt = $db->prepare("SELECT * FROM itens WHERE cardapio_id=?");
$stmt->execute([$id]);
$itens = $stmt->fetchAll();
?>

<h2>Itens</h2>

<?php foreach($itens as $item): ?>
    <div class="card">
        <img src="<?= $item['imagem'] ?>" width="100%">
        <h3><?= $item['nome'] ?></h3>
        <p><?= $item['descricao'] ?></p>
        <strong>R$ <?= $item['preco'] ?></strong>
    </div>
<?php endforeach; ?>

<form action="salvar_item.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="cardapio_id" value="<?= $id ?>">
    <input name="nome" placeholder="Nome">
    <input name="descricao" placeholder="Descrição">
    <input name="preco" placeholder="Preço">
    <input type="file" name="imagem">
    <button>Adicionar Item</button>
</form>