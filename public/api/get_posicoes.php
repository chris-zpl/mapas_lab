<?php
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
//Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

$sala = filter_input(INPUT_GET, 'sala', FILTER_SANITIZE_ENCODED);

if ($sala) {
    $layout = $cms->getLayoutMapa()->getPerSala($sala);
    if (!$layout) {
        header('Content-Type: application/json');
        echo json_encode(['colunas' => 8, 'linhas' => 6, 'posicoes' => null]);
        exit;
    } else {
        header('Content-Type: application/json');
        echo json_encode(['colunas' => $layout['colunas'], 'linhas' => $layout['linhas'], 'posicoes' => $layout['posicoes']]);
        exit;
    }
} else{
    redirect('/painel');
}
