<section class="container-fluid">
    <div class="d-flex justify-content-center update-msg container no-print" id="alertContainer"></div>
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
    <div class="container-xl p-3 my-4 bg-body rounded shadow-sm" id="conteudo-mapa">
        <p class="d-flex flex-row justify-content-between text-body text-start fw-semibold no-print border-bottom border-light-subtle pb-2 gap-2">
            <span> <?php echo $titulo; ?></span>
            <?php if (Sessao::get('usuario')) {
                $salaExistente = $cms->getLayoutMapa()->getPerSala($sala); ?>
                <a type="button" href="admin/editar_sala?id=<?php echo $salaExistente['id']; ?>" class="d-flex btn btn-primary" style="--bs-btn-padding-y: .25rem; --bs-btn-padding-x: .5rem; --bs-btn-font-size: .75rem;">Editar informações da sala</a>
            <?php } ?>
        </p>
        <?php if (Sessao::get('usuario')) { ?>
            <!-- Botões para editar grid -->
            <div class="botoes-grid my-2 gap-2 no-print">
                <div id="botoesEditarGrid"></div>
                <div class="d-flex flex-row gap-2" id="controles-grid"></div>
            </div>
        <?php } ?>
        <div class="d-flex flex-row mapa gap-3">
            <div class="mapa-grid">
                <div class="titulo-mapa">
                    <p id="titulo_sala"><strong><?php echo mb_strtoupper($titulo, 'UTF-8'); ?></strong></p>
                </div>
                <!-- Divs geradas pelo script -->
            </div>
            <div class="tabela-info">
                <div class="d-flex flex-row legenda-table no-print">
                    <div class="container-fluid d-flex flex-row justify-content-start align-items-end info-modelos">
                        <a role="button" class="text-body" data-bs-toggle="popover" data-bs-placement="top" data-bs-trigger="manual" data-bs-title="Informação dos Modelos" data-bs-html="true" data-bs-content-id="popover-content">Modelos <i class="fa-solid fa-circle-info fa-xs"></i></a>
                    </div>
                    <?php infoModelos($modelos, $sala); ?>
                    <div class="container-fluid d-flex flex-row justify-content-end info-status">
                        <div class="d-flex flex-row align-items-center py-1 px-2 mx-1 border border-light-subtle rounded">
                            <div class="status-border text-bg-warning">
                            </div>
                            <span class="ps-1 text-body">Manutenção</span>
                        </div>
                        <div class="d-flex flex-row align-items-center py-1 px-2 mx-1 border border-light-subtle rounded">
                            <div class="status-border text-bg-danger">
                            </div>
                            <span class="ps-1 text-body">Defeito</span>
                        </div>
                        <div class="d-flex flex-row align-items-center py-1 px-2 mx-1 border border-light-subtle rounded">
                            <div class="status-border text-bg-success">
                            </div>
                            <span class="ps-1 text-body">Reserva</span>
                        </div>
                    </div>
                </div>
                <div class="table-responsive max-height-1 shadow-sm border" style="border-collapse: none;">
                    <table class="table table-sm h-100" id="tabela-patrimonios">
                        <thead class="table-medium text-center">
                            <tr>
                                <th class="border-end border-dark col-md-1" scope="col">#</th>
                                <th class="border-bottom border-dark col-md-2" scope="col">Modelo Máquina</th>
                                <th class="border-end border-dark col-md-2" scope="col">Máquina</th>
                                <th class="border-bottom border-dark col-md-2" scope="col">Modelo Monitor</th>
                                <th class="border-end border-dark col-md-2" scope="col">Monitor</th>
                                <th class="border-end border-dark col-md-1" scope="col">P. Rede</th>
                                <th class="border-bottom border-dark col-md-1" scope="col">Disco</th>
                                <th class="d-none" scope="col">Status PC</th>
                                <?php
                                if (Sessao::get('usuario')) { ?>
                                    <th scope="col" class="border-start border-dark col-md-1 no-print">Ação</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider text-center">
                            <!-- Dados serão inseridos aqui via PHP -->
                            <?php
                            if (count($patrimonios) > 0) {
                                foreach ($patrimonios as $key => $row) {
                                    if ($row['mostrar'] === true) { ?>
                                        <tr class="align-middle" id="patrimonio-<?php echo $row['id']; ?>">
                                            <th class="border-end" scope="row"><?php echo html_escape($row['num']) ?></th>

                                            <!-- Informações relacionadas à máquina e seu modelo -->
                                            <td <?php echo ($row['status_pc'] !== 'funcionando') ? 'class=' . statusMudaCor($row['status_pc']) : '' ?>>
                                                <?php
                                                if ($row['status_pc'] !== 'funcionando') {
                                                    echo ($row['reserva_modelo_pc'] !== '') ? html_escape($row['reserva_modelo_pc']) : html_escape($row['modelo_maquina']);
                                                } else {
                                                    echo html_escape($row['modelo_maquina']);
                                                } ?></td>
                                            <td class="border-end  <?php echo ($row['status_pc'] !== 'funcionando') ? statusMudaCor($row['status_pc']) : '' ?>">
                                                <?php
                                                if ($row['status_pc'] !== 'funcionando') {
                                                    echo ($row['reserva_pc'] !== '') ? html_escape($row['reserva_pc']) : html_escape($row['maquina']);

                                                    // Informações para serem geradas no popover da máquina
                                                    popOvers($row['status_pc'], $row['maquina'], $row['modelo_maquina'], $row['reserva_pc'], $row['reserva_modelo_pc'], $row['obs_pc']);
                                                } else {
                                                    echo html_escape($row['maquina']);
                                                } ?></td>

                                            <!-- Informações relacionadas ao monitor e seu modelo -->
                                            <td <?php echo ($row['status_monitor'] !== 'funcionando') ? 'class=' . statusMudaCor($row['status_monitor']) : '' ?>>
                                                <?php
                                                if ($row['status_monitor'] !== 'funcionando') {
                                                    echo ($row['reserva_modelo_monitor'] !== '') ? html_escape($row['reserva_modelo_monitor']) : html_escape($row['modelo_monitor']);
                                                } else {
                                                    echo html_escape($row['modelo_monitor']);
                                                } ?></td>
                                            <td class="border-end <?php echo ($row['status_monitor'] !== 'funcionando') ? statusMudaCor($row['status_monitor']) : '' ?>">
                                                <?php
                                                if ($row['status_monitor'] !== 'funcionando') {
                                                    echo ($row['reserva_monitor'] !== '') ? html_escape($row['reserva_monitor']) : html_escape($row['monitor']);

                                                    // Informações para serem geradas no popover do monitor
                                                    popOvers($row['status_monitor'], $row['monitor'], $row['modelo_monitor'], $row['reserva_monitor'], $row['reserva_modelo_monitor'], $row['obs_monitor']);
                                                } else {
                                                    echo html_escape($row['monitor']);
                                                } ?></td>

                                            <!-- Informações do ponto de rede -->
                                            <td class="border-end"><?php echo html_escape($row['p_rede']) ?></td>
                                            <!-- Informações para serem geradas no DISCO -->
                                            <td class="border-end"><?php echo infoDisco($row); ?></td>

                                            <td class="d-none"><?php echo html_escape($row['status_pc']); ?></td>
                                            <?php
                                            if (Sessao::get('usuario')) { ?>
                                                <td class="align-middle no-print">
                                                    <div class="acao gap-2">
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Editar patrimônio" href="<?php echo 'admin/editar_patrimonio.php?id=' . $row['id']; ?>"><i class="fa-solid fa-file-pen fa-xl"></i></a>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Mover patrimônio" href="<?php echo 'admin/mover_patrimonio.php?id=' . $row['id']; ?>"><i class="fa-solid fa-arrow-right-arrow-left fa-xl"></i></a>
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-center">
                                                            <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Remover patrimônio" href="<?php echo 'admin/remover_patrimonio.php?id=' . $row['id']; ?>"><i class="fa-solid fa-trash fa-xl"></i></a>
                                                        </div>
                                                    </div>
                                                </td>
                                        <?php }
                                    } ?>
                                        </tr>
                                    <?php
                                }
                            } else { ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum patrimônio encontrado.</td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex flex-column captions my-3 no-print">
                    <div class="d-flex flex-row" id="captions-da-tabela">
                    </div>
                </div>
                <div class="container-fluid update-modificado">
                    <span><?php
                            if (!empty($modificado_salas) || !empty($modificado_modelos)) {
                                echo 'Atualizado em <b>';

                                $data_salas   = !empty($modificado_salas['modificado']) ? $modificado_salas['modificado'] : '';
                                $data_modelos = !empty($modificado_modelos['modificado']) ? $modificado_modelos['modificado'] : '';

                                if (strtotime($data_salas) > strtotime($data_modelos)) {
                                    echo html_escape(date("d/m/Y", strtotime($data_salas)));
                                    echo '</b> às <b>' . html_escape(date("H:i", strtotime($data_salas))) . '</b>';
                                } else {
                                    echo html_escape(date("d/m/Y", strtotime($data_modelos)));
                                    echo '</b> às <b>' . html_escape(date("H:i", strtotime($data_modelos))) . '</b>';
                                }
                            } else {
                                echo 'Não há atualizações';
                            }
                            ?>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>