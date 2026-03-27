<?php
try {
    $db = new PDO('sqlite:' . __DIR__ . '/cardapio.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function colunaExiste(PDO $db, string $tabela, string $coluna): bool {
        $stmt = $db->query("PRAGMA table_info($tabela)");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($colunas as $info) {
            if (($info['name'] ?? '') === $coluna) {
                return true;
            }
        }

        return false;
    }

    if (!colunaExiste($db, 'cardapios', 'imagem_fundo')) {
        $db->exec("ALTER TABLE cardapios ADD COLUMN imagem_fundo TEXT");
    }

    if (!colunaExiste($db, 'itens', 'imagem')) {
        $db->exec("ALTER TABLE itens ADD COLUMN imagem TEXT");
    }

    if (!colunaExiste($db, 'itens', 'custo')) {
        $db->exec("ALTER TABLE itens ADD COLUMN custo REAL DEFAULT 0");
    }

    $novasColunasCardapio = [
        'cor_preco' => "ALTER TABLE cardapios ADD COLUMN cor_preco TEXT DEFAULT '#f97316'",
        'cor_botao_adicionar' => "ALTER TABLE cardapios ADD COLUMN cor_botao_adicionar TEXT DEFAULT '#ef4444'",
        'cor_botao_ver_carrinho' => "ALTER TABLE cardapios ADD COLUMN cor_botao_ver_carrinho TEXT DEFAULT '#ef4444'",
        'cor_botao_finalizar_pedido' => "ALTER TABLE cardapios ADD COLUMN cor_botao_finalizar_pedido TEXT DEFAULT '#ef4444'",
        'cor_titulo_cabecalho' => "ALTER TABLE cardapios ADD COLUMN cor_titulo_cabecalho TEXT DEFAULT '#2f2f2f'",
        'cor_descricao_cabecalho' => "ALTER TABLE cardapios ADD COLUMN cor_descricao_cabecalho TEXT DEFAULT '#4b5563'",
        'cor_fundo_cardapio' => "ALTER TABLE cardapios ADD COLUMN cor_fundo_cardapio TEXT DEFAULT '#f3f4f6'",
        'endereco_estabelecimento' => "ALTER TABLE cardapios ADD COLUMN endereco_estabelecimento TEXT",
        'horario_abertura' => "ALTER TABLE cardapios ADD COLUMN horario_abertura TEXT DEFAULT '18:00'",
        'horario_fechamento' => "ALTER TABLE cardapios ADD COLUMN horario_fechamento TEXT DEFAULT '23:00'"
    ];

    foreach ($novasColunasCardapio as $coluna => $sqlAlter) {
        if (!colunaExiste($db, 'cardapios', $coluna)) {
            $db->exec($sqlAlter);
        }
    }

    $db->exec("
        CREATE TABLE IF NOT EXISTS categorias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cardapio_id INTEGER NOT NULL,
            nome TEXT NOT NULL,
            UNIQUE(cardapio_id, nome),
            FOREIGN KEY (cardapio_id) REFERENCES cardapios(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS vendas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            cardapio_id INTEGER NOT NULL,
            usuario_id INTEGER NOT NULL,
            cliente_nome TEXT NOT NULL,
            cliente_endereco TEXT,
            total REAL NOT NULL DEFAULT 0,
            lucro_estimado REAL NOT NULL DEFAULT 0,
            origem TEXT DEFAULT 'cardapio',
            observacoes TEXT,
            criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (cardapio_id) REFERENCES cardapios(id),
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        )
    ");

    $db->exec("
        CREATE TABLE IF NOT EXISTS venda_itens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            venda_id INTEGER NOT NULL,
            item_id INTEGER,
            nome_item TEXT NOT NULL,
            preco_unitario REAL NOT NULL DEFAULT 0,
            custo_unitario REAL NOT NULL DEFAULT 0,
            quantidade INTEGER NOT NULL DEFAULT 1,
            subtotal REAL NOT NULL DEFAULT 0,
            lucro_item REAL NOT NULL DEFAULT 0,
            FOREIGN KEY (venda_id) REFERENCES vendas(id)
        )
    ");
} catch (PDOException $e) {
    die("Erro ao conectar com o banco: " . $e->getMessage());
}
