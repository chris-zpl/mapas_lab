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

// Obtém o valor de 'sala' da URL
$sala = filter_input(INPUT_GET, 'sala', FILTER_SANITIZE_ENCODED);

if ($sala) {
    $patrimonios = $cms->getSalas()->getPerSala($sala);
    header('Content-Type: application/json');
    echo json_encode($patrimonios);
    exit;
}
