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
} catch (PDOException $e) {
    die("Erro ao conectar com o banco: " . $e->getMessage());
}
