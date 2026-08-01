<?php
require_once 'config.php';

echo "<h1>Teste de Conexão com o Banco de Dados</h1>";

if ($conn) {
    echo "<p style='color: green;'>✓ Conexão com o banco de dados estabelecida com sucesso!</p>";
    
    // Test if tables exist
    $result = mysqli_query($conn, "SHOW TABLES");
    $tables = [];
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    
    echo "<h2>Tabelas encontradas:</h2>";
    if (!empty($tables)) {
        echo "<ul>";
        foreach ($tables as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ Nenhuma tabela encontrada. Por favor, importe o arquivo database.sql no phpMyAdmin.</p>";
    }
    
} else {
    echo "<p style='color: red;'>✗ Erro na conexão: " . mysqli_connect_error() . "</p>";
}

mysqli_close($conn);
?>
