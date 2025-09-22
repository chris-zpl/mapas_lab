<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Cadastrar Modelo';

// Dá um get nas salas em todas as salas do banco
$salas = $cms->getLayoutMapa()->get();

// Armazena dentro de um dicionário as informações de valor e texto para as salas
foreach ($salas as $sala) {
    if ($sala['mostrar_sala']) {
        $partes = explode("-", $sala['sala']);
        $label = isset($partes[3]) ? $partes[0] . '/' . mb_strtoupper($partes[1], 'UTF-8') . '/' . $partes[2] . '.' . $partes[3] : $partes[0] . '/' . mb_strtoupper($partes[1], 'UTF-8') . '/' . $partes[2];
        $salas_disponiveis[$sala['sala']] = $label;
    }
}

// Busca o get da sala e realiza um select para pegar as informações com base na sala
$sala_get = isset($_GET['sala']) ? html_escape($_GET['sala']) : '';
$modelos = $cms->getModelos()->getPerSalaModelo($sala_get);
$sala_valida = Validate::is_sala_valida($sala_get, $salas_disponiveis);

// Verifica se existe uma sala, caso contrário retorna para página não encontrada
if (!$sala_valida) {
    redirect('/pagina-nao-encontrada');
} else {
    $atualizar = false;
    $id_para_atualizar = null;

    // Realiza um foreach nas colunas modelo e obs para verificar se são diferentes de um "-"
    foreach ($modelos as $row) {
        if ($row['mostrar'] === false) {
            $atualizar = true;
            $id_para_atualizar = $row['id'];
            break;
        }
    }
    // Inicializa as variáveis vazias
    $mensagem_log = '';
    $mensagens_log = [];
    $infos = [
        'id'         => $id_para_atualizar,
        'titulo'     => '',
        'descricao'  => '',
        'mostrar'    => false,
        'modificado' => '',
        'log'        => '',
    ];
    $errors = [
        'modelo_titulo'     => '',
        'descricao_modelo'  => '',
    ];
    $novoLog = [
        'id'        => '',
        'novoLog'   => '',
    ];

    // Realiza o Request se não existir um id com "-" na respectiva sala
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $infos['titulo']     = trim($_POST['modelo_titulo']);
        $infos['descricao']  = trim($_POST['descricao_modelo']);
        $infos['mostrar']    = true;
        $infos['sala']       = $sala_get;
        $infos['modificado'] = date('Y-m-d H:i:s');
        $novoLog['id']       = $infos['id'];

        // Se o atualizar for false, realiza a inserção do log de cadastrado no sistema
        if (!$atualizar) {
            $infos['log'] = registrarLog($infos['modificado'], Sessao::get('usuario'), 'Modelo cadastrado no sistema');
        } else {
            // Se houver mensagens de log, atualiza e registra o log
            $novoLog['novoLog']  = "\n" . registrarLog($infos['modificado'], Sessao::get('usuario'), 'Modelo recadastrado no sistema');
        }

        // Checagem de erros
        $errors['modelo_titulo']     = Validate::is_texto($infos['titulo'], 1, 100)     ? '' : 'O nº de caractéres permitidos é de 1 até 100.';
        $errors['descricao_modelo']  = Validate::is_texto($infos['descricao'], 1, 250)  ? '' : 'O nº de caractéres permitidos é de 1 até 250.';

        $invalido = implode('', $errors);

        // Verifica se há erros. Caso haja, informa uma mensagem de erro
        if ($invalido) {
            $msg_erro = 'Por favor, corrija os erros abaixo:';
        } else {
            $arguments = $infos;

            // Decide entre update ou insert
            if ($atualizar && $id_para_atualizar !== null) {
                unset($arguments['log']);
                $update = $cms->getModelos()->update($arguments);
                $update_log = $cms->getModelos()->updateLog($novoLog);

                unset($update);
            } else {
                unset($arguments['id']);
                $cadastro = $cms->getModelos()->insert($arguments);

                unset($cadastro);
            }
            Sessao::set('msg_success', 'Modelo cadastrado com sucesso.');
            redirect('/salas?sala=' . $arguments['sala']);
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
            <section class="col-md-3 order-md-last">
                <div class="p-3 mb-4 bg-body rounded shadow-sm text-center">
                    <p class="m-0 fst-italic text-body-secondary">Insira as informações para cadastrar um modelo de computador, presente na sala <span class="fw-bold"><?php echo formatarSala(html_escape($sala_get)); ?></span>.</p>
                </div>
            </section>
            <section class="col-md-9">
                <div class="d-flex p-3 flex-column align-items-center container-xl mb-4 bg-body rounded shadow-sm modelo">
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
                                        <span class="error mb-1"><?php echo $errors['modelo_titulo']; ?></span>
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
                        <div class="d-flex flex-row justify-content-center gap-2">
                            <a class="btn btn-secondary" href="<?php echo DOC_ROOT . '/salas?sala=' . $sala_get; ?>" role="button">Cancelar</a>
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