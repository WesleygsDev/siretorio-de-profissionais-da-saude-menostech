<?php
require_once 'config.php';

require_once __DIR__ . '/includes/especialidades.php';
$especialidades = $especialidades_reconhecidas;
$regioesHelper = __DIR__ . '/includes/regioes.php';
if (file_exists($regioesHelper)) {
    require_once $regioesHelper;
}

$estados = ['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'];

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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diretório de Certificados - MenoTech</title>
    <?php require_once __DIR__ . '/includes/brand-head.php'; ?>
</head>
<body>
    <?php require_once __DIR__ . '/includes/header.php'; ?>
    <header class="hero-section">
        <div class="container">
            <div class="text-center mb-5" id="filtros-busca">
                <h1 class="display-4 fw-bold mb-3 hero-heading">Encontre um profissional certificado em  <span class="hero-highlight">MENOPAUSA </span>pela MenoTech</h1>
            </div>
            <p class="lead text-center">O nosso diretório conecta você a esses perfis, mas cada atendimento é realizado de forma autônoma, sob responsabilidade do próprio profissional.</p>
              <div class="seal-explainer d-flex align-items-center gap-3">
                <img src="<?php echo site_url('assets/images/Selo_MenoTech_Principal_Vinho.png'); ?>" alt="Selo MenoTech">
                <p>O Selo MenoTech identifica profissionais que concluíram nossa formação avançada em menopausa e longevidade feminina. É a sua garantia de que está diante de alguém preparado para cuidar dessa fase com base em evidência.</p>
            </div>
            <div class="search-box row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="especialidade" class="form-label search-label">Especialidade</label>
                    <select id="especialidade" class="form-select">
                        <option value="">Todas as especialidades</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?php echo $esp; ?>"><?php echo $esp; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label search-label">Estado</label>
                    <select id="estado" class="form-select">
                        <option value="">Todos os estados</option>
                        <?php foreach ($estados as $est): ?>
                            <option value="<?php echo $est; ?>"><?php echo $est; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="cidade" class="form-label search-label">Cidade</label>
                    <input type="text" id="cidade" class="form-control" placeholder="Cidade">
                </div>
                <div class="col-12">
                    <div class="search-actions">
                        <button id="buscarBtn" type="button" class="btn btn-primary-custom search-submit-btn">
                            <i class="bi bi-search me-2"></i>
                            <span>Buscar</span>
                        </button>
                        <a href="<?php echo site_url('busca-avancada.php'); ?>" class="advanced-link advanced-link-button" aria-label="Pesquisa avançada" title="Pesquisa avançada">
                            <i class="bi bi-plus-lg me-2"></i>
                            <span>Mais opcoes</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="platform-section" style='background-color:#fff;'>
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <div class="platform-image-wrap">
                        <img src="<?php echo site_url('assets/images/mapa.png'); ?>" alt="Plataforma MenoTech conectando especialistas em diversas regiões do Brasil" class="img-fluid platform-image">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="platform-content">
                        <p class="eyebrow text-white mb-3">Atendimento Em Todo O Brasil</p>
                        <h2 class="section-title text-white mb-3">Uma plataforma que conecta pacientes a especialistas em diversas regiões do Brasil</h2>
                        <p class="platform-text mb-3">O Diretório MenoTech foi estruturado para facilitar a busca por profissionais qualificados em diferentes estados e cidades, ampliando o acesso ao cuidado especializado em menopausa e saúde da mulher.</p>
                        <p class="platform-text mb-4">Nossa plataforma reúne especialistas com atuação em várias regiões do país, permitindo encontrar o profissional mais adequado de forma organizada, ética e segura.</p>
                        <a href="#filtros-busca" class="btn btn-outline-light">Encontrar um especialista</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
<!--
    <section class="directory-info-section">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-6">
                    <div class="feature-image-placeholder">
                        <img src="<?php echo site_url('assets/images/menotech-sobre.jpeg'); ?>" alt="Sobre o diretório MenoTech" class="img-fluid rounded-4" style="width: 100%; height: 100%; object-fit: cover;" />
                    </div>
                </div>
                <div class="col-lg-6">
                    <h2 class="section-title">O que é o Diretório MenoTech?</h2>
                    <p class="section-subtitle">O diretório reúne profissionais certificados em menopausa e longevidade feminina para facilitar o encontro entre quem busca atendimento especializado e quem já está preparado para oferecer cuidados seguros.</p>
                    <div class="feature-card">
                        <p>Este é o lugar ideal para encontrar profissionais que passaram pela formação MenoTech e têm certificação reconhecida. Cada perfil passa por validação e traz informações atualizadas para sua escolha.</p>
                        <ul class="list-unstyled mt-3">
                            <li class="mb-2"><i class="bi bi-check2-circle text-primary me-2"></i>Profissionais com selo de certificação.</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-primary me-2"></i>Especialistas em menopausa e longevidade.</li>
                            <li class="mb-2"><i class="bi bi-check2-circle text-primary me-2"></i>Busca por categoria, estado e cidade.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>-->
<!--
    <section class="category-section bg-nude">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row align-items-lg-end justify-content-between gap-3 mb-5">
                <div class="text-center text-lg-start">
                    <h2 class="section-title">Profissionais por especialidade</h2>
                    <p class="mb-0">Explore os principais campos de atuação para encontrar o profissional mais adequado às suas necessidades.</p>
                </div>
                <div class="carousel-controls">
                    <button type="button" class="carousel-control-btn" id="especialidadesPrev" aria-label="Especialidade anterior">
                        <i class="bi bi-arrow-left"></i>
                    </button>
                    <button type="button" class="carousel-control-btn" id="especialidadesNext" aria-label="Próxima especialidade">
                        <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </div>
            <div class="category-carousel" id="especialidadesCarousel">
                <?php foreach ($especialidades as $esp): ?>
                    <?php
                        $icon = $especialidades_icons[$esp] ?? 'bi-tag';
                        $desc = $especialidades_descriptions[$esp] ?? '';
                    ?>
                    <div class="category-carousel-item">
                        <a class="category-card d-block text-decoration-none" href="<?php echo site_url('busca-avancada.php?especialidade=' . urlencode($esp) . '&focus=especialidade'); ?>">
                            <i class="bi <?php echo $icon; ?>"></i>
                            <h3 class="text-dark"><?php echo htmlspecialchars($esp); ?></h3>
                            <?php if ($desc !== ''): ?>
                                <p><?php echo htmlspecialchars($desc); ?></p>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center category-cta">
                <a href="<?php echo site_url('busca-avancada.php'); ?>" class="btn btn-primary-custom">Veja outras especialidades</a>
            </div>
        </div>
    </section>-->

  

   

    <?php require_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#buscarBtn').on('click', function(e) {
                e.preventDefault();
                var especialidade = $('#especialidade').val();
                var estado = $('#estado').val();
                var cidade = $('#cidade').val();

                var params = new URLSearchParams();
                if (especialidade) params.set('especialidade', especialidade);
                if (estado) params.set('estado', estado);
                if (cidade) params.set('cidade', cidade);

                var focus = '';
                if (cidade) focus = 'cidade';
                else if (estado) focus = 'estado';
                else if (especialidade) focus = 'especialidade';
                if (focus) params.set('focus', focus);

                var url = 'busca-avancada.php' + (params.toString() ? '?' + params.toString() : '');
                window.location.href = url;
            });

            function scrollEspecialidades(direction) {
                var carousel = document.getElementById('especialidadesCarousel');
                if (!carousel) return;
                var item = carousel.querySelector('.category-carousel-item');
                var step = item ? item.offsetWidth + 24 : 320;
                carousel.scrollBy({ left: direction * step, behavior: 'smooth' });
            }

            $('#especialidadesPrev').on('click', function() {
                scrollEspecialidades(-1);
            });

            $('#especialidadesNext').on('click', function() {
                scrollEspecialidades(1);
            });
        });
    </script>
</body>
</html>
