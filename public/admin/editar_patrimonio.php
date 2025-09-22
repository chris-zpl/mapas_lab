<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Editar Patrimônio';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Dá um get no id para verificar sua existencia. Caso não exista, redireciona para a página não encontrada.
// Caso exista, inicia as funções para atualização.
if ($id != '') {
    $salaSendoExibida = false;
    $verifica = $cms->getSalas()->get($id);
    $verifica_sala = $cms->getLayoutMapa()->getPerSala($verifica['sala']);
    if ($verifica_sala['mostrar_sala']) {
        $salaSendoExibida = true;
    }
} else {
    redirect('/pagina-nao-encontrada');
}

if (!$verifica || !$salaSendoExibida) {
    redirect('/pagina-nao-encontrada');
} else {
    // Inicia as variáveis vazias
    $mensagem_log = '';
    $mensagens_log = [];
    $infos_novas = [
        'id'                     => $id,
        'modelo_maquina'         => '',
        'maquina'                => '',
        'modelo_monitor'         => '',
        'monitor'                => '',
        'p_rede'                 => '',
        'status_pc'              => '',
        'status_monitor'         => '',
        'reserva_pc'             => '',
        'reserva_monitor'        => '',
        'reserva_modelo_pc'      => '',
        'reserva_modelo_monitor' => '',
        'obs_pc'                 => '',
        'obs_monitor'            => '',
        'modificado'             => '',
        'disco'                  => '',
    ];
    $novoLog = [
        'id'            => '',
        'novoLog'       => '',
    ];
    $errors = [
        'id'                     => '',
        'modelo_maquina'         => '',
        'maquina'                => '',
        'modelo_monitor'         => '',
        'monitor'                => '',
        'p_rede'                 => '',
        'status_pc'              => '',
        'status_monitor'         => '',
        'reserva_pc'             => '',
        'reserva_monitor'        => '',
        'reserva_modelo_pc'      => '',
        'reserva_modelo_monitor' => '',
        'obs_pc'                 => '',
        'obs_monitor'            => '',
        'modificado'             => '',
        'disco'                  => '',
    ];
    // Armazena dentro de um dicionário as informações de valor e texto para o status
    $status_disponivel = [
        'funcionando'   => 'Funcionando',
        'manutencao'    => 'Manutenção',
        'defeito'       => 'Defeito',
        'reserva'       => 'Reserva',
    ];

    // Armazena dentro de um dicionário as informações de valor e texto para o disco
    $disco_disponivel = [
        'nenhum'        => 'Sem Disco',
        'ssd250'        => 'SSD 250GB',
        'ssd480'        => 'SSD 480GB',
        'ssd500'        => 'SSD 500GB',
        'ssd1000'       => 'SSD 1TB',
        'hd250'         => 'HD 250 GB',
        'hd320'         => 'HD 320 GB',
        'hd500'         => 'HD 500GB',
        'hd1000'        => 'HD 1TB',
    ];

    // Se houver o id, dá um get nas informações
    $infos = $cms->getSalas()->get($id);

    // Realiza o Request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verifica se cada campo foi alterado e adiciona a mensagem de log correspondente
        if ($_POST['modelo_maquina'] !== $infos['modelo_maquina']) {
            $mensagens_log[] = 'O modelo da máquina [' . $infos['modelo_maquina'] . '] foi alterado para [' . $_POST['modelo_maquina'] . ']';
        }
        if ($_POST['maquina'] !== $infos['maquina']) {
            $mensagens_log[] = 'A máquina [' . $infos['maquina'] . '] foi alterada para [' . $_POST['maquina'] . ']';
        }
        if ($_POST['modelo_monitor'] !== $infos['modelo_monitor']) {
            $mensagens_log[] = 'O modelo do monitor [' . $infos['modelo_monitor'] . '] foi alterado para [' . $_POST['modelo_monitor'] . ']';
        }
        if ($_POST['monitor'] !== $infos['monitor']) {
            $mensagens_log[] = 'O monitor [' . $infos['monitor'] . '] foi alterado para [' . $_POST['monitor'] . ']';
        }
        if ($_POST['p_rede'] !== $infos['p_rede']) {
            $mensagens_log[] = 'A porta de rede [' . $infos['p_rede'] . '] foi alterada para [' . $_POST['p_rede'] . ']';
        }
        if ($_POST['status_pc'] !== $infos['status_pc']) {
            $mensagens_log[] = 'O status do PC [' . $infos['status_pc'] . '] foi alterado para [' . $_POST['status_pc'] . ']';
        }
        if ($_POST['status_monitor'] !== $infos['status_monitor']) {
            $mensagens_log[] = 'O status do monitor [' . $infos['status_monitor'] . '] foi alterado para [' . $_POST['status_monitor'] . ']';
        }
        if ($_POST['reserva_pc'] !== $infos['reserva_pc']) {
            $mensagens_log[] = 'O PC reserva [' . $infos['reserva_pc'] . '] foi alterada para [' . $_POST['reserva_pc'] . ']';
        }
        if ($_POST['reserva_monitor'] !== $infos['reserva_monitor']) {
            $mensagens_log[] = 'O monitor reserva [' . $infos['reserva_monitor'] . '] foi alterada para [' . $_POST['reserva_monitor'] . ']';
        }
        if ($_POST['reserva_modelo_pc'] !== $infos['reserva_modelo_pc']) {
            $mensagens_log[] = 'O modelo do PC reserva [' . $infos['reserva_modelo_pc'] . '] foi alterada para [' . $_POST['reserva_modelo_pc'] . ']';
        }
        if ($_POST['reserva_modelo_monitor'] !== $infos['reserva_modelo_monitor']) {
            $mensagens_log[] = 'O modelo do monitor reserva [' . $infos['reserva_modelo_monitor'] . '] foi alterada para [' . $_POST['reserva_modelo_monitor'] . ']';
        }
        if ($_POST['obs_pc'] !== $infos['obs_pc']) {
            $mensagens_log[] = 'As observações [' . $infos['obs_pc'] . '], referentes ao PC, foram alterados para [' . $_POST['obs_pc'] . ']';
        }
        if ($_POST['obs_monitor'] !== $infos['obs_monitor']) {
            $mensagens_log[] = 'As observações [' . $infos['obs_monitor'] . '], referentes ao monitor, foram alterados para [' . $_POST['obs_monitor'] . ']';
        }
        if ($_POST['disco'] !== $infos['disco']) {
            $mensagens_log[] = 'O disco [' . $infos['disco'] . '] foi alterado para [' . $_POST['disco'] . ']';
        }
        $infos_novas['modelo_maquina']                    = trim($_POST['modelo_maquina']);
        $infos_novas['maquina']                           = trim($_POST['maquina']);
        $infos_novas['modelo_monitor']                    = trim($_POST['modelo_monitor']);
        $infos_novas['monitor']                           = trim($_POST['monitor']);
        $infos_novas['p_rede']                            = trim(mb_strtoupper($_POST['p_rede'], 'UTF-8'));
        $infos_novas['status_pc']                         = trim($_POST['status_pc']);
        $infos_novas['status_monitor']                    = trim($_POST['status_monitor']);
        $infos_novas['reserva_pc']                        = trim($_POST['reserva_pc']);
        $infos_novas['reserva_monitor']                   = trim($_POST['reserva_monitor']);
        $infos_novas['reserva_modelo_pc']                 = trim($_POST['reserva_modelo_pc']);
        $infos_novas['reserva_modelo_monitor']            = trim($_POST['reserva_modelo_monitor']);
        $infos_novas['obs_pc']                            = trim($_POST['obs_pc']);
        $infos_novas['obs_monitor']                       = trim($_POST['obs_monitor']);
        $infos_novas['disco']                             = trim($_POST['disco']);
        $novoLog['id']                                    = $infos_novas['id'];

        // Se houver mensagens de log, atualiza o campo 'modificado' e registra o log
        if (!empty($mensagens_log)) {
            $infos_novas['modificado'] = date('Y-m-d H:i:s'); // Data e hora atual
            // Concatena todas as mensagens de log em uma única string
            $mensagem_log = implode(" | ", $mensagens_log);
            $novoLog['novoLog'] = "\n" . registrarLog($infos_novas['modificado'], Sessao::get('usuario'), $mensagem_log);
        }

        // Checagem de erros
        $errors['modelo_maquina']           = Validate::is_texto($infos_novas['modelo_maquina'], 1, 45)         ? '' : 'O nº de caractéres permitidos é de 1 até 45.';
        $errors['maquina']                  = Validate::is_texto($infos_novas['maquina'], 1, 10)                ? '' : 'O nº de caractéres permitidos é de 1 até 10.';
        $errors['modelo_monitor']           = Validate::is_texto($infos_novas['modelo_monitor'], 1, 50)         ? '' : 'O nº de caractéres permitidos é de 1 até 50.';
        $errors['monitor']                  = Validate::is_texto($infos_novas['monitor'], 1, 30)                ? '' : 'O nº de caractéres permitidos é de 1 até 30.';
        $errors['p_rede']                   = Validate::is_texto($infos_novas['p_rede'], 1, 10)                 ? '' : 'O nº de caractéres permitidos é de 1 até 10.';
        $errors['reserva_pc']               = Validate::is_texto($infos_novas['reserva_pc'], 0, 10)             ? '' : 'O nº de caractéres permitidos é de 0 até 10.';
        $errors['obs_pc']                   = Validate::is_texto($infos_novas['obs_pc'], 0, 250)                ? '' : 'Limite de 250 caractéres atingido.';
        $errors['reserva_monitor']          = Validate::is_texto($infos_novas['reserva_monitor'], 0, 30)        ? '' : 'O nº de caractéres permitidos é de 0 até 30.';
        $errors['reserva_modelo_pc']        = Validate::is_texto($infos_novas['reserva_modelo_pc'], 0, 50)      ? '' : 'O nº de caractéres permitidos é de 0 até 50.';
        $errors['reserva_modelo_monitor']   = Validate::is_texto($infos_novas['reserva_modelo_monitor'], 0, 50) ? '' : 'O nº de caractéres permitidos é de 0 até 50.';
        $errors['obs_monitor']              = Validate::is_texto($infos_novas['obs_monitor'], 0, 250)           ? '' : 'Limite de 250 caractéres atingido.';
        $errors['status_pc']                = Validate::is_status_valido($infos_novas['status_pc'])                        ? '' : 'O status não é válido.';
        $errors['status_monitor']           = Validate::is_status_valido($infos_novas['status_monitor'])                   ? '' : 'O status não é válido.';
        $errors['disco']                    = Validate::is_disco_valido($infos_novas['disco'])                             ? '' : 'O disco não é válido.';

        $invalido = implode('', $errors);

        // Verifica se há erros. Caso haja, informa uma mensagem de erro
        if ($invalido) {
            $msg_erro = 'Por favor, corrija os erros abaixo:';
        } else {
            $arguments = $infos_novas;

            // Se o array de logs não estiver vazio, realiza o update
            if (!empty($mensagens_log)) {
                $update = $cms->getSalas()->update($arguments);
                $update_log = $cms->getSalas()->updateLog($novoLog);
            } else {
                unset($arguments['id']);
            }

            // Se o update retornar true, informa ao usuário. Caso contrário, informa que não houveram atualizações
            if ($update) {
                Sessao::set('msg_success', 'Patrimônio atualizado com sucesso.');
                unset($update);
                redirect('/salas?sala=' . $infos['sala']);
                exit;
            } else {
                Sessao::set('msg_warning', 'Nenhuma alteração foi realizada no patrimônio.');
                redirect('/salas?sala=' . $infos['sala']);
                exit;
            }
        }
    }
}

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

include APP_ROOT . '/public/includes/admin-header.php';
?>
<main class="flex-grow-1 bg-body-tertiary">
    <div class="container">
        <div class="row">
            <p class="text-black text-center fw-bold mt-4"><?php echo $titulo; ?></p>
            <section class="col-md-3 order-md-last">
                <div class="p-3 mb-4 bg-body rounded shadow-sm text-center">
                    <p class="m-0 fst-italic text-body-secondary">Insira as novas informações do patrimônio (nº <?php echo html_escape($infos['num']); ?>), presente na sala <span class="fw-bold"><?php echo formatarSala(html_escape($infos['sala'])); ?></span>.</p>
                </div>
            </section>
            <section class="col-md-9">
                <div class="d-flex p-3 flex-column align-items-center container-xl editar mb-4 bg-body rounded shadow-sm">
                    <?php
                    // Verifica se há uma mensagem de erro 
                    if (isset($msg_erro)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <p><b><?php echo $msg_erro; ?></b></p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>
                    <form action="" method="post" class="container-md form-editar gap-3" enctype="application/x-www-form-urlencoded">
                        <div class="flex-group gap-4">
                            <!-- Máquina -->
                            <div class="d-flex flex-column form-group bg-body border rounded-3">
                                <span class="mb-3 fw-bold text-center">Máquina</span>
                                <div class="d-flex flex-column gap-1">
                                    <!-- Modelo da Máquina -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="modelo_maquina" style="width: 50px;">Modelo</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="modelo_maquina" id="modelo_maquina" class="<?php echo ($errors['modelo_maquina']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo html_escape($infos['modelo_maquina']); ?>" placeholder="Ex: 9020" required>
                                            <?php if ($errors['modelo_maquina']) { ?>
                                                <span class="d-flex error mb-1 justify-content-center"><?php echo $errors['modelo_maquina']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- Patrimônio da Máquina -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="maquina" style="width: 50px;">Patrim.</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="maquina" id="maquina" class="<?php echo ($errors['maquina']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo html_escape($infos['maquina']); ?>" placeholder="Ex: 0000000" required>
                                            <?php if ($errors['maquina']) { ?>
                                                <span class="d-flex error mb-1 justify-content-center"><?php echo $errors['maquina']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- Status da Máquina -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="status_pc" style="width: 50px;">Status</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <select class="<?php echo ($errors['status_pc']) ? 'border border-danger' : ''; ?> form-select ellipsis" name="status_pc" id="status_pc" aria-label="Select do status">
                                                <?php
                                                foreach ($status_disponivel as $value => $text) { ?>
                                                    <option value="<?php echo $value; ?>" <?php echo ($infos['status_pc'] === $value) ? 'selected' : '' ?>><?php echo $text; ?></option>
                                                <?php }
                                                ?>
                                            </select>
                                            <?php if ($errors['status_pc']) { ?>
                                                <span class="d-flex error mb-1 justify-content-center"><?php echo $errors['status_pc']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- Opções extras -->
                                    <div id="forms_extra_pc">
                                        <!-- Modelo sustituto da máquina -->
                                        <div>
                                            <label for="reserva_modelo_pc_id">Modelo substituto</label>
                                            <input type="text" name="reserva_modelo_pc" id="reserva_modelo_pc_id" class="mb-1 <?php echo ($errors['reserva_modelo_pc']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo ($infos['reserva_modelo_pc']) ? html_escape($infos['reserva_modelo_pc']) : ''; ?>" placeholder="Não informar se for o mesmo modelo.">
                                            <?php if ($errors['reserva_modelo_pc']) { ?>
                                                <span class="error mb-1"><?php echo $errors['reserva_modelo_pc']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <!-- Patrimônio sustituto da máquina -->
                                        <div>
                                            <label for="reserva_pc_id">Patrimônio substituto</label>
                                            <input type="text" name="reserva_pc" id="reserva_pc_id" class="mb-1 <?php echo ($errors['reserva_pc']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo ($infos['reserva_pc']) ? html_escape($infos['reserva_pc']) : ''; ?>" placeholder="Não informar se for o mesmo patrimônio.">
                                            <?php if ($errors['reserva_pc']) { ?>
                                                <span class="error mb-1"><?php echo $errors['reserva_pc']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <!-- Observações da máquina -->
                                        <div>
                                            <label for="obs_pc_id">Observações</label>
                                            <textarea name="obs_pc" id="obs_pc_id" class="<?php echo ($errors['obs_pc']) ? 'border border-danger' : ''; ?> form-control" rows="4" placeholder="Faça uma breve descrição, caso haja observações."><?php echo ($infos['obs_pc']) ? html_escape($infos['obs_pc']) : ''; ?></textarea>
                                            <?php if ($errors['obs_pc']) { ?>
                                                <span class="error mb-1"><?php echo $errors['obs_pc']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Monitor -->
                            <div class="d-flex flex-column form-group bg-body border rounded-3">
                                <span class="mb-3 fw-bold text-center">Monitor</span>
                                <div class="d-flex flex-column gap-1">
                                    <!-- Modelo do Monitor -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="modelo_monitor" style="width: 50px;">Modelo</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="modelo_monitor" id="modelo_monitor" class="<?php echo ($errors['modelo_monitor']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo html_escape($infos['modelo_monitor']); ?>" placeholder="Ex: 9020" required>
                                            <?php if ($errors['modelo_monitor']) { ?>
                                                <span class="d-flex error mb-1 justify-content-center"><?php echo $errors['modelo_monitor']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- Patrimônio do Monitor -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="monitor" style="width: 50px;">Patrim.</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="monitor" id="monitor" class="<?php echo ($errors['monitor']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo html_escape($infos['monitor']); ?>" placeholder="Ex: 0000000" required>
                                            <?php if ($errors['monitor']) { ?>
                                                <span class="d-flex error mb-1 justify-content-center"><?php echo $errors['monitor']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- Status do Monitor -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="status_monitor" style="width: 50px;">Status</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <select class="<?php echo ($errors['status_monitor']) ? 'border border-danger' : ''; ?> form-select ellipsis" name="status_monitor" id="status_monitor" aria-label="Select do status">
                                                <?php
                                                foreach ($status_disponivel as $value => $text) { ?>
                                                    <option value="<?php echo $value; ?>" <?php echo ($infos['status_monitor'] === $value) ? 'selected' : '' ?>><?php echo $text; ?></option>
                                                <?php }
                                                ?>
                                            </select>
                                            <?php if ($errors['status_monitor']) { ?>
                                                <span class="d-flex error mb-1 justify-content-center"><?php echo $errors['status_monitor']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <!-- Opções extras -->
                                    <div id="forms_extra_monitor">
                                        <!-- Modelo substituto para o monitor -->
                                        <div>
                                            <label for="reserva_modelo_monitor_id">Modelo substituto</label>
                                            <input type="text" name="reserva_modelo_monitor" id="reserva_modelo_monitor_id" class="mb-1 <?php echo ($errors['reserva_modelo_monitor']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo ($infos['reserva_modelo_monitor']) ? html_escape($infos['reserva_modelo_monitor']) : ''; ?>" placeholder="Não informar se for o mesmo modelo.">
                                            <?php if ($errors['reserva_modelo_monitor']) { ?>
                                                <span class="error"><?php echo $errors['reserva_modelo_monitor']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <!-- Patrimônio substituto para o monitor -->
                                        <div>
                                            <label for="reserva_monitor_id">Patrimônio substituto</label>
                                            <input type="text" name="reserva_monitor" id="reserva_monitor_id" class="mb-1 <?php echo ($errors['reserva_monitor']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo ($infos['reserva_monitor']) ? html_escape($infos['reserva_monitor']) : ''; ?>" placeholder="Não informar se for o mesmo patrimônio.">
                                            <?php if ($errors['reserva_monitor']) { ?>
                                                <span class="error"><?php echo $errors['reserva_monitor']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <!-- Observações para o monitor -->
                                        <div>
                                            <label for="obs_monitor_id">Observações</label>
                                            <textarea name="obs_monitor" id="obs_monitor_id" class="<?php echo ($errors['obs_monitor']) ? 'border border-danger' : ''; ?> form-control" rows="4" placeholder="Faça uma breve descrição, caso haja observações."><?php echo ($infos['obs_monitor']) ? html_escape($infos['obs_monitor']) : ''; ?></textarea>
                                            <?php if ($errors['obs_monitor']) { ?>
                                                <span class="error"><?php echo $errors['obs_monitor']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="flex-group gap-4">
                            <!-- P. Rede -->
                            <div class="form-group bg-body border rounded-3">
                                <label for="p_rede" class="mb-3 text-center w-100">P. de Rede</label>
                                <div class="d-flex flex-column">
                                    <input type="text" name="p_rede" id="p_rede" class="<?php echo ($errors['p_rede']) ? 'border border-danger' : ''; ?> form-control" value="<?php echo html_escape($infos['p_rede']); ?>" oninput="converterParaMaisculas(this)" placeholder="Ex: 2B0000A" required>
                                    <?php if ($errors['p_rede']) { ?>
                                        <span class="error"><?php echo $errors['p_rede']; ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- Disco -->
                            <div class="form-group bg-body border rounded-3">
                                <label for="disco" class="mb-3 text-center w-100">Disco</label>
                                <div class="d-flex flex-column flex-grow-1">
                                    <select class="<?php echo ($errors['disco']) ? 'border border-danger' : ''; ?> form-select ellipsis" name="disco" id="disco" aria-label="Select do status">
                                        <?php
                                        foreach ($disco_disponivel as $value => $text) { ?>
                                            <option value="<?php echo $value; ?>" <?php echo ($infos['disco'] === $value) ? 'selected' : '' ?>><?php echo $text; ?></option>
                                        <?php }
                                        ?>
                                    </select>
                                    <?php if ($errors['disco']) { ?>
                                        <span class="error"><?php echo $errors['disco']; ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-center gap-3">
                            <a class="btn btn-secondary" href="<?php echo DOC_ROOT . '/salas?sala=' . $infos['sala']; ?>" role="button">Cancelar</a>
                            <input type="submit" class="btn btn-primary" value="Atualizar">
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</main>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        toggleInputs();
    });
</script>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>