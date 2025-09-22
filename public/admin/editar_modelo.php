<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Editar Modelo';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Dá um get no id para verificar sua existencia. Caso não exista, redireciona para a página não encontrada.
// Caso exista, inicia as funções para atualização.
if ($id != '') {
    $salaSendoExibida = false;
    $verifica = $cms->getModelos()->get($id);
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
        'id'            => $id,
        'titulo'        => '',
        'descricao'     => '',
        'mostrar'       => '',
        'modificado'    => '',
    ];
    $novoLog = [
        'id'        => '',
        'novoLog'   => '',
    ];
    $errors = [
        'id'                => '',
        'modelo_titulo'     => '',
        'descricao_modelo'  => '',
    ];

    // Se houver o id, dá um get nas informações
    $infos = $cms->getModelos()->get($id);

    // Realiza o Request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Verifica se cada campo foi alterado e adiciona a mensagem de log correspondente
        if ($_POST['modelo_titulo'] !== $infos['titulo']) {
            $mensagens_log[] = 'O titulo do modelo [' . $infos['titulo'] . '] foi alterado para [' . $_POST['modelo_titulo'] . '].';
        }
        if ($_POST['descricao_modelo'] !== $infos['descricao']) {
            $mensagens_log[] = 'A descrição [' . $infos['descricao'] . '], referente ao modelo, foi alterado para [' . $_POST['descricao_modelo'] . '].';
        }

        $infos_novas['titulo']     = trim($_POST['modelo_titulo']);
        $infos_novas['descricao']  = trim($_POST['descricao_modelo']);
        $infos_novas['mostrar']    = true;
        $infos_novas['modificado'] = date('Y-m-d H:i:s');
        $novoLog['id']       = $infos_novas['id'];

        // Se houver mensagens de log, atualiza o campo 'modificado' e registra o log
        if (!empty($mensagens_log)) {
            // Concatena todas as mensagens de log em uma única string
            $mensagem_log = implode(" | ", $mensagens_log);
            $novoLog['novoLog'] = "\n" . registrarLog($infos_novas['modificado'], Sessao::get('usuario'), $mensagem_log);
        }

        // Checagem de erros
        $errors['modelo_titulo']    = Validate::is_texto($infos_novas['titulo'], 1, 100)      ? '' : 'O nº de caractéres permitidos é de 1 até 100.';
        $errors['descricao_modelo'] = Validate::is_texto($infos_novas['descricao'], 1, 250)   ? '' : 'O nº de caractéres permitidos é de 1 até 250.';

        $invalido = implode('', $errors);

        // Verifica se há erros. Caso haja, informa uma mensagem de erro
        if ($invalido) {
            $msg_erro = 'Por favor, corrija os erros abaixo:';
        } else {
            $arguments = $infos_novas;

            // Se o array de logs não estiver vazio, realiza o update
            if (!empty($mensagens_log)) {
                $update     = $cms->getModelos()->update($arguments);
                $update_log = $cms->getModelos()->updateLog($novoLog);
            } else {
                unset($arguments['id']);
            }

            // Se o update retornar true, informa ao usuário. Caso contrário, informa que não houveram atualizações
            if ($update) {
                Sessao::set('msg_success', 'Modelo atualizado com sucesso.');
                unset($update);
                redirect('/salas?sala=' . $infos['sala']);
                exit;
            } else {
                Sessao::set('msg_warning', 'Nenhuma alteração foi realizada no modelo.');
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
                    <p class="m-0 fst-italic text-body-secondary">Insira as novas informações do modelo, presente na sala <span class="fw-bold"><?php echo formatarSala(html_escape($infos['sala'])); ?></span>.</p>
                </div>
            </section>
            <section class="col-md-9">
                <div class="d-flex flex-column align-items-center container-xl modelo p-3 mb-4 bg-body rounded shadow-sm">
                    <?php
                    // Verifica se há uma mensagem de erro 
                    if (isset($msg_erro)) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <p><b><?php echo $msg_erro; ?></b></p>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>
                    <form action="" method="post" class="container-md form-modelo" enctype="application/x-www-form-urlencoded">
                        <div class="d-flex flex-column flex-group">
                            <div class="form-group mb-3 bg-body border rounded-3">
                                <label for="modelo_titulo">Modelo:</label>
                                <div class="d-flex flex-column">
                                    <input type="text" name="modelo_titulo" id="modelo_titulo" class="<?php echo ($errors['modelo_titulo']) ? 'border border-danger' : ''; ?> form-control" value="<?php echo html_escape($infos['titulo']); ?>" placeholder="Ex: 9020" required>
                                    <?php if ($errors['modelo_titulo']) { ?>
                                        <span class="error mb-1"><?php echo $errors['modelo_titulo'] ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group mb-3 bg-body border rounded-3">
                                <div class="d-flex flex-column">
                                    <label for="descricao_modelo">Descrição:</label>
                                    <textarea name="descricao_modelo" id="descricao_modelo" class="<?php echo ($errors['descricao_modelo']) ? 'border border-danger' : ''; ?> form-control" rows="6" placeholder="Informe neste campo a descrição do modelo." required><?php echo ($infos['descricao']) ? html_escape($infos['descricao']) : ''; ?></textarea>
                                    <?php if ($errors['descricao_modelo']) { ?>
                                        <span class="error mb-1"><?php echo $errors['descricao_modelo']; ?></span>
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
</main>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>