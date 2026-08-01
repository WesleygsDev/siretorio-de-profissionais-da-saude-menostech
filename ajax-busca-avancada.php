<?php
require_once 'config.php';
require_once __DIR__ . '/includes/especialidades.php';
$regioesHelper = __DIR__ . '/includes/regioes.php';
if (file_exists($regioesHelper)) {
    require_once $regioesHelper;
}

function column_exists_public($conn, $column) {
    static $cache = [];
    if (array_key_exists($column, $cache)) {
        return $cache[$column];
    }
    $res = mysqli_query($conn, "SHOW COLUMNS FROM profissionais LIKE '" . mysqli_real_escape_string($conn, $column) . "'");
    $cache[$column] = ($res && mysqli_num_rows($res) > 0);
    return $cache[$column];
}

function build_order_sql() {
    return 'nome ASC';
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

function render_cards_html($profissionais) {
    ob_start();
    if (!empty($profissionais)) {
        foreach ($profissionais as $prof) {
            $profileUrl = site_url('especialista/' . slugify($prof['especialidade']) . '/' . slugify($prof['nome']));
            $especialidadeExibicao = especialidade_validada_public($prof);
            $registroExibicao = format_registro_profissional_public($prof);
            ?>
            <div class="col-md-6 col-xl-4">
                <div class="card card-profissional h-100">
                    <?php if (SHOW_CARD_CONSULTA): ?>
                        <a href="<?php echo $profileUrl; ?>">
                            <?php if ($prof['foto']): ?>
                                <img src="<?php echo site_url('uploads/' . $prof['foto']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prof['nome']); ?>">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x250?text=Sem+Foto" class="card-img-top" alt="<?php echo htmlspecialchars($prof['nome']); ?>">
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <?php if ($prof['foto']): ?>
                            <img src="<?php echo site_url('uploads/' . $prof['foto']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($prof['nome']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x250?text=Sem+Foto" class="card-img-top" alt="<?php echo htmlspecialchars($prof['nome']); ?>">
                        <?php endif; ?>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title mb-1">
                            <?php if (SHOW_CARD_CONSULTA): ?>
                                <a href="<?php echo $profileUrl; ?>" class="text-decoration-none text-dark"><?php echo htmlspecialchars($prof['nome']); ?></a>
                            <?php else: ?>
                                <?php echo htmlspecialchars($prof['nome']); ?>
                            <?php endif; ?>
                        </h5>
                        <?php if ($especialidadeExibicao !== ''): ?>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($especialidadeExibicao); ?></p>
                        <?php endif; ?>
                        <?php if ($registroExibicao !== ''): ?>
                            <p class="small text-muted mb-1"><i class="bi bi-card-text"></i> <?php echo htmlspecialchars($registroExibicao); ?></p>
                        <?php endif; ?>
                        <p class="small text-muted mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($prof['cidade']); ?> - <?php echo htmlspecialchars($prof['estado']); ?></p>
                        <?php if (SHOW_CARD_CONSULTA): ?>
                            <a href="<?php echo $profileUrl; ?>" class="btn btn-primary-custom w-100 mt-3">Ver Perfil Completo</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="col-12">
            <div class="text-center py-5">
                <h3 class="h5 mb-2">Nenhum profissional encontrado</h3>
                <p class="text-muted mb-0">Tente ajustar os filtros para ampliar a busca.</p>
            </div>
        </div>
        <?php
    }
    return ob_get_clean();
}

function render_pagination_html($currentPage, $totalPages) {
    if ($totalPages <= 1) {
        return '';
    }

    $start = max(1, $currentPage - 2);
    $end = min($totalPages, $currentPage + 2);
    ob_start();
    ?>
    <nav aria-label="Paginação dos resultados">
        <ul class="pagination pagination-brand justify-content-center mb-0">
            <li class="page-item <?php echo $currentPage <= 1 ? 'disabled' : ''; ?>">
                <button class="page-link" type="button" data-page="<?php echo max(1, $currentPage - 1); ?>">Anterior</button>
            </li>
            <?php for ($page = $start; $page <= $end; $page++): ?>
                <li class="page-item <?php echo $page === $currentPage ? 'active' : ''; ?>">
                    <button class="page-link" type="button" data-page="<?php echo $page; ?>"><?php echo $page; ?></button>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php echo $currentPage >= $totalPages ? 'disabled' : ''; ?>">
                <button class="page-link" type="button" data-page="<?php echo min($totalPages, $currentPage + 1); ?>">Próxima</button>
            </li>
        </ul>
    </nav>
    <?php
    return ob_get_clean();
}

$especialidade = isset($_GET['especialidade']) ? mysqli_real_escape_string($conn, $_GET['especialidade']) : '';
$regiao = isset($_GET['regiao']) ? mysqli_real_escape_string($conn, $_GET['regiao']) : '';
$estado = isset($_GET['estado']) ? mysqli_real_escape_string($conn, $_GET['estado']) : '';
$cidade = isset($_GET['cidade']) ? mysqli_real_escape_string($conn, $_GET['cidade']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$atendimento = isset($_GET['atendimento']) ? mysqli_real_escape_string($conn, $_GET['atendimento']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$perPage = 9;

$hasAtendimento = column_exists_public($conn, 'atendimento');

$where = " FROM profissionais WHERE ativo = 1";
if ($regiao !== '') {
    $whereRegiao = build_regiao_where_sql($conn, $regiao);
    if ($whereRegiao !== '') {
        $where .= " AND " . $whereRegiao;
    }
}
if ($especialidade !== '') {
    $whereEspecialidade = build_especialidade_where_sql($conn, $especialidade);
    if ($whereEspecialidade !== '') {
        $where .= " AND " . $whereEspecialidade;
    }
}
if ($estado !== '') {
    $where .= " AND estado = '$estado'";
}
if ($cidade !== '') {
    $where .= " AND cidade LIKE '%$cidade%'";
}
if ($keyword !== '') {
    $where .= " AND (nome LIKE '%$keyword%' OR especialidade LIKE '%$keyword%' OR cidade LIKE '%$keyword%' OR biografia LIKE '%$keyword%')";
}
if ($hasAtendimento && $atendimento !== '') {
    $where .= " AND atendimento = '$atendimento'";
}

$countResult = mysqli_query($conn, "SELECT COUNT(*) AS total" . $where);
$countRow = $countResult ? mysqli_fetch_assoc($countResult) : ['total' => 0];
$total = (int) ($countRow['total'] ?? 0);
$totalPages = max(1, (int) ceil($total / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;
$orderSql = build_order_sql();

$query = "SELECT *" . $where . " ORDER BY " . $orderSql . " LIMIT $perPage OFFSET $offset";
$result = mysqli_query($conn, $query);
$profissionais = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $profissionais[] = $row;
    }
}

$start = $total > 0 ? $offset + 1 : 0;
$end = min($offset + $perPage, $total);

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'total' => $total,
    'current_page' => $page,
    'total_pages' => $totalPages,
    'summary' => $total > 0 ? "Exibindo $start-$end de $total profissional(is)" : 'Nenhum resultado encontrado',
    'html' => render_cards_html($profissionais),
    'pagination' => render_pagination_html($page, $totalPages)
]);
