<?php
function html_escape($string)
{
    return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8', true);
}
function redirect($location, $parameters = [], $response_code = 302)
{
    $qs = $parameters ? '?' . http_build_query($parameters) : '';
    $location = $location . $qs;
    header('Location: ' . DOC_ROOT . $location, true, $response_code);
    exit;
}
function tituloCase($string)
{
    // Palavras para ignorar
    $palavrasIgnoradas = ['de', 'do', 'da', 'dos', 'das', 'e', 'em', 'na', 'no', 'nas', 'nos', 'à', 'às', 'ao', 'aos'];

    /* // Converte de windows-1252 para UTF-8 usando mb_convert_encoding
    $nomeUtf8 = mb_convert_encoding($string, 'UTF-8', 'windows-1252'); */

    // Divide a string em palavras
    $palavras = explode(' ', mb_strtolower($string, 'UTF-8'));

    // Processa cada palavra
    $novasPalavras = array_map(function ($palavra) use ($palavrasIgnoradas) {
        // Se a palavra estiver na lista de ignorados, retorna como está
        if (in_array($palavra, $palavrasIgnoradas)) {
            return $palavra;
        }
        // Caso contrário, capitaliza a primeira letra
        return mb_convert_case($palavra, MB_CASE_TITLE, "UTF-8");
    }, $palavras);

    // Junta as palavras de volta em uma string
    return implode(' ', $novasPalavras);
}

function saudacaoUser()
{
    $horario = date('H');

    if ($horario >= 7 && $horario < 12) {
        return 'Bom dia, ';
    } elseif ($horario >= 12 && $horario < 18) {
        return 'Boa tarde, ';
    } elseif ($horario >= 18) {
        return 'Boa noite, ';
    } else {
        return 'Boa madrugada, ';
    }
}
function cadastrar($cms, $arguments, $contagem, $qtde)
{
    if ($contagem < $qtde) {
        return $cms->getSalas()->insert($arguments);
    } else {
        return 0;
    }
}
function array_column_function(array $input, $columnKey, $indexKey = null)
{
    $result = [];
    foreach ($input as $row) {
        if (!is_array($row)) continue;

        $value = isset($row[$columnKey]) ? $row[$columnKey] : null;

        if ($indexKey !== null && isset($row[$indexKey])) {
            $result[$row[$indexKey]] = $value;
        } else {
            $result[] = $value;
        }
    }
    return $result;
}
function array_key_last_function(array $array)
{
    if (empty($array)) return null;
    end($array);
    return key($array);
}
function registrarLog($data_horario, $usuario, $acao)
{
    // Concatenar as informações do log
    $mensagem = $data_horario . " - [" . $usuario . "] - LOG: " . $acao;
    return $mensagem;
}
function estruturarSalas($todas_salas)
{
    $salas = [];
    foreach ($todas_salas as $linha) {
        $salas[] = [
            'raw' => $linha['sala'],
            'mostrar_sala' => (bool) $linha['mostrar_sala']
        ];
    }

    $menu = [];

    if (count($salas) > 0) {
        foreach ($salas as $sala_data) {
            $sala = $sala_data['raw'];
            $mostrar = $sala_data['mostrar_sala'];

            $partes = explode('-', $sala);
            $predio = $partes[0];
            $bloco  = mb_strtoupper($partes[1], 'UTF-8');
            $sala_gerada = isset($partes[3]) ? $partes[2] . '.' . $partes[3] : $partes[2];

            // Inicializa o prédio, se ainda não existir
            if (!isset($menu[$predio])) {
                $menu[$predio] = [
                    'predio' => $predio,
                    'blocos' => [],
                ];
            }

            // Procura o índice do bloco dentro do prédio
            $blocoIndex = array_search($bloco, array_column_function($menu[$predio]['blocos'], 'bloco'));

            if ($blocoIndex === false) {
                // Se o bloco não existir, cria
                $menu[$predio]['blocos'][] = [
                    'bloco' => $bloco,
                    'infos' => [],
                ];
                $blocoIndex = array_key_last_function($menu[$predio]['blocos']);
            }

            // Adiciona a sala ao bloco correspondente
            $menu[$predio]['blocos'][$blocoIndex]['infos'][] = [
                'link' => $sala,
                'sala' => $sala_gerada,
                'mostrar_sala' => $mostrar,
            ];
        }
    }

    // Reorganiza os valores para índice numérico
    return array_values($menu);
}
function gerarBreadcrumb($todas_salas)
{
    // Utiliza a função para pegar as salas encapsuladas
    $links_menu = estruturarSalas($todas_salas);

    $pagina_atual = (isset($_GET['sala'])) ? $_GET['sala'] : pathinfo(basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)), PATHINFO_FILENAME);

    //var_dump($pagina_atual);

    $breadcrumb = ($pagina_atual === 'painel')
        ? '<li class="breadcrumb-item active" aria-current="page"><i class="fa-solid fa-house"></i><span class="ms-1">Painel</span></li>'
        : '<li class="breadcrumb-item"><a href="painel"><i class="fa-solid fa-house"></i><span class="ms-1">Painel</span></a></li>';

    $breadcrumb .= ($pagina_atual === 'pesquisa')
        ? '<li class="breadcrumb-item active" aria-current="page">Pesquisar</li>'
        : '';

    $breadcrumb .= ($pagina_atual === 'pagina-nao-encontrada')
        ? '<li class="breadcrumb-item active" aria-current="page">Página não encontrada</li>'
        : '';

    $breadcrumb .= ($pagina_atual === 'nao-autorizado')
        ? '<li class="breadcrumb-item active" aria-current="page">Não autorizado</li>'
        : '';

    foreach ($links_menu as $grupo) {
        foreach ($grupo['blocos'] as $bloco) {
            foreach ($bloco['infos'] as $info) {
                if ($info['link'] === $pagina_atual) {
                    $breadcrumb .= '<li class="breadcrumb-item"><a href="#" class="pe-none" tabindex="-1" aria-disabled="true">Prédio ' . html_escape($grupo['predio']) . '</a></li>';
                    $breadcrumb .= '<li class="breadcrumb-item"><a href="#" class="pe-none" tabindex="-1" aria-disabled="true">Bloco ' . html_escape($bloco['bloco']) . '</a></li>';
                    $breadcrumb .= '<li class="breadcrumb-item active" aria-current="page">' . html_escape($info['sala']) . '</li>';
                    break 3; // Sai de todos os loops ao encontrar a sala
                }
            }
        }
    }

    return $breadcrumb;
}

function statusMudaCor($status)
{
    switch ($status) {
        case 'defeito':
            return 'text-bg-danger';
        case 'reserva':
            return 'text-bg-success';
        case 'manutencao':
            return 'text-bg-warning';
        default:
            return '';
    }
}
function infoModelos($modelos, $sala)
{
    echo "<div id='popover-content' class='d-none'>";
    if (count($modelos) > 0) {
        foreach ($modelos as $row) {
            if ($row['mostrar'] != false) {
                echo "
            <div class='d-flex flex-column mb-3'>
                <div class='border border-1 rounded p-2'>
                    <div class='text-center fw-bold'>
                        <span>" . html_escape($row['titulo']) . "</span>
                    </div>
                    <span class='text-start'>" . nl2br(html_escape($row['descricao'])) . "</span>
                </div>";
                if (Sessao::get('usuario')) {
                    echo "
                    <div class='d-flex flex-row justify-content-start gap-2 ms-2'>
                        <a href='admin/editar_modelo?id=" . $row['id'] . "' title='Editar Modelo' class='d-flex align-items-center gap-1'>
                            <i class='fa-solid fa-file-pen fa-md'></i>Editar
                        </a>
                        <a href='admin/remover_modelo?id=" . $row['id'] . "' title='Remover Modelo' class='d-flex align-items-center gap-1'>
                            <i class='fa-solid fa-trash fa-md'></i>Remover
                        </a>
                    </div>";
                }
                echo "</div>";
            }
        }
    } else {
        echo "
            <div class='d-flex flex-column mb-3'>
                <div class='text-center fw-bold'>
                    <span>Nenhum modelo encontrado.</span>
                </div>
            </div>";
    }
    if (Sessao::get('usuario')) {
        echo "
        <div class='d-flex flex-column p-3 align-items-center botao-add-modelo rounded-start'>
            <a href='admin/cadastrar_modelo?sala=" . $sala . "' title='Cadastrar Modelo'>
                <i class='fa-solid fa-circle-plus fa-xl'></i>
            </a>
        </div>";
    }
    echo "</div>"; // fecha #popover-content

}

function popOvers($status, $patrimonio = '', $modelo = '', $reserva_patrimonio = '', $reserva_modelo = '', $obs = '')
{
    switch ($status) {
        case 'manutencao':
            $titulo_popover = 'Manutenção';
            break;
        case 'defeito':
            $titulo_popover = 'Defeito';
            break;
        case 'reserva';
            $titulo_popover = 'Reserva';
            break;
        default:
            $titulo_popover = '-';
            break;
    }

    $patrimonio_anterior = ($reserva_patrimonio !== $patrimonio && $reserva_patrimonio !== '')
        ? '<span class="mb-2"><strong>Patrim. anterior:</strong> ' . html_escape($patrimonio) . '</span>'
        : '';

    $modelo_anterior = ($reserva_modelo !== $modelo && $reserva_modelo !== '')
        ? '<span class="mb-2"><strong>Modelo anterior:</strong> ' . html_escape($modelo) . '</span>'
        : '';

    $observacoes = (!empty($obs))
        ? '<span><strong>Obs:</strong> ' . nl2br(html_escape($obs)) . '</span>'
        : '';
    // Fim
    if (!empty($observacoes) || !empty($patrimonio_anterior) || !empty($modelo_anterior)) {
        $conteudo = '<div class="d-flex flex-column">' . $modelo_anterior . $patrimonio_anterior . $observacoes . '</div>';
        echo '
        <a 
            tabindex="0" 
            role="button" 
            data-bs-toggle="popover" 
            data-bs-title="Status - ' . $titulo_popover . '" 
            data-bs-html="true" 
            data-bs-content="' . html_escape($conteudo) . '"
        >
            <i class="fa-solid fa-circle-info no-print"></i>
        </a>';
    }
}

function infoDisco($row)
{
    switch ($row['disco']) {
        case 'nenhum':
            $nome_disco = '-';
            break;
        case 'ssd250':
            $nome_disco = 'SSD 250GB';
            break;
        case 'ssd480':
            $nome_disco = 'SSD 480GB';
            break;
        case 'ssd500':
            $nome_disco = 'SSD 500GB';
            break;
        case 'ssd1000':
            $nome_disco = 'SSD 1TB';
            break;
        case 'hd250';
            $nome_disco = 'HD 250GB';
            break;
        case 'hd320';
            $nome_disco = 'HD 320GB';
            break;
        case 'hd500';
            $nome_disco = 'HD 500GB';
            break;
        case 'hd1000';
            $nome_disco = 'HD 1TB';
            break;
        default:
            $nome_disco = '-';
            break;
    };
    return $nome_disco;
}

function salas($getSala, $salas)
{
    foreach ($salas as $key => $value) {
        echo ($key === html_escape($getSala)) ? $value : '';
    }
}

function sisOperacionalCompativel($info)
{
    switch ($info) {
        case 1:
            $so = '(Windows)';
            break;
        case 2:
            $so =  '(Linux)';
            break;
        case 3:
            $so =  '(Mac)';
            break;
        default:
            $so =  '(Outro)';
            break;
    }
    return $so;
}
function licencaTipo($info)
{
    switch ($info) {
        case 1:
            $licenca = 'LOCAL';
            break;
        case 2:
            $licenca =  'REDE';
            break;
        default:
            $licenca =  'DESCONHECIDO';
            break;
    }
    return $licenca;
}

function alertMessage($tipo_alert, $session_gerada, $icone)
{
    echo '
    <div class="d-flex justify-content-center update-msg container no-print">
                <div class="alert ' . $tipo_alert . ' alert-dismissible fade show" role="alert">
                    <p><i class="fa-solid ' . $icone . ' fa-lg pe-1"></i><strong>' . html_escape($_SESSION[$session_gerada]) . '</strong></p>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>';
}

function formatarSala($sala)
{
    $sala = preg_replace_callback(
        '/^(\d+)-([a-zA-Z])-/',
        'callbackFormatarSala',
        $sala
    );

    // Segundo: substitui o último '-' por '.' (se houver)
    $sala = preg_replace('/-(\d+)$/', '.$1', $sala);

    return $sala;
}
function callbackFormatarSala($matches)
{
    return $matches[1] . '/' . mb_strtoupper($matches[2], 'UTF-8') . '/';
}
