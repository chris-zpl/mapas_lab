<?php
// Realiza o include das configurações
include '../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Pesquisar';

$term  = trim(isset($_GET['term']) ? html_escape($_GET['term']) : '');
$show = filter_input(INPUT_GET, 'show', FILTER_VALIDATE_INT);
$show = isset($show) ? $show : 4;

$from = filter_input(INPUT_GET, 'from', FILTER_VALIDATE_INT);
$from = isset($from) ? $from : 0;

$count = 0;

if ($term) {
    $all_results = $cms->getSalas()->search($term); // Busca todos os resultados
    $patrimonios = array_filter($all_results, function ($row) {
        return $row['status_pc'] !== '-' && $row['status_monitor'] !== '-';
    });
    $count = count($patrimonios); // Atualiza a contagem com os itens filtrados

    // Realiza a paginação após filtrar os resultados
    $patrimonios = array_slice($patrimonios, $from, $show); // Aplica paginação após filtrar
}


if ($count === 0) {
    $msg = 'Nenhum resultado foi encontrado';
}

if ($count > $show) {
    $total_pages  = ceil($count / $show);
    $current_page = ceil($from / $show) + 1;

    // Determinar as páginas a serem exibidas
    $start = max(1, $current_page - 1);
    $end = min($total_pages, $current_page + 1);
}

include APP_ROOT . '/public/includes/header.php'; ?>
<main class="flex-grow-1 bg-body-tertiary">
    <section class="container-fluid">
        <p class="text-black text-center fw-semibold no-print mt-4"><?php echo $titulo; ?></p>
        <div class="d-flex flex-column p-3 align-items-center container-xl mb-4 bg-body rounded shadow-sm">
            <span><b>Resultados encontrados para "<?php echo trim($term); ?>"</b></span>
            <p>Quantidade: <?php echo $count ?></p>
            <?php
            if (isset($msg)) { ?>
                <p><?php echo $msg; ?></p>
            <?php
            } else { ?>
                <div class="container justify-content-center grid-search">
                    <?php foreach ($patrimonios as $row) {
                        if ($row['status_pc'] !== '-' && $row['status_monitor'] !== '-') { ?>
                            <div class="d-flex flex-column align-items-center m-2 bg-body shadow-sm rounded-3 border border-light-subtle">
                                <div class="container icone-salas">
                                    <i class="fa-solid fa-desktop fa-2xl" aria-hidden="true"></i>
                                </div>
                                <div class="d-flex justify-content-center container text-center border-bottom">
                                    <span class="fw-bold">Sala: <?php echo formatarSala(html_escape($row['sala'])); ?></span>
                                </div>
                                <div class="d-flex justify-content-center container text-center texto-search border-bottom p-1">
                                    <span class="fw-bold">ID: <?php echo $row['num'] ?></span>
                                </div>
                                <div class="p-1 w-100 border-bottom">
                                    <span class="d-flex justify-content-center fw-bold">Máquina</span>
                                    <div class="d-flex flex-row bg-body-tertiary border rounded-3">
                                        <div class="d-flex flex-column justify-content-center container text-center texto-search <?php echo ($row['status_pc'] !== 'funcionando') ? statusMudaCor($row['status_pc']) : '' ?> rounded-start-3 p-1">
                                            <span class="d-flex justify-content-center fw-bold">Modelo</span>
                                            <span class="d-flex justify-content-center"><?php
                                                                                        if ($row['status_pc'] !== 'funcionando') {
                                                                                            echo ($row['reserva_modelo_pc'] !== '') ? html_escape($row['reserva_modelo_pc']) : html_escape($row['modelo_maquina']);
                                                                                        } else {
                                                                                            echo html_escape($row['modelo_maquina']);
                                                                                        } ?></td>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center container text-center texto-search <?php echo statusMudaCor($row['status_pc']); ?> rounded-end-3 p-1">
                                            <span class="d-flex justify-content-center fw-bold">Patrimônio</span>
                                            <span class="d-flex flex-row justify-content-evenly">
                                                <?php
                                                if ($row['status_pc'] !== 'funcionando') {
                                                    echo ($row['reserva_pc'] !== '') ? html_escape($row['reserva_pc']) : html_escape($row['maquina']);

                                                    // Informações para serem geradas no popover da máquina
                                                    popOvers($row['status_pc'], $row['maquina'], $row['modelo_maquina'], $row['reserva_pc'], $row['reserva_modelo_pc'], $row['obs_pc']);
                                                } else {
                                                    echo html_escape($row['maquina']);
                                                } ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-1 w-100 border-bottom">
                                    <span class="d-flex justify-content-center fw-bold">Monitor</span>
                                    <div class="d-flex flex-row bg-body-tertiary border rounded-3">
                                        <div class="d-flex flex-column justify-content-center container text-center texto-search <?php echo ($row['status_monitor'] !== 'funcionando') ? statusMudaCor($row['status_monitor']) : '' ?> rounded-start-3 p-1">
                                            <span class="d-flex justify-content-center fw-bold">Modelo</span>
                                            <span class="d-flex justify-content-center">
                                                <?php
                                                if ($row['status_monitor'] !== 'funcionando') {
                                                    echo ($row['reserva_modelo_monitor'] !== '') ? html_escape($row['reserva_modelo_monitor']) : html_escape($row['modelo_monitor']);
                                                } else {
                                                    echo html_escape($row['modelo_monitor']);
                                                } ?>
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column justify-content-center container text-center texto-search <?php echo statusMudaCor($row['status_monitor']); ?> rounded-end-3 p-1">
                                            <span class="d-flex justify-content-center fw-bold">Patrimônio</span>
                                            <span class="d-flex flex-row justify-content-evenly">
                                                <?php
                                                if ($row['status_monitor'] !== 'funcionando') {
                                                    echo ($row['reserva_monitor'] !== '') ? html_escape($row['reserva_monitor']) : html_escape($row['monitor']);

                                                    // Informações para serem geradas no popover da máquina
                                                    popOvers($row['status_monitor'], $row['monitor'], $row['modelo_monitor'], $row['reserva_monitor'], $row['reserva_modelo_monitor'], $row['obs_monitor']);
                                                } else {
                                                    echo html_escape($row['monitor']);
                                                } ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-row w-100 flex-grow-1">
                                    <div class="d-flex flex-column justify-content-center container text-center mb-1 texto-search border-bottom border-end p-1">
                                        <span class="d-flex justify-content-center fw-bold">P. Rede</span>
                                        <span class="d-flex justify-content-center"><?php echo html_escape($row['p_rede']); ?></spanclass>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center container text-center mb-1 texto-search border-bottom p-1">
                                        <span class="d-flex justify-content-center fw-bold">Disco</span>
                                        <span class="d-flex justify-content-center"><?php echo infoDisco($row); ?></span>
                                    </div>
                                </div>
                                <div class="container d-flex flex-row align-items-center my-2">
                                    <?php if (Sessao::get('usuario')) { ?>
                                        <div class="d-flex justify-content-center container text-center mb-1 texto-search">
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar Patrimônio" href="admin/editar_patrimonio?id=<?php echo $row['id'] ?>"><i class="fa-solid fa-file-pen fa-xl"></i></a>
                                        </div>
                                        <div class="d-flex justify-content-center container text-center mb-1 texto-search">
                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Mover patrimônio" href="<?php echo 'admin/mover_patrimonio.php?id=' . $row['id']; ?>"><i class="fa-solid fa-arrow-right-arrow-left fa-xl"></i></a>
                                        </div>
                                    <?php } ?>
                                    <div class="d-flex justify-content-center container text-center mb-1 texto-search">
                                        <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Localizar patrimônio" href="salas?sala=<?php echo html_escape($row['sala']) ?>#patrimonio-<?php echo $row['id'] ?>"><i class="fa-solid fa-location-dot fa-xl"></i></a>
                                    </div>
                                </div>
                            </div>
                <?php
                        }
                    }
                } ?>
                </div>
                <?php
                if ($count > $show) { ?>
                    <nav class="mt-3" aria-label="Pagination Navigation">
                        <ul class="pagination">
                            <?php
                            // Exibir o link para a página anterior, se não estivermos na primeira página
                            if ($current_page > 1) {
                                echo '
                        <li class="page-item">
                            <a href="?term=' . $term . '&show=' . $show . '&from=' . (($current_page - 2) * $show) . '" aria-label="Anterior" class="page-link m-1">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>';
                            } else {
                                echo '
                        <li class="page-item disabled">
                            <a href="#" class="page-link m-1" aria-label="Anterior">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>';
                            }

                            // Exibir links das páginas calculadas
                            for ($i = $start; $i <= $end; $i++) {
                                echo '
                            <li class="page-item">
                                <a href="?term=' . $term . '&show=' . $show . '&from=' . (($i - 1) * $show) . '" class="page-link m-1 ' . ($i == $current_page ? 'active" aria-current="true' : '') . '">' . $i . '</a>
                            </li>';
                            }

                            // Exibir o link para a próxima página, se não estivermos na última página
                            if ($current_page < $total_pages) {
                                echo '
                            <li class="page-item">
                                <a href="?term=' . $term . '&show=' . $show . '&from=' . ($current_page * $show) . '" aria-label="Próxima" class="page-link m-1">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>';
                            } else {
                                echo '
                            <li class="page-item disabled">
                                <a href="#" class="page-link m-1" aria-label="Próxima">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>';
                            } ?>
                        </ul>
                    </nav>
                <?php
                } ?>
        </div>
    </section>
</main>
<?php include APP_ROOT . '/public/includes/footer.php'; ?>