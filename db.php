<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/cardapio.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco: " . $e->getMessage());
}