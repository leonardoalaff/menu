<?php
$db = new PDO('sqlite:database.sqlite');

$db->exec("CREATE TABLE IF NOT EXISTS itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cardapio_id INTEGER,
    nome TEXT,
    descricao TEXT,
    preco REAL,
    imagem TEXT
);");

// 👇 ADICIONE AQUI (no final)
try {
    $db->exec("ALTER TABLE cardapios ADD COLUMN imagem TEXT");
} catch (Exception $e) {}

try {
    $db->exec("ALTER TABLE cardapios ADD COLUMN whatsapp TEXT");
} catch (Exception $e) {}