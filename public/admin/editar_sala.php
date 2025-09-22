<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Editar Sala';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Dá um get no id para verificar sua existencia. Caso não exista, redireciona para a página não encontrada.
// Caso exista, inicia as funções para atualização.
if ($id != '') {
    $verifica = $cms->getLayoutMapa()->getPorId($id);
} else {
    redirect('/pagina-nao-encontrada');
}

if (!$verifica) {
    redirect('/pagina-nao-encontrada');
} else {
    // Inicia as variáveis vazias
    $mensagem_log = '';
    $mensagens_log = [];
    $infos = [
        'id'                => $id,
        'predio'            => '',
        'bloco'             => '',
        'n_sala'            => '',
        'sala'              => '',
        'titulo'            => '',
        'qtde_gerada'       => '',
        'mostrar_softwares' => false,
        'mostrar_sala'      => false,
    ];
    $novoLog = [
        'id'            => '',
        'novoLog'       => '',
    ];
    $errors = [
        'id'                     => '',
        'predio' => '',
        'bloco' => '',
        'n_sala' => '',
        'sala'              => '',
        'titulo'            => '',
        'qtde_gerada'       => '',
        'mostrar_softwares' => '',
        'mostrar_sala'      => '',
    ];

    // Se houver o id, dá um get nas informações
    $dados = $cms->getLayoutMapa()->getPorId($id);
    $infos_banco = $dados; // <- aqui!
    $infos = array_merge($infos, $dados);

    if (!empty($infos['sala'])) {
        $partes = explode('-', $infos['sala']);
        $infos['predio'] = isset($partes[0]) ? trim($partes[0]) : '';
        $infos['bloco'] = isset($partes[1]) ? trim($partes[1]) : '';
        if (isset($partes[3])) {
            // Se houver 4 partes, junta a terceira e a quarta
            $infos['n_sala'] = trim($partes[2]) . '-' . trim($partes[3]);
        } elseif (isset($partes[2])) {
            // Caso comum com 3 partes
            $infos['n_sala'] = trim($partes[2]);
        } else {
            $infos['n_sala'] = '';
        }
    }

    // Realiza o Request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verifica se cada campo foi alterado e adiciona a mensagem de log correspondente
        if ($_POST['predio'] !== $infos['predio']) {
            $mensagens_log[] = 'O prédio [' . $infos['predio'] . '] foi alterado para [' . $_POST['predio'] . '].';
        }
        if (strtolower($_POST['bloco']) !== strtolower($infos['bloco'])) {
            $mensagens_log[] = 'O bloco [' . mb_strtoupper($infos['bloco'], 'UTF-8') . '] foi alterado para [' . mb_strtoupper($_POST['bloco'], 'UTF-8') . '].';
        }
        if ($_POST['n_sala'] !== $infos['n_sala']) {
            $mensagens_log[] = 'O nº/nome da sala [' . $infos['n_sala'] . '] foi alterado para [' . $_POST['n_sala'] . '].';
        }
        if ((int)$_POST['qtde_gerada'] !== (int)$infos['qtde_gerada']) {
            $mensagens_log[] = 'A quantidade gerada [' . $infos['qtde_gerada'] . '] foi alterada para [' . $_POST['qtde_gerada'] . '].';
        }
        if (isset($_POST['mostrar_sala'])) {
            if ((bool)$_POST['mostrar_sala'] !== (bool)$infos['mostrar_sala']) {
                $mensagens_log[] = 'A visualização da sala foi alterada para [' . ($_POST['mostrar_sala'] ? 'Sim' : 'Não') . '].';
            }
        } else {
            // checkbox desmarcada (não enviada), considere como false
            if ((bool)$infos['mostrar_sala'] !== false) {
                $mensagens_log[] = 'A visualização da sala foi alterada para [Não].';
            }
        }
        if (isset($_POST['mostrar_softwares'])) {
            if ((bool)$_POST['mostrar_softwares'] !== (bool)$infos['mostrar_softwares']) {
                $mensagens_log[] = 'A visualização dos softwares foi alterada para [' . ($_POST['mostrar_softwares'] ? 'Sim' : 'Não') . '].';
            }
        } else {
            // checkbox desmarcada (não enviada), considere como false
            if ((bool)$infos['mostrar_softwares'] !== false) {
                $mensagens_log[] = 'A visualização dos softwares foi alterada para [Não].';
            }
        }

        $infos['predio']            = trim($_POST['predio']);
        $infos['bloco']             = trim($_POST['bloco']);
        $infos['n_sala']            = trim($_POST['n_sala']);

        // Divide para criar o título
        $partes_sala                = explode("-", $infos['n_sala']);
        $infos['sala']              = $infos['predio'] . '-' . strtolower($infos['bloco']) . '-' . $infos['n_sala'];
        $infos['titulo']            = 'Sala ' . (isset($partes_sala[1]) ? trim($partes_sala[0]) . '.' . trim($partes_sala[1]) : trim($partes_sala[0])) . '/' . $infos['bloco'] . ' - Prédio ' . $infos['predio']; //trim($_POST['titulo']);

        // Gera o log para o título
        if ($infos_banco['titulo'] !== $infos['titulo']) {
            $mensagens_log[] = 'O título [' . $infos_banco['titulo'] . '] foi alterado para [' . $infos['titulo'] . '].';
        }

        $infos['qtde_gerada']       = trim($_POST['qtde_gerada']);
        $infos['mostrar_sala']      = isset($_POST['mostrar_sala']) ? true : false;
        $infos['mostrar_softwares'] = isset($_POST['mostrar_softwares']) ? true : false;
        $novoLog['id']              = $infos['id'];

        // Se houver mensagens de log, atualiza o campo 'modificado' e registra o log
        if (!empty($mensagens_log)) {
            // Concatena todas as mensagens de log em uma única string
            $mensagem_log = implode(" | ", $mensagens_log);
            $novoLog['novoLog'] = "\n" . registrarLog(date('Y-m-d H:i:s'), Sessao::get('usuario'), $mensagem_log);
        }

        // Checagem de erros
        $errors['predio']               = Validate::is_texto($infos['predio'], 1, 5) ? '' : 'O nº de caractéres permitidos é de 1 até 5.';
        $errors['bloco']                = Validate::is_texto($infos['bloco'], 1, 10) ? '' : 'O nº de caractéres permitidos é de 1 até 10.';
        $errors['n_sala']               = Validate::is_texto($infos['n_sala'], 1, 10) ? '' : 'O nº de caractéres permitidos é de 1 até 10.';
        $errors['qtde_gerada']          = Validate::is_numero($infos['qtde_gerada'], 1, 50)  ? '' : 'O nº de computadores permitidos é de 1 até 50.';
        $errors['mostrar_softwares']    = is_bool($infos['mostrar_softwares']) ? '' : 'Opção inválida para exibir os softwares';
        $errors['mostrar_sala']         = is_bool($infos['mostrar_sala']) ? '' : 'opção inválida para exibir a sala.';

        $salaExistente = $cms->getLayoutMapa()->getPerSala($infos['sala']);

        // Já existe outra sala com o mesmo nome
        $errors['sala'] = ($salaExistente && $salaExistente['id'] != $infos['id']) ? 'Já existe uma sala cadastrada com este nome.' : '';

        $invalido = implode('', $errors);

        // Verifica se há erros. Caso haja, informa uma mensagem de erro
        if ($invalido) {
            $msg_erro = 'Por favor, corrija os erros abaixo:';
        } else {
            // Escolhe os argumentos
            $arguments = [
                'id'                => $infos['id'],
                'sala'              => $infos['sala'],
                'titulo'            => $infos['titulo'],
                'qtde_gerada'       => (int)$infos['qtde_gerada'],
                'mostrar_softwares' => ((bool)$infos['mostrar_sala']) ? (bool)$infos['mostrar_softwares'] : false,
                'mostrar_sala'      => (bool)$infos['mostrar_sala'],
            ];

            // Faz a verificação se houveram atualizações
            $campos = ['sala', 'titulo', 'qtde_gerada', 'mostrar_softwares', 'mostrar_sala'];
            $booleanos = ['mostrar_softwares', 'mostrar_sala'];
            $alteracao_confirmada = false;

            foreach ($campos as $campo) {
                $v1 = $arguments[$campo];
                $v2 = $infos_banco[$campo];

                if (in_array($campo, $booleanos)) {
                    // Normaliza os dois valores para booleano real
                    $v1 = filter_var($v1, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                    $v2 = filter_var($v2, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                } else {
                    // Normaliza para string os campos normais
                    $v1 = (string) $v1;
                    $v2 = (string) $v2;
                }

                if ($v1 !== $v2) {
                    $alteracao_confirmada = true;
                    break;
                }
            }

            // Se retornar true, informa ao usuário. Caso contrário, informa que não houveram atualizações
            if ($alteracao_confirmada) {
                $update = $cms->getLayoutMapa()->update($arguments);
                $update_log = $cms->getLayoutMapa()->updateLog($novoLog);
                Sessao::set('msg_success', 'Sala atualizada com sucesso.');
                if ($arguments['mostrar_sala']) {
                    redirect('/salas?sala=' . $arguments['sala']);
                } else {
                    redirect('/admin/salas_cadastradas');
                }
                unset($update);
                exit;
            } else {
                Sessao::set('msg_warning', 'Nenhuma alteração foi realizada na sala.');
                if ($arguments['mostrar_sala']) {
                    redirect('/salas?sala=' . $arguments['sala']);
                } else {
                    redirect('/admin/salas_cadastradas');
                }
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
                    <p class="m-0 fst-italic text-body-secondary">Insira as novas informações da sala <span class="fw-bold"><?php echo formatarSala(html_escape($infos_banco['sala'])); ?></span>.</p>
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
                    <?php } ?>
                    <form action="" method="post" class="container-md form-editar gap-3" enctype="application/x-www-form-urlencoded">
                        <div class="flex-group">
                            <div class="d-flex flex-column gap-3 form-group bg-body border <?php echo ($errors['sala']) ? ' border-danger' : ''; ?> rounded-3">
                                <span class="fw-bold text-center">Informações da sala</span>
                                <div class="d-flex flex-column gap-1">
                                    <div class="d-flex flex-row justify-content-center gap-2 mx-auto">
                                        <!-- Prédio -->
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <label for="predio">Prédio</label>
                                            <input type="text" name="predio" id="predio" class="form-control ellipsis <?php echo ($errors['predio']) ? 'border border-danger' : ''; ?>"
                                                value="<?php echo html_escape($infos['predio']); ?>" placeholder="Ex: 30" required>
                                            <?php if ($errors['predio']) { ?>
                                                <span class="d-flex error m-0 justify-content-center"><?php echo $errors['predio']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <span class="d-flex align-items-end fs-4">/</span>
                                        <!-- Bloco -->
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <label for="bloco">Bloco</label>
                                            <input type="text" name="bloco" id="bloco" class="form-control ellipsis <?php echo ($errors['bloco']) ? 'border border-danger' : ''; ?>" value="<?php echo mb_strtoupper(html_escape($infos['bloco']), 'UTF-8'); ?>" oninput="converterParaMaisculas(this)" placeholder="Ex: A" required>
                                            <?php if ($errors['bloco']) { ?>
                                                <span class="d-flex error m-0 justify-content-center"><?php echo $errors['bloco']; ?></span>
                                            <?php } ?>
                                        </div>
                                        <span class="d-flex align-items-end fs-4">/</span>
                                        <!-- Sala -->
                                        <div class="d-flex flex-column align-items-center gap-1">
                                            <label for="n_sala">Sala <span class="fs-small fw-lighter"></span>
                                                <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-html="true" class="link-body-emphasis icon-link icon-link-hover" style="--bs-icon-link-transform: translate3d(0, -.125rem, 0);" data-bs-content='Se houver mais de uma designação na sala, utilize sempre "-" para separar.<br><strong>Ex: 212-01</strong>'><i class="fa-solid fa-circle-info fa-xs bi align-content-center"></i>
                                                </a></label>
                                            <input type="text" name="n_sala" id="n_sala" class="form-control ellipsis <?php echo ($errors['n_sala']) ? 'border border-danger' : ''; ?>" value="<?php echo html_escape($infos['n_sala']); ?>" placeholder="Ex: 211" required>
                                            <?php if ($errors['n_sala']) { ?>
                                                <span class="d-flex error m-0 justify-content-center"><?php echo $errors['n_sala']; ?></span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <?php if ($errors['sala']) { ?>
                                        <span class="d-flex error mt-2 justify-content-center"><?php echo $errors['sala']; ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <div class="flex-group gap-4">
                            <div class="form-group bg-body border rounded-3">
                                <div class="d-flex flex-column mb-1 gap-3">
                                    <label for="qtde_gerada" class="text-center">Quantidade de computadores</label>
                                    <div class="d-flex flex-column align-items-center">
                                        <div class="d-flex flex-column text-center" style="width: 150px;">
                                            <div class="btn-group botao-incremento gap-1" role="group" aria-label="Botõe Incrementais">
                                                <button type="button" class="btn btn-primary" id="buttonDecrement">-</button>
                                                <input type="text" name="qtde_gerada" id="qtde_gerada" class="text-center w-50 <?php echo ($errors['qtde_gerada']) ? 'border border-danger' : ''; ?> form-control" value="<?php echo html_escape($infos['qtde_gerada']); ?>" style="border-radius: 0;">
                                                <button type="button" class="btn btn-primary" id="buttonIncrement">+</button>
                                            </div>
                                            <div class="d-flex flex-row justify-content-center align-items-center gap-1">
                                                <span class="fs-small fw-lighter"></span>
                                                <a tabindex="0" role="button" data-bs-toggle="popover" data-bs-html="true" class="link-body-emphasis icon-link icon-link-hover" style="--bs-icon-link-transform: translate3d(0, -.125rem, 0);" data-bs-content="Selecione a quantidade de computadores que serão apresentados no mapa.<br>Ex: Ao selecionar <b>10</b>, o sistema pegará do primeiro até o décimo, da tabela de patrimônios, e colocará no mapa."><span class="fs-small fw-lighter">Ajuda</span><i class="fa-solid fa-circle-info fa-xs bi align-content-center"></i>
                                                </a>
                                            </div>
                                        </div>
                                        <?php if ($errors['qtde_gerada']) { ?>
                                            <span class="error mb-1"><?php echo $errors['qtde_gerada']; ?></span>
                                        <?php } ?>
                                    </div>

                                </div>
                            </div>
                            <div class="form-group bg-body border rounded-3">
                                <div class="d-flex flex-column gap-3">
                                    <span class="text-center fw-bold">Opções</span>
                                    <div class="d-flex flex-column gap-2 align-self-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="check-sala" name="mostrar_sala" <?php echo $infos['mostrar_sala'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-bold" for="check-sala">Exibir a sala no sistema.</label>
                                        </div>
                                        <?php if ($errors['mostrar_sala']) { ?>
                                            <span class="error mb-1"><?php echo $errors['mostrar_sala']; ?></span>
                                        <?php } ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="check-softwares" name="mostrar_softwares" <?php echo $infos['mostrar_softwares'] ? 'checked' : ''; ?> disabled>
                                            <label class="form-check-label fw-bold" for="check-softwares">Exibir tabela de softwares.</label>
                                        </div>
                                        <?php if ($errors['mostrar_softwares']) { ?>
                                            <span class="error mb-1"><?php echo $errors['mostrar_softwares']; ?></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex flex-row justify-content-center gap-3">
                            <a class="btn btn-secondary" href="<?php echo ($infos_banco['mostrar_sala']) ? '../salas?sala=' . html_escape($infos_banco['sala']) : 'salas_cadastradas' ?>" role="button">Cancelar</a>
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
        qtdeGeradaPcs();
        habilitarBotaoSoftwares();

        function habilitarBotaoSoftwares() {
            const checkButtonSala = document.getElementById('check-sala');
            const checkButtonSoftware = document.getElementById('check-softwares');

            if (checkButtonSala.checked) {
                checkButtonSoftware.disabled = false;
                checkButtonSala.addEventListener('change', function() {
                    checkButtonSoftware.disabled = !this.checked;
                    checkButtonSoftware.checked = false;
                })
            } else {
                checkButtonSoftware.disabled = true;
                checkButtonSala.addEventListener('change', function() {
                    checkButtonSoftware.disabled = !this.checked;
                    checkButtonSoftware.checked = false;
                })
            }
        }

        function qtdeGeradaPcs() {
            const counter = document.querySelector('#qtde_gerada');
            const buttonDecrement = document.querySelector('#buttonDecrement');
            const buttonIncrement = document.querySelector('#buttonIncrement');

            const MIN = 1;
            const MAX = 50;

            let value = parseInt(counter.value) || MIN;
            counter.value = value;

            function updateButtons() {
                buttonDecrement.disabled = value <= MIN;
                buttonIncrement.disabled = value >= MAX;
            }

            buttonIncrement.addEventListener('click', () => {
                if (value < MAX) {
                    value++;
                    counter.value = value;
                    updateButtons();
                }
            });

            buttonDecrement.addEventListener('click', () => {
                if (value > MIN) {
                    value--;
                    counter.value = value;
                    updateButtons();
                }
            });

            // Verifica valor ao sair do input (quando o usuário digita)
            counter.addEventListener('blur', () => {
                let enteredValue = parseInt(counter.value);

                if (isNaN(enteredValue)) {
                    enteredValue = MIN;
                } else if (enteredValue < MIN) {
                    enteredValue = MIN;
                } else if (enteredValue > MAX) {
                    enteredValue = MAX;
                }

                value = enteredValue;
                counter.value = value;
                updateButtons();
            });

            // Atualiza os botões ao iniciar
            updateButtons();
        }
    });
</script>

<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>