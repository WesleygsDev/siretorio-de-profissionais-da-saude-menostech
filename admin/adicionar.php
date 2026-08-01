<?php
session_start();
require_once '../config.php';
require_once __DIR__ . '/../includes/especialidades.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$especialidades = $especialidades_reconhecidas;

$estados = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

$success = '';
$error = '';

$rqeCheck = mysqli_query($conn, "SHOW COLUMNS FROM profissionais LIKE 'rqe'");
if (!$rqeCheck || mysqli_num_rows($rqeCheck) === 0) {
    mysqli_query($conn, "ALTER TABLE profissionais ADD COLUMN rqe VARCHAR(30) NULL");
}

function column_exists($conn, $table, $column) {
    static $cache = [];
    $key = $table . '.' . $column;
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $res = mysqli_query($conn, "SHOW COLUMNS FROM `$table` LIKE '" . mysqli_real_escape_string($conn, $column) . "'");
    $cache[$key] = ($res && mysqli_num_rows($res) > 0);
    return $cache[$key];
}

function create_image_from_path($path, $type) {
    if ($type === IMAGETYPE_JPEG) {
        return imagecreatefromjpeg($path);
    }
    if ($type === IMAGETYPE_PNG) {
        return imagecreatefrompng($path);
    }
    if ($type === IMAGETYPE_WEBP) {
        return imagecreatefromwebp($path);
    }
    if ($type === IMAGETYPE_GIF) {
        return imagecreatefromgif($path);
    }
    return false;
}

function process_image_center_crop($srcPath, $destPath, $targetW, $targetH) {
    if (!function_exists('imagecreatetruecolor')) {
        return false;
    }

    $info = @getimagesize($srcPath);
    if (!$info) {
        return false;
    }

    $srcW = $info[0];
    $srcH = $info[1];
    $type = $info[2];
    $srcImg = create_image_from_path($srcPath, $type);
    if (!$srcImg) {
        return false;
    }

    $targetRatio = $targetW / $targetH;
    $srcRatio = $srcW / $srcH;

    if ($srcRatio > $targetRatio) {
        $cropH = $srcH;
        $cropW = (int) round($srcH * $targetRatio);
        $srcX = (int) round(($srcW - $cropW) / 2);
        $srcY = 0;
    } else {
        $cropW = $srcW;
        $cropH = (int) round($srcW / $targetRatio);
        $srcX = 0;
        $srcY = (int) round(($srcH - $cropH) / 2);
    }

    $dstImg = imagecreatetruecolor($targetW, $targetH);
    imageinterlace($dstImg, true);
    imagealphablending($dstImg, true);
    imagesavealpha($dstImg, true);
    imagecopyresampled($dstImg, $srcImg, 0, 0, $srcX, $srcY, $targetW, $targetH, $cropW, $cropH);

    $saved = imagejpeg($dstImg, $destPath, 90);
    imagedestroy($srcImg);
    imagedestroy($dstImg);
    return $saved;
}

function save_cropped_data_url($dataUrl, $destDir, $targetW, $targetH) {
    if (!is_dir($destDir)) {
        return '';
    }
    if (!preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,/', $dataUrl, $m)) {
        return '';
    }
    $raw = substr($dataUrl, strpos($dataUrl, ',') + 1);
    $bin = base64_decode($raw, true);
    if ($bin === false) {
        return '';
    }

    $tmp = tempnam(sys_get_temp_dir(), 'crop_');
    if ($tmp === false) {
        return '';
    }
    file_put_contents($tmp, $bin);

    $filename = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
    $destPath = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $filename;

    $ok = process_image_center_crop($tmp, $destPath, $targetW, $targetH);
    @unlink($tmp);

    if ($ok) {
        return $filename;
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = mysqli_real_escape_string($conn, $_POST['nome']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $especialidade = mysqli_real_escape_string($conn, $_POST['especialidade']);
    $cidade = mysqli_real_escape_string($conn, $_POST['cidade']);
    $estado = mysqli_real_escape_string($conn, $_POST['estado']);
    $instagram = mysqli_real_escape_string($conn, $_POST['instagram']);
    $telefone = mysqli_real_escape_string($conn, $_POST['telefone']);
    $whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp'] ?? '');
    $site = mysqli_real_escape_string($conn, $_POST['site']);
    $biografia = mysqli_real_escape_string($conn, $_POST['biografia']);
    $conselho_tipo = mysqli_real_escape_string($conn, $_POST['conselho_tipo'] ?? '');
    $conselho_numero = mysqli_real_escape_string($conn, $_POST['conselho_numero'] ?? '');
    $rqe = mysqli_real_escape_string($conn, $_POST['rqe'] ?? '');
    $atendimento = mysqli_real_escape_string($conn, $_POST['atendimento'] ?? '');
    $endereco = mysqli_real_escape_string($conn, $_POST['endereco'] ?? '');
    $bairro = mysqli_real_escape_string($conn, $_POST['bairro'] ?? '');
    $cep = mysqli_real_escape_string($conn, $_POST['cep'] ?? '');
    $foto = '';

    $validationError = validate_registro_e_especialidade($especialidade, $conselho_tipo, $conselho_numero, $rqe);
    if ($validationError !== '') {
        $error = $validationError;
    } else {
    $uploadDir = '../uploads/';
    if (!empty($_POST['cropped_image'] ?? '')) {
        $saved = save_cropped_data_url($_POST['cropped_image'], $uploadDir, 1200, 750);
        if ($saved) {
            $foto = $saved;
        }
    } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $tmp = $_FILES['foto']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (in_array($ext, $allowed, true)) {
            $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.jpg';
            $destPath = $uploadDir . $fileName;
            if (process_image_center_crop($tmp, $destPath, 1200, 750)) {
                $foto = $fileName;
            } else {
                $fallbackName = time() . '_' . bin2hex(random_bytes(4)) . '_' . basename($_FILES['foto']['name']);
                $fallbackPath = $uploadDir . $fallbackName;
                if (move_uploaded_file($tmp, $fallbackPath)) {
                    $foto = $fallbackName;
                }
            }
        }
    }

    $fields = ['nome', 'email', 'especialidade', 'cidade', 'estado', 'instagram', 'telefone', 'site', 'biografia', 'foto'];
    $values = [$nome, $email, $especialidade, $cidade, $estado, $instagram, $telefone, $site, $biografia, $foto];

    if (column_exists($conn, 'profissionais', 'whatsapp')) {
        $fields[] = 'whatsapp';
        $values[] = $whatsapp;
    }
    if (column_exists($conn, 'profissionais', 'conselho_tipo')) {
        $fields[] = 'conselho_tipo';
        $values[] = $conselho_tipo;
    }
    if (column_exists($conn, 'profissionais', 'conselho_numero')) {
        $fields[] = 'conselho_numero';
        $values[] = $conselho_numero;
    }
    if (column_exists($conn, 'profissionais', 'rqe')) {
        $fields[] = 'rqe';
        $values[] = $rqe;
    }
    if (column_exists($conn, 'profissionais', 'atendimento')) {
        $fields[] = 'atendimento';
        $values[] = $atendimento;
    }
    if (column_exists($conn, 'profissionais', 'endereco')) {
        $fields[] = 'endereco';
        $values[] = $endereco;
    }
    if (column_exists($conn, 'profissionais', 'bairro')) {
        $fields[] = 'bairro';
        $values[] = $bairro;
    }
    if (column_exists($conn, 'profissionais', 'cep')) {
        $fields[] = 'cep';
        $values[] = $cep;
    }

    $fieldsSql = implode(', ', array_map(function($f) { return "`$f`"; }, $fields));
    $valuesSql = implode(', ', array_map(function($v) use ($conn) { return "'" . $v . "'"; }, $values));
    $query = "INSERT INTO profissionais ($fieldsSql) VALUES ($valuesSql)";

    if (mysqli_query($conn, $query)) {
        $success = 'Profissional adicionado com sucesso!';
    } else {
        $error = 'Erro: ' . mysqli_error($conn);
    }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Profissional - MenoTech</title>
    <?php require_once __DIR__ . '/includes/brand-head.php'; ?>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" rel="stylesheet">
</head>
<body class="admin-page">
    <nav class="navbar navbar-expand-lg navbar-dark admin-navbar">
        <div class="container">
            <a class="navbar-brand" href="index.php">MenoTech Admin</a>
            <div class="d-flex">
                <a href="logout.php" class="btn btn-light btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2>Adicionar Profissional</h2>
        <a href="index.php" class="btn btn-secondary mb-3">Voltar</a>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="cropped_image" id="cropped_image">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nome*</label>
                    <input type="text" name="nome" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">E-mail*</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Especialidade*</label>
                    <select name="especialidade" class="form-select" required>
                        <option value="">Selecione</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?php echo $esp; ?>"><?php echo $esp; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Cidade*</label>
                    <input type="text" name="cidade" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Estado*</label>
                    <select name="estado" class="form-select" required>
                        <option value="">Selecione</option>
                        <?php foreach ($estados as $est): ?>
                            <option value="<?php echo $est; ?>"><?php echo $est; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Instagram</label>
                    <input type="text" name="instagram" class="form-control" placeholder="@usuario">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Telefone</label>
                    <input type="text" name="telefone" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Site</label>
                    <input type="url" name="site" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" class="form-control" placeholder="(DDD) 9xxxx-xxxx">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Conselho</label>
                    <select name="conselho_tipo" class="form-select">
                        <option value="">Selecione</option>
                        <option value="CRM">CRM</option>
                        <option value="CRP">CRP</option>
                        <option value="CRN">CRN</option>
                        <option value="CREFITO">CREFITO</option>
                        <option value="CREF">CREF</option>
                        <option value="COREN">COREN</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Número do registro</label>
                    <input type="text" name="conselho_numero" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">RQE (médicos)</label>
                    <input type="text" name="rqe" class="form-control">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Atendimento</label>
                    <select name="atendimento" class="form-select">
                        <option value="">Selecione</option>
                        <option value="online">Online</option>
                        <option value="presencial">Presencial</option>
                        <option value="ambos">Online e Presencial</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">CEP</label>
                    <input type="text" name="cep" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Bairro</label>
                    <input type="text" name="bairro" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Endereço</label>
                <input type="text" name="endereco" class="form-control" placeholder="Rua, número, complemento">
            </div>
            <div class="mb-3">
                <label class="form-label">Biografia</label>
                <textarea name="biografia" class="form-control" rows="4"></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">Foto</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
                <div class="mt-3 crop-preview d-none" id="crop_preview_wrap">
                    <img id="crop_preview_img" alt="">
                </div>
            </div>
            <button type="submit" class="btn btn-brand">Adicionar</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <div class="modal fade" id="cropModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cortar foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="w-100" style="max-height:70vh;">
                        <img id="cropImage" alt="" style="max-width:100%; display:block;">
                    </div>
                    <div class="d-flex gap-2 mt-3 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="ratioCard">Card (16:10)</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="ratioSquare">Quadrado (1:1)</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-brand" id="applyCrop">Usar corte</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        (function () {
            var fileInput = document.querySelector('input[name="foto"]');
            var croppedInput = document.getElementById('cropped_image');
            var previewWrap = document.getElementById('crop_preview_wrap');
            var previewImg = document.getElementById('crop_preview_img');
            var cropImg = document.getElementById('cropImage');
            var modalEl = document.getElementById('cropModal');
            var modal = new bootstrap.Modal(modalEl);
            var cropper = null;

            function setAspect(ratio) {
                if (!cropper) return;
                cropper.setAspectRatio(ratio);
            }

            document.getElementById('ratioCard').addEventListener('click', function () { setAspect(16 / 10); });
            document.getElementById('ratioSquare').addEventListener('click', function () { setAspect(1); });

            fileInput.addEventListener('change', function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) return;
                if (!file.type || file.type.indexOf('image/') !== 0) return;

                var reader = new FileReader();
                reader.onload = function (e) {
                    cropImg.src = e.target.result;
                    croppedInput.value = '';
                    modal.show();
                };
                reader.readAsDataURL(file);
            });

            modalEl.addEventListener('shown.bs.modal', function () {
                if (cropper) cropper.destroy();
                cropper = new Cropper(cropImg, {
                    aspectRatio: 16 / 10,
                    viewMode: 1,
                    autoCropArea: 1,
                    responsive: true
                });
            });

            modalEl.addEventListener('hidden.bs.modal', function () {
                if (cropper) {
                    cropper.destroy();
                    cropper = null;
                }
            });

            document.getElementById('applyCrop').addEventListener('click', function () {
                if (!cropper) return;
                var canvas = cropper.getCroppedCanvas({ width: 1200, height: 750, imageSmoothingQuality: 'high' });
                var dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                croppedInput.value = dataUrl;
                previewImg.src = dataUrl;
                previewWrap.classList.remove('d-none');
                modal.hide();
            });
        })();
    </script>
    <script>
        (function () {
            var especialidadeSelect = document.querySelector('select[name="especialidade"]');
            var conselhoSelect = document.querySelector('select[name="conselho_tipo"]');
            var rqeInput = document.querySelector('input[name="rqe"]');

            var especialidadeToConselho = {
                'Ginecologia': 'CRM',
                'Endocrinologia': 'CRM',
                'Nutrição': 'CRN',
                'Psicologia': 'CRP',
                'Fisioterapia': 'CREFITO',
                'Educação Física': 'CREF',
                'Enfermagem': 'COREN'
            };

            function sync() {
                var esp = especialidadeSelect.value;
                var required = especialidadeToConselho[esp] || '';
                if (required && conselhoSelect.value !== required) {
                    conselhoSelect.value = required;
                }
                var isCrm = conselhoSelect.value === 'CRM';
                rqeInput.disabled = !isCrm;
                rqeInput.required = isCrm;
                if (!isCrm) {
                    rqeInput.value = '';
                }
            }

            if (especialidadeSelect) especialidadeSelect.addEventListener('change', sync);
            if (conselhoSelect) conselhoSelect.addEventListener('change', sync);
            sync();
        })();
    </script>
</body>
</html>
