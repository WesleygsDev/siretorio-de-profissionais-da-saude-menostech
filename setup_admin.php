<?php
/**
 * Script para configurar o usuário admin corretamente
 */

require_once 'config.php';

// Senha desejada
$senha = 'admin123';

// Gerar hash seguro
$hash = password_hash($senha, PASSWORD_DEFAULT);

echo "<h1>Configuração do Admin</h1>";
echo "<p>Senha: $senha</p>";
echo "<p>Hash gerado: $hash</p>";

// Verificar se a tabela admin existe
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'admin'");
if (mysqli_num_rows($check_table) == 0) {
    echo "<p style='color: orange;'>Tabela 'admin' não existe. Criando...</p>";
    
    // Criar tabela
    $sql_create = "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if (mysqli_query($conn, $sql_create)) {
        echo "<p style='color: green;'>Tabela 'admin' criada com sucesso!</p>";
    } else {
        echo "<p style='color: red;'>Erro ao criar tabela: " . mysqli_error($conn) . "</p>";
    }
}

// Garantir índice único no username
$checkIndex = mysqli_query($conn, "SHOW INDEX FROM admin WHERE Key_name = 'username_unique'");
if (!$checkIndex || mysqli_num_rows($checkIndex) == 0) {
    mysqli_query($conn, "ALTER TABLE admin ADD UNIQUE KEY username_unique (username)");
}

// Garantir colunas extras em profissionais
$profTable = mysqli_query($conn, "SHOW TABLES LIKE 'profissionais'");
if ($profTable && mysqli_num_rows($profTable) > 0) {
    $columnsToAdd = [
        "whatsapp VARCHAR(20) NULL",
        "conselho_tipo VARCHAR(20) NULL",
        "conselho_numero VARCHAR(30) NULL",
        "rqe VARCHAR(30) NULL",
        "atendimento VARCHAR(20) NULL",
        "endereco VARCHAR(255) NULL",
        "bairro VARCHAR(120) NULL",
        "cep VARCHAR(10) NULL"
    ];

    foreach ($columnsToAdd as $colDef) {
        $colName = trim(explode(' ', $colDef)[0]);
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM profissionais LIKE '$colName'");
        if (!$colCheck || mysqli_num_rows($colCheck) == 0) {
            mysqli_query($conn, "ALTER TABLE profissionais ADD COLUMN $colDef");
        }
    }
}

// Inserir ou atualizar usuário admin
$sql = "INSERT INTO admin (username, password) VALUES ('admin', '$hash') 
        ON DUPLICATE KEY UPDATE password='$hash'";

if (mysqli_query($conn, $sql)) {
    echo "<p style='color: green; font-size: 18px;'>✅ Usuário admin configurado com sucesso!</p>";
    echo "<p>Agora você pode logar com:</p>";
    echo "<ul>";
    echo "<li><strong>Usuário:</strong> admin</li>";
    echo "<li><strong>Senha:</strong> $senha</li>";
    echo "</ul>";
    echo "<p><a href='admin/login.php'>Ir para a página de login</a></p>";
} else {
    echo "<p style='color: red;'>Erro: " . mysqli_error($conn) . "</p>";
}

mysqli_close($conn);
?>
