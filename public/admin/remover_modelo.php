<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Remover Modelo';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$sala = isset($_GET['sala']) ? html_escape($_GET['sala']) : '';

// Dá um get no id para verificar sua existencia. Caso não exista, redireciona para a página não encontrada.
// Caso exista, inicia as funções para atualização.
if ($id != '') {
    $verifica = $cms->getModelos()->get($id);
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
    // Armazena dentro de um dicionário as informações de valor e texto para as salas
    $salas_disponiveis = [
        '30-f-201-01' => '30/F/201.01',
        '30-f-201-02' => '30/F/201.02',
        '30-f-211-01' => '30/F/211.01',
        '30-f-211-02' => '30/F/211.02',
        '30-f-211-03' => '30/F/211.03',
        '30-f-212-01' => '30/F/212.01',
        '30-f-212-02' => '30/F/212.02',
        '30-f-212-03' => '30/F/212.03',
        '30-f-212-04' => '30/F/212.04',
        '30-f-212-05' => '30/F/212.05',
        '30-a-211'    => '30/A/211',
        '30-a-212'    => '30/A/212',
        '30-a-213'    => '30/A/213',
        '30-a-214'    => '30/A/214',
        '30-a-215'    => '30/A/215',
        '12-a-303'    => '12/A/303',
        '12-a-305'    => '12/A/305',
    ];

    // Se houver o id, dá um get nas informações
    $infos = $cms->getModelos()->get($id);

    // Realiza o Request
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $infos_novas['mostrar']   = 0; // 0 == false
        $infos_novas['modificado'] = date('Y-m-d H:i:s');
        $novoLog['id']      = $infos_novas['id'];

        $novoLog['novoLog'] = "\n" . registrarLog($infos_novas['modificado'], Sessao::get('usuario'), 'Removido da visualização');

        $arguments = $infos_novas;

        $exclusao = $cms->getModelos()->update($arguments);
        $update_log = $cms->getModelos()->updateLog($novoLog);

        // Se o exclusao retornar true, informa ao usuário
        if ($exclusao) {
            Sessao::set('msg_success', 'Modelo excluído com sucesso.');
            unset($cadastro);
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
                <div class="d-flex p-3 flex-column align-items-center container-xl mb-4 bg-body rounded shadow-sm modelo">
                    <p class="text-center"><b>Deseja remover o modelo "<?php echo html_escape($infos['titulo']); ?>", presente na sala <?php echo formatarSala(html_escape($infos['sala'])); ?> ?</b></p>
                    <form action="" method="post" class="container-md form-modelo" enctype="application/x-www-form-urlencoded">
                        <div class="d-flex flex-column flex-group">
                            <div class="form-group mb-3 bg-body border rounded-3">
                                <div class="d-flex flex-column">
                                    <label for="descricao_modelo">Descrição:</label>
                                    <textarea name="descricao_modelo" id="descricao_modelo" class="form-control" rows="6" disabled><?php echo ($infos['descricao']) ? html_escape($infos['descricao']) : ''; ?></textarea>
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