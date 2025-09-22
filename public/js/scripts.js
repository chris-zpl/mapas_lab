// Inicializa algumas funções
inicializarTooltip();
inicializarPopovers();

function obterClasseStatus(status) {
  return (
    {
      defeito: "text-bg-danger",
      reserva: "text-bg-success",
      manutencao: "text-bg-warning",
    }[status] || ""
  );
}

// Função para gerar os patrimônios no mapa
async function mapaDosPatrimonios(salaUrl, numeroDeElementos) {
  const divPrincipalLayout = document.querySelector(".mapa-grid"); // Div pai do layout
  const tabela = document.querySelector("table tbody");
  const linhasTabela = tabela.querySelectorAll("tr");
  const alertContainer = document.getElementById("alertContainer");
  let { valueCols, valueRows, posicoes } = await carregarConteudo(salaUrl);

  /* Variáveis vazias */
  let controlGrids = "";
  let botaoSalvarEditar = "";
  let gridContainer = "";
  let botoesGrid = "";
  let captionsTable = "";
  const celulas = [];
  const elementos = [];
  let origemCell = null;
  let modoEdicao = false;
  let estadoOriginal = {
    valueCols: 0,
    valueRows: 0,
    posicoes: {},
  };

  /* Divide a string da sala */
  const partesSalaUrl = salaUrl.split("-");
  const bloco = partesSalaUrl[1];
  const sala = partesSalaUrl.includes(partesSalaUrl[3])
    ? `${partesSalaUrl[2]}-${partesSalaUrl[3]}`
    : partesSalaUrl[2];

  /* Montando o grid pela primeira vez */
  const divGrid = document.createElement("div");
  divGrid.id = `grid-${bloco}-${sala}`;
  divGrid.className = "estilo-mapa rounded";

  /* Aloca para seus respectivos filhos */
  divPrincipalLayout.appendChild(divGrid);

  /* Pega o id para criar o container do grid */
  gridContainer = document.getElementById(`grid-${bloco}-${sala}`);
  criarGridTemplate(gridContainer);

  // Se o primeiro elemento for diferente de "Nenhum patrimônio encontrado"
  // Renderiza os botões e os patrimônios
  if (
    linhasTabela[0].cells[0].textContent !== "Nenhum patrimônio encontrado."
  ) {
    botoesGrid = document.getElementById("botoesEditarGrid");
    captionsTable = document.getElementById("captions-da-tabela");
    (botoesGrid) ? botoesGrid.innerHTML = renderBotaoEditarGrid() : '';
    controlGrids = document.getElementById("controles-grid");
    botaoSalvarEditar = document.getElementById("botaoSalvarEditarPosicoes");
    captionsTable.innerHTML = renderCaptionsTable();
    inicializarTooltip();

    // Coleta os elementos da tabela
    const maxElementos = Math.min(numeroDeElementos, linhasTabela.length);
    for (let i = 1; i <= maxElementos; i++) {
      const linha = linhasTabela[i - 1];
      const div = criarElementoDaLinha(linha, bloco, sala, i);
      elementos.push(div);
    }

    // Monta a grade
    montarGrade(valueCols, valueRows, gridContainer, elementos, true, posicoes);
  } else {
    botoesGrid.innerHTML = "";
    captionsTable.innerHTML = "";
    /* Insere o spinner até a inserção de um patrimônio */
    const spinner = document.createElement("div");
    spinner.style =
      "position: relative; top: 171px; left: 204px; text-align: center; align-items: center; display: flex; flex-direction: column;width: 40px";
    spinner.innerHTML = renderSpinnerLoading();
    const spinnerText = document.createElement("span");
    spinnerText.textContent = "Aguardando patrimônios...";
    spinnerText.style = "width: max-content";
    divGrid.appendChild(spinner);
    spinner.appendChild(spinnerText);
  }

  /* Botão para salvar ou editar as posições */
  if (botaoSalvarEditar) {
    botaoSalvarEditar.addEventListener("click", async () => {
      if (!modoEdicao) {
        // Modo: Editar → Salvar
        modoEdicao = true;
        botaoSalvarEditar.textContent = "Salvar posições";
        gridContainer.classList.add("sem-selecao");

        /* Salva o estado original */
        estadoOriginal = {
          valueCols,
          valueRows,
          posicoes: structuredClone(posicoes),
        };
        document
          .getElementById("botoesCancelaSalvaPosicoes")
          .insertAdjacentHTML("beforeend", renderCancelarButton());
        controlGrids.innerHTML = renderButtons();
        carregarButtons(valueCols, valueRows);

        /* Altera o draggable para true, assim habilitando o arrastar dos objetos */
        document.querySelectorAll("[draggable='false']").forEach((el) => {
          el.classList = "border border-1 border-primary";
          el.setAttribute("draggable", true);
        });

        /* Adiciona a classe ao div pai, junto com "cell" */
        document.querySelectorAll(".cell").forEach((el) => {
          el.classList += " draggable-cell border";
        });

        /* BOTÃO CANCELAR */
        const botaoCancelar = document.getElementById("botaoCancelarPosicoes");
        botaoCancelar.addEventListener("click", async () => {
          /* Restaura valores originais */
          valueCols = estadoOriginal.valueCols;
          valueRows = estadoOriginal.valueRows;
          posicoes = structuredClone(estadoOriginal.posicoes);

          criarGridTemplate(gridContainer);
          montarGrade(
            valueCols,
            valueRows,
            gridContainer,
            elementos,
            true,
            posicoes
          );

          gridContainer.classList.remove("sem-selecao");

          /* Remove controles de edição */
          controlGrids.innerHTML = "";
          botaoSalvarEditar.textContent = "Editar posições no mapa";
          modoEdicao = false;
          if (botaoCancelar) {
            botaoCancelar.remove();
          }

          /* Altera o draggable para false, assim desabilitando o arrastar dos objetos */
          document.querySelectorAll("[draggable='true']").forEach((el) => {
            el.classList.remove("border-primary");
            if (el.textContent.trim().toLowerCase().startsWith("copy")) {
              el.classList.remove("border", "border-1", "border-primary");
            }
            el.setAttribute("draggable", false);
          });

          /* Remove a classe do div pai, junto com "cell" */
          document.querySelectorAll(".cell").forEach((el) => {
            el.classList.remove("draggable-cell", "border");
          });
        });
      } else {
        // Modo: Salvar → Editar novamente
        const botaoCancelar = document.getElementById("botaoCancelarPosicoes");
        posicoes = coletarPosicoes(celulas);

        const payload = montarPayload(salaUrl, valueRows, valueCols, posicoes);

        const formData = new URLSearchParams();
        formData.append("sala", payload.sala);
        formData.append("linhas", payload.linhas);
        formData.append("colunas", payload.colunas);
        formData.append("posicoes", JSON.stringify(payload.posicoes));

        try {
          const response = await fetch("./api/set_posicoes.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: formData.toString(),
          });
          const resultado = await response.json();

          if (resultado.status === "ok") {
            alertContainer.innerHTML = renderAlert();
            const alerta = alertContainer.querySelector(".alert");

            /* Remove o alerta do DOM ao fechar manualmente  */
            alerta.addEventListener("closed.bs.alert", () => {
              alertContainer.innerHTML = "";
            });

            /* Remove os controles do DOM, desativa o modo edição e modifica o texto */
            controlGrids.innerHTML = "";

            botaoSalvarEditar.textContent = "Editar posições no mapa";
            modoEdicao = false;
            if (botaoCancelar) {
              botaoCancelar.remove();
            }
            gridContainer.classList.remove("sem-selecao");

            /* Altera o draggable para false, assim desabilitando o arrastar dos objetos */
            document.querySelectorAll("[draggable='true']").forEach((el) => {
              el.classList.remove("border-primary");
              if (el.textContent.trim().toLowerCase().startsWith("copy")) {
                el.classList.remove("border", "border-1", "border-primary");
              }
              el.setAttribute("draggable", false);
            });
            /* Remove a classe do div pai, junto com "cell" */
            document.querySelectorAll(".cell").forEach((el) => {
              el.classList.remove("draggable-cell", "border");
            });
          } else {
            console.error("Erro ao salvar:", resultado.message);
          }
        } catch (error) {
          console.error("Erro na requisição:", error);
        }
      }
    });
  }

  /* Funções */
  async function carregarConteudo(salaUrl) {
    const response = await fetch(
      `./api/get_posicoes.php?sala=${encodeURIComponent(salaUrl)}`
    );
    const data = await response.json();

    const cols = data.colunas;
    const rows = data.linhas;
    const posicoes = JSON.parse(data.posicoes);

    return { valueCols: cols, valueRows: rows, posicoes };
  }

  function carregarButtons(valueCols, valueRows) {
    const counterInputColumns = document.getElementById("col-value-column");
    const counterInputRows = document.getElementById("col-value-row");
    if (counterInputColumns && counterInputRows) {
      counterInputColumns.value = valueCols;
      counterInputRows.value = valueRows;
      buttonsEditTemplate();
    }
  }

  function criarElementoDaLinha(linha, bloco, sala, i) {
    const divConteudo = document.createElement("div");
    divConteudo.draggable = false;
    divConteudo.id = `${bloco}-${sala}-${i.toString().padStart(2, "0")}`;

    divConteudo.addEventListener("dragstart", (e) => {
      e.dataTransfer.setData("text/plain", e.target.id);
      e.dataTransfer.effectAllowed = "move";
      origemCell = e.target.closest(".cell");
    });

    divConteudo.dataset.row = Math.floor((i - 1) / valueCols);
    divConteudo.dataset.col = (i - 1) % valueCols;

    const divImg = document.createElement("div");
    divImg.className = "d-flex justify-content-center border-bottom";

    const divPatrimonio = document.createElement("div");
    const celulas = linha.querySelectorAll("td");
    const celulaPatrimonio = celulas[1];
    const celulaStatus = celulas[6];
    let texto = celulaPatrimonio.textContent.trim();
    let textoFormatado = texto;
    const imgElement = document.createElement("img");
    imgElement.id = "image-container";

    // Adiciona a div da imagem do pc para o div pai
    divConteudo.appendChild(divImg);

    // Caso o texto não comece com "copy", adiciona o src da img, o número e da o append
    // Caso comece com, coloca a img da impressora
    if (!texto.toLowerCase().startsWith("copy")) {
      const divImgExtra = document.createElement("div");
      divImgExtra.id = "extra-image-container";

      imgElement.src = "../public/img/computer.png";
      imgElement.alt = "Imagem do PC";
      imgElement.style = "width: 40px; height: 40px;";

      const divNum = document.createElement("div");
      divNum.textContent = `${i.toString().padStart(2, "0")}`;
      divNum.className = "text-center fw-bold border-bottom";

      divImg.appendChild(imgElement);
      divImg.appendChild(divImgExtra);
      divConteudo.className = "border border-1";
      divConteudo.appendChild(divNum);
    } else {
      //const divImgPrinter = document.createElement("div");
      //divImgPrinter.id = "image-container";
      divConteudo.style = "border: none; font-weight: 800;";
      imgElement.src = "../public/img/printer.png";
      imgElement.style = "width: 48px; height: 48px;";
      /* switch (texto.toLowerCase()){
        // Impressoras SPC440
        case 'copy3009': // 30/A/212
        case 'copy3011': // 30/F/201.01
          imgElement.src = "../public/img/spc440.png";
          imgElement.style = 'width: 48px; height: 48px;';
          break;
        // Impressoras Ricoh
        case 'copy3036': // 30/A/212
        case 'copy3014': // 30/A/212
        case 'copy3020': // 30/F/211.01
        case 'copy3018': // 30/F/211.01
        case 'copy3034': // 30/F/212.02
        case 'copy3041': // 30/F/212.02
          imgElement.src = "../public/img/RicohSP5310DN2.png";
          imgElement.style = 'width: 48px; height: 48px;';
          break;
        case 'copy3022': // 30/F/212.02
          imgElement.src = "../public/img/c2003.png";
          imgElement.style = 'width: 45.8px; height: 48px;';
          break;
        // Impressoras Plotter
        case 'copy3019': // 30/F/201.01
        case 'copy3056': // 30/F/212.05
        case 'copy3016': // 30/F/212.05
          imgElement.src = "../public/img/hp_designjet.png";
          imgElement.style = 'width: 63.91px; height: 48px;';
          break;
      } */

      imgElement.alt = "Imagem da Impressora";
      divImg.style = "border: none !important";
      divImg.appendChild(imgElement);
    }

    // Pega o texto e verifica se começa com "copy".
    // Caso inicie, quebra a linha.
    if (texto.toLowerCase().startsWith("copy")) {
      textoFormatado = texto.replace(/(COPY)/i, "$1\n");
    }

    divPatrimonio.className = `p-1 ${
      celulaStatus ? obterClasseStatus(celulaStatus.textContent.trim()) : ""
    } text-center lh-1`;
    divPatrimonio.innerText = textoFormatado;

    // Dá o append em todo o conteúdo e informa o texto com suas classes
    divConteudo.appendChild(divPatrimonio);

    return divConteudo;
  }

  function criarGridTemplate(gridContainer) {
    gridContainer.style.gridTemplateColumns = `repeat(${valueCols}, minmax(60px, 1fr))`;
    gridContainer.style.gridTemplateRows = `repeat(${valueRows}, minmax(60px, 1fr))`;
  }

  function criarCelula(row, col) {
    const cell = document.createElement("div");
    cell.className = "cell";
    cell.dataset.row = row;
    cell.dataset.col = col;
    registrarEventosDragAndDrop(cell);
    return cell;
  }

  function registrarEventosDragAndDrop(cell) {
    cell.addEventListener("dragover", (e) => {
      e.preventDefault();
      cell.classList.add("over");
    });

    cell.addEventListener("dragleave", () => {
      cell.classList.remove("over");
    });

    cell.addEventListener("drop", (e) => {
      e.preventDefault();
      const id = e.dataTransfer.getData("text/plain");
      const dragged = document.getElementById(id);

      if (dragged && origemCell && origemCell !== cell) {
        const destinoElemento = cell.firstElementChild;

        // Move o elemento de destino para a célula de origem
        if (destinoElemento) {
          origemCell.appendChild(destinoElemento);
          atualizarPosicaoElemento(
            destinoElemento,
            origemCell.dataset.row,
            origemCell.dataset.col
          );
        }

        // Move o elemento arrastado para a célula de destino
        cell.appendChild(dragged);
        atualizarPosicaoElemento(dragged, cell.dataset.row, cell.dataset.col);
      }

      cell.classList.remove("over");
    });
  }

  function atualizarPosicaoElemento(el, row, col) {
    el.dataset.row = row;
    el.dataset.col = col;
  }

  function recalcularPosicaoElemento(el, newCols, ocupadoMap) {
    let row = parseInt(el.dataset.row, 10);
    let col = parseInt(el.dataset.col, 10);

    // Se a coluna for maior do que o novo número de colunas, ajusta
    if (isNaN(col) || col >= newCols) {
      col = 0; // Joga para a última coluna válida
    }

    // Busca próxima célula desocupada (mantendo a mesma coluna, depois descendo linha)
    while (ocupadoMap[`${row}-${col}`]) {
      col++;
      if (col >= newCols) {
        col = 0;
        row++;
      }
    }

    // Marca a nova posição como ocupada
    ocupadoMap[`${row}-${col}`] = true;

    return { row, col };
  }

  function montarGrade(
    valueCols,
    valueRows,
    gridContainer,
    elementos = [],
    usarPosicoesSalvas = false,
    posicoesSalvas = {}
  ) {
    /* Limpa o grid antes de colocar os patrimônios */
    gridContainer.innerHTML = "";
    celulas.length = 0;

    for (let r = 0; r < valueRows; r++) {
      for (let c = 0; c < valueCols; c++) {
        const cell = criarCelula(r, c);
        gridContainer.appendChild(cell);
        celulas.push(cell);
      }
    }
    const ocupadoMap = {};

    elementos.forEach((el) => {
      let row = parseInt(el.dataset.row);
      let col = parseInt(el.dataset.col);

      if (posicoesSalvas !== null) {
        if (usarPosicoesSalvas && posicoesSalvas[el.id]) {
          row = posicoesSalvas[el.id].row;
          col = posicoesSalvas[el.id].col;
        }
      }
      const ajustado = recalcularPosicaoElemento(
        { dataset: { row, col } },
        valueCols,
        ocupadoMap
      );

      const index = ajustado.row * valueCols + ajustado.col;
      const targetCell = celulas[index];

      if (targetCell) {
        targetCell.innerHTML = "";
        targetCell.appendChild(el);
        atualizarPosicaoElemento(el, ajustado.row, ajustado.col);
      }
    });
  }

  function coletarElementosDoGrid(celulas) {
    const elementos = [];

    celulas.forEach((cell) => {
      [...cell.children].forEach((el) => {
        if (el.id) {
          atualizarPosicaoElemento(el, cell.dataset.row, cell.dataset.col);
          elementos.push(el);
        }
      });
    });

    return elementos;
  }

  async function atualizarGrid() {
    const elementos = coletarElementosDoGrid(celulas);

    const ocupadoMap = {};
    const novasPosicoes = [];

    // Recalcula posição para todos os elementos com base na nova grade
    elementos.forEach((el) => {
      const novaPosicao = recalcularPosicaoElemento(el, valueCols, ocupadoMap);
      novasPosicoes.push({ el, ...novaPosicao });
    });

    // Ajusta a quantidade de linhas necessária
    const maxRow = Math.max(...novasPosicoes.map((p) => p.row));
    valueRows = Math.max(valueRows, maxRow + 1); // +1 porque começa do 0

    criarGridTemplate(gridContainer);
    montarGrade(
      valueCols,
      valueRows,
      gridContainer,
      elementos,
      false,
      posicoes
    );

    if (modoEdicao) {
      document.querySelectorAll(".cell").forEach((el) => {
        el.classList.add("draggable-cell", "border");
      });
    }
  }

  function coletarPosicoes(celulas) {
    const elementos = coletarElementosDoGrid(celulas);
    const posicoes = {};

    elementos.forEach((el) => {
      if (el.id) {
        posicoes[el.id] = {
          row: parseInt(el.dataset.row, 10),
          col: parseInt(el.dataset.col, 10),
        };
      }
    });

    return posicoes;
  }

  function renderBotaoEditarGrid() {
    return `<div class="d-flex flex-row gap-2" id="botoesCancelaSalvaPosicoes">
              <button type="button" id="botaoSalvarEditarPosicoes" class="d-flex btn btn-primary btn-sm">Editar posições do mapa</button>
            </div>`;
  }

  function renderButtons() {
    controlGrids.style = "max-width: 250px;";
    return `<div class="d-flex flex-column text-center">
                <label for="col-value-column">Colunas</label>
                <div class="btn-group botao-incremento" role="group" aria-label="Botõe Incrementais">
                    <button type="button" class="btn btn-primary" id="buttonDecrementColumn">-</button>
                    <input type="text" class="text-center w-50" value="6" id="col-value-column" disabled>
                    <button type="button" class="btn btn-primary" id="buttonIncrementColumn">+</button>
                </div>
            </div>
            <div class="d-flex flex-column text-center">
                <label for="col-value-row">Linhas</label>
                <div class="btn-group botao-incremento" role="group" aria-label="Botõe Incrementais">
                    <button type="button" class="btn btn-primary" id="buttonDecrementRow">-</button>
                    <input type="text" class="text-center w-50" value="4" id="col-value-row" disabled>
                    <button type="button" class="btn btn-primary" id="buttonIncrementRow">+</button>
                </div>
            </div>`;
  }

  function renderCancelarButton() {
    return `<button type="button" id="botaoCancelarPosicoes" class="d-flex btn btn-secondary btn-sm">Cancelar</button>`;
  }

  function renderSpinnerLoading() {
    return `
        <div class="dot-loader">
          <span></span>
          <span></span>
          <span></span>
        </div>`;
  }

  function renderCaptionsTable() {
    return `<div class="container-fluid text-start">
              <a type="button" onclick="imprimirPagina();">Imprimir</a>
            </div>
            <div class="container-fluid text-center">
                <a data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Exportar a tabela de patrimônios desta sala para o Excel" type="button" class="no-print" onclick="exportarParaExcel();">Exportar tabela</a>
            </div>
            <div class="container-fluid text-end">
              <a data-bs-toggle="tooltip" 
              data-bs-placement="top" 
              data-bs-title="Copiar os patrimônios da coluna máquina" 
              type="button" 
              class="no-print" 
              onclick="copiarColunaMaquina()">Copiar Patrimônios</a>
              <div class="toast-container position-fixed bottom-0 end-0 p-3 border-0">
                <div id="copyToast" class="toast bg-body" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="10000">
                  <div class="toast-header text-bg-primary">
                    <strong class="me-auto">Mensagem</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                  </div>
                  <div class="d-flex flex-column text-start">
                    <div class="toast-body" id="copyToastText"></div>
                  </div>
                </div>
              </div>
            </div>`;
  }

  function renderAlert() {
    return `<div class="alert alert-success alert-dismissible fade show" role="alert">
              <p><i class="fa-solid fa-circle-check fa-lg pe-1"></i><strong>Todas as posições foram alinhas com sucesso.</strong></p>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            `;
  }

  function buttonsEditTemplate() {
    const counterInputColumns = document.getElementById("col-value-column");
    const counterInputRows = document.getElementById("col-value-row");
    const buttonIncrementColumn = document.getElementById(
      "buttonIncrementColumn"
    );
    const buttonDecrementColumn = document.getElementById(
      "buttonDecrementColumn"
    );
    const buttonIncrementRow = document.getElementById("buttonIncrementRow");
    const buttonDecrementRow = document.getElementById("buttonDecrementRow");

    function getMinLinhasNecessarias() {
      const totalElementos = elementos.length;
      return Math.ceil(totalElementos / valueCols);
    }

    function isUltimaLinhaVazia() {
      const startIndex = (valueRows - 1) * valueCols;
      for (let i = startIndex; i < startIndex + valueCols; i++) {
        const cell = celulas[i];
        if (cell && cell.children.length > 0) {
          return false;
        }
      }
      return true;
    }

    function atualizarBotoes() {
      const minLinhas = getMinLinhasNecessarias();

      buttonIncrementColumn.disabled = valueCols >= 9;
      buttonDecrementColumn.disabled = valueCols <= 3;

      buttonIncrementRow.disabled = valueRows >= 8;

      const podeDiminuirLinha = valueRows > minLinhas && isUltimaLinhaVazia();
      buttonDecrementRow.disabled = !podeDiminuirLinha;
    }

    // Observa mudanças na última linha
    function observarUltimaLinha() {
      const observer = new MutationObserver(() => {
        atualizarBotoes();
      });

      const startIndex = (valueRows - 1) * valueCols;
      for (let i = startIndex; i < startIndex + valueCols; i++) {
        const cell = celulas[i];
        if (cell) observer.observe(cell, { childList: true, subtree: true });
      }
    }

    // Incrementar colunas
    buttonIncrementColumn.addEventListener("click", () => {
      if (valueCols < 9) {
        valueCols++;
      }

      const minLinhas = getMinLinhasNecessarias();
      if (valueRows < minLinhas) {
        valueRows = minLinhas;
      }

      counterInputColumns.value = valueCols;
      counterInputRows.value = valueRows;
      atualizarGrid();
      atualizarBotoes();
      observarUltimaLinha();
    });

    // Decrementar colunas
    buttonDecrementColumn.addEventListener("click", () => {
      if (valueCols > 3) {
        valueCols--;
      }

      const minLinhas = getMinLinhasNecessarias();
      if (valueRows < minLinhas) {
        valueRows = minLinhas;
      }

      counterInputColumns.value = valueCols;
      counterInputRows.value = valueRows;
      atualizarGrid();
      atualizarBotoes();
      observarUltimaLinha();
    });

    // Incrementar linhas
    buttonIncrementRow.addEventListener("click", () => {
      if (valueRows < 12) {
        valueRows++;
      }

      counterInputRows.value = valueRows;
      atualizarGrid();
      atualizarBotoes();
      observarUltimaLinha();
    });

    // Decrementar linhas
    buttonDecrementRow.addEventListener("click", () => {
      const minLinhas = getMinLinhasNecessarias();
      if (valueRows > minLinhas && isUltimaLinhaVazia()) {
        valueRows--;
      }

      counterInputRows.value = valueRows;
      atualizarGrid();
      atualizarBotoes();
      observarUltimaLinha();
    });
  }

  function montarPayload(salaUrl, linhas, colunas, posicoes) {
    const payload = {
      sala: salaUrl,
      linhas: linhas,
      colunas: colunas,
      posicoes: posicoes,
    };

    return payload;
  }
}

// Função para copiar o texto da coluna máquina
async function copiarColunaMaquina() {
  // Seleciona a tabela
  const tabela = document.querySelector("#tabela-patrimonios");
  const colunaMaquinaIndex = 2; // Índice da coluna "Maquina"
  const colunaModeloIndex = 1;
  let valores = [];

  // Itera pelas linhas da tabela, ignorando o cabeçalho, impressoras e projetores
  for (let i = 1; i < tabela.rows.length; i++) {
    let celulaMaquina = tabela.rows[i].cells[colunaMaquinaIndex];
    let celulaModelo = tabela.rows[i].cells[colunaModeloIndex];
    if (
      !celulaMaquina.innerText.startsWith("COPY") &&
      !celulaModelo.innerText.startsWith("Projetor") &&
      !celulaMaquina.innerText.startsWith("-")
    ) {
      valores.push("• " + celulaMaquina.innerText.trim());
    }
  }
  // Junta os valores com ";" exceto o último
  const resultado = valores.join(";\n") + "\n";

  // Inicializa a variável vazia para a mensagem
  let mensagem = "";

  try {
    await navigator.clipboard.writeText(resultado);
    mensagem =
      "<span class='text-body'>Todos os patrimônios da coluna <b>'Máquina'</b> foram copiados para a área de transferência!</span><p class='fs-xsmall text-danger m-0'>* Impressoras e projetores foram ignorados.</p>";
  } catch (err) {
    mensagem =
      "<span class='text-body'><span class='fw-bold text-danger m-0'>Erro: </span>Os patrimônios da coluna <b>'Máquina'</b> não puderam ser copiados.</span>";
  }

  // Exibe o a mensagem em Toast
  mostrarToast(mensagem);
}

// Função para copiar o título dos softwares
async function copiarSoftwares() {
  const spans = document.querySelectorAll(".nome_software");
  let valores = [];

  spans.forEach((span) => {
    let texto = span.innerText.trim();
    if (texto) {
      valores.push("• " + texto);
    }
  });

  // Junta os valores com ";" exceto o último
  const resultado = valores.join(";\n") + "\n";

  // Inicializa a variável vazia para a mensagem
  let mensagem = "";

  try {
    await navigator.clipboard.writeText(resultado);
    mensagem =
      "<span class='text-body'>Todos os softwares foram copiados para a área de transferência!</span>";
  } catch (err) {
    mensagem =
      "<span class='text-body'><span class='fw-bold text-danger m-0'>Erro: </span>Não foi possível copiar os softwares.</span>";
  }

  // Exibe o a mensagem em Toast
  mostrarToast(mensagem);
}

//Função para mostrar a senha no login
function mostrarSenha() {
  const senha = document.querySelector("#senha");
  const hide = document.querySelector("#hide");
  const unhide = document.querySelector("#unhide");

  if (senha.type === "password") {
    senha.type = "text";
    hide.style.display = "none";
    unhide.style.display = "block";
  } else {
    senha.type = "password";
    hide.style.display = "block";
    unhide.style.display = "none";
  }
}

// Converter para maiúsculo
function converterParaMaisculas(input) {
  input.value = input.value.toUpperCase();
}

// Função para ativar o imprimir do navegador
function imprimirPagina() {
  window.print();
}

// Função que ativa os toggles no editar e cadastrar patrimônio
function toggleInputs() {
  // Status do monitor e computador
  const statusPc = document.querySelector("#status_pc");
  const statusMonitor = document.querySelector("#status_monitor");

  // Alterna as informações do computador com base no status
  const toggleExtraInputPc = () => {
    const patrimonioMaquina = document.querySelector("#maquina");
    const modeloMaquina = document.querySelector("#modelo_maquina");
    const divExtraPc = document.querySelector("#forms_extra_pc");
    const reservaPc = document.querySelector("#reserva_pc_id");
    const reservaModeloPc = document.querySelector("#reserva_modelo_pc_id");
    const obsPc = document.querySelector("#obs_pc_id");
    if (statusPc.value !== "funcionando") {
      patrimonioMaquina.readOnly = true;
      modeloMaquina.readOnly = true;
      patrimonioMaquina.required = false;
      modeloMaquina.required = false;
      patrimonioMaquina.style =
        "background-color: var(--bs-secondary-bg); opacity: 1;";
      modeloMaquina.style =
        "background-color: var(--bs-secondary-bg); opacity: 1;";
      divExtraPc.classList =
        "d-flex flex-column my-1 p-2 bg-body-tertiary border rounded-3 gap-1"; // Mostrar o input
    } else {
      patrimonioMaquina.readOnly = false;
      modeloMaquina.readOnly = false;
      patrimonioMaquina.required = true;
      modeloMaquina.required = true;
      patrimonioMaquina.style = "";
      modeloMaquina.style = "";
      divExtraPc.classList = "d-none"; // Ocultar o input
      reservaPc.value = "";
      reservaModeloPc.value = "";
      obsPc.value = "";
    }
  };

  // Alterna as informações do monitor com base no status
  const toggleExtraInputMonitor = () => {
    const patrimonioMonitor = document.querySelector("#monitor");
    const modeloMonitor = document.querySelector("#modelo_monitor");
    const divExtraMonitor = document.querySelector("#forms_extra_monitor");
    const reservaMonitor = document.querySelector("#reserva_monitor_id");
    const reservaModeloMonitor = document.querySelector(
      "#reserva_modelo_monitor_id"
    );
    const obsMonitor = document.querySelector("#obs_monitor_id");
    if (statusMonitor.value !== "funcionando") {
      patrimonioMonitor.readOnly = true;
      modeloMonitor.readOnly = true;
      patrimonioMonitor.required = false;
      modeloMonitor.required = false;
      patrimonioMonitor.style =
        "background-color: var(--bs-secondary-bg); opacity: 1;";
      modeloMonitor.style =
        "background-color: var(--bs-secondary-bg); opacity: 1;";
      divExtraMonitor.classList =
        "d-flex flex-column my-1 p-2 bg-body-tertiary border rounded-3 gap-1"; // Mostrar o input
    } else {
      patrimonioMonitor.readOnly = false;
      modeloMonitor.readOnly = false;
      patrimonioMonitor.required = true;
      modeloMonitor.required = true;
      patrimonioMonitor.style = "";
      modeloMonitor.style = "";
      divExtraMonitor.classList = "d-none"; // Ocultar o input
      reservaMonitor.value = "";
      reservaModeloMonitor.value = "";
      obsMonitor.value = "";
    }
  };
  // Adiciona um eventListener para alternar
  statusPc.addEventListener("change", toggleExtraInputPc);
  statusMonitor.addEventListener("change", toggleExtraInputMonitor);

  // Inicializar o estado correto do input ao carregar a página
  toggleExtraInputPc();
  toggleExtraInputMonitor();
}

// Carregar os logs
function logs(tipoLog) {
  let logsCache = [];

  // Mapeamento de campos para cada tipo de log
  const colunasMap = {
    patrimonios: [
      "num",
      "modelo_maquina",
      "maquina",
      "modelo_monitor",
      "monitor",
      "mostrar",
      "log",
    ],
    modelos: [
      "titulo",
      "descricao",
      "mostrar",
      "log"
    ],
  };

  // Função para criar/atualizar tabela
  const carregarLogs = async (sala) => {
    try {
      const url = `logs_${tipoLog}.php?sala=${encodeURIComponent(sala)}`;
      const resposta = await fetch(url);
      const logs = await resposta.json();

      const tableBody = document.getElementById("logTableBody");

      if (!logs || logs.length === 0) {
        tableBody.innerHTML = `<tr><td colspan="${colunasMap[tipoLog].length}" class="text-center">Nenhum log encontrado.</td></tr>`;
        return;
      }

      if(tipoLog == "patrimonios"){
        logs.sort((a, b) => a.num - b.num);
      }else{
        logs.sort((a, b) => a.id - b.id);
      }
      

      let tableUpdated = false;

      logs.forEach((log) => {
        const logExistente = logsCache.find((l) => l.id === log.id);

        if (logExistente) {
          const row = document.querySelector(`#log-row-${logExistente.id}`);
          if (row) {
            colunasMap[tipoLog].forEach((campo, idx) => {
              row.cells[idx].innerHTML = formatarCampo(campo, log);
            });
          }
          Object.assign(logExistente, log);
          tableUpdated = true;
        } else {
          logsCache = [];
          const oldRow = document.querySelector(`#log-row-${log.id}`);
          if (oldRow) oldRow.remove();

          const row = document.createElement("tr");
          row.classList = "align-middle text-center";
          row.id = `log-row-${log.id}`;

          let campos;

          row.innerHTML = colunasMap[tipoLog]
            .map((campo) => {
              switch (campo) {
                case 'log':
                case 'descricao':
                  campos = `<td class="text-start"><div style="max-height: 300px; overflow: auto;">${formatarCampo(campo, log)}</div></td>`
                  break
                case 'num':
                  campos = `<th scope='row'>${formatarCampo(campo, log)}</th>`
                  break
                case 'mostrar':
                  campos = `<td>${formatarCampo(campo, log) ? 'Sim' : 'Não'}</td>`
                  break
                default:
                  campos = `<td>${formatarCampo(campo, log)}</td>`
                  break
              }
              return campos
          }).join("");

          tableBody.appendChild(row);
          logsCache.push(log);
          tableUpdated = true;
        }
      });

      if (tableUpdated) {
        const loadingRow = tableBody.querySelector("tr");
        if (loadingRow && loadingRow.innerHTML.includes("Carregando logs...")) {
          loadingRow.remove();
        }
      }
    } catch (error) {
      console.error("Erro ao buscar logs:", error);
    }
  };

  // Função para formatar campos (coloque aqui regras específicas)
  const formatarCampo = (campo, log) => {
    if (campo === "num") {
      return log.num === "-" ? "Modelo" : log.num;
    }
    if (campo === "log") {
      return log.log
        .split(/\n/)
        .reduce((acc, linha) => {
          if (
            /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} - \[\d+\]/.test(linha)
          ) {
            acc.push(
              linha
                .replace(
                  /^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} - \[\d+\]) - LOG:/,
                  '<span style="color:#0069af;">$1 - LOG:</span>'
                )
                .replace(
                  /^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2} - \[\d+\]) - Ação:/,
                  '<span style="color:#0069af;">$1 - Ação:</span>'
                )
            );
          } else {
            acc[acc.length - 1] += `${linha.trim()}`;
          }
          return acc;
        }, [])
        .join("<br>");
    }
    return log[campo] || "";
  };

  // Função para selecionar sala
  const selecionarSala = (sala) => {
    logsCache = [];
    const tableBody = document.getElementById("logTableBody");
    tableBody.innerHTML = `<tr><td colspan="${colunasMap[tipoLog].length}" class="text-center">Carregando logs...</td></tr>`;
    carregarLogs(sala);
  };

  const salaSelect = document.getElementById("salaSelect");
  salaSelect.addEventListener("change", (e) => selecionarSala(e.target.value));

  selecionarSala(salaSelect.value);
}

// Função para pesquisar os softwares sem recarregar a página
function pesquisarSoftwares() {
  const input = document.querySelector("#search_software"); // Campo de busca
  const softwaresContainer = document.querySelector(".softwares"); // Container dos softwares

  // Caso não exista a classe softwares na página, retorna sem carregar o resto
  if (!softwaresContainer) {
    return;
  }

  const accordionItems = softwaresContainer.querySelectorAll(".accordion-item"); // Itens do acordeão

  // Cria a mensagem de "Nenhum resultado encontrado"
  let noResultsMessage = document.createElement("div");
  noResultsMessage.textContent = "Nenhum resultado encontrado.";
  noResultsMessage.className = "text-center text-secondary border rounded p-3";
  noResultsMessage.style.display = "none"; // Inicialmente oculto
  softwaresContainer.appendChild(noResultsMessage);

  input.addEventListener("keyup", function () {
    const value = input.value.toLowerCase(); // Valor digitado no campo de busca
    let hasResults = false;

    accordionItems.forEach(function (item) {
      const button = item.querySelector(".accordion-button"); // Botão do acordeão
      if (button && button.textContent.toLowerCase().includes(value)) {
        item.style.display = ""; // Mostra o item
        hasResults = true;
      } else {
        item.style.display = "none"; // Oculta o item
      }
    });

    // Mostra ou oculta a mensagem "Nenhum resultado encontrado"
    noResultsMessage.style.display = hasResults ? "none" : "block";
  });
}

// O dropdown para que apareça o login ao clicar no ícone
function loginDropdown() {
  const userDropdown = document.querySelector("#userDropdown");
  const dropdownMenu = userDropdown.querySelector(".dropdown-menu");
  userDropdown.classList.add("show");
  dropdownMenu.classList.add("show");
  dropdownMenu.style.position = "absolute";
  dropdownMenu.style.inset = "0px 0px auto auto";
  dropdownMenu.style.margin = "0px";
  dropdownMenu.style.transform = "translate3d(-0.5px, 22px, 0px)";
  dropdownMenu.style.boxShadow = "3px 3px 9px 1px rgb(0, 0, 0, 0.2)";

  // Adiciona um evento de clique ao documento
  document.addEventListener("click", function (event) {
    // Verifica se o clique foi fora do dropdown
    if (!userDropdown.contains(event.target)) {
      userDropdown.classList.remove("show");
      dropdownMenu.classList.remove("show");
    }
  });
}

// Função para trocar patrimônios de local
async function trocarPatrim() {
  // Carrega o spinner antes de tudo
  const spinner = document.getElementById("loading_spinner");
  spinner.innerHTML = spinnerLoading(); // Coloca no DOM o spinner

  // Utilitários
  let dadosSelecionados;
  const salaSelect = document.getElementById("salaSelect");
  const patrimSelect = document.getElementById("patrimSelect");
  const idPatrimonio = new URLSearchParams(window.location.search).get("id");
  const dadosAntigos = await getInfoPatrimonio(idPatrimonio);
  const radioOptions = document.querySelectorAll(
    'input[name="inlineRadioOptions"]'
  );
  const infoPatrimonioAntigo = document.getElementById(
    "info-patrimonio-antigo"
  );
  const infoPatrimonioSelecionado = document.getElementById("info-patrimonio");
  const containerMoverPatrimonio = document.getElementById(
    "button-mover-patrimonio"
  );

  // Pega os dados através do ID na URL e monta as informações
  const predioSelect = document.getElementById("predioSelect");
  const tipoSelecionado =
    document.querySelector('input[name="inlineRadioOptions"]:checked')?.value ||
    "patrim_maquina";
  const bttnCancelar = document.getElementById("button-cancelar");

  // Cria o botão de cancelar
  bttnCancelar.appendChild(botaoCancelar());

  // Ao clicar, redireciona para a página de origem
  bttnCancelar.addEventListener("click", function (e) {
    e.preventDefault();
    window.location.href = `/labs/v4/mapas/public/salas?sala=${encodeURIComponent(
      dadosAntigos.sala
    )}`;
  });

  // Inicia com as informações vazias onde haverá os dados do patrimonio selecionado, dependendo do tipo escolhido
  infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);

  // Se já tem um valor no select do prédio (restaurado pelo navegador), dispara o evento de change
  // Espera um momento para garantir que os listeners foram adicionados
  setTimeout(() => {
    if (predioSelect.value) {
      predioSelect.dispatchEvent(new Event("change"));
    }
  }, 0);

  dadosSelecionados = dadosAntigos;
  if (dadosSelecionados) {
    spinner.innerHTML = "";
    infoPatrimonioAntigo.innerHTML = renderizarInfoPatrimonio(
      tipoSelecionado,
      dadosSelecionados
    );
    inicializarPopovers();
  }

  // Coloca as opções em cada select ao carregar a função
  document.getElementById("salaSelect").innerHTML =
    '<option value="" disabled selected>Selecione um prédio primeiro</option>';
  document.getElementById("patrimSelect").innerHTML =
    '<option value="" disabled selected>Selecione uma sala primeiro</option>';

  // Ao trocar para monitor, atualiza as informações com base no radio
  radioOptions.forEach((radio) => {
    radio.addEventListener("change", async function () {
      const idPatrimonio = new URLSearchParams(window.location.search).get(
        "id"
      );
      const tipoSelecionado =
        document.querySelector('input[name="inlineRadioOptions"]:checked')
          ?.value || "patrim_maquina";

      // Se nenhum patrimônio estiver selecionado, limpa e retorna
      limpaHtml(idPatrimonio, infoPatrimonioAntigo, containerMoverPatrimonio);

      // Busca os dados relacionados do patrimonio antigo e renderiza no DOM
      const dadosAntigos = await getInfoPatrimonio(idPatrimonio);
      dadosSelecionados = dadosAntigos;
      if (dadosSelecionados) {
        infoPatrimonioAntigo.innerHTML = renderizarInfoPatrimonio(
          tipoSelecionado,
          dadosSelecionados
        );
        inicializarPopovers();
      }
    });
  });

  // Eventos
  predioSelect.addEventListener("change", function () {
    const predioSelect = this.value;
    const salaSelect = document.getElementById("salaSelect");
    const patrimSelect = document.getElementById("patrimSelect");

    const tipoSelecionado =
      document.querySelector('input[name="inlineRadioOptions"]:checked')
        ?.value || "patrim_maquina";
    infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);
    containerMoverPatrimonio.innerHTML = "";

    if (!predioSelect) return;

    salaSelect.innerHTML =
      '<option value="" disabled selected>Selecione um prédio primeiro</option>';
    patrimSelect.innerHTML =
      '<option value="" disabled selected>Selecione uma sala primeiro</option>';

    fetch(
      `../api/get_salas_por_predio.php?predio=${encodeURIComponent(
        predioSelect
      )}`
    )
      .then((res) => res.json())
      .then((data) => {
        salaSelect.innerHTML =
          '<option value="" disabled selected>Selecione uma sala</option>';
        data.forEach(({ link, bloco, sala }) => {
          const opt = new Option(`Bloco ${bloco} - Sala ${sala}`, link);
          salaSelect.appendChild(opt);
        });
      });
    // Durante o Desenvolvimento
    /* .catch(err => console.error('Erro ao carregar salas:', err)); */
  });

  // Evento: mudança de sala
  salaSelect.addEventListener("change", atualizarSelectPatrimonios);

  // Evento: mudança de tipo de patrimônio (máquina ou monitor)
  radioOptions.forEach((radio) => {
    radio.addEventListener("change", () => {
      // Limpa a visualização e selects ao trocar entre máquina/monitor
      const tipoSelecionado =
        document.querySelector('input[name="inlineRadioOptions"]:checked')
          ?.value || "patrim_maquina";
      infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);
      containerMoverPatrimonio.innerHTML = "";

      // Evita bug de exibir dados antigos
      dadosSelecionados = "";

      // Atualiza os patrimônios com base no novo tipo selecionado
      atualizarSelectPatrimonios();
    });
  });

  // Evento para preencher as informações no "info-patrimonio"
  patrimSelect.addEventListener("change", async function () {
    const idPatrimonio = this.value;
    const dados = await getInfoPatrimonio(idPatrimonio);
    const dadosAntigos = await getInfoPatrimonio(
      new URLSearchParams(window.location.search).get("id")
    );

    // Se nenhum patrimônio estiver selecionado, limpa e retorna
    limpaHtml(idPatrimonio, infoPatrimonioAntigo, containerMoverPatrimonio);

    dadosSelecionados = dados; // salva os dados para o radio usar
    const tipoSelecionado = document.querySelector('input[name="inlineRadioOptions"]:checked')
        ?.value || "patrim_maquina";
    infoPatrimonioSelecionado.innerHTML = renderizarInfoPatrimonio(
      tipoSelecionado,
      dadosSelecionados
    );

    // Com base no tipo selecionado, ele impossibilita do botão renderizar
    if (tipoSelecionado === "patrim_maquina") {
      if (dados.maquina === dadosAntigos.maquina) {
        containerMoverPatrimonio.innerHTML = "";
        inicializarPopovers();
        return;
      }
    } else {
      if (dados.monitor === dadosAntigos.monitor) {
        containerMoverPatrimonio.innerHTML = "";
        inicializarPopovers();
        return;
      }
    }

    // Botão e seu modal para fazer a troca do patrimônio
    containerMoverPatrimonio.innerHTML = botaoMoverComModal();

    inicializarPopovers();

    // Evento ao clicar em "Mover Patrimônio"
    document
      .getElementById("btnAtualizarInfo")
      .addEventListener("click", () => {
        const tipoSelecionado =
          document.querySelector('input[name="inlineRadioOptions"]:checked')
            ?.value || "patrim_maquina";
        const payload = montarPayload(dadosAntigos, dados, tipoSelecionado);
        fetch("../api/set_info_patrimonio.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams(payload),
        })
          .then((res) => res.json())
          .then((result) => {
            if (result.status === "ok") {
              window.location.href = `/labs/v4/mapas/public/salas?sala=${payload.sala_atual}`;
            } else {
              alert("Erro ao atualizar: " + result.mensagem);
            }
          });
        // Durante o Desenvolvimento
        /* .catch(err => console.error('Erro ao atualizar:', err)); */
      });
  });

  // Listener para os radios
  radioOptions.forEach((radio) => {
    radio.addEventListener("change", function () {
      if (dadosSelecionados) {
        infoPatrimonioSelecionado.innerHTML = renderizarInfoPatrimonio(
          this.value,
          dadosSelecionados
        );
      }
    });
  });

  /* Funções */
  function salaFormatada(sala) {
    if (!sala) {
      return "Sala não encontrada";
    }
    
    const salaSelecionada = sala.split("-")
    const salaPredioBloco = {
      predio: salaSelecionada[0],
      bloco: salaSelecionada[1].toUpperCase(),
      sala: (salaSelecionada[3]) ? `${salaSelecionada[2]}.${salaSelecionada[3]}` : salaSelecionada[2]
    }
    
    return Object.values(salaPredioBloco).join("/")
  }

  function limpaHtml(
    idPatrimonio,
    infoPatrimonioSelecionado,
    containerMoverPatrimonio
  ) {
    if (!idPatrimonio || idPatrimonio === "" || isNaN(parseInt(idPatrimonio))) {
      const tipoSelecionado =
        document.querySelector('input[name="inlineRadioOptions"]:checked')
          ?.value || "patrim_maquina";
      infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);
      containerMoverPatrimonio.innerHTML = "";
      return;
    }
  }

  function discoDisponivel(disco) {
    return (
      {
        nenhum: "Sem Disco",
        ssd250: "SSD 250GB",
        ssd480: "SSD 480GB",
        ssd500: "SSD 500GB",
        ssd1000: "SSD 1TB",
        hd250: "HD 250 GB",
        hd320: "HD 320 GB",
        hd500: "HD 500GB",
        hd1000: "HD 1TB",
      }[disco] || "Disco não encontrado"
    );
  }

  function popOverConteudo(
    status,
    patrimonio = "",
    modelo = "",
    reserva_patrimonio = "",
    reserva_modelo = "",
    obs = ""
  ) {
    const titulos = {
      manutencao: "Manutenção",
      defeito: "Defeito",
      reserva: "Reserva",
    };

    const titulo = titulos[status] || "-";
    const modelo_anterior =
      reserva_modelo !== modelo && reserva_modelo !== ""
        ? `<span class="mb-2"><strong>Modelo anterior:</strong> ${modelo}</span>`
        : "";

    const patrimonio_anterior =
      reserva_patrimonio !== patrimonio && reserva_patrimonio !== ""
        ? `<span class="mb-2"><strong>Patrim. anterior:</strong> ${patrimonio}</span>`
        : "";

    const observacoes = obs ? `<span><strong>Obs:</strong> ${obs}</span>` : "";

    if (observacoes || patrimonio_anterior || modelo_anterior) {
      const conteudo = `<div class="d-flex flex-column">${modelo_anterior}${patrimonio_anterior}${observacoes}</div>`;
      return `
      <a tabindex = "0" role = "button" data-bs-toggle="popover" data-bs-title="Status - ${titulo}" data-bs-html="true" data-bs-content='${conteudo.replace(
        /'/g,
        "&apos;"
      )}'><i class="fa-solid fa-circle-info no-print"></i></a>`;
    }
    // Garantir retorno mesmo sem conteúdo
    return "";
  }

  function obterHtmlSemInfo(tipoSelecionado) {
    const htmlSemInfoMaquina = `
        <div class="d-flex flex-column align-items-center m-2 bg-body shadow-sm rounded-3 border border-light-subtle w-100">
          <div class="container icone-salas"><i class="fa-solid fa-desktop fa-2xl" aria-hidden="true"></i></div>
          <div class="d-flex justify-content-center container text-center border-bottom"><span class="fw-bold">Sala: -</span></div>
          <div class="d-flex justify-content-center container text-center texto-search border-bottom p-1"><span class="fw-bold">ID: -</span></div>
          <div class="p-1 w-100 border-bottom">
            <span class="d-flex justify-content-center fw-bold">Máquina</span>
            <div class="d-flex flex-row bg-body-tertiary border rounded-3">
              <div class="d-flex flex-column justify-content-center container text-center texto-search rounded-start-3 p-1">
                <span class="d-flex justify-content-center fw-bold">Modelo</span>
                <span class="d-flex justify-content-center">-</span>
              </div>
              <div class="d-flex flex-column justify-content-center container text-center texto-search rounded-end-3 p-1">
                <span class="d-flex justify-content-center fw-bold">Patrimônio</span>
                <span class="d-flex flex-row justify-content-evenly">-</span>
              </div>
            </div>
          </div>
          <div class="d-flex flex-row w-100 flex-grow-1">
            <div class="d-flex flex-column justify-content-center container text-center mb-1 texto-search p-1">
              <span class="d-flex justify-content-center fw-bold">Disco</span>
              <span class="d-flex justify-content-center">-</span>
            </div>
          </div>
        </div>
        `;

    const htmlSemInfoMonitor = `
        <div class="d-flex flex-column align-items-center m-2 bg-body shadow-sm rounded-3 border border-light-subtle w-100">
          <div class="container icone-salas"><i class="fa-solid fa-desktop fa-2xl" aria-hidden="true"></i></div>
          <div class="d-flex justify-content-center container text-center border-bottom"><span class="fw-bold">Sala: -</span></div>
          <div class="d-flex justify-content-center container text-center texto-search border-bottom p-1"><span class="fw-bold">ID: -</span></div>
          <div class="p-1 w-100">
            <span class="d-flex justify-content-center fw-bold">Monitor</span>
            <div class="d-flex flex-row bg-body-tertiary border rounded-3">
              <div class="d-flex flex-column justify-content-center container text-center texto-search rounded-start-3 p-1">
                <span class="d-flex justify-content-center fw-bold">Modelo</span>
                <span class="d-flex justify-content-center">-</span>
              </div>
              <div class="d-flex flex-column justify-content-center container text-center texto-search rounded-end-3 p-1">
                <span class="d-flex justify-content-center fw-bold">Patrimônio</span>
                <span class="d-flex flex-row justify-content-evenly">-</span>
              </div>
            </div>
          </div>
        </div>
        `;
    return tipoSelecionado === "patrim_monitor"
      ? htmlSemInfoMonitor
      : htmlSemInfoMaquina;
  }

  function renderizarInfoPatrimonio(tipo, dados) {
    if (!dados) return;

    const status =
      tipo === "patrim_monitor"
        ? (dados.status_monitor || "").toLowerCase()
        : (dados.status_pc || "").toLowerCase();
    const obs = tipo === "patrim_monitor" ? dados.obs_monitor : dados.obs_pc;

    let modelo, patrimonio, reserva_modelo, reserva_patrimonio;

    if (tipo === "patrim_monitor") {
      modelo = dados.modelo_monitor;
      patrimonio = dados.monitor;
      reserva_modelo = dados.reserva_modelo_monitor?.trim() || "";
      reserva_patrimonio = dados.reserva_monitor?.trim() || "";
    } else {
      modelo = dados.modelo_maquina;
      patrimonio = dados.maquina;
      reserva_modelo = dados.reserva_modelo_pc?.trim() || "";
      reserva_patrimonio = dados.reserva_pc?.trim() || "";
    }

    const status_text = obterClasseStatus(status);

    const modelo_render =
      status !== "funcionando" && reserva_modelo !== ""
        ? reserva_modelo
        : modelo;
    const patrimonio_render =
      status !== "funcionando" && reserva_patrimonio !== ""
        ? reserva_patrimonio
        : patrimonio;

    const pop =
      status !== "funcionando"
        ? popOverConteudo(
            status,
            patrimonio,
            modelo,
            patrimonio_render,
            modelo_render,
            obs
          )
        : "";

    const html = `
        <div class="d-flex flex-column align-items-center m-2 bg-body shadow-sm rounded-3 border border-light-subtle w-100">
        <div class="container icone-salas"><i class="fa-solid fa-desktop fa-2xl" aria-hidden="true"></i></div>
        <div class="d-flex justify-content-center container text-center border-bottom"><span class="fw-bold">Sala: ${salaFormatada(
          dados.sala
        )}</    span></div>
        <div class="d-flex justify-content-center container text-center texto-search border-bottom p-1"><span class="fw-bold">ID: ${
          dados.num
        }</    span></div>
        <div class="p-1 w-100 ${
          tipo === "patrim_monitor" ? "" : "border-bottom"
        }">
          <span class="d-flex justify-content-center fw-bold">${
            tipo === "patrim_monitor" ? "Monitor" : "Máquina"
          }</span>
          <div class="d-flex flex-row bg-body-tertiary border rounded-3">
            <div class="d-flex flex-column justify-content-center container text-center texto-search ${status_text} rounded-start-3 p-1">
              <span class="fw-bold">Modelo</span>
              <span>${modelo_render}</span>
            </div>
            <div class="d-flex flex-column justify-content-center container text-center texto-search ${status_text} rounded-end-3 p-1">
              <span class="fw-bold">Patrimônio</span>
              <span id="patrimonio">${patrimonio_render} ${pop}</span>
            </div>
          </div>
        </div>
        ${
          tipo === "patrim_monitor"
            ? ""
            : `
          <div class="d-flex flex-row w-100 flex-grow-1">
            <div class="d-flex flex-column justify-content-center container text-center mb-1 texto-search p-1">
              <span class="fw-bold">Disco</span>
              <span>${discoDisponivel(dados.disco)}</span>
            </div>
          </div>
          </div>`
        }
        `;

    return html;
  }

  async function getInfoPatrimonio(id) {
    const res = await fetch(`../api/get_info_patrimonio.php?id=${id}`);
    return res.json();
  }

  function spinnerLoading() {
    return `
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Carregando...</span>
        </div>`;
  }

  function botaoMoverComModal() {
    return `<div class="d-flex justify-content-center">
              <button class="w-100 btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTrocaPatrim">Mover Patrimônio</button>
            </div>
            <div class="modal fade" id="modalTrocaPatrim" tabindex="-1" aria-labelledby="modalTrocaPatrimLabel" aria-hidden="true">
              <div class="modal-dialog"><div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5 fw-bold" id="modalTrocaPatrimLabel">Aviso</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <span class="lh-sm">Ao clicar em <b>confirmar</b>, as informações do patrimônio serão transferidas para o <b>ID</b> do outro patrimônio e vice-versa.</span>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-primary" id="btnAtualizarInfo">Confirmar</button>
                </div>
              </div></div>
            </div>`;
  }

  function botaoCancelar() {
    const a = document.createElement("a");

    a.classList = "w-100 btn btn-secondary";
    a.role = "button";
    a.text = "Cancelar";
    a.href = "#";

    return a;
  }

  function obterPatrimonio(item, tipoSelecionado) {
    if (tipoSelecionado === "patrim_monitor") {
      return item.reserva_monitor && item.reserva_monitor !== "null"
        ? item.reserva_monitor
        : item.monitor;
    } else {
      return item.reserva_pc && item.reserva_pc !== "null"
        ? item.reserva_pc
        : item.maquina;
    }
  }

  function obterModelo(item, tipoSelecionado) {
    if (tipoSelecionado === "patrim_monitor") {
      return item.reserva_modelo_monitor &&
        item.reserva_modelo_monitor !== "null"
        ? item.reserva_modelo_monitor
        : item.modelo_monitor;
    } else {
      return item.reserva_modelo_pc && item.reserva_modelo_pc !== "null"
        ? item.reserva_modelo_pc
        : item.modelo_maquina;
    }
  }

  function obterStatus(item, tipoSelecionado) {
    return tipoSelecionado === "patrim_monitor"
      ? item.status_monitor
      : item.status_pc;
  }

  function filtroPatrimonio(patrimonio, filtrarPorCopy) {
    if (!patrimonio) return false;
    const patrimonioLower = patrimonio.toLowerCase();

    return filtrarPorCopy
      ? patrimonioLower.startsWith("copy")
      : !patrimonioLower.startsWith("copy");
  }

  // Atualiza os patrimônios conforme a sala e o tipo (máquina/monitor)
  function atualizarSelectPatrimonios() {
    const sala = salaSelect.value;
    const tipoSelecionado =
      document.querySelector('input[name="inlineRadioOptions"]:checked')
        ?.value || "patrim_maquina";
    infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);

    if (!sala) {
      patrimSelect.innerHTML =
        '<option value="" disabled selected>Selecione uma sala primeiro</option>';
      containerMoverPatrimonio.innerHTML = "";
      return;
    }

    const patrimonioTexto = document.getElementById("patrimonio")?.textContent || ""; // ajuste aqui conforme seu HTML
    const filtrarPorCopy = patrimonioTexto.toLowerCase().startsWith("copy");

    fetch(`../api/get_patrimonios.php?sala=${encodeURIComponent(sala)}`)
      .then((res) => res.json())
      .then((data) => {
        if (!data || data.length === 0) {
          infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);
          patrimSelect.innerHTML = '<option value="" disabled selected>Nenhum patrimônio encontrado</option>';
          return;
        } else {
          containerMoverPatrimonio.innerHTML = "";
          patrimSelect.innerHTML = '<option value="" disabled selected>Selecione um patrimônio</option>';
        }

        // Aplica filtro se necessário
        const filtrados = data.filter((item) => {
          const patrimonio = obterPatrimonio(item, tipoSelecionado);
          return filtroPatrimonio(patrimonio, filtrarPorCopy);
        });

        if (filtrados.length === 0) {
          patrimSelect.innerHTML = '<option value="" disabled selected>Nenhum patrimônio correspondente</option>';
          return;
        }

        filtrados.forEach((item) => {
          // Mostra apenas os que são true
          if(item.mostrar === true){
            const modelo = obterModelo(item, tipoSelecionado);
            const patrimonio = obterPatrimonio(item, tipoSelecionado);
            const status = obterStatus(item, tipoSelecionado);

            const opt = new Option(
              `${item.num}) ${modelo} - ${patrimonio}`,
              item.id
            );
            opt.className = obterClasseStatus(status);
            patrimSelect.appendChild(opt);
          }
        });
      })
      .catch((err) => {
        infoPatrimonioSelecionado.innerHTML = obterHtmlSemInfo(tipoSelecionado);
      });
  }

  function montarPayload(orig, novo, tipo) {
    const payload = {
      id: orig.id,
      num: orig.num,
      p_rede: orig.p_rede,
      sala_atual: orig.sala,
      modificado: "",
      id_enviado: novo.id,
      num_enviado: novo.num,
      p_rede_enviado: novo.p_rede,
      sala_antiga: novo.sala,
      modificado_enviado: "",
      tipo: tipo,
    };

    if (tipo === "patrim_maquina") {
      Object.assign(payload, {
        maquina: novo.maquina,
        modelo_maquina: novo.modelo_maquina,
        status_pc: novo.status_pc,
        reserva_pc: novo.reserva_pc,
        reserva_modelo_pc: novo.reserva_modelo_pc,
        obs_pc: novo.obs_pc,
        disco: novo.disco,

        maquina_enviado: orig.maquina,
        modelo_maquina_enviado: orig.modelo_maquina,
        status_pc_enviado: orig.status_pc,
        reserva_pc_enviado: orig.reserva_pc,
        reserva_modelo_pc_enviado: orig.reserva_modelo_pc,
        obs_pc_enviado: orig.obs_pc,
        disco_enviado: orig.disco,
      });
    } else {
      Object.assign(payload, {
        monitor: novo.monitor,
        modelo_monitor: novo.modelo_monitor,
        status_monitor: novo.status_monitor,
        reserva_monitor: novo.reserva_monitor,
        reserva_modelo_monitor: novo.reserva_modelo_monitor,
        obs_monitor: novo.obs_monitor,

        monitor_enviado: orig.monitor,
        modelo_monitor_enviado: orig.modelo_monitor,
        status_monitor_enviado: orig.status_monitor,
        reserva_monitor_enviado: orig.reserva_monitor,
        reserva_modelo_monitor_enviado: orig.reserva_modelo_monitor,
        obs_monitor_enviado: orig.obs_monitor,
      });
    }

    return payload;
  }
}

// Função para localizar os patrimônios ao pesquisa-los
function localizarPatrimonio(numero) {
  const el = document.getElementById(`patrimonio-${numero}`);
  if (!el) return;
  // Rolagem suave até o elemento
  el.scrollIntoView({ behavior: "smooth", block: "center" });

  el.classList.add("table-info", "fade", "show");

  // Espera 4 segundos e inicia fade out
  setTimeout(() => {
    el.classList.remove("table-info", "show"); // isso dispara a transição de opacity: 1 → 0

    // Quando o fade-out terminar:
    el.addEventListener("transitionend", function handler() {
      // remove o listener para evitar disparar mais de uma vez
      el.removeEventListener("transitionend", handler);

      // Faz o fade in
      el.classList.add("show");
    });
  }, 4000);

  // Remove o hash da URL após iniciar a animação
  history.replaceState(
    null,
    "",
    window.location.pathname + window.location.search
  );
}

// Função para pegar as informações e gerar o excel
function exportarParaExcel() {
  const tabela = document.getElementById("tabela-patrimonios");
  const dados = [];

  // Armazena os dados da tabela da tabela
  for (let row of tabela.rows) {
    const linha = [];
    for (let i = 0; i < row.cells.length - 2; i++) {
      // <- Ignora últimas 2 colunas
      linha.push(row.cells[i].innerText.trim());
    }
    dados.push(linha);
  }

  //Titulos
  const salaTitulo = document.getElementById("titulo_sala").innerText.trim();
  // Extrai só o numero e bloco da sala. Ex: "212.04/F"
  const codigoSala =
    salaTitulo.match(/\d{3}(?:\.\d{2})?(?:\/[A-Z])?/i)?.[0] || "sala";
  // Extrai número do prédio (ex: 12)
  const numeroPredio = salaTitulo.match(/Pr[eé]dio\s*(\d+)/i)?.[1] || "0";
  // Substitui "/" por "-" caso haja na sala. Ex -> "212.04-F"
  const nomeSalaFormatado = codigoSala.replace("/", "-");
  // Formata o prédio
  const predioFormatado = `P${numeroPredio}`;
  // Substitui "." por "_" caso haja na sala. Ex -> "212_04-F"
  const nomeParaDownload = nomeSalaFormatado.replace(".", "_");
  // Formata como "p-12"
  const predioParaDownload = `p-${numeroPredio}`;

  fetch("./api/exporta_excel.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ dados, nomeSalaFormatado, predioFormatado }),
  })
    .then((resp) => resp.blob())
    .then((blob) => {
      const hoje = new Date();
      const dia = String(hoje.getDate()).padStart(2, "0");
      const mes = String(hoje.getMonth() + 1).padStart(2, "0");
      const ano = String(hoje.getFullYear()).slice(-2);

      const data = `${dia}-${mes}-${ano}`;
      const a = document.createElement("a");
      a.href = URL.createObjectURL(blob);
      a.download = `patrimonios_${predioParaDownload}_sala_${nomeParaDownload}_${data}.xlsx`;
      a.click();
    });
}

// ------> Funções de bootstrap
function inicializarTooltip() {
  // Ativar os tooltips
  const tooltipTriggerList = document.querySelectorAll(
    '[data-bs-toggle="tooltip"]'
  );
  const tooltipList = [...tooltipTriggerList].map(
    (tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl)
  );
}

function mostrarToast(mensagemHTML) {
  const toastElement = document.getElementById("copyToast");
  const toastBody = document.getElementById("copyToastText");

  toastBody.innerHTML = mensagemHTML;

  const toast = bootstrap.Toast.getOrCreateInstance(toastElement);

  // Reinicia o toast se já estiver visível
  if (toastElement.classList.contains("show")) {
    toast.hide();
    toastElement.addEventListener("hidden.bs.toast", () => toast.show(), {
      once: true,
    });
  } else {
    toast.show();
  }
}

function inicializarPopovers() {
  const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );

  // Limpar popovers antigos se necessário
  popoverTriggerList.forEach((el) => {
    bootstrap.Popover.getInstance(el)?.dispose(); // Remove popover existente
    el.replaceWith(el.cloneNode(true)); // Remove event listeners
  });

  const freshTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
  );
  const popoverList = [...freshTriggerList].map((el) => {
    let options = { trigger: "manual", sanitize: true };

    if (el.hasAttribute("data-bs-content-id")) {
      const contentElement = document.getElementById(
        el.getAttribute("data-bs-content-id")
      );
      if (contentElement) {
        options.content = contentElement.innerHTML;
        options.html = true;
      }
    }

    return new bootstrap.Popover(el, options);
  });

  freshTriggerList.forEach((el, index) => {
    el.addEventListener("click", function (e) {
      popoverList[index].toggle();
      e.stopPropagation();
    });
  });

  document.addEventListener("click", function (e) {
    let clickedInside = false;

    freshTriggerList.forEach((el) => {
      const popoverContent = document.querySelector(".popover");
      if (
        el.contains(e.target) ||
        (popoverContent && popoverContent.contains(e.target))
      ) {
        clickedInside = true;
      }
    });

    if (!clickedInside) {
      popoverList.forEach((popover) => popover.hide());
    }
  });
}
