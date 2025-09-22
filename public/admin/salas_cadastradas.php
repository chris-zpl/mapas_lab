<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Salas Cadastradas';

$salasExistentes = $cms->getLayoutMapa()->get();

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

include APP_ROOT . '/public/includes/admin-header.php';
?>
<main class="flex-grow-1 bg-body-tertiary">
    <section class="container-fluid">
        <?php
        // Verifica se há uma mensagem de atualização, ao realizar o update
        if (Sessao::get('msg_success')) {
            alertMessage('alert-success', 'msg_success', 'fa-circle-check');
            unset($_SESSION['msg_success']);
        }
        if (Sessao::get('msg_warning')) {
            alertMessage('alert-warning', 'msg_warning', 'fa-triangle-exclamation');
            unset($_SESSION['msg_warning']);
        } ?>
        <div class="d-flex p-3 flex-column container-xl my-4 bg-body rounded shadow-sm">
            <p class="text-body text-start fw-semibold no-print border-bottom border-light-subtle pb-2 gap-2"><?php echo $titulo; ?></p>
            <div class="d-flex flex-column p-3 align-items-center rounded-start">
                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Cadastrar uma sala no sistema" href="cadastrar_sala">
                    <i class="fa-solid fa-circle-plus fa-xl" aria-hidden="true"></i>
                </a>
            </div>
            <?php
            if (count($salasExistentes) > 0) { ?>
                <ol class="list-group list-group-numbered max-height-1 overflow-auto">
                    <?php foreach ($salasExistentes as $sala) { ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">
                                    <span><?php echo html_escape($sala['titulo']); ?></span>
                                    <span class="fst-italic text-secondary fs-small">(<?php echo formatarSala(html_escape($sala['sala'])); ?>)</span>
                                </div>
                                <div class="d-flex flex-row gap-2 fs-small">
                                    <div class="d-flex flex-row gap-1">
                                        <span class="text-secondary">Computadores:</span><span class="fw-bold"><?php echo html_escape($sala['qtde_gerada']); ?></span>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="d-flex flex-row gap-1">
                                        <span class="text-secondary">Softwares visíveis:</span><?php echo html_escape($sala['mostrar_softwares']) ? '<span class="fw-bold text-success">Sim</span>' : '<span class="fw-bold text-danger">Não</span>'; ?>
                                    </div>
                                    <div class="vr"></div>
                                    <div class="d-flex flex-row gap-1">
                                        <span class="text-secondary">Sala visível:</span><span class="fw-bold"><?php echo html_escape($sala['mostrar_sala']) ? '<span class="fw-bold text-success">Sim</span>' : '<span class="fw-bold text-danger">Não</span>'; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar informações da sala" href="editar_sala.php?id=<?php echo $sala['id']; ?>"><i class="fa-solid fa-file-pen fa-xl" aria-hidden="true"></i></a>
                            </div>
                        </li>
                    <?php
                    } ?>
                </ol>
            <?php } else { ?>
                <ol class="list-group max-height-1 overflow-auto">
                    <li class="list-group-item d-flex justify-content-center fst-italic">Nenhuma sala encontrada.</li>
                </ol>
            <?php } ?>
        </div>
    </section>
</main>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>