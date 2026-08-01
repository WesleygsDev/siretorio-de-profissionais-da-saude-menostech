<?php
$headerEspecialidades = $especialidades ?? [];
$headerEstados = $estados ?? [];
?>
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
                            <?php foreach ($headerEspecialidades as $esp): ?>
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
