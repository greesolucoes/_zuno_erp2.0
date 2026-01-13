/**********************************************************/
/* JS: /assets/js/js_views/ferias/cadastro.js (Exemplo)   */
/**********************************************************/
document.addEventListener('DOMContentLoaded', function () {
    // 1) Armazena o template original (sem inicialização do Select2)
    window.rowTemplate = document.querySelector("table#conteudoTable tbody tr").outerHTML;
    console.log("Template da linha armazenado:", window.rowTemplate);

    // 2) Inicializa Select2 e eventos dinâmicos
    initSelect2();
    bindDynamicEvents();

    // 3) Seleciona os inputs de data para cálculo (formato dd/mm/aaaa)
    var $dataInicialInput = $("input[name='data_inicial']");
    var $dataFinalInput   = $("input[name='data_final']");

    // 3.1) Converte data no formato dd/mm/aaaa em objeto Date
    function parseDate(value) {
        if (!value) return null; // Se estiver vazio
        var parts = value.split('/');
        // Mês em JS é 0-indexado (0 = Janeiro)
        return new Date(parts[2], parts[1] - 1, parts[0]);
    }

    // 3.2) Função que atualiza todos os cálculos da tabela (dias selecionados, programados etc.)
    function updateAllRows() {
        var dataInicialStr = $dataInicialInput.val(); // dd/mm/aaaa
        var dataFinalStr   = $dataFinalInput.val();   // dd/mm/aaaa

        // Se a data final estiver preenchida e não houver colaborador selecionado, exibe o alerta
        if (dataFinalStr !== "" && $("select[name='user_id[]']").filter(function(){
            return $(this).val() !== "";
        }).length === 0) {
            swal({
                title: "Erro!",
                text: "Selecione pelo menos um colaborador na tabela para realizar a busca.",
                type: "error",
                confirmButtonText: "OK"
            });
            return; // Aborta o updateAllRows se nenhum usuário estiver selecionado
        }

        // Converte para Date
        var dataInicial = parseDate(dataInicialStr);
        var dataFinal   = parseDate(dataFinalStr);

        // Calcula a diferença em dias (se as datas forem válidas)
        var diffDays = 0;
        if (dataInicial && dataFinal) {
            var diffTime = dataFinal - dataInicial;
            diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        }

        // Preenche cada linha da tabela
        document.querySelectorAll("table#conteudoTable tbody tr").forEach(function (row) {
            // Quantidade de dias selecionados
            var inputSelecionados = row.querySelector("input[name='dias_selecionados[]']");
            var inputProgramados  = row.querySelector("input[name='quantidade_programados[]']");
            var inputDisponiveis  = row.querySelector("input[name='quantidade_dias_disponiveis[]']");
            var selectUser        = row.querySelector("select[name='user_id[]']");

            if (!selectUser || !selectUser.value) {
                // Se não tiver colaborador selecionado, limpa os campos
                if (inputSelecionados) inputSelecionados.value = '';
                if (inputProgramados)  inputProgramados.value  = '';
                if (inputDisponiveis)  inputDisponiveis.value  = '';
                return;
            }

            // Se diffDays >= 0, temos intervalo válido
            if (diffDays >= 0) {
                if (inputSelecionados) inputSelecionados.value = diffDays;
            } else {
                // Intervalo inválido
                if (inputSelecionados) inputSelecionados.value = '';
            }

            // ---- 3.2.1) Calcula dias já programados via "usersData" ----
            var userId = selectUser.value;
            var usuario = usersData.find(function(u) {
                return u.id == userId;
            });

            if (!usuario || !dataInicial || !dataFinal) {
                // Sem colaborador válido ou datas inválidas
                if (inputProgramados) inputProgramados.value = '';
                if (inputDisponiveis) inputDisponiveis.value = '';
                return;
            }

            // Correção: cálculo de dias programados com "reset anual"
            var diasProgramados = calcularDiasProgramados(usuario, dataInicial, dataFinal);
            if (inputProgramados) {
                inputProgramados.value = diasProgramados;
            }

            // ---- 3.2.2) Calcula dias disponíveis (30 dias - programados) ----
            var diasDisponiveis = 30 - diasProgramados;
            if (diasDisponiveis < 0) diasDisponiveis = 0;
            if (inputDisponiveis) {
                inputDisponiveis.value = diasDisponiveis;
            }
        });
    }

    // 3.3) Função que soma todos os dias de férias "já marcados" no user.ferias (com reset anual)
    function calcularDiasProgramados(usuario, dataInicio, dataFim) {
        // 1) Definir data de reset anual (considerando data de admissão)
        var admissionDate = new Date(usuario.admission_date);
        var resetDate = new Date(admissionDate);
        // Ajusta o ano do reset para o mesmo ano da dataInicial
        resetDate.setFullYear(dataInicio.getFullYear());

        // Se a resetDate calculada ficar maior que a dataInicial, diminui 1 ano
        if (resetDate > dataInicio) {
            resetDate.setFullYear(resetDate.getFullYear() - 1);
        }

        // 2) Soma as férias que começam entre resetDate e dataFim
        var total = 0;
        usuario.ferias.forEach(function (ferias) {
            var fIni = new Date(ferias.data_inicial); // AAAA-MM-DD
            var fFim = new Date(ferias.data_final);

            // Considera apenas férias iniciadas após o reset e antes do fim atual
            if (fIni >= resetDate && fIni <= dataFim) {
                var diff = (fFim - fIni) / (1000 * 60 * 60 * 24);
                diff = Math.floor(diff) + 1; // +1 para contar inclusivo
                total += diff;
            }
        });
        return total;
    }

    // Dispara o updateAllRows ao alterar data inicial ou final
    $dataInicialInput.on('change blur', updateAllRows);
    $dataFinalInput.on('change blur', updateAllRows);

    // Dispara também ao carregar a página, caso os campos já tenham valor
    updateAllRows();

    // 6) Exemplo: se houver algo de soma de preços/valores
    // "somaCampos()" já foi chamado no bindDynamicEvents, mas chamamos aqui também
    somaCampos();
});

/**************************************************/
/* 4) Inicializa selects (.user_id) com Select2   */
/**************************************************/
function initSelect2() {
    document.querySelectorAll('.user_id').forEach(function (select) {
        if (!$(select).hasClass('select2-hidden-accessible')) {
            $(select).select2({
                placeholder: 'Selecione o colaborador',
                language: 'pt-BR'
            });
            console.log("Select2 inicializado para:", select);

            // Quando mudar o select, recalcula as linhas
            $(select).on('change', function() {
                updateAllRows();
            });
        }
    });
}

/************************************************************/
/* 5) Liga eventos para adicionar/remover linhas dinâmicas  */
/************************************************************/
function bindDynamicEvents() {
    // Evento para adicionar nova linha na tabela
    document.querySelector("table#conteudoTable button.addItens").addEventListener("click", function () {
        let tbody = document.querySelector("table#conteudoTable tbody");
        let novaLinha = document.createElement('tr');
        novaLinha.innerHTML = window.rowTemplate;

        // Limpa inputs da nova linha
        novaLinha.querySelectorAll('input').forEach(function (input) {
            input.value = "";
        });
        // Reseta selects para valor padrão
        novaLinha.querySelectorAll('select').forEach(function (select) {
            select.selectedIndex = 0;
        });
        // Ativa o botão de remover (se estiver desabilitado no template original)
        let btnRemove = novaLinha.querySelector('button.removeItens');
        if (btnRemove) {
            btnRemove.disabled = false;
        }

        // Adiciona a nova linha
        tbody.appendChild(novaLinha);
        initSelect2();     // Inicializa o Select2 nessa nova linha
        updateAllRows();   // Recalcula dias imediatamente
        somaCampos();      // Recalcula valores (caso use a lógica de soma)
    });

    // Evento para remover linha (delegado)
    document.querySelector("table#conteudoTable tbody").addEventListener("click", function (e) {
        if (e.target.closest('button.removeItens')) {
            let btn = e.target.closest('button.removeItens');
            let row = btn.closest('tr');
            row.remove();
            updateAllRows();
            somaCampos();
        }
    });

    // Eventos de keyup para recalcular valores de cada linha (ex.: quantidade x preço)
    document.querySelector("table#conteudoTable").addEventListener("keyup", function (e) {
        if (e.target.matches('input.quantidade, input.preco')) {
            somaCampos();
        }
    });
}

/**********************************************************/
/* 6) Exemplo: somaCampos (subtotal, total) - Opcional    */
/**********************************************************/
function somaCampos() {
    let total = 0;
    document.querySelectorAll("table#conteudoTable tbody tr").forEach(function (row) {
        let qtdVal        = row.querySelector('input.quantidade').value;
        let precoVal      = row.querySelector('input.preco').value;
        let subtotalField = row.querySelector('input.subTotal');

        if (qtdVal && precoVal) {
            let quantidade = qtdVal.includes(",")
                ? toFloat(qtdVal, qtdVal.split(",")[1].length)
                : toFloat(qtdVal, 0);

            // Exemplo: multiplica por "precoVal" convertido
            let subtotal = quantidade * convertCurrencyToFloat(configLocation.codigo, precoVal);
            subtotalField.value = formatFloatToCurrency(configLocation.codigo, subtotal);
            total += subtotal;
        } else {
            // Se faltam valores, zera
            subtotalField.value = formatFloatToCurrency(configLocation.codigo, 0);
        }
    });

    // Atualiza o campo total geral (exemplo)
    let totalGeralEl = document.querySelector("table#conteudoTable input.totalGeral");
    if (totalGeralEl) {
        totalGeralEl.value = formatFloatToCurrency(configLocation.codigo, total);
    }
}

/**********************************************************/
/* 7) Funções fictícias de conversão/formatter de moedas  */
/**********************************************************/
function toFloat(value, decimals) {
    return parseFloat(value.replace(",", ".")) || 0;
}

function convertCurrencyToFloat(codigo, value) {
    return parseFloat(value.replace(",", ".")) || 0;
}

function formatFloatToCurrency(codigo, value) {
    return value.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/**********************************************************/
/* 8) Função global p/ recalcular as linhas (caso precise)*/
/**********************************************************/
function updateAllRows() {
    // Esta função é declarada novamente no escopo do DOMContentLoaded para ter acesso às variáveis
    // $dataInicialInput e $dataFinalInput.
    // Se quiser chamar aqui, basta mover seu conteúdo ou fazer uma referência global.
    console.warn("updateAllRows() chamado antes de DOM Ready. Se precisar, mova a função para escopo global.");
}
