<?php
require_once 'config.php';

require_once __DIR__ . '/includes/especialidades.php';
$especialidades = $especialidades_reconhecidas;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$especialidadeParam = isset($_GET['especialidade']) ? mysqli_real_escape_string($conn, $_GET['especialidade']) : '';
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

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

function get_profile_permalink($profissional) {
    return site_url('especialista/' . slugify($profissional['especialidade']) . '/' . slugify($profissional['nome']));
}

$profissional = null;

if ($id > 0) {
    $query = "SELECT * FROM profissionais WHERE id = $id AND ativo = 1";
    $result = mysqli_query($conn, $query);
    $profissional = mysqli_fetch_assoc($result);
} elseif ($especialidadeParam && $slug) {
    $especialidadeSlug = strtolower($especialidadeParam);
    $slug = strtolower($slug);
    $query = "SELECT * FROM profissionais WHERE ativo = 1";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        if (slugify($row['especialidade']) === $especialidadeSlug && slugify($row['nome']) === $slug) {
            $profissional = $row;
            break;
        }
    }
}

if (!$profissional) {
    header('Location: index.php');
    exit;
}

$especialidadeExibicao = especialidade_validada_public($profissional);
$registroExibicao = format_registro_profissional_public($profissional);


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
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
        $baseUrl = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $profileUrl = $baseUrl . '/' . get_profile_permalink($profissional);
        $description = !empty($profissional['biografia'])
            ? mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($profissional['biografia']))), 0, 155, 'UTF-8')
            : "Confira o perfil de {$profissional['nome']} com atendimento em {$profissional['cidade']} - {$profissional['estado']}.";
        $ogImage = $profissional['foto']
            ? $baseUrl . site_url('uploads/' . $profissional['foto'])
            : $baseUrl . site_url('assets/images/Selo_MenoTech_Principal_Vinho.png');
        $titleSuffix = $especialidadeExibicao !== '' ? $especialidadeExibicao : 'Profissional';
        $pageTitle = "{$profissional['nome']} | {$titleSuffix} certificado MenoTech";
        $keywords = htmlspecialchars("{$profissional['nome']}, {$titleSuffix}, menopausa, especialista, diretório MenoTech, {$profissional['cidade']}, {$profissional['estado']}");
        $sameAs = [];
        if (!empty($profissional['instagram'])) {
            $sameAs[] = '"https://instagram.com/' . ltrim(strtolower($profissional['instagram']), '@') . '"';
        }
        if (!empty($profissional['site'])) {
            $sameAs[] = '"' . htmlspecialchars($profissional['site']) . '"';
        }
        $sameAsJson = !empty($sameAs) ? implode(',', $sameAs) : '';
    ?>
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo $keywords; ?>">
    <meta name="author" content="MenoTech">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($profileUrl); ?>">
    <meta property="og:type" content="profile">
    <meta property="og:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($profileUrl); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <meta property="og:site_name" content="MenoTech">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($pageTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($ogImage); ?>">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Person",
        "name": "<?php echo addslashes($profissional['nome']); ?>",
        "jobTitle": "<?php echo addslashes($especialidadeExibicao !== '' ? $especialidadeExibicao : 'Profissional'); ?>",
        "url": "<?php echo addslashes($profileUrl); ?>",
        "image": "<?php echo addslashes($ogImage); ?>",
        "description": "<?php echo addslashes($description); ?>",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "<?php echo addslashes($profissional['cidade']); ?>",
            "addressRegion": "<?php echo addslashes($profissional['estado']); ?>"
        }<?php echo $sameAsJson ? ",\n        \"sameAs\": [{$sameAsJson}]" : ''; ?>
    }
    </script>
    <?php require_once __DIR__ . '/includes/brand-head.php'; ?>
</head>
<body class="profile-page">
    <nav class="navbar navbar-expand-lg site-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="https://menotech.com.br/"><img src="<?php echo site_url('assets/images/logo.webp'); ?>" alt="MenoTech" style=""></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Alternar navegação">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="mainNav">
                <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="https://menotech.com.br/">Início</a></li>
                    <li class="nav-item"><a class="nav-link" href="https://menotech.com.br/formacao/">Formação</a></li>
                    <li class="nav-item dropdown position-static">
                        <a class="nav-link dropdown-toggle" href="#" id="especialidadesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">Especialidades</a>
                        <div class="dropdown-menu mega-menu p-4" aria-labelledby="especialidadesDropdown">
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-2">
                                <?php foreach ($especialidades as $esp): ?>
                                    <?php $icon = $especialidades_icons[$esp] ?? 'bi-tag'; ?>
                                    <div class="col">
                                        <a class="dropdown-item d-flex align-items-center gap-2" href="<?php echo site_url('busca-avancada.php?especialidade=' . urlencode($esp)); ?>">
                                            <span class="menu-item-icon"><i class="bi <?php echo $icon; ?>"></i></span>
                                            <span><?php echo $esp; ?></span>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="https://menotech.com.br/blog/">Blog</a></li>
                </ul>
            </div>
            <div class="d-flex align-items-center ms-lg-auto mt-3 mt-lg-0">
                <a class="btn btn-primary-custom" href="https://menotech.com.br/formacao/">Eu quero fazer parte</a>
            </div>
        </div>
    </nav>
    <header class="hero-section mb-5">
        <div class="container">
            <a href="<?php echo site_url('index.php'); ?>" class="text-decoration-none mb-3 d-inline-block">
                <i class="bi bi-arrow-left"></i> Voltar ao diretório
            </a>
            <nav aria-label="breadcrumb" class="mt-3">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo site_url('index.php'); ?>">Início</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo site_url('busca-avancada.php'); ?>">Buscar profissionais</a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($profissional['nome']); ?></li>
                </ol>
            </nav>
        </div>
    </header>

    <main class="container mb-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card border-0 shadow-lg">
                    <?php if ($profissional['foto']): ?>
                        <img src="<?php echo site_url('uploads/' . $profissional['foto']); ?>" class="card-img-top" alt="<?php echo $profissional['nome']; ?>" style="height: 400px; object-fit: cover;">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x400?text=Sem+Foto" class="card-img-top" alt="<?php echo $profissional['nome']; ?>">
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <p class="contact-invite mb-3">Entre em contato com este profissional</p>
                        <div class="contact-actions">
                            <?php if (!empty($profissional['whatsapp'])): ?>
                                <a href="<?php echo whatsapp_link($profissional['whatsapp']); ?>" target="_blank" class="contact-chip whatsapp" aria-label="WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                            <?php endif; ?>
                            <a href="mailto:<?php echo $profissional['email']; ?>" class="contact-chip email" aria-label="E-mail">
                                <i class="bi bi-envelope-fill"></i>
                            </a>
                            <?php if ($profissional['instagram']): ?>
                                <a href="https://instagram.com/<?php echo ltrim($profissional['instagram'], '@'); ?>" target="_blank" class="contact-chip instagram" aria-label="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($profissional['telefone']): ?>
                                <a href="tel:<?php echo $profissional['telefone']; ?>" class="contact-chip phone" aria-label="Telefone">
                                    <i class="bi bi-telephone"></i>
                                </a>
                            <?php endif; ?>
                            <?php if ($profissional['site']): ?>
                                <a href="<?php echo $profissional['site']; ?>" target="_blank" class="contact-chip site" aria-label="Site">
                                    <i class="bi bi-globe2"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                    <h1 class="fw-bold mb-0"><?php echo $profissional['nome']; ?></h1>
                    <img src="<?php echo site_url('assets/images/Selo_MenoTech_Principal_Vinho.png'); ?>" class="selo-inline selo-inline-lg" alt="Certificado MenoTech">
                </div>
                <?php if ($especialidadeExibicao !== ''): ?>
                    <h2 class="h4 text-muted mb-4"><?php echo htmlspecialchars($especialidadeExibicao); ?></h2>
                <?php endif; ?>
                <p class="mb-4">
                    <strong>Localização:</strong> <?php echo $profissional['cidade']; ?> - <?php echo $profissional['estado']; ?>
                </p>
                <?php
                    $conselhoTipo = $profissional['conselho_tipo'] ?? '';
                    $conselhoNumero = $profissional['conselho_numero'] ?? '';
                    $atendimento = $profissional['atendimento'] ?? '';
                    $whatsapp = $profissional['whatsapp'] ?? '';
                    $endereco = $profissional['endereco'] ?? '';
                    $bairro = $profissional['bairro'] ?? '';
                    $cep = $profissional['cep'] ?? '';
                ?>
                <?php if (!empty($registroExibicao) || !empty($atendimento) || !empty($whatsapp) || !empty($endereco) || !empty($bairro) || !empty($cep)): ?>
                    <div class="info-card mb-4">
                        <h3 class="fw-semibold mb-3">Dados cadastrais</h3>
                        <div class="row g-3">
                            <?php if (!empty($registroExibicao)): ?>
                                <div class="col-md-6">
                                    <div class="info-label">Registro profissional</div>
                                    <div><?php echo htmlspecialchars($registroExibicao); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($atendimento)): ?>
                                <div class="col-md-6">
                                    <div class="info-label">Atendimento</div>
                                    <div>
                                        <?php
                                            if ($atendimento === 'online') echo 'Online';
                                            elseif ($atendimento === 'presencial') echo 'Presencial';
                                            elseif ($atendimento === 'ambos') echo 'Online e Presencial';
                                            else echo htmlspecialchars($atendimento);
                                        ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($whatsapp)): ?>
                                <div class="col-md-6">
                                    <div class="info-label">WhatsApp</div>
                                    <div><?php echo htmlspecialchars($whatsapp); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($endereco) || !empty($bairro) || !empty($cep)): ?>
                                <div class="col-md-6">
                                    <div class="info-label">Endereço</div>
                                    <div><?php echo htmlspecialchars(trim($endereco . ($bairro ? ' - ' . $bairro : '') . ($cep ? ' (CEP ' . $cep . ')' : ''))); ?></div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!empty($profissional['biografia'])): ?>
                    <div class="mb-5">
                        <h3 class="fw-bold mb-3">Sobre</h3>
                        <div class="lead text-muted">
                            <?php
                                $bio = trim($profissional['biografia']);
                                $paragraphs = preg_split('/\r?\n\s*\r?\n/', $bio);
                                foreach ($paragraphs as $paragraph):
                                    if (trim($paragraph) === '') continue;
                            ?>
                                <p><?php echo nl2br(htmlspecialchars(trim($paragraph))); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
