<?php
require_once 'config.php';
require_once __DIR__ . '/includes/especialidades.php';
$regioesHelper = __DIR__ . '/includes/regioes.php';
if (file_exists($regioesHelper)) {
    require_once $regioesHelper;
}

$especialidade = isset($_GET['especialidade']) ? mysqli_real_escape_string($conn, $_GET['especialidade']) : '';
$regiao = isset($_GET['regiao']) ? mysqli_real_escape_string($conn, $_GET['regiao']) : '';
$estado = isset($_GET['estado']) ? mysqli_real_escape_string($conn, $_GET['estado']) : '';
$cidade = isset($_GET['cidade']) ? mysqli_real_escape_string($conn, $_GET['cidade']) : '';

$query = "SELECT * FROM profissionais WHERE ativo = 1";

if ($regiao) {
    $whereRegiao = build_regiao_where_sql($conn, $regiao);
    if ($whereRegiao !== '') {
        $query .= " AND " . $whereRegiao;
    }
}
if ($especialidade) {
    $whereEspecialidade = build_especialidade_where_sql($conn, $especialidade);
    if ($whereEspecialidade !== '') {
        $query .= " AND " . $whereEspecialidade;
    }
}

if ($estado) {
    $query .= " AND estado = '$estado'";
}

if ($cidade) {
    $query .= " AND cidade LIKE '%$cidade%'";
}

$query .= " ORDER BY nome ASC";

$result = mysqli_query($conn, $query);
$profissionais = [];
while ($row = mysqli_fetch_assoc($result)) {
    $profissionais[] = $row;
}

function slugify($text) {
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $text = str_replace(
        ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç','ñ','Á','À','Ã','Â','Ä','É','È','Ê','Ë','Í','Ì','Î','Ï','Ó','Ò','Õ','Ô','Ö','Ú','Ù','Û','Ü','Ç','Ñ'],
        ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n','a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n'],
        $text
    );
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);
    $text = trim($text, '-');
    return $text ?: 'perfil';
}


function whatsapp_link($number) {
    $digits = preg_replace('/\D+/', '', (string) $number);
    if ($digits === '') {
        return '';
    }
    if (strlen($digits) === 10 || strlen($digits) === 11) {
        $digits = '55' . $digits;
    }
    return 'https://wa.me/' . $digits;
}
?>
<?php if (!empty($profissionais)): ?>
    <?php foreach ($profissionais as $prof): ?>
    <?php $profileUrl = site_url('especialista/' . slugify($prof['especialidade']) . '/' . slugify($prof['nome'])); ?>
    <?php $especialidadeExibicao = especialidade_validada_public($prof); ?>
    <?php $registroExibicao = format_registro_profissional_public($prof); ?>
    <div class="col-md-4">
        <div class="card card-profissional h-100">
            <div class="position-relative">
                <?php if (SHOW_CARD_CONSULTA): ?>
                    <a href="<?php echo $profileUrl; ?>">
                        <?php if ($prof['foto']): ?>
                            <img src="<?php echo site_url('uploads/' . $prof['foto']); ?>" class="card-img-top" alt="<?php echo $prof['nome']; ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x250?text=Sem+Foto" class="card-img-top" alt="<?php echo $prof['nome']; ?>">
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <?php if ($prof['foto']): ?>
                        <img src="<?php echo site_url('uploads/' . $prof['foto']); ?>" class="card-img-top" alt="<?php echo $prof['nome']; ?>">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x250?text=Sem+Foto" class="card-img-top" alt="<?php echo $prof['nome']; ?>">
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <h5 class="card-title mb-1">
                    <?php if (SHOW_CARD_CONSULTA): ?>
                        <a href="<?php echo $profileUrl; ?>" class="text-decoration-none text-dark"><?php echo $prof['nome']; ?></a>
                    <?php else: ?>
                        <?php echo $prof['nome']; ?>
                    <?php endif; ?>
                </h5>
                <?php if ($especialidadeExibicao !== ''): ?>
                    <p class="card-text text-muted"><?php echo htmlspecialchars($especialidadeExibicao); ?></p>
                <?php endif; ?>
                <?php if ($registroExibicao !== ''): ?>
                    <p class="small text-muted mb-1"><i class="bi bi-card-text"></i> <?php echo htmlspecialchars($registroExibicao); ?></p>
                <?php endif; ?>
                <p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?php echo $prof['cidade']; ?> - <?php echo $prof['estado']; ?></p>
                <?php if (SHOW_CARD_CONSULTA): ?>
                    <a href="<?php echo $profileUrl; ?>" class="btn btn-primary-custom w-100 mt-3">Ver Perfil Completo</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="col-12 text-center">
        <p class="lead text-muted">Nenhum profissional encontrado.</p>
    </div>
<?php endif; ?>
