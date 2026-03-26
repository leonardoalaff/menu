<?php
require 'db.php';

$db->exec("
CREATE TABLE IF NOT EXISTS usuarios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    senha TEXT NOT NULL
);
");

$db->exec("
CREATE TABLE IF NOT EXISTS cardapios (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id INTEGER NOT NULL,
    nome_negocio TEXT NOT NULL,
    cor_principal TEXT DEFAULT '#ff7a00',
    descricao TEXT,
    imagem_fundo TEXT,
    cor_preco TEXT DEFAULT '#f97316',
    cor_botao_adicionar TEXT DEFAULT '#ef4444',
    cor_botao_ver_carrinho TEXT DEFAULT '#ef4444',
    cor_botao_finalizar_pedido TEXT DEFAULT '#ef4444',
    cor_titulo_cabecalho TEXT DEFAULT '#2f2f2f',
    cor_descricao_cabecalho TEXT DEFAULT '#4b5563',
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
");

$db->exec("
CREATE TABLE IF NOT EXISTS itens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cardapio_id INTEGER NOT NULL,
    nome TEXT NOT NULL,
    descricao TEXT,
    preco REAL NOT NULL,
    categoria TEXT,
    imagem TEXT,
    FOREIGN KEY (cardapio_id) REFERENCES cardapios(id)
);
");



$db->exec("
CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    cardapio_id INTEGER NOT NULL,
    nome TEXT NOT NULL,
    UNIQUE(cardapio_id, nome),
    FOREIGN KEY (cardapio_id) REFERENCES cardapios(id)
);
");

echo "Banco criado com sucesso!";
