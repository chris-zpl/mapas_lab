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

// Supondo que você já tenha linksMenu ou algo semelhante
$todas_salas = $cms->getLayoutMapa()->get();
$predios_salas = estruturarSalas($todas_salas); // Receberá dados do linksMenu($todas_salas)

// Obtém o prédio selecionado
$predioSelecionado = filter_input(INPUT_GET, 'predio', FILTER_SANITIZE_ENCODED);

if ($predioSelecionado) {
    // Filtra as salas para o prédio selecionado
    $salas = [];
    foreach ($predios_salas as $predio) {
        if ($predio['predio'] == $predioSelecionado) {
            foreach ($predio['blocos'] as $bloco) {
                foreach ($bloco['infos'] as $info) {
                    if (!empty($info['mostrar_sala'])) {
                        $salas[] = [
                            'link' =>  html_escape($info['link']),
                            'sala' => html_escape($info['sala']),
                            'bloco' => html_escape($bloco['bloco'])
                        ];
                    }
                }
            }
        }
    }

    // Retorna as salas em formato JSON
    header('Content-Type: application/json');
    echo json_encode($salas);
    exit;
} else {
    // Se não selecionar o prédio
    header('Content-Type: application/json');
    echo json_encode(['erro' => 'Prédio não selecionado']);
    exit;
}
