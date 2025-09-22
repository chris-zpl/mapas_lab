<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Painel de Admin';

// Um array para que seja gerado as salas no painel
$array_salas = [
    ['link' => 'admin/cadastrados', 'icone' => 'fa-users', 'titulo' => 'Usuários cadastrados'],
    ['link' => 'admin/salas_cadastradas', 'icone' => 'fa-solid fa-house-flag', 'titulo' => 'Salas cadastradas'],
    ['link' => 'admin/cadastrar_patrimonio', 'icone' => 'fa-file-circle-plus', 'titulo' => 'Cadastrar patrimônio'],
    ['link' => 'admin/cadastrar_sala', 'icone' => 'fa-house-chimney-medical', 'titulo' => 'Cadastrar sala'],
    ['link' => 'admin/logs_patrimonios', 'icone' => 'fa-clock-rotate-left', 'titulo' => 'Logs dos patrimônios'],
    ['link' => 'admin/logs_modelos', 'icone' => 'fa-clock-rotate-left', 'titulo' => 'Logs dos modelos'],
];

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

// Verifica em um array se o usuário logado pertence aos administradores
$usuarioLinksPermitidos = in_array(Sessao::get('usuario'), $usuariosAdmins);

// Se o usuário não for um administrador, permitir apenas "Cadastrar Patrimônio"
if (!$usuarioLinksPermitidos) {
    $array_salas = array_filter($array_salas, function ($sala) {
        return $sala['link'] === 'admin/cadastrar_patrimonio' || $sala['link'] === 'admin/salas_cadastradas' || $sala['link'] === 'admin/cadastrar_sala';
    });

    // Reindexa o array após o filtro
    $array_salas = array_values($array_salas);
}

include APP_ROOT . '/public/includes/admin-header.php';
?>
<main class="flex-grow-1 bg-body-tertiary">
    <section class="container-fluid">
        <p class="text-black text-center fw-semibold no-print mt-4"><?php echo $titulo; ?></p>
        <div class="d-flex flex-column p-3 align-items-center container-xl mb-4 bg-body rounded shadow-sm">
            <div class="container justify-content-center grid gap-4">
                <?php
                for ($i = 0; $i < count($array_salas); $i++) { ?>
                    <a href="<?php echo DOC_ROOT . '/' . $array_salas[$i]['link']; ?>" class="d-flex justify-content-center">
                        <div class="d-flex flex-column justify-content-center align-items-center painel-container flex-grow-1 rounded-3 bg-body shadow-sm border border-light-subtle">
                            <div class="container icone-salas">
                                <i class="fa-solid <?php echo $array_salas[$i]['icone']; ?> fa-2xl" aria-hidden="true"></i>
                            </div>
                            <div class="d-inline-flex container text-center mb-3 titulo-salas lh-2">
                                <span><?php echo $array_salas[$i]['titulo']; ?></span>
                            </div>
                        </div>
                    </a>
                <?php } ?>
            </div>
        </div>
    </section>
</main>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>