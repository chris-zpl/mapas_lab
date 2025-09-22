<?php
// Realiza o include das configurações
include '../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Painel';

include APP_ROOT . '/public/includes/header.php'; ?>
<main class="flex-grow-1 bg-body-tertiary">
    <section class="container-fluid">
        <p class="text-black text-center fw-semibold no-print mt-4"><?php echo $titulo; ?></p>
        <div class="d-flex flex-column py-5 align-items-center container-xl mb-4 bg-body rounded shadow-sm">
            <ul class="nav nav-tabs d-flex justify-content-center w-100" id="salasTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active nav-link-painel" id="inicio-tab" data-bs-toggle="tab" data-bs-target="#inicio-tab-pane" type="button" role="tab" aria-controls="inicio-tab-pane" aria-selected="true">Boas Vindas</button>
                </li>
                <?php foreach ($array_salas as $painel) {
                    $tem_sala_visivel = false;
                    foreach ($painel['blocos'] as $bloco) {
                        foreach ($bloco['infos'] as $info) {
                            if ($info['mostrar_sala']) {
                                $tem_sala_visivel = true;
                                break 2;
                            }
                        }
                    }

                    if (!$tem_sala_visivel) continue;
                ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link nav-link-painel" data-bs-toggle="tab" id="predio<?php echo html_escape($painel['predio']); ?>-tab" data-bs-target="#predio<?php echo html_escape($painel['predio']); ?>-tab-pane" type="button" role="tab" aria-controls="predio<?php echo html_escape($painel['predio']); ?>-tab-pane" aria-selected="false">Prédio <?php echo html_escape($painel['predio']); ?><?php echo $painel['predio'] === '12' ? '<i class="fa-solid fa-triangle-exclamation fa-sm ps-1"></i>' : ''; ?></button>
                    </li>
                <?php } ?>
            </ul>
            <div class="container tab-content" id="salasTabContent">
                <!-- Para o Boas Vindas -->
                <div class="tab-pane fade show active" id="inicio-tab-pane" role="tabpanel" aria-labelledby="inicio-tab" tabindex="0">
                    <div class="my-5 text-center container-fluid">
                        <img class="d-block mx-auto mb-4" src="/bs/imgs/favicon.png" alt="Logo PUCRS" style="width: 100px;">
                        <h1 class="display-5 fw-bold text-body-emphasis">Portal de patrimônios<br>Escola Politécnica</h1>
                        <p class="mt-4 pt-3">Escolha um dos prédios acima para visualizar os <b>patrimônios</b> presentes em cada sala.</p>
                        <div class="border-top border-1 my-4" style="margin: 0 30%;"></div>
                    </div>
                </div>
                <!-- Gerando os prédio com suas salas -->
                <?php foreach ($array_salas as $painel) {
                    $tem_sala_visivel = false;
                    foreach ($painel['blocos'] as $bloco) {
                        foreach ($bloco['infos'] as $info) {
                            if ($info['mostrar_sala']) {
                                $tem_sala_visivel = true;
                                break 2;
                            }
                        }
                    }

                    if (!$tem_sala_visivel) continue;
                ?>
                    <div class="tab-pane fade" id="predio<?php echo html_escape($painel['predio']); ?>-tab-pane" role="tabpanel" aria-labelledby="predio<?php echo html_escape($painel['predio']); ?>-tab" tabindex="0">
                        <div class="mt-4 container justify-content-center grid gap-4">
                            <?php
                            foreach ($painel['blocos'] as $bloco) {
                                foreach ($bloco['infos'] as $infos) {
                                    if ($infos['mostrar_sala']) {
                                        echo '<a href="salas?sala=' . html_escape($infos['link']) . '" class="d-flex justify-content-center">
                            <div class="d-flex flex-column justify-content-center align-items-center painel-container flex-grow-1 rounded-3 bg-body shadow-sm border border-light-subtle">
                                <div class="container icone-salas">
                                    <i class="fa-solid fa-desktop fa-2xl" aria-hidden="true"></i>
                                </div>
                                <div class="d-flex flex-column container text-center mb-3 titulo-salas">
                                    <span class="border-top">Bloco ' . html_escape($bloco['bloco']) . '</span>
                                    <span class="border-top border-bottom">' . html_escape($infos['sala']) . '</span>
                                </div>
                            </div>
                        </a>';
                                    }
                                }
                            } ?>
                        </div>
                    </div>
                <?php
                } ?>
            </div>
        </div>
    </section>
</main>
<?php include APP_ROOT . '/public/includes/footer.php'; ?>