<?php

// Utiliza a função para pegar as salas encapsuladas
$todas_salas = $cms->getLayoutMapa()->get();
$array_salas = estruturarSalas($todas_salas);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/bs/imgs/favicon.ico" rel="shortcut icon" type="image/x-icon">
    <title>PUCRS | Escola Politécnica | Mapa de Patrimônios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="<?php echo DOC_ROOT . '/css/style.css' ?>">
    <link rel="stylesheet" type="text/css" href="<?php echo DOC_ROOT . '/css/responsive.css' ?>">
    <!-- <link href="https://getbootstrap.com/docs/5.3/assets/css/docs.css" rel="stylesheet"> -->
    <script src="https://kit.fontawesome.com/d8ee45b57f.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</head>

<body>
    <header class="no-print">
        <div class="topbar no-print">
            <div class="container-fluid d-flex justify-content-end align-items-center">
                <div class="dropdown" id="userDropdown">
                    <a href="#" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="outside"><span style="color: #fff; margin-right: 7px; font-weight: bold;"><?php echo (Sessao::get('usuario')) ? 'Logado' : 'Fazer login' ?> </span><i class="fa-solid fa-user fa-lg user-menu"></i></a>
                    <div class="dropdown-menu" style="box-shadow: 3px 3px 9px 1px rgb(0, 0, 0, 0.2);">
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
                        <?php } else {
                            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                                if (isset($_POST['usuario']) && isset($_POST['senha'])) {
                                    if (in_array($_POST['usuario'], $usuariosAutorizados)) {
                                        $login = $cms->getLogin()->autenticar($_POST['usuario'], $_POST['senha']);
                                        if (!$login) {
                                            $error = "Nome de usuário ou senha inválidos.";
                                        }
                                    }else{
                                         $error = "Nome de usuário ou senha inválidos.";
                                    }
                                }
                            } ?>
                            <div class="container no-padding login-box">
                                <?php if (isset($error)) { ?>
                                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                        <p><strong><?php echo $error; ?></strong></p>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>
                                <?php } ?>
                                <div class='login-form'>
                                    <!-- <div class="d-flex justify-content-center mt-3">
                                        <span><strong>Insira suas informações abaixo</strong></span>
                                    </div> -->
                                    <form action="" method="post" class="px-4 py-3">
                                        <div class="form-floating mb-3">
                                            <input type="text" class="form-control inputs-style" id="usuario" name="usuario" placeholder="Ex: 080101" required autofocus aria-describedby="blocoDescrUser">
                                            <label for="usuario">Usuário</label>
                                            <div id="blocoDescrUser" class="form-text">
                                                Credencial dos sistemas internos da POLI.
                                            </div>
                                        </div>
                                        <div class="form-floating mb-3">
                                            <input type="password" id="senha" name="senha" placeholder="Senha da rede" class="form-control inputs-style" aria-describedby="blocoDescrPasswd" required>
                                            <label for="senha">Senha</label>
                                            <div id="blocoDescrPasswd" class="form-text">
                                                Senha dos sistemas internos da POLI.
                                            </div>
                                            <div class='revelar-senha-login'>
                                                <i class="fa-solid fa-eye" onclick="mostrarSenha()" id="hide"></i>
                                                <i class="fa-solid fa-eye-slash" onclick="mostrarSenha()" id="unhide"></i>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <input class="btn btn-primary" type="submit" value="Login">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="header">
            <nav class="navbar menu navbar-expand-lg border-bottom border-light-subtle bg-white">
                <a href="index" class="logo navbar-brand">
                    <img src="/bs/imgs/escola-politecnica.png" alt="Logo Politécnica">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse justify-content-between navbar-collapse" id="navbarSupportedContent">
                    <div class="dropdown">
                        <ul class="navbar-nav me-auto mb-2 mb-lg-0 menu-principal">
                            <li class="nav-item">
                                <a href="painel" class="d-flex align-items-center nav-link links">Início</a>
                            </li>
                            <?php foreach ($array_salas as $menu) {
                                // Verifica se o prédio tem ao menos uma sala visível
                                $tem_sala_visivel = false;
                                foreach ($menu['blocos'] as $bloco) {
                                    foreach ($bloco['infos'] as $info) {
                                        if ($info['mostrar_sala']) {
                                            $tem_sala_visivel = true;
                                            break 2;
                                        }
                                    }
                                }

                                if (!$tem_sala_visivel) continue;
                            ?>
                                <li class="nav-item dropdown">
                                    <a href="#" class="d-flex align-items-center nav-link links dropdown-toggle" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Prédio
                                        <?php echo html_escape($menu['predio']); ?>
                                    </a>
                                    <ul class="dropdown-menu sub-menu-link">
                                        <?php foreach ($menu['blocos'] as $bloco) {
                                            // Verifica se o bloco tem ao menos uma sala visível
                                            $tem_sala_visivel_bloco = false;
                                            foreach ($bloco['infos'] as $info) {
                                                if ($info['mostrar_sala']) {
                                                    $tem_sala_visivel_bloco = true;
                                                    break;
                                                }
                                            }

                                            if (!$tem_sala_visivel_bloco) continue; ?>
                                            <li class="nav-item dropdown dropend">
                                                <a href="#" class="d-flex align-items-center nav-link links dropdown-toggle ms-2" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Bloco <?php echo html_escape($bloco['bloco']); ?>
                                                </a>
                                                <ul class="dropdown-menu bg-secondary bg-gradient">
                                                    <?php foreach ($bloco['infos'] as $infos) {
                                                        // Se for verdadeiro, exibe a sala
                                                        if ($infos['mostrar_sala']) { ?>
                                                            <li>
                                                                <a class="dropdown-item" href="salas?sala=<?php echo html_escape($infos['link']); ?>"><?php echo $infos['sala']; ?></a>
                                                            </li>
                                                    <?php }
                                                    } ?>
                                                </ul>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <form action="pesquisa" method="get" class="d-flex flex-row search-input" role="search">
                        <input class="form-control form-control-md ellipsis inputs-style" type="search" name="term" placeholder="Pesquisar..." aria-label="Pesquisa">
                        <button type="submit" class="btn p-1 d-flex align-items-center"><i class="fa-solid fa-magnifying-glass fa-lg"></i></button>
                    </form>
                </div>
            </nav>
        </div>
        <nav class="d-flex align-items-center bg-body-tertiary py-2" style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='%236c757d'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
            <div class="container-fluid">
                <ol class="breadcrumb">
                    <!-- ## Breadcrumb gerado por PHP ## -->
                    <?php echo gerarBreadcrumb($todas_salas);
                    ?>
                </ol>
            </div>
        </nav>
    </header>