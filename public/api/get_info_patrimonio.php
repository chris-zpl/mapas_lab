<?php
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

// Verificar o referer, permitindo apenas requisições do 'trocar_patrimonio.php'
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
if (strpos($referer, 'mover_patrimonio') === false) {
    redirect('/nao-autorizado');
    exit();
}

// Consulta (GET)
//$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id > 0) {
    $info = $cms->getSalas()->get($id);

    if ($info) {
        header('Content-Type: application/json');
        echo json_encode($info);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['erro' => 'Patrimônio não encontrado']);
        exit;
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'ID inválido']);
    exit;
}
