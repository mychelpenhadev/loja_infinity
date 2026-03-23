<?php
require_once 'db.php';

try {
    $sql = file_get_contents('../papelaria_db.sql');
    
    // Executa o SQL
    $pdo->exec($sql);
    
    echo "<h1>Sucesso!</h1>";
    echo "<p>As tabelas foram criadas e os dados iniciais foram inseridos com sucesso.</p>";
    echo "<p><a href='../index.html'>Ir para a Loja</a></p>";
    
    // Deleta o arquivo por segurança após o uso
    // unlink(__FILE__);
} catch (PDOException $e) {
    echo "<h1>Erro ao configurar o banco de dados:</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>
