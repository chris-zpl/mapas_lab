<section class="container-fluid no-print">
    <div class="container-xl p-3 my-4 bg-body rounded shadow-sm">
        <p class="text-body text-start fw-semibold border-bottom border-light-subtle pb-2">Softwares Instalados</p>
        <div class="mb-5">
            <?php if($contagem_softwares > 0){ ?>
            <div class="d-flex flex-row justify-content-end search-input-software mb-2 me-1">
                <div class="d-flex align-items-center">
                    <span class="text-secondary me-2">Pesquise pelo nome do software</span>
                </div>
                <div class="d-flex flex-row border search-border rounded-3 p-1">
                    <div class="p-1 d-flex align-items-center text-secondary"><i class="fa-solid fa-magnifying-glass fa-lg" aria-hidden="true"></i></div>
                    <input type="text" class="form-control inputs-style" placeholder="Pesquisar" id="search_software" aria-label="Procurar">
                </div>
            </div>
            <?php } ?>
            <!-- Dados serão inseridos aqui via PHP -->
            <div class="softwares max-height-1 overflow-y-auto">
                <div class="accordion" id="collapse_info">
                    <?php
                    if ($contagem_softwares > 0) {
                        foreach ($softwares as $software) { ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse_<?php echo $software['id']; ?>" aria-expanded="false" aria-controls="collapse_<?php echo $software['id']; ?>">
                                        <div class="d-flex flex-column">
                                            <span class="nome_software"><?php echo html_escape($software['nome']) . " " . html_escape($software['versao_instalada']) . (html_escape($software['fabricante']) ? " (" . html_escape($software['fabricante']) . ")" : ''); ?></span>
                                            <p class="text-secondary m-0"><small><?php echo sisOperacionalCompativel(html_escape($software['so_compativel'])); ?></small></p>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse_<?php echo $software['id']; ?>" class="accordion-collapse collapse" data-bs-parent="#collapse_info">
                                    <div class="accordion-body">
                                        <div class="d-flex flex-column align-items-start fs-small">
                                            <div class="d-flex flex-row align-items-center mb-2">
                                                <span class="fw-bold">Tipo de licença:</span><span class="ms-1"><?php echo licencaTipo($software['licencas_tipo']); ?></span>
                                            </div>
                                            <div class="d-flex flex-row align-items-center mb-3">
                                                <span class="fw-bold">Expira em:</span><span class="ms-1"><?php echo ($software['licencas_validade'] !== null) ? html_escape(date("d/m/Y", strtotime($software['licencas_validade']))) : 'SEM VALIDADE'; ?></span>
                                            </div>
                                            <div class="d-flex flex-row align-items-center">
                                                <?php
                                                if ($software['em_uso']) { ?>
                                                    <span class='badge text-bg-success'>INSTALADO</span>
                                                <?php
                                                } else { ?>
                                                    <span class='badge text-bg-warning'>NÃO INSTALADO</span>
                                                <?php
                                                }
                                                echo (!empty($software['licencas_validade']) && date($software['licencas_validade']) < date("Y-m-d")) ? "<span class='badge text-bg-danger ms-2'>EXPIRADO</span>" : '';  ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }else{ ?>
                        <div class="text-center text-secondary border rounded p-3">Nenhum software encontrado.</div>
                    <?php }
                    ?>
                </div>
            </div>
            <div class="d-flex flex-row">
                <div class="container-fluid text-start mt-3 captions">
                    <a type="button" onclick="copiarSoftwares()">Copiar Softwares</a>
                    <div class="toast-container position-fixed bottom-0 end-0 p-3 border-0">
                        <div id="copyToast" class="toast bg-body" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="10000">
                            <div class="toast-header text-bg-primary">
                                <strong class="me-auto">Mensagem</strong>
                                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                            <div class="d-flex flex-column text-start">
                                <!-- Texto inserido pelo script -->
                                <div class="toast-body" id="copyToastText"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid text-end mt-3">
                    <span class="text-secondary">Há um total de <?php echo $contagem_softwares; ?> softwares.</ma>
                </div>
            </div>
        </div>
    </div>
</section>