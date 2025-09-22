<?php
// Realiza o include das configurações
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Mover patrimônio';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Dá um get no id para verificar sua existencia. Caso não exista, redireciona para a página não encontrada.
// Caso exista, inicia as funções para atualização.
if ($id != '') {
    $verifica = $cms->getSalas()->get($id);
} else {
    redirect('/pagina-nao-encontrada');
}

if (!$verifica) {
    redirect('/pagina-nao-encontrada');
} else {
    $todas_salas = $cms->getLayoutMapa()->get();
    $predios_salas = estruturarSalas($todas_salas);

    // Array para armazenar prédios únicos
    $predios = [];
    foreach ($predios_salas as $salas) {
        $tem_sala_visivel = false;
        foreach ($salas['blocos'] as $bloco) {
            foreach ($bloco['infos'] as $info) {
                if ($info['mostrar_sala']) {
                    $tem_sala_visivel = true;
                    break 2;
                }
            }
        }

        if ($tem_sala_visivel){
            $predios[] = $salas['predio'];
        };
    }
    $predios = array_unique($predios);
}

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAutorizados);

include APP_ROOT . '/public/includes/admin-header.php';
?>
<main class="flex-grow-1 bg-body-tertiary">
    <section class="container">
        <p class="text-black text-center fw-bold mt-4"><?php echo $titulo; ?></p>
        <div class="container troca-patrimonio mb-4">
            <!-- Primeiro quadro (informações do patrimônio antigo) -->
            <div class="d-flex p-3 flex-column justify-content-center align-items-center container-xl ms-4 me-4 bg-body rounded shadow-sm">
                <div class="d-flex justify-content-center w-100" id="info-patrimonio-antigo">
                </div>
                <div class="d-flex justify-content-center w-100" id="loading_spinner"></div>
            </div>
            <!-- Primeira seta -->
            <div class="setas">
                <div class="container">
                    <i class="fa-solid fa-right-long fa-xl"></i>
                </div>
            </div>
            <!-- Segundo quadro (informações de prédio, sala e patrimônio) -->
            <div class="d-flex p-3 gap-2 flex-column container-xl ms-4 me-4 bg-body rounded shadow-sm">
                <div class="d-flex flex-row gap-4 justify-content-center border border-light-subtle rounded p-2">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadioMaquina" value="patrim_maquina" checked>
                        <label class="form-check-label" for="inlineRadioMaquina">Máquina</label>
                    </div>
                    <div class="vr"></div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="inlineRadioOptions" id="inlineRadioMonitor" value="patrim_monitor">
                        <label class="form-check-label" for="inlineRadioMonitor">Monitor</label>
                    </div>
                </div>
                <div class="border-top border-1 border-opacity-25"></div>
                <div class="d-flex flex-column justify-content-start mb-1">
                    <label for="predioSelect">Prédio:</label>
                    <select id="predioSelect" class="form-select inputs-style" aria-label="Select dos prédios">
                        <option value="" disabled selected>Selecione um prédio</option>
                        <?php foreach ($predios as $predio): ?>
                            <option value="<?php echo html_escape($predio); ?>"><?php echo "Prédio $predio"; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="border-top border-1 border-opacity-25"></div>
                <div class="d-flex flex-column justify-content-start mb-1">
                    <label for="salaSelect">Sala:</label>
                    <select id="salaSelect" class="form-select inputs-style" aria-label="Select das salas">
                    </select>
                </div>
                <div class="border-top border-1 border-opacity-25"></div>
                <div class="d-flex flex-column justify-content-start mb-1">
                    <label for="patrimSelect">Patrimônio:</label>
                    <select id="patrimSelect" class="form-select inputs-style" aria-label="Select dos patrimônios">
                    </select>
                </div>
                <div class="border-top border-1 border-opacity-25"></div>
                <div class="w-100" id="button-mover-patrimonio"></div>
                <div class="w-100" id="button-cancelar"></div>
            </div>
            <!-- Segunda seta -->
            <div class="setas">
                <div class="container">
                    <i class="fa-solid fa-right-long fa-xl"></i>
                </div>
            </div>
            <!-- Terceiro quadro (informações do patrimônio) -->
            <div class="d-flex p-3 flex-column justify-content-center align-items-center container-xl ms-4 me-4 bg-body rounded shadow-sm">
                <div class="d-flex justify-content-center w-100" id="info-patrimonio">
                </div>
            </div>
        </div>
    </section>
</main>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        trocarPatrim();
    });
</script>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>