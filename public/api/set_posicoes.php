<?php
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

/* Verifica se existe um POST com a sala. Caso não haja, redireciona */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect("/painel");
    exit;
} else {
    $mensagem_log = '';
    $mensagens_log = [];
    $info = [
        'linhas' => '',
        'colunas' => '',
        'posicoes' => '',
        'sala' => '',
    ];
    $novoLog = [
        'id'            => '',
        'novoLog'       => '',
    ];
    $response = ['status' => false]; // Valor padrão

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $exists = $cms->getLayoutMapa()->getPerSala($_POST['sala']);

        $mensagens_log[] = 'As posições foram alteradas.';

        $info['linhas'] = trim($_POST['linhas']);
        $info['colunas'] = trim($_POST['colunas']);
        $info['sala'] = trim($_POST['sala']);
        $info['posicoes'] = trim($_POST['posicoes']);
        $novoLog['id'] = $exists['id'];

        // Se houver mensagens de log, atualiza o campo 'modificado' e registra o log
        if (!empty($mensagens_log)) {
            // Concatena todas as mensagens de log em uma única string
            $mensagem_log = implode(" | ", $mensagens_log);
            $novoLog['novoLog'] = "\n" . registrarLog(date('Y-m-d H:i:s'), Sessao::get('usuario'), $mensagem_log);
        }

        try {
            if ($exists) {
                $result = $cms->getLayoutMapa()->updatePosicoes($info);
                $update_log = $cms->getLayoutMapa()->updateLog($novoLog);
            } /* else {
                $result = $cms->getLayoutMapa()->insert($info);
            } */
            if ($result) {
                $response['status'] = 'ok';
            } else {
                $response['message'] = 'Falha ao salvar os dados.';
            }
        } catch (Exception $e) {
            $response['message'] = 'Erro interno: ' . $e->getMessage();
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
