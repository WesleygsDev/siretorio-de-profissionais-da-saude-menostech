<?php

$especialidades_reconhecidas = [
    'Ginecologia',
    'Endocrinologia',
    'Nutrição',
    'Psicologia',
    'Fisioterapia',
    'Educação Física',
    'Enfermagem'
];

$especialidades_icons = [
    'Ginecologia' => 'bi-heart-pulse',
    'Endocrinologia' => 'bi-droplet-half',
    'Nutrição' => 'bi-apple',
    'Psicologia' => 'bi-person-lines-fill',
    'Fisioterapia' => 'bi-person-bounding-box',
    'Educação Física' => 'bi-activity',
    'Enfermagem' => 'bi-hospital'
];

$especialidades_descriptions = [
    'Ginecologia' => 'Saúde da mulher e cuidado especializado na menopausa.',
    'Endocrinologia' => 'Hormônios, metabolismo e saúde integral.',
    'Nutrição' => 'Alimentação, composição corporal e bem-estar.',
    'Psicologia' => 'Apoio emocional e acompanhamento terapêutico.',
    'Fisioterapia' => 'Mobilidade, força, dor e qualidade de vida.',
    'Educação Física' => 'Treino seguro para saúde e desempenho.',
    'Enfermagem' => 'Acompanhamento e cuidado clínico centrado na pessoa.'
];

function normalize_especialidade_public($raw) {
    $value = trim((string) $raw);
    if ($value === '') {
        return '';
    }

    $normalized = mb_strtolower($value, 'UTF-8');

    if (preg_match('/gineco/', $normalized)) {
        return 'Ginecologia';
    }
    if (preg_match('/endo/', $normalized)) {
        return 'Endocrinologia';
    }
    if (preg_match('/nutri/', $normalized)) {
        return 'Nutrição';
    }
    if (preg_match('/psico/', $normalized)) {
        return 'Psicologia';
    }
    if (preg_match('/fisioter/', $normalized)) {
        return 'Fisioterapia';
    }
    if (preg_match('/educa[cç][aã]o\\s+f[ií]sica|ed\\.?\\s*f[ií]sica|educador(a)?\\s+f[ií]sic/', $normalized)) {
        return 'Educação Física';
    }
    if (preg_match('/enferm/', $normalized)) {
        return 'Enfermagem';
    }

    return $value;
}

function especialidade_filtro_para_db_vals($especialidadeSelecionada) {
    $esp = trim((string) $especialidadeSelecionada);
    if ($esp === '') {
        return [];
    }

    $normalized = mb_strtolower($esp, 'UTF-8');

    if ($normalized === 'ginecologia') {
        return ['Ginecologia', 'Ginecologista', 'Ginecologista(a)', 'Ginecologista/Obstetra', 'Ginecologista e Obstetra'];
    }
    if ($normalized === 'endocrinologia') {
        return ['Endocrinologia', 'Endocrinologista', 'Endocrinologista(a)'];
    }
    if ($normalized === 'nutrição' || $normalized === 'nutricao') {
        return ['Nutrição', 'Nutricionista', 'Nutricionista(a)'];
    }
    if ($normalized === 'psicologia') {
        return ['Psicologia', 'Psicóloga', 'Psicologo', 'Psicólogo', 'Psicologa', 'Psicoterapeuta'];
    }
    if ($normalized === 'fisioterapia') {
        return ['Fisioterapia', 'Fisioterapeuta', 'Fisioterapeuta(a)'];
    }
    if ($normalized === 'educação física' || $normalized === 'educacao fisica') {
        return ['Educação Física', 'Educadora Física', 'Educador Físico', 'Educador Fisico', 'Educadora Fisica', 'Profissional de Educação Física'];
    }
    if ($normalized === 'enfermagem') {
        return ['Enfermagem', 'Enfermeira', 'Enfermeiro', 'Enfermeira(o)'];
    }

    return [$esp];
}

function build_especialidade_where_sql($conn, $especialidadeSelecionada) {
    $vals = especialidade_filtro_para_db_vals($especialidadeSelecionada);
    if (empty($vals)) {
        return '';
    }
    $escaped = array_map(function($v) use ($conn) {
        return "'" . mysqli_real_escape_string($conn, $v) . "'";
    }, $vals);
    return 'especialidade IN (' . implode(',', $escaped) . ')';
}

function normalize_conselho_tipo_public($raw) {
    $value = trim((string) $raw);
    if ($value === '') {
        return '';
    }
    $value = mb_strtoupper($value, 'UTF-8');
    $value = preg_replace('/\s+/', '', $value);
    return $value;
}

function required_conselho_for_especialidade($especialidadeNormalizada) {
    $esp = normalize_especialidade_public($especialidadeNormalizada);
    if ($esp === 'Ginecologia' || $esp === 'Endocrinologia') {
        return 'CRM';
    }
    if ($esp === 'Nutrição') {
        return 'CRN';
    }
    if ($esp === 'Psicologia') {
        return 'CRP';
    }
    if ($esp === 'Fisioterapia') {
        return 'CREFITO';
    }
    if ($esp === 'Educação Física') {
        return 'CREF';
    }
    if ($esp === 'Enfermagem') {
        return 'COREN';
    }
    return '';
}

function especialidade_validada_public($prof) {
    $especialidade = normalize_especialidade_public($prof['especialidade'] ?? '');
    if ($especialidade === '') {
        return '';
    }

    $conselhoTipo = normalize_conselho_tipo_public($prof['conselho_tipo'] ?? '');
    $conselhoNumero = trim((string) ($prof['conselho_numero'] ?? ''));
    $rqe = trim((string) ($prof['rqe'] ?? ''));

    $required = required_conselho_for_especialidade($especialidade);
    if ($required === '') {
        return '';
    }

    if ($conselhoTipo !== $required) {
        return '';
    }
    if ($conselhoNumero === '') {
        return '';
    }
    if ($required === 'CRM' && $rqe === '') {
        return '';
    }

    return $especialidade;
}

function format_registro_profissional_public($prof) {
    $conselhoTipo = normalize_conselho_tipo_public($prof['conselho_tipo'] ?? '');
    $conselhoNumero = trim((string) ($prof['conselho_numero'] ?? ''));
    if ($conselhoTipo === '' || $conselhoNumero === '') {
        return '';
    }

    if ($conselhoTipo === 'CRM') {
        $rqe = trim((string) ($prof['rqe'] ?? ''));
        if ($rqe !== '') {
            return 'CRM ' . $conselhoNumero . ' • RQE ' . $rqe;
        }
        return 'CRM ' . $conselhoNumero;
    }

    return $conselhoTipo . ' ' . $conselhoNumero;
}

function validate_registro_e_especialidade($especialidade, $conselhoTipo, $conselhoNumero, $rqe) {
    global $especialidades_reconhecidas;

    $espNorm = normalize_especialidade_public($especialidade);
    if (!in_array($espNorm, $especialidades_reconhecidas, true)) {
        return 'Selecione uma especialidade válida.';
    }

    $required = required_conselho_for_especialidade($espNorm);
    $ct = normalize_conselho_tipo_public($conselhoTipo);
    $cn = trim((string) $conselhoNumero);
    $rq = trim((string) $rqe);

    if ($required === '') {
        return 'Especialidade sem conselho configurado.';
    }
    if ($ct !== $required) {
        return 'O conselho informado não corresponde à especialidade selecionada.';
    }
    if ($cn === '') {
        return 'Informe o número do registro profissional.';
    }
    if ($required === 'CRM' && $rq === '') {
        return 'Para médicos, informe o RQE.';
    }

    return '';
}
