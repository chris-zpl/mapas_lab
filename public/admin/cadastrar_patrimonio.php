<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Cadastrar Patrimônio';

// Dá um get nas salas em todas as salas do banco
$salas = $cms->getLayoutMapa()->get();

$infos = [
    'id'                     => '',
    'num'                    => '',
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
    'sala'                   => '',
    'log'                    => '',
    'mostrar'                => false,
];

$errors = [
    'modelo_maquina' => '',
    'maquina'        => '',
    'modelo_monitor' => '',
    'monitor'        => '',
    'p_rede'         => '',
    'status_pc'      => '',
    'status_monitor' => '',
    'modificado'     => '',
    'disco'          => '',
    'sala'           => '',
];

$novoLog = [
    'id'        => '',
    'novoLog'   => '',
];

// Armazena dentro de um dicionário as informações de valor e texto para as salas
foreach ($salas as $sala) {
    if ($sala['mostrar_sala']) {
        $partes = explode("-", $sala['sala']);
        $label = isset($partes[3]) ? $partes[0] . '/' . mb_strtoupper($partes[1], 'UTF-8') . '/' . $partes[2] . '.' . $partes[3] : $partes[0] . '/' . mb_strtoupper($partes[1], 'UTF-8') . '/' . $partes[2];
        $salas_disponiveis[$sala['sala']] = $label;
    }
}

// Armazena dentro de um dicionário as informações de valor e texto para o disco
$disco_disponivel = [
    'nenhum'  => 'Sem Disco',
    'ssd250'  => 'SSD 250GB',
    'ssd480'  => 'SSD 480GB',
    'ssd500'  => 'SSD 500GB',
    'ssd1000' => 'SSD 1TB',
    'hd250'   => 'HD 250 GB',
    'hd320'   => 'HD 320 GB',
    'hd500'   => 'HD 500GB',
    'hd1000'  => 'HD 1TB',
];

// Realiza o Request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $infos['modelo_maquina']            = trim($_POST['modelo_maquina']);
    $infos['maquina']                   = trim($_POST['maquina']);
    $infos['modelo_monitor']            = trim($_POST['modelo_monitor']);
    $infos['monitor']                   = trim($_POST['monitor']);
    $infos['p_rede']                    = trim(mb_strtoupper($_POST['p_rede'], 'UTF-8'));
    $infos['status_pc']                 = 'funcionando';
    $infos['status_monitor']            = 'funcionando';
    $infos['reserva_pc']                = '';
    $infos['reserva_monitor']           = '';
    $infos['reserva_modelo_pc']         = '';
    $infos['reserva_modelo_monitor']    = '';
    $infos['obs_pc']                    = '';
    $infos['obs_monitor']               = '';
    $infos['disco']                     = trim($_POST['disco']);
    $infos['modificado']                = date('Y-m-d H:i:s'); // Data e hora atual
    $infos['sala']                      = trim($_POST['sala']);
    $infos['mostrar']                   = true;
    $infos['log']                       = registrarLog($infos['modificado'], Sessao::get('usuario'), 'Cadastrado no sistema');

    // Checagem de erros
    $errors['modelo_maquina'] = Validate::is_texto($infos['modelo_maquina'], 1, 45) ? '' : 'O nº de caractéres permitidos é de 1 até 45.';
    $errors['maquina'] = Validate::is_texto($infos['maquina'], 1, 10) ? '' : 'O nº de caractéres permitidos é de 1 até 10.';
    $errors['modelo_monitor'] = Validate::is_texto($infos['modelo_monitor'], 1, 50) ? '' : 'O nº de caractéres permitidos é de 1 até 50.';
    $errors['monitor'] = Validate::is_texto($infos['monitor'], 1, 30) ? '' : 'O nº de caractéres permitidos é de 1 até 30.';
    $errors['p_rede'] = Validate::is_texto($infos['p_rede'], 1, 10) ? '' : 'O nº de caractéres permitidos é de 1 até 10.';
    $errors['disco'] = Validate::is_disco_valido($infos['disco']) ? '' : 'O disco não é válido.';
    $errors['sala'] = Validate::is_sala_valida($infos['sala'], $salas_disponiveis) ? '' : 'Sala inexistente.';

    $invalido = implode('', $errors);

    // Verifica se há erros. Caso haja, informa uma mensagem de erro
    if ($invalido) {
        $msg_erro = 'Por favor, corrija os erros abaixo:';
    } else {
        //Pega a contagem máxima e armazena na variável
        $resultado  = $cms->getSalas()->getValorMax($infos['sala']);
        $max_num    = $resultado['max_num'];

        // Calcula o próximo valor de num
        $next_num  = ($resultado['max_num'] === null) ? '01' : str_pad((int)$resultado['max_num'] + 1, 2, '0', STR_PAD_LEFT);

        // Verifica se existe algum registro oculto (mostrar=false) na mesma sala
        $registro_oculto = $cms->getSalas()->getPrimeiroOculto($infos['sala']);

        if ($registro_oculto) {
            unset($infos['log']);
            $registro_oculto = $registro_oculto[0]; // agora é um array associativo

            // Atualiza o registro existente
            $infos['num']     = $next_num;
            $infos['mostrar'] = true;
            $infos['id'] = $registro_oculto['id'];
            $novoLog['id'] = $infos['id'];
            $novoLog['novoLog']  = "\n" . registrarLog($infos['modificado'], Sessao::get('usuario'), 'Modelo recadastrado no sistema');

            $cms->getSalas()->update($infos);
            $cms->getSalas()->updateLog($novoLog);
            
            //Sessao::set('msg_success', 'Patrimônio recadastrado com sucesso.');
        } else {
            unset($infos['id']);
            // Insere novo registro
            $infos['num']     = $next_num;
            $infos['mostrar'] = true;
            $cms->getSalas()->insert($infos);
        }

        Sessao::set('msg_success', 'Patrimônio cadastrado com sucesso.');
        $_SESSION['sala_selecionada'] = $infos['sala'];
        redirect('/admin/cadastrar_patrimonio');
        exit;
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
                    <p class="m-0 fst-italic text-body-secondary">Os campos com <span class="text-danger">*</span> são de preenchimento obrigatório.</p>
                </div>
            </section>
            <section class="col-md-9">
                <div class="d-flex p-3 flex-column align-items-center container-xl cadastrar mb-4 bg-body rounded shadow-sm">
                    <?php
                    // Verifica se há uma mensagem de erro 
                    if (isset($msg_erro)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <p><b><?php echo $msg_erro; ?></b></p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php }
                    // Verifica se há uma mensagem de sucesso
                    if (Sessao::get('msg_success')) {
                        alertMessage('alert-success', 'msg_success', 'fa-circle-check');
                        unset($_SESSION['msg_success']);
                    } ?>
                    <form action="" method="post" class="container-md form-cadastrar gap-3" enctype="application/x-www-form-urlencoded">
                        <div class="flex-group gap-4">
                            <!-- Máquina -->
                            <div class="d-flex flex-column form-group bg-body border rounded-3">
                                <span class="mb-3 fw-bold text-center"><span class="text-danger me-1">*</span>Máquina</span>
                                <div class="d-flex flex-column gap-1">
                                    <!-- Modelo da Máquina -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="modelo_maquina" style="width: 50px;">Modelo</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="modelo_maquina" id="modelo_maquina" class="<?php echo ($errors['modelo_maquina']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo html_escape($infos['modelo_maquina']); ?>" placeholder="Ex: Dell Optiplex 9020" required>
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
                                </div>
                            </div>
                            <!-- Monitor -->
                            <div class="d-flex flex-column form-group bg-body border rounded-3">
                                <span class="mb-3 fw-bold text-center"><span class="text-danger me-1">*</span>Monitor</span>
                                <div class="d-flex flex-column gap-1">
                                    <!-- Modelo do Monitor -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="modelo_monitor" style="width: 50px;">Modelo</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="modelo_monitor" id="modelo_monitor" class="<?php echo ($errors['modelo_monitor']) ? 'border border-danger' : ''; ?> form-control ellipsis" value="<?php echo html_escape($infos['modelo_monitor']); ?>" placeholder="Ex: Dell E1911C" required>
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
                                </div>
                            </div>
                        </div>
                        <div class="flex-group gap-4">
                            <!-- P. Rede -->
                            <div class="form-group bg-body border rounded-3">
                                <label for="p_rede" class="mb-3 text-center w-100"><span class="text-danger me-1">*</span>P. de Rede</label>
                                <div class="d-flex flex-column">
                                    <input type="text" name="p_rede" id="p_rede" class="<?php echo ($errors['p_rede']) ? 'border border-danger' : ''; ?> form-control" value="<?php echo html_escape($infos['p_rede']); ?>" oninput="converterParaMaisculas(this)" placeholder="Ex: 2B0000A" required>
                                    <?php if ($errors['p_rede']) { ?>
                                        <span class="error"><?php echo $errors['p_rede']; ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <!-- Disco -->
                            <div class="form-group bg-body border rounded-3">
                                <label for="disco" class="mb-3 text-center w-100"><span class="text-danger me-1">*</span>Disco</label>
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
                        <div class="flex-group">
                            <div class="form-group bg-body border rounded-3">
                                <label for="sala" class="mb-3 text-center w-100"><span class="text-danger me-1">*</span>Sala</label>
                                <div class="d-flex flex-column flex-grow-1">
                                    <select class="<?php echo ($errors['sala']) ? 'border border-danger' : ''; ?> form-select ellipsis inputs-style" name="sala" id="sala" aria-label="Select das salas">
                                        <option selected disabled>Selecione uma sala</option>
                                        <?php
                                        foreach ($salas_disponiveis as $key => $value) {
                                        ?>
                                            <option value="<?php echo $key; ?>" <?php echo ($infos['sala'] === $key || (isset($_SESSION['sala_selecionada']) && $_SESSION['sala_selecionada'] === $key)) ? 'selected' : '' ?>><?php echo $value; ?></option>
                                        <?php }
                                        ?>
                                    </select>
                                    <span class="error mb-1"><?php echo $errors['sala'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-center">
                            <input type="submit" class="btn btn-primary" value="Cadastrar">
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</main>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>