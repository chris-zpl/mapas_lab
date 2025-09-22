<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Usuários cadastrados';

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAdmins);

include APP_ROOT . '/public/includes/admin-header.php';
?>
<main class="flex-grow-1 bg-body-tertiary">
    <section class="container-fluid">
        <div class="d-flex p-3 flex-column container-xl my-4 bg-body rounded shadow-sm">
            <p class="text-body text-start fw-semibold no-print border-bottom border-light-subtle pb-2 gap-2"><?php echo $titulo; ?></p>
            <ol class="list-group list-group-numbered max-height-1 overflow-auto">
                <?php
                foreach ($usuariosAutorizados as $usuario) { ?>
                    <li class="d-flex list-group-item">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">
                                <span><?php echo tituloCase(iconv('Windows-1252', 'UTF-8', $username[$usuario]['NOME'])); ?></span>
                            </div>
                            <div class="d-flex flex-row gap-1 fs-small">
                                <span class="text-secondary">Matrícula:</span><span class="fw-bold"><?php echo $usuario; ?></span>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ol>
        </div>
    </section>
</main>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>