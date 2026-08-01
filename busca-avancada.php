<?php
require_once 'config.php';

require_once __DIR__ . '/includes/especialidades.php';
$especialidades = $especialidades_reconhecidas;
$regioesHelper = __DIR__ . '/includes/regioes.php';
if (file_exists($regioesHelper)) {
    require_once $regioesHelper;
}

$estados = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

$especialidade = isset($_GET['especialidade']) ? mysqli_real_escape_string($conn, $_GET['especialidade']) : '';
$regiao = isset($_GET['regiao']) ? mysqli_real_escape_string($conn, $_GET['regiao']) : '';
$estado = isset($_GET['estado']) ? mysqli_real_escape_string($conn, $_GET['estado']) : '';
$cidade = isset($_GET['cidade']) ? mysqli_real_escape_string($conn, $_GET['cidade']) : '';
$keyword = isset($_GET['keyword']) ? mysqli_real_escape_string($conn, $_GET['keyword']) : '';
$atendimento = isset($_GET['atendimento']) ? mysqli_real_escape_string($conn, $_GET['atendimento']) : '';
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;

if ($especialidade !== '') {
    $especialidade = normalize_especialidade_public($especialidade);
}
if ($regiao !== '') {
    $regiao = normalize_regiao_public($regiao);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesquisa Avançada - Diretório MenoTech</title>
    <?php require_once __DIR__ . '/includes/brand-head.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>

    <section class="page-hero">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 align-items-lg-center">
                <div>
                    <p class="eyebrow mb-2">Diretório MenoTech</p>
                    <h1 class="h2 fw-bold mb-2">Pesquisa avançada de profissionais</h1>
                    <p class="mb-0 text-muted">Refine por localização, atendimento e palavra-chave.</p>
                </div>
                <a href="<?php echo site_url('index.php'); ?>" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i> Voltar para busca rápida</a>
            </div>
            <nav aria-label="breadcrumb" class="mt-3">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('index.php'); ?>">Início</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Busca Avançada</li>
                </ol>
            </nav>
        </div>
    </section>

    <main class="container py-5">
        <div class="row g-4">
            <div class="col-lg-4 col-xl-3">
                <aside class="sidebar-card">
                    <form method="GET" id="advancedSearchForm">
                        <div class="mb-3">
                            <label class="form-label">Especialidade</label>
                            <select name="especialidade" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach ($especialidades as $esp): ?>
                                    <option value="<?php echo $esp; ?>" <?php echo $especialidade === $esp ? 'selected' : ''; ?>><?php echo $esp; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Região</label>
                            <select name="regiao" class="form-select">
                                <option value="">Todas</option>
                                <?php foreach (array_keys($regioes_brasil) as $r): ?>
                                    <option value="<?php echo $r; ?>" <?php echo $regiao === $r ? 'selected' : ''; ?>><?php echo $r; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="">Todos</option>
                                <?php foreach ($estados as $est): ?>
                                    <option value="<?php echo $est; ?>" <?php echo $estado === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="cidade" class="form-control" value="<?php echo htmlspecialchars($cidade); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Palavra-chave</label>
                            <input type="text" name="keyword" class="form-control" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Nome, área, tema...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Atendimento</label>
                            <select name="atendimento" class="form-select">
                                <option value="">Todos</option>
                                <option value="online" <?php echo $atendimento === 'online' ? 'selected' : ''; ?>>Online</option>
                                <option value="presencial" <?php echo $atendimento === 'presencial' ? 'selected' : ''; ?>>Presencial</option>
                                <option value="ambos" <?php echo $atendimento === 'ambos' ? 'selected' : ''; ?>>Online e Presencial</option>
                            </select>
                        </div>
                        <input type="hidden" name="page" value="<?php echo $page; ?>" id="pageInput">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-custom">Aplicar filtros</button>
                            <a href="<?php echo site_url('busca-avancada.php'); ?>" class="btn btn-outline-secondary">Limpar filtros</a>
                        </div>
                    </form>
                </aside>
            </div>
            <div class="col-lg-8 col-xl-9">
                <div class="results-head d-flex flex-column flex-md-row justify-content-between gap-2 align-items-md-center">
                    <div>
                        <h2 class="h4 mb-1">Resultados</h2>
                        <p class="mb-0 results-summary" id="resultsSummary">Carregando resultados...</p>
                        <p class="small text-muted mb-0">Resultados em ordem alfabética.</p>
                    </div>
                </div>
                <div class="results-area" id="resultsArea">
                    <div class="row g-4" id="advancedResults">
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="spinner-border text-secondary" role="status"></div>
                            </div>
                        </div>
                    </div>
                    <div class="pt-4" id="advancedPagination"></div>
                </div>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        (function() {
            var $form = $('#advancedSearchForm');
            var $resultsArea = $('#resultsArea');
            var $results = $('#advancedResults');
            var $pagination = $('#advancedPagination');
            var $summary = $('#resultsSummary');
            var $pageInput = $('#pageInput');
            var debounceTimer = null;
            var focusTarget = (new URLSearchParams(window.location.search)).get('focus') || '';

            function buildParams(page) {
                var data = $form.serializeArray();
                data = data.filter(function(item) {
                    return item.name !== 'page';
                });
                data.push({ name: 'page', value: page || 1 });
                return $.param(data);
            }

            function updateUrl(page) {
                var params = new URLSearchParams(buildParams(page));
                if (page === 1) {
                    params.delete('page');
                }
                var query = params.toString();
                var newUrl = 'busca-avancada.php' + (query ? '?' + query : '');
                window.history.replaceState({}, '', newUrl);
            }

            function loadResults(page) {
                var targetPage = page || 1;
                $pageInput.val(targetPage);
                $resultsArea.addClass('loading');
                $.ajax({
                    url: 'ajax-busca-avancada.php',
                    type: 'GET',
                    dataType: 'json',
                    data: buildParams(targetPage),
                    success: function(response) {
                        $summary.text(response.summary);
                        $results.html(response.html);
                        $pagination.html(response.pagination);
                        $pageInput.val(response.current_page);
                        updateUrl(response.current_page);
                    },
                    error: function() {
                        $summary.text('Não foi possível carregar os resultados agora.');
                        $results.html('<div class="col-12"><div class="text-center py-5"><h3 class="h5 mb-2">Erro ao carregar</h3><p class="text-muted mb-0">Tente novamente em instantes.</p></div></div>');
                        $pagination.html('');
                    },
                    complete: function() {
                        $resultsArea.removeClass('loading');
                    }
                });
            }

            function queueReload() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    loadResults(1);
                }, 250);
            }

            $form.on('submit', function(e) {
                e.preventDefault();
                loadResults(1);
            });

            $form.on('change', 'select', function() {
                loadResults(1);
            });

            $form.on('keyup', 'input[type="text"]', function() {
                queueReload();
            });

            $(document).on('click', '#advancedPagination .page-link', function() {
                var $item = $(this).closest('.page-item');
                if ($item.hasClass('disabled') || $item.hasClass('active')) {
                    return;
                }
                var page = parseInt($(this).data('page'), 10) || 1;
                loadResults(page);
                window.scrollTo({ top: document.querySelector('.results-head').offsetTop - 90, behavior: 'smooth' });
            });

            if (focusTarget) {
                var focusEl = $form.find('[name="' + focusTarget + '"]')[0];
                if (focusEl && typeof focusEl.focus === 'function') {
                    focusEl.focus();
                }
            }

            loadResults(<?php echo $page; ?>);
        })();
    </script>
</body>
</html>
