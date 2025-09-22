<?php

include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if ($id != '') {
    $verifica = $cms->getSalas()->get($id);
} else {
    redirect('/pagina-nao-encontrada');
}

if (!$verifica) {
    redirect('/pagina-nao-encontrada');
} else {

    // Inicia as variáveis vazias
    $mensagens_log_recebido = '';
    $mensagens_log_enviado = '';

    $infos_recebidas = [
        'id' => '',
        'num' => '',
        'modelo_maquina' => '',
        'maquina' => '',
        'modelo_monitor' => '',
        'monitor' => '',
        'p_rede' => '',
        'status_pc' => '',
        'status_monitor' => '',
        'reserva_pc' => '',
        'reserva_monitor' => '',
        'reserva_modelo_pc' => '',
        'reserva_modelo_monitor' => '',
        'obs_pc' => '',
        'obs_monitor' => '',
        'disco' => '',
        'sala' => '',
        'modificado' => '',
    ];
    $infos_enviadas = [
        'id' => '',
        'num' => '',
        'modelo_maquina' => '',
        'maquina' => '',
        'modelo_monitor' => '',
        'monitor' => '',
        'p_rede' => '',
        'status_pc' => '',
        'status_monitor' => '',
        'reserva_pc' => '',
        'reserva_monitor' => '',
        'reserva_modelo_pc' => '',
        'reserva_modelo_monitor' => '',
        'obs_pc' => '',
        'obs_monitor' => '',
        'disco' => '',
        'sala' => '',
        'modificado' => '',
    ];

    $novoLogRecebido = [
        'id'            => '',
        'novoLog'       => '',
    ];
    $novoLogEnviado = [
        'id'            => '',
        'novoLog'       => '',
    ];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $tipo = $_POST['tipo'] ? $_POST['tipo'] : 'patrim_maquina'; // Receba o tipo (enviado pelo JS)

        if ($tipo === 'patrim_monitor') {
            $mensagens_log_recebido = 'Troca de monitor realizada. As informações do antigo monitor se encontram na sala [' . formatarSala($_POST['sala_antiga']) . '] no ID de número [' . $_POST['num_enviado'] . ']';
            $mensagens_log_enviado  = 'Troca de monitor realizada. As informações do antigo monitor se encontram na sala [' . formatarSala($_POST['sala_atual']) . '] no ID de número [' . $_POST['num'] . ']';
        } else {
            $mensagens_log_recebido = 'Troca de máquina realizada. As informações da antiga máquina se encontram na sala [' . formatarSala($_POST['sala_antiga']) . '] no ID de número [' . $_POST['num_enviado'] . ']';
            $mensagens_log_enviado  = 'Troca de máquina realizada. As informações da antiga máquina se encontram na sala [' . formatarSala($_POST['sala_atual']) . '] no ID de número [' . $_POST['num'] . ']';
        }

        // Campos comuns (sempre incluídos)
        $infos_recebidas = [
            'id'         => $_POST['id'],
            'num'        => trim($_POST['num']),
            'p_rede'     => trim(strtoupper($_POST['p_rede'])),
            'sala'       => trim($_POST['sala_atual']),
            'modificado' => date('Y-m-d H:i:s')
        ];

        $infos_enviadas = [
            'id'         => $_POST['id_enviado'],
            'num'        => trim($_POST['num_enviado']),
            'p_rede'     => trim(strtoupper($_POST['p_rede_enviado'])),
            'sala'       => trim($_POST['sala_antiga']),
            'modificado' => date('Y-m-d H:i:s')
        ];

        // Preenchimento condicional
        if ($tipo === 'patrim_maquina') {
            $infos_recebidas += [
                'modelo_maquina'        => trim($_POST['modelo_maquina']),
                'maquina'               => trim($_POST['maquina']),
                'status_pc'             => trim($_POST['status_pc']),
                'reserva_pc'            => trim($_POST['reserva_pc']),
                'reserva_modelo_pc'     => trim($_POST['reserva_modelo_pc']),
                'obs_pc'                => trim($_POST['obs_pc']),
                'disco'                 => trim($_POST['disco']),
            ];
            $infos_enviadas += [
                'modelo_maquina'        => trim($_POST['modelo_maquina_enviado']),
                'maquina'               => trim($_POST['maquina_enviado']),
                'status_pc'             => trim($_POST['status_pc_enviado']),
                'reserva_pc'            => trim($_POST['reserva_pc_enviado']),
                'reserva_modelo_pc'     => trim($_POST['reserva_modelo_pc_enviado']),
                'obs_pc'                => trim($_POST['obs_pc_enviado']),
                'disco'                 => trim($_POST['disco_enviado']),
            ];
        } elseif ($tipo === 'patrim_monitor') {
            $infos_recebidas += [
                'modelo_monitor'            => trim($_POST['modelo_monitor']),
                'monitor'                   => trim($_POST['monitor']),
                'status_monitor'            => trim($_POST['status_monitor']),
                'reserva_monitor'           => trim($_POST['reserva_monitor']),
                'reserva_modelo_monitor'    => trim($_POST['reserva_modelo_monitor']),
                'obs_monitor'               => trim($_POST['obs_monitor']),
            ];
            $infos_enviadas += [
                'modelo_monitor'            => trim($_POST['modelo_monitor_enviado']),
                'monitor'                   => trim($_POST['monitor_enviado']),
                'status_monitor'            => trim($_POST['status_monitor_enviado']),
                'reserva_monitor'           => trim($_POST['reserva_monitor_enviado']),
                'reserva_modelo_monitor'    => trim($_POST['reserva_modelo_monitor_enviado']),
                'obs_monitor'               => trim($_POST['obs_monitor_enviado']),
            ];
        }

        // Logs
        $novoLogRecebido = [
            'id'      => $infos_recebidas['id'],
            'novoLog' => "\n" . registrarLog($infos_recebidas['modificado'], Sessao::get('usuario'), $mensagens_log_recebido)
        ];
        $novoLogEnviado = [
            'id'      => $infos_enviadas['id'],
            'novoLog' => "\n" . registrarLog($infos_enviadas['modificado'], Sessao::get('usuario'), $mensagens_log_enviado)
        ];

        // Atualização no banco
        $update_recebido     = $cms->getSalas()->updateTrocaPatrim($infos_recebidas);
        $update_enviado      = $cms->getSalas()->updateTrocaPatrim($infos_enviadas);
        $update_log_recebido = $cms->getSalas()->updateLog($novoLogRecebido);
        $update_log_enviado  = $cms->getSalas()->updateLog($novoLogEnviado);

        if ($update_recebido) {
            Sessao::set('msg_success', 'Patrimônio movido com sucesso para a sala ' . formatarSala($infos_enviadas['sala']) . '.');
            echo json_encode(['status' => 'ok']);
            exit;
        } else {
            echo json_encode(['status' => 'erro', 'mensagem' => 'Nenhuma alteração foi realizada.']);
            exit;
        }
    }
}
