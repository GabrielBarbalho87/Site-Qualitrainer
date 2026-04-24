<?php
header('Content-Type: application/json; charset=utf-8');

$nome     = trim($_POST['nome']     ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email    = trim($_POST['email']    ?? '');
$curso    = trim($_POST['curso']    ?? '');
$unidade  = trim($_POST['unidade']  ?? '');
$mensagem = trim($_POST['mensagem'] ?? '');

if (!$nome || !$telefone) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Nome e telefone são obrigatórios.']);
    exit;
}

$source = 'Site - Qualitrainer';
if (strpos($unidade, 'Paulista') !== false) {
    $source = 'Site - Paulista';
} elseif (strpos($unidade, 'Goiana') !== false) {
    $source = 'Site - Goiana';
}

$dados = [
    'name'   => $nome,
    'phone'  => $telefone,
    'source' => $source,
];

if ($email) $dados['email'] = $email;

$cursoTagMap = [
    'Informática'      => '160f86f6-cdf8-45c3-8cae-0d5a90476ce4',
    'Designer Gráfico' => 'c080295c-b28b-4ccd-a720-a36094f55610',
    'Criação de Jogos' => '6381eb7a-d7fd-48a4-b62b-9a0a1821bfca',
    'Administração'    => 'e89283ab-653d-4d86-ac8c-b112e2e1a443',
];

$unidadeTagMap = [
    'Paulista' => 'a7b9c401-575d-4a4a-a9e5-18b32908b36f',
    'Goiana'   => '7538ae66-cbab-424c-837b-94f49a2783f3',
];

$tags = [];
if ($unidade && isset($unidadeTagMap[$unidade])) {
    $tags[] = ['id' => $unidadeTagMap[$unidade]];
}
if ($curso && isset($cursoTagMap[$curso])) {
    $tags[] = ['id' => $cursoTagMap[$curso]];
}
if ($tags) {
    $dados['tags'] = $tags;
}

$token = 'dc_eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY5ZWEyYmQxZGU0MzM4MzlhZWI0ZDIyMiIsInRlbmFudElkIjoiYmU3ZThhNmEtOGE5ZS00ODhhLTlkMzUtZjRmYThlMjQ2MjE0IiwibmFtZSI6IkludGVncmHDp8OjbyBjb20gbyBTaXRlIiwicm9sZXMiOlsiYWRtaW4iXSwiaXNBZG1pbiI6dHJ1ZSwiaWF0IjoxNzc2OTU0MzIyLCJleHAiOjE4OTM0NjY3OTl9.Zrvu8WqS2jiYAjUUG7sTTCGhoJEE9PRvJePpVZNd2Ig';

$ch = curl_init('https://api.g1.datacrazy.io/api/v1/leads');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode($dados),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token,
    ],
    CURLOPT_TIMEOUT        => 15,
]);

$resposta = curl_exec($ch);
$status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$erro_curl = curl_error($ch);
curl_close($ch);

if ($status === 200 || $status === 201) {
    echo json_encode(['sucesso' => true]);
} else {
    echo json_encode([
        'sucesso'  => false,
        'mensagem' => 'Não foi possível enviar. Tente novamente.',
        '_debug'   => ['status' => $status, 'resposta' => $resposta, 'curl_erro' => $erro_curl],
    ]);
}
