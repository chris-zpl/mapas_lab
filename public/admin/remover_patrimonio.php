<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Remover Patrimônio';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$sala = isset($_GET['sala']) ? html_escape($_GET['sala']) : '';

// Dá um get no id para verificar sua existencia. Caso não exista, redireciona para a página não encontrada.
// Caso exista, inicia as funções para atualização.
if ($id != '') {
    $verifica = $cms->getSalas()->get($id);
} else {
    redirect('/pagina-nao-encontrada');
}

if (!$verifica) {
    redirect('/pagina-nao-encontrada');
} else {
    // Inicia as variáveis vazias
    $infos_novas = [
        'id'            => $id,
        'mostrar'       => '',
        'modificado'    => '',
    ];
    $novoLog = [
        'id'        => '',
        'novoLog'   => '',
    ];
    // Armazena dentro de um dicionário as informações de valor e texto para os discos
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

    // Se houver o id, dá um get nas informações
    $infos = $cms->getSalas()->get($id);

    // Realiza o Request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Passo 1: Descobre o último num da sala
        $ultimo = $cms->getSalas()->getValorMax($infos['sala']);
        $novo_num = str_pad(((int)$ultimo['max_num'] + 1), 2, '0', STR_PAD_LEFT);

        // Passo 2: Atualiza o patrimônio removido
        $infos_novas['mostrar']    = 0; // 0 == false
        $infos_novas['modificado'] = date('Y-m-d H:i:s');

        // Define o patrimônio como último
        $arguments = $infos_novas;
        $arguments['num'] = $novo_num;

        $novoLog['id']      = $infos_novas['id'];
        $novoLog['novoLog'] = "\n" . registrarLog($infos_novas['modificado'], Sessao::get('usuario'), 'Removido da visualização');

        $exclusao   = $cms->getSalas()->update($arguments);
        $update_log = $cms->getSalas()->updateLog($novoLog);

        // Passo 3: Reordena os patrimônios ativos da sala
        $ativos = $cms->getSalas()->getAtivosPorSala($infos['sala']); // método novo para pegar só os mostrar=1

        $i = 1;
        foreach ($ativos as $row) {
            $cms->getSalas()->update([
                'id'  => $row['id'],
                'num' => str_pad($i, 2, '0', STR_PAD_LEFT)
            ]);
            $i++;
        }

        // Feedback pro usuário
        if ($exclusao) {
            Sessao::set('msg_success', 'Patrimônio excluído com sucesso.');
            redirect('/salas?sala=' . $infos['sala']);
            exit;
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
            <section class="col-md-12">
                <div class="d-flex p-3 flex-column align-items-center container-xl mb-4 bg-body rounded shadow-sm cadastrar">
                    <p class="text-center"><b>Deseja remover o patrimônio nº <?php echo html_escape($infos['num']); ?>, presente na sala <?php echo formatarSala(html_escape($infos['sala'])); ?> ?</b></p>
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
                                            <input type="text" name="modelo_maquina" id="modelo_maquina" class="form-control ellipsis" value="<?php echo html_escape($infos['modelo_maquina']); ?>" disabled>
                                        </div>
                                    </div>
                                    <!-- Patrimônio da Máquina -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="maquina" style="width: 50px;">Patrim.</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="maquina" id="maquina" class="form-control ellipsis" value="<?php echo html_escape($infos['maquina']); ?>" disabled>
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
                                            <input type="text" name="modelo_monitor" id="modelo_monitor" class="form-control ellipsis" value="<?php echo html_escape($infos['modelo_monitor']); ?>" disabled>
                                        </div>
                                    </div>
                                    <!-- Patrimônio do Monitor -->
                                    <div class="d-flex flex-row gap-2">
                                        <label for="monitor" style="width: 50px;">Patrim.</label>
                                        <div class="d-flex flex-column flex-grow-1">
                                            <input type="text" name="monitor" id="monitor" class="form-control ellipsis" value="<?php echo html_escape($infos['monitor']); ?>" disabled>
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
                                    <input type="text" name="p_rede" id="p_rede" class="<?php echo ($errors['p_rede']) ? 'border border-danger' : ''; ?> form-control" value="<?php echo html_escape($infos['p_rede']); ?>" disabled>
                                </div>
                            </div>
                            <!-- Disco -->
                            <div class="form-group bg-body border rounded-3">
                                <label for="disco" class="mb-3 text-center w-100"><span class="text-danger me-1">*</span>Disco</label>
                                <div class="d-flex flex-column flex-grow-1">
                                    <?php
                                    foreach ($disco_disponivel as $value => $text) {
                                        if ($value === html_escape($infos['disco'])) { ?>
                                            <input
                                                type="text"
                                                name="disco"
                                                id="disco"
                                                class="form-control"
                                                value="<?php echo $text; ?>" disabled>
                                    <?php }
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-center gap-2">
                            <a class="btn btn-secondary" href="<?php echo DOC_ROOT . '/salas?sala=' . $infos['sala']; ?>" role="button">Cancelar</a>
                            <input type="submit" class="btn btn-primary" value="Remover">
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