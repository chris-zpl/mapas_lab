<?php
include '../src/bootstrap.php';
include APP_ROOT . '/templates/ini_sessao.php';

// Pega a sala da URL
$sala = filter_input(INPUT_GET, 'sala', FILTER_SANITIZE_ENCODED);

if (!$sala) {
    redirect('/pagina-nao-encontrada');
}

// Busca dados da sala com base no ID da sala
$sala_data = $cms->getLayoutMapa()->getPerSala($sala);

// Se não houver dados ou se a sala não estiver sendo exibida, redireciona
if (!$sala_data || !$sala_data['mostrar_sala']) {
    redirect('/pagina-nao-encontrada');
}

// Separa a sala em partes para compor os dados dinâmicos
$partes = explode("-", $sala);

// Dados dinâmicos vindos do banco
$titulo = $sala_data['titulo'] ? html_escape($sala_data['titulo']) : 'Sala desconhecida';
$qtde_gerada = isset($sala_data['qtde_gerada']) ? $sala_data['qtde_gerada'] : 1;

// Dados para o JS
/* $data = [
    'salaUrl' => html_escape($sala),
    'quantidade' => (int)$qtde_gerada
]; */

// Informações vindas do banco de dados
$patrimonios = $cms->getSalas()->getPerSala($sala);
$modelos = $cms->getModelos()->getPerSalaModelo($sala);
$modificado_salas = $cms->getSalas()->getModificado($sala);
$modificado_modelos = $cms->getModelos()->getModificado($sala);
if ($sala_data['mostrar_softwares']) {
    $softwares = $cms->getSoftwares()->getPerSala($sala);
    $contagem_softwares = $cms->getSoftwares()->getContagemElementos($sala);
}

// Templates
include APP_ROOT . '/public/includes/header.php'; ?>
<main class="flex-grow-1 bg-body-tertiary print-config">
    <?php include APP_ROOT . '/templates/t_salas.php';
    ($sala_data['mostrar_softwares']) ? include APP_ROOT . '/templates/t_softwares.php' : '';
    ?>
</main>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Verifica se há dados
        mapaDosPatrimonios('<?php echo $sala; ?>', <?php echo $qtde_gerada; ?>);

        <?php
        if ($sala_data['mostrar_softwares']) {
            if ($contagem_softwares > 0) { ?>
                pesquisarSoftwares();
        <?php }
        } ?>

        const hash = window.location.hash;
        if (hash.startsWith('#patrimonio-')) {
            const numero = hash.replace('#patrimonio-', '');

            // Espera DOM + elementos renderizarem
            setTimeout(() => {
                localizarPatrimonio(numero);
            }, 300); // Ajuste conforme necessário
        }

    });
</script>
<?php include APP_ROOT . '/public/includes/footer.php';
