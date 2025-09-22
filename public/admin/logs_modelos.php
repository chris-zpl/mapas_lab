<?php
include '../../src/bootstrap.php';

// Inicia a sessão
include APP_ROOT . '/templates/ini_sessao.php';

$titulo = 'Painel de Logs';

$salas = $cms->getLayoutMapa()->get();

// Armazena dentro de um dicionário as informações de valor e texto para as salas
foreach ($salas as $sala) {
    $partes = explode("-", $sala['sala']);
    $label = isset($partes[3]) ? $partes[0] . '/' . mb_strtoupper($partes[1],'UTF-8') . '/' . $partes[2] . '.' . $partes[3] : $partes[0] . '/' . mb_strtoupper($partes[1],'UTF-8') . '/' .$partes[2];
    $salas_disponiveis[$sala['sala']] = $label;
}

// Obtém o valor de 'sala' da URL
$sala_get = isset($_GET['sala']) ? $_GET['sala'] : null;

// Se houver a sala, chama o método e transforma em json
if($sala_get){
    // Chama o método com o timestamp da última modificação
    $logs = $cms->getModelos()->getLogs($sala_get);
    header('Content-Type: application/json');
    echo json_encode($logs);
    exit;
}

// Verificar se o usuário está logado. Se não estiver, redireciona para a tela de login
Sessao::naologado('_secureAuthMapas', Sessao::get('usuario'), $usuariosAdmins);

include APP_ROOT . '/public/includes/admin-header.php';
?>
<section class="flex-grow-1">
    <div class="d-flex flex-column container-fluid painel-logs">
        <!-- Dropdown para selecionar a sala -->
        <div class="d-flex form-group mb-3">
            <div class="d-flex justify-content-start align-items-center flex-row">
                <label for="salaSelect" class="me-1">Escolha a sala:</label>
                <select id="salaSelect" class="form-select inputs-style" aria-label="Select das salas">
                    <option>Selecione uma sala</option>
                    <?php
                    foreach ($salas_disponiveis as $key => $value) { ?>
                        <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                    <?php }
                    ?>
                </select>
            </div>
        </div>
        <!-- Tabela para exibir os logs -->
        <div class="table-responsive">
            <table class="table" id="logsTable">
                <thead class="table-medium align-middle text-center">
                    <tr>
                        <th class="col-md-2" scope="col">Modelo</th>
                        <th class="text-start col-md-3" scope="col">Descrição</th>
                        <th class="col-md-1" scope="col">Mostrando</th>
                        <th class="col-md-6" scope="col">Log</th>
                    </tr>
                </thead>
                <tbody id="logTableBody" class="table-group-divider">
                    <!-- Conteúdo será carregado pelo script -->
                </tbody>
            </table>
        </div>
    </div>
</section>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        logs('modelos');
    });
</script>
<?php
include APP_ROOT . '/public/includes/admin-footer.php';
?>