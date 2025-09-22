<?php

$array_salas_header = [
    ['link' => 'index', 'titulo' => 'Painel de salas', 'icone' => 'fa-solid fa-house'],
    ['link' => 'admin/painel', 'titulo' => 'Painel admin', 'icone' => 'fa-solid fa-user-tie'],
    ['link' => 'admin/cadastrados', 'titulo' => 'Usuários cadastrados', 'icone' => 'fa-solid fa-people-group'],
    ['link' => 'admin/salas_cadastradas', 'titulo' => 'Salas cadastradas', 'icone' => 'fa-solid fa-house-flag'],
    ['link' => 'admin/cadastrar_patrimonio', 'titulo' => 'Cadastrar patrimônio', 'icone' => 'fa-solid fa-file-circle-plus'],
    ['link' => 'admin/cadastrar_sala', 'titulo' => 'Cadastrar sala', 'icone' => 'fa-solid fa-house-chimney-medical'],
    ['link' => 'admin/logs_patrimonios', 'titulo' => 'Logs dos patrimônios', 'icone' => 'fa-solid fa-clock-rotate-left'],
    ['link' => 'admin/logs_modelos', 'titulo' => 'Logs dos modelos', 'icone' => 'fa-solid fa-clock-rotate-left'],
];

// Verifica em um array se o usuário logado pertence aos administradores
$usuarioLinksPermitidos = in_array(Sessao::get('usuario'), $usuariosAdmins);

// Se o usuário não for dos administradores, permiti apenas algumas páginas
if (!$usuarioLinksPermitidos) {
    $array_salas_header  = array_filter($array_salas_header, function ($sala) {
        return $sala['link'] === 'admin/cadastrar_patrimonio' ||
            $sala['link'] === 'index' ||
            $sala['link'] === 'admin/painel' || $sala['link'] === 'admin/salas_cadastradas' || $sala['link'] === 'admin/cadastrar_sala';
    });

    // Reindexa o array após o filtro
    $array_salas_header  = array_values($array_salas_header);
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/bs/imgs/favicon.ico" rel="shortcut icon" type="image/x-icon">
    <title>PUCRS | Escola Politécnica | <?php echo isset($titulo) ? $titulo : 'Mapa de Patrimônios'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?php echo DOC_ROOT . '/css/style.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo DOC_ROOT . '/css/responsive.css' ?>">
    <script src="https://kit.fontawesome.com/d8ee45b57f.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>
    <header>
        <div class="topbar">
            <div class="container-fluid d-flex justify-content-end align-items-center">
                <div class="dropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"><span style="color: #fff; margin-right: 7px; font-weight: bold;"><?php echo (Sessao::get('usuario')) ? 'Logado' : 'Fazer login' ?> </span><i class="fa-solid fa-user fa-lg user-menu"></i></a>
                    <form class="dropdown-menu">
                        <?php
                        if (Sessao::get('usuario')) { ?>
                            <div>
                                <?php
                                try { ?>
                                    <div class="d-flex flex-direction-row justify-content-center pe-2 ps-2 fw-bold">
                                        <p class="user-texto m-2">Matrícula: <?php echo html_escape(Sessao::get('usuario')); ?></p>
                                        <div class="vr"></div>
                                        <p class="user-texto m-2">Ramal: <?php echo html_escape($username[Sessao::get('usuario')]['RAMAL']); ?></p>
                                    </div>
                                    <hr class="dropdown-divider">
                                    <p class="user-texto p-3"><?php echo saudacaoUser(); ?><b><?php echo html_escape(tituloCase(iconv('Windows-1252', 'UTF-8', $username[Sessao::get('usuario')]['NOME']))); ?></b>.</p>
                                    <hr class="dropdown-divider">
                                    </li>
                                    <div class="d-flex flex-direction-row justify-content-evenly align-items-center">
                                        <a href="<?php echo DOC_ROOT . '/admin/painel' ?>" class="user-texto">Admin</a>
                                        <div class="vr"></div>
                                        <a href="<?php echo DOC_ROOT . '/admin/logout' ?>" class="user-texto">Logout</a>
                                    </div>
                                <?php } catch (Exception $e) { ?>
                                    <p><?php echo $e->getMessage(); ?></p>
                                <?php }
                                ?>
                            </div>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="header">
            <nav class="navbar menu bg-body border-bottom border-light-subtle">
                <a href="index" class="logo navbar-brand">
                    <img src="/bs/imgs/escola-politecnica.png" alt="Logo Politécnica">
                </a>
                <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i class="fa-solid fa-bars"></i> Menu</button>
                <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
                    <div class="offcanvas-header border-bottom border-1">
                        <h5 class="offcanvas-title" id="offcanvasRightLabel">Menu Administradores</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <ul class="navbar-nav menu-admin my-3">
                            <?php
                            for ($i = 0; $i < count($array_salas_header); $i++) { ?>
                                <li class="nav-item">
                                    <a href="<?php echo DOC_ROOT . '/' . $array_salas_header[$i]['link']; ?>" class="d-flex align-items-center gap-2 links nav-link"><?php echo $array_salas_header[$i]['icone'] !== '' ? '<i class="' . $array_salas_header[$i]['icone'] . ' fa-lg"></i>' : ''; ?><?php echo $array_salas_header[$i]['titulo']; ?></a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </nav>
        </div>
    </header>