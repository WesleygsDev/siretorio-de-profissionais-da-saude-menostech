<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$query = "SELECT * FROM profissionais WHERE id = $id";
$result = mysqli_query($conn, $query);
$profissional = mysqli_fetch_assoc($result);

if ($profissional) {
    if ($profissional['foto'] && file_exists('../uploads/' . $profissional['foto'])) {
        unlink('../uploads/' . $profissional['foto']);
    }
    mysqli_query($conn, "DELETE FROM profissionais WHERE id = $id");
}

header('Location: index.php');
exit;
?>
