<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$query = "SELECT * FROM profissionais ORDER BY created_at DESC";
$result = mysqli_query($conn, $query);
$profissionais = [];
while ($row = mysqli_fetch_assoc($result)) {
    $profissionais[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Admin - MenoTech</title>
    <?php require_once __DIR__ . '/includes/brand-head.php'; ?>
</head>
<body class="admin-page">
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="#">MenoTech Admin</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-light btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Profissionais Certificados</h2>
            <a href="adicionar.php" class="btn btn-brand">Adicionar Profissional</a>
        </div>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nome</th>
                        <th>Especialidade</th>
                        <th>Cidade/UF</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($profissionais as $prof): ?>
                    <tr>
                        <td><?php echo $prof['id']; ?></td>
                        <td>
                            <?php if ($prof['foto']): ?>
                                <img src="../uploads/<?php echo $prof['foto']; ?>" alt="Foto" width="50" height="50" class="rounded-circle" style="object-fit:cover;">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/50" alt="Foto" width="50" height="50" class="rounded-circle">
                            <?php endif; ?>
                        </td>
                        <td><?php echo $prof['nome']; ?></td>
                        <td><?php echo $prof['especialidade']; ?></td>
                        <td><?php echo $prof['cidade']; ?>/<?php echo $prof['estado']; ?></td>
                        <td>
                            <a href="editar.php?id=<?php echo $prof['id']; ?>" class="btn btn-sm btn-warning">Editar</a>
                            <a href="deletar.php?id=<?php echo $prof['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Deletar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
