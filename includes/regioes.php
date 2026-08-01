<?php

if (!isset($regioes_brasil) || !is_array($regioes_brasil)) {
    $regioes_brasil = [
        'Norte' => ['AC','AP','AM','PA','RO','RR','TO'],
        'Nordeste' => ['AL','BA','CE','MA','PB','PE','PI','RN','SE'],
        'Centro-Oeste' => ['DF','GO','MT','MS'],
        'Sudeste' => ['ES','MG','RJ','SP'],
        'Sul' => ['PR','RS','SC']
    ];
}

if (!function_exists('normalize_regiao_public')) {
    function normalize_regiao_public($raw) {
        $value = trim((string) $raw);
        if ($value === '') {
            return '';
        }
        $value = mb_strtolower($value, 'UTF-8');
        $value = str_replace(['ç','ã','á','à','â','é','ê','í','ó','ô','õ','ú','ü'], ['c','a','a','a','a','e','e','i','o','o','o','u','u'], $value);

        if ($value === 'norte') return 'Norte';
        if ($value === 'nordeste') return 'Nordeste';
        if ($value === 'centro-oeste' || $value === 'centro oeste') return 'Centro-Oeste';
        if ($value === 'sudeste') return 'Sudeste';
        if ($value === 'sul') return 'Sul';

        return '';
    }
}

if (!function_exists('build_regiao_where_sql')) {
    function build_regiao_where_sql($conn, $regiaoSelecionada) {
        global $regioes_brasil;
        $regiao = normalize_regiao_public($regiaoSelecionada);
        if ($regiao === '') {
            return '';
        }
        $ufs = $regioes_brasil[$regiao] ?? [];
        if (empty($ufs)) {
            return '';
        }
        $escaped = array_map(function($uf) use ($conn) {
            return "'" . mysqli_real_escape_string($conn, $uf) . "'";
        }, $ufs);
        return 'estado IN (' . implode(',', $escaped) . ')';
    }
}
