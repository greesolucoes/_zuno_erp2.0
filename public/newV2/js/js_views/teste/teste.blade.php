<style>
    table.tabela-monitoramento tbody tr td.dia.amarelo .bar-status-dia {
        background-color: var(--yellow-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.amarelo:hover {
        background-color: var(--yellow-absolute) !important;
        opacity: 1 !important;
        color: var(--blue2-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.verde .bar-status-dia {
        background-color: var(--green-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.verde:hover {
        background-color: var(--green-absolute) !important;
        opacity: 1 !important;
        color: var(--white-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.vermelho .bar-status-dia {
        background-color: var(--red-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.vermelho:hover {
        background-color: var(--red-absolute) !important;
        opacity: 1 !important;
        color: var(--white-absolute) !important;
    }

    /* Cinza virou amarelous */

    table.tabela-monitoramento tbody tr td.dia.cinza .bar-status-dia {
        background-color: var(--yellow-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.cinza:hover {
        background-color: var(--yellow-absolute) !important;
        opacity: 1 !important;
        color: var(--blue2-absolute) !important;
    }

    /* Laranja virou amayellow */

    table.tabela-monitoramento tbody tr td.dia.laranja .bar-status-dia {
        background-color: var(--yellow-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.laranja:hover {
        background-color: var(--yellow-absolute) !important;
        opacity: 1 !important;
        color: var(--blue2-absolute) !important;
    }

    /* Roxo também é yelero */

    table.tabela-monitoramento tbody tr td.dia.roxo .bar-status-dia {
        background-color: var(--yellow-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.roxo:hover {
        background-color: var(--yellow-absolute) !important;
        opacity: 1 !important;
        color: var(--blue2-absolute) !important;
    }

    /* Roxo virou verdin */

    table.tabela-monitoramento tbody tr td.dia.musgo .bar-status-dia {
        background-color: var(--green-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.musgo:hover {
        background-color: var(--green-absolute) !important;
        opacity: 1 !important;
        color: var(--white-absolute) !important;
    }

    table.tabela-monitoramento tbody tr td.dia.azul .bar-status-dia {
        background-color: var(--yellow-absolute) !important;
    }

    /* Azul virou amarelo */

    table.tabela-monitoramento tbody tr td.dia.azul:hover {
        background-color: var(--yellow-absolute) !important;
        opacity: 1 !important;
        color: var(--white-absolute) !important;
    }

    /*table.tabela-monitoramento tbody tr td.dia .dia_has_nota .circle-nota-inserida {*/

    /*	color: var(--green-absolute) !important;*/

    /*	font-size: 0.8rem;*/

    /*}*/
</style>

<div class="modal_especificacoes_dia modal fade" id="modal_especificacoes_dia" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content d-flex flex-wrap flex-column">
            <div class="data_modal_especificacoes_dia" data-mon_date="" data-user_id=""></div>
            <div class="modal-header col-12 d-flex align-items-center justify-content-between">
                <h3 class="modal-title col-11 titulo_especificacoes">
                    <span class="fw-medium titulo_filial" style="font-size: 2rem;"></span>
                    <br>
                    <span class="fw-normal titulo_data"></span>
                </h3>
                <button type="button" class="btn-close bg-blue-absolute txt-white-absolute d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Fechar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body py-4 p-0 col-12">

            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-md-end justify-content-start col-12 mb-5 gap-3 flex-wrap">
    <button type="button" class="button-form primary-button-blue1 col-12 px-md-4 col-md-auto" data-bs-toggle="modal" data-bs-target="#modalErrors">
        Erros no período		</button>

    <div class="modalErrors modal fade" id="modalErrors" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content d-flex flex-wrap flex-column">
                <div class="modal-header col-12 d-flex align-items-center justify-content-between">
                    <h3 class="modal-title col-11" id="modalErrorsLabel">
                        Erros no período </h3>
                    <button type="button" class="btn-close bg-blue-absolute txt-white-absolute d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Fechar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body py-4 p-0 col-12">
                    <table class="table table-striped padding-first">
                        <thead>
                        <tr>
                            <th class="fw-medium">
                                Tipo </th>
                            <th class="fw-medium">
                                Data </th>
                            <th class="fw-medium">
                                Filiais </th>
                            <th class="fw-medium">
                                Descrição </th>
                        </tr>
                        </thead>




                        <tr>
                            <td>
                                Vendas - Card Services </td>
                            <td>
                                07/03/2025 </td>
                            <td>
                                10 - 1900_09_VILA LEOPOLDINA </td>
                            <td>
                                <div class="ocultar">B1FWC18 - </div>Erros do sistema - Contas à receber dos Card Services:
                                <div class="ocultar">B1FWC74 - </div>Cartão 'Pré-pago Master Débito' (código '711'), correspondente a adquirente 'PagSeguro' (código '35') não parametrizado! </td>
                        </tr>
                        <tr>
                            <td>
                                Vendas - Card Services </td>
                            <td>
                                07/03/2025 </td>
                            <td>
                                10 - 1900_09_VILA LEOPOLDINA </td>
                            <td>
                                <div class="ocultar"> - </div>
                            </td>
                        </tr>
                    </table>


                    <table id="gerarExcel" style="display: none;">
                        <thead>
                        <tr class="bcinza1 fazul h50">
                            <th colspan="4" class="center f18 n">Erros no período</th>
                        </tr>
                        <tr>
                            <th colspan="4"></th>
                        </tr>
                        <tr>
                            <th class="left">Empresa:</th>
                            <td colspan="3" class="left">(P) Pizzeria 1900 </td>
                        </tr>
                        <tr>
                            <th class="left">Filial:</th>
                            <td colspan="3" class="left">1900_01_VILA MARIANA</td>
                        </tr>
                        <tr>
                            <th class="left">Data inicial de consulta:</th>
                            <td colspan="3" class="left">01/03/2025</td>
                        </tr>
                        <tr>
                            <th class="left">Data fim:</th>
                            <td colspan="3" class="left">31/03/2025</td>
                        </tr>
                        </thead>
                        <thead>
                        <tr>
                            <th class="text-center">
                                <strong>Tipo</strong>
                            </th>
                            <th class="text-center">
                                <strong>Data</strong>
                            </th>
                            <th class="text-center">
                                <strong>Filiais</strong>
                            </th>
                            <th class="text-center">
                                <strong>Descrição</strong>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="text-center">
                                Vendas - Card Services </td>
                            <td class="text-center">06/03/2025</td>
                            <td class="text-center">1 - 1900_01_VILA MARIANA</td>
                            <td class="text-justify">
                                <div class="ocultar">B1FWC23 - </div>Erros do sistema - Depósito:
                                <div class="ocultar">B1FWC24 - </div>Erro ao adicionar depósito para o cartão 120</td>
                        </tr>

                        <tr>
                            <td class="text-center">
                                Vendas - Card Services </td>
                            <td class="text-center">07/03/2025</td>
                            <td class="text-center">1 - 1900_01_VILA MARIANA</td>
                            <td class="text-justify">
                                <div class="ocultar">B1FWC23 - </div>Erros do sistema - Depósito:
                                <div class="ocultar">B1FWC24 - </div>Erro ao adicionar depósito para o cartão 120</td>
                        </tr>



                        <tr>
                            <th colspan="4"></th>
                        </tr>
                        </tbody>
                    </table>

                    <script>
                        $('button.gerarExcel').click(function() {
                            save2excel($('table#gerarExcel'), {
                                not: null,
                                name: 'Erros no período',
                                filename: 'Erros no período_2025-03-01-2025-03-31.xls'
                            });
                        });
                    </script>
                </div>
                <div class="modal-footer">
                    <div class="d-flex align-items-center justify-content-start gap-3 col-12 flex-wrap">
                        <button type="button" class="button-form confirm-button col-md-auto col-12 gerarExcel" title="Exportar para Excel">
                            Gerar planilha							</button>

                        <button type="button" class="button-form secondary-button col-md-auto col-12" data-bs-dismiss="modal">
                            Fechar							</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!--	Se existir a flag, habilita essas funções	-->
    <button type="button" class="button-form primary-button-blue1 col-12 px-md-4 col-md-auto" data-bs-toggle="modal" data-bs-target="#modalErrorsEstoqueNegativo">
        Erros de estoque negativo			</button>

    <div class="modalErrorsEstoqueNegativo modal fade" id="modalErrorsEstoqueNegativo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content d-flex flex-wrap flex-column">
                <div class="modal-header col-12 d-flex align-items-center justify-content-between">
                    <h3 class="modal-title col-11" id="modalErrorsLabel">
                        Erros de estoque negativo </h3>
                    <button type="button" class="btn-close bg-blue-absolute txt-white-absolute d-flex align-items-center justify-content-center" data-bs-dismiss="modal" aria-label="Fechar">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body py-4 p-0 col-12">
                    <table class="table table-striped" id="table-erros-estoque-negativo">
                        <thead>
                        <tr>
                            <th class="fw-medium">
                                <strong>Unidade</strong>
                            </th>
                            <th class="fw-medium">
                                <strong>Data</strong>
                            </th>
                            <th class="fw-medium">
                                <strong>Log</strong>
                            </th>
                            <th class="fw-medium">
                                <strong>Código SAP</strong>
                            </th>
                            <th class="fw-medium">
                                <strong>Quantidade</strong>
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <div class="d-flex align-items-center justify-content-between col-12 flex-wrap mt-5">
                        <button type="button" class="button-form confirm-button erros-negativos-to-xls" title="Gerar planilha">
                            Gerar planilha								</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex align-items-center justify-content-between justify-content-xl-start justify-content-xxl-between col-12 gap-5 gap-md-3 gap-xl-5 gap-xxl-3 flex-wrap position-relative">
    <div class="legenda-monitoramento legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <div class="bg-white status-caption-bar dark-color"></div>
        <p class="fw-normal txt-blue2">Dia não importado</p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Quer dizer que não existe vendas vindas do PDV para esse dia, e caso tenha, não foi possível realizar a importação. Nesses casos, é necessário visualizar os Logs de importação. </div>
    </div>
    <div class="legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <div class="bg-yellow-absolute status-caption-bar"></div>
        <p class="fw-normal txt-blue2">
            Dia não finalizado </p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Aponta que há processos pendentes a serem concluídos, relacionados a integração de vendas ou recebimentos. </div>
    </div>
    <div class="legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <div class="bg-green-absolute status-caption-bar"></div>
        <p class="fw-normal txt-blue2">Dia finalizado</p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Indica que todas as operações do dia foram concluídas e os documentos foram devidamente integrados ao ERP. </div>
    </div>
    <div class="legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <div class="bg-red-absolute status-caption-bar"></div>
        <p class="fw-normal txt-blue2">
            Dia com erro </p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Esse status significa que foi gerado um erro ao tentar realizar a integração com o ERP. Para mais detalhes do erro, clique no dia. </div>
    </div>
    <div class="legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <span data-icon="ion:card" class="iconify fs-4"></span>
        <p class="fw-normal txt-blue2">Recebimentos</p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Sinaliza que as vendas ou pagamentos relacionadas ao Card Service foram integradas com sucesso. </div>
    </div>
    <div class="legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <span data-icon="uil:dollar-sign-alt" class="iconify fs-4"></span>
        <p class="fw-normal txt-blue2">Dia com vendas</p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Indica que as vendas realizadas no PDV já foram integradas e estão aguardando a importação dos dados de Card Service. </div>
    </div>
    <div class="legenda-monitoramento d-flex align-items-center justify-content-start gap-4">
        <span data-icon="tabler:currency-dollar-off" class="iconify fs-4"></span>
        <p class="fw-normal txt-blue2">Dia sem vendas</p>
        <div class="position-absolute txt-white-absolute bg-blue-absolute rounded-4 p-4 descricao-hover">
            Refere-se aos dias que não contém vendas no PDV, mas ainda estão aguardando a importação de dados de Card Service. </div>
    </div>
</div>

<!--Espaço destinado às legendas do monitoramento-->
<div class="content table-responsive mt-5">

    <table class="table table-exibe display tabela-monitoramento" data-url="https://manyfood.manyminds.com.br/Conciliacao/getInformacoesDiaMonitoramento">
        <thead>
        <tr>
            <th class="text-uppercase" style="min-width: 25rem;">
                Filial </th>
            <th class="dia" style="min-width: 4rem;">
                01 </th>
            <th class="dia" style="min-width: 4rem;">
                02 </th>
            <th class="dia" style="min-width: 4rem;">
                03 </th>
            <th class="dia" style="min-width: 4rem;">
                04 </th>
            <th class="dia" style="min-width: 4rem;">
                05 </th>
            <th class="dia" style="min-width: 4rem;">
                06 </th>
            <th class="dia" style="min-width: 4rem;">
                07 </th>
            <th class="dia" style="min-width: 4rem;">
                08 </th>
            <th class="dia" style="min-width: 4rem;">
                09 </th>
            <th class="dia" style="min-width: 4rem;">
                10 </th>
            <th class="dia" style="min-width: 4rem;">
                11 </th>
            <th class="dia" style="min-width: 4rem;">
                12 </th>
            <th class="dia" style="min-width: 4rem;">
                13 </th>
            <th class="dia" style="min-width: 4rem;">
                14 </th>
            <th class="dia" style="min-width: 4rem;">
                15 </th>
            <th class="dia" style="min-width: 4rem;">
                16 </th>
            <th class="dia" style="min-width: 4rem;">
                17 </th>
            <th class="dia" style="min-width: 4rem;">
                18 </th>
            <th class="dia" style="min-width: 4rem;">
                19 </th>
            <th class="dia" style="min-width: 4rem;">
                20 </th>
            <th class="dia" style="min-width: 4rem;">
                21 </th>
            <th class="dia" style="min-width: 4rem;">
                22 </th>
            <th class="dia" style="min-width: 4rem;">
                23 </th>
            <th class="dia" style="min-width: 4rem;">
                24 </th>
            <th class="dia" style="min-width: 4rem;">
                25 </th>
            <th class="dia" style="min-width: 4rem;">
                26 </th>
            <th class="dia" style="min-width: 4rem;">
                27 </th>
            <th class="dia" style="min-width: 4rem;">
                28 </th>
            <th class="dia" style="min-width: 4rem;">
                29 </th>
            <th class="dia" style="min-width: 4rem;">
                30 </th>
            <th class="dia" style="min-width: 4rem;">
                31 </th>
        </tr>
        </thead>
        <tbody>
        <tr data-user_id="1438">
            <td title="Adriano Teixeira" class="fw-medium">
                Adriano Teixeira </td>
            <td class=" td-data-monitoramento dia dia-1 verde" title="Dia finalizado" data-mon_date="2025-03-01" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="1">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-2 verde" title="Dia finalizado" data-mon_date="2025-03-02" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="1">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-3 verde" title="Dia finalizado" data-mon_date="2025-03-03" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="1">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-4 verde" title="Dia finalizado" data-mon_date="2025-03-04" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="1">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-5 verde" title="Dia finalizado" data-mon_date="2025-03-05" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="1">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-6 vermelho" title="Vendas de CardServices não importadas no SAP" data-mon_date="2025-03-06" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-7 vermelho" title="Vendas de CardServices não importadas no SAP" data-mon_date="2025-03-07" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-8 amarelo" title="Dia parcialmente conciliado" data-mon_date="2025-03-08" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-9 cinza" title="Aguardando vendas dos Card Services serem importadas" data-mon_date="2025-03-09" data-is_nota_inserida="0" data-has_vendas_pdv="1" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-10 amarelo" title="Dia esperando retorno do SAP" data-mon_date="2025-03-10" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-11 amarelo" title="Dia esperando retorno do SAP" data-mon_date="2025-03-11" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-12 amarelo" title="Dia esperando retorno do SAP" data-mon_date="2025-03-12" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class=" td-data-monitoramento dia dia-13 amarelo" title="Dia esperando retorno do SAP" data-mon_date="2025-03-13" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-14 branco" title="Dia não importado" data-mon_date="2025-03-14" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-15 branco" title="Dia não importado" data-mon_date="2025-03-15" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-16 branco" title="Dia não importado" data-mon_date="2025-03-16" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-17 branco" title="Dia não importado" data-mon_date="2025-03-17" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-18 branco" title="Dia não importado" data-mon_date="2025-03-18" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-19 branco" title="Dia não importado" data-mon_date="2025-03-19" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-20 branco" title="Dia não importado" data-mon_date="2025-03-20" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-21 branco" title="Dia não importado" data-mon_date="2025-03-21" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-22 branco" title="Dia não importado" data-mon_date="2025-03-22" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-23 branco" title="Dia não importado" data-mon_date="2025-03-23" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-24 branco" title="Dia não importado" data-mon_date="2025-03-24" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-25 branco" title="Dia não importado" data-mon_date="2025-03-25" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-26 branco" title="Dia não importado" data-mon_date="2025-03-26" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-27 branco" title="Dia não importado" data-mon_date="2025-03-27" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-28 branco" title="Dia não importado" data-mon_date="2025-03-28" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-29 branco" title="Dia não importado" data-mon_date="2025-03-29" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-30 branco" title="Dia não importado" data-mon_date="2025-03-30" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
            <td class="  dia dia-31 branco" title="Dia não importado" data-mon_date="2025-03-31" data-is_nota_inserida="0" data-has_vendas_pdv="0" data-has_cardservice="0">
                <!-- Em relação ao valor hasVendasPDV, tem tais opções:
                                - 0: caso retorne 0, significa que a conciliação não está finalizada ainda;
                                - 1: a conciliação do dia foi finalizada, e está na etapa de inserção de Card Service;
                                - 2: caso a parte, no qual, está ativo a flag para integração de card services independente de ter carga no PDV.
                            Ademais, o hasVendasPDV e hasCardServices, é utilizado apenas para exibição dos ícones, indicando o "status"
                            atual do dia. Alterando o formato antigo, que era unicamente baseado na cor. -->
            </td>
        </tr>



        </tbody>
        <tfoot>
        <tr>
            <td class="ocultar" colspan="32"></td>
        </tr>
        </tfoot>
    </table>
</div>

<script>
    $(".erros-negativos-to-xls").click(function() {
        save2excel($('table#table-erros-estoque-negativo'), {
            not: null,
            name: 'Erros de estoque negativo',
            filename: ('Erros de estoque negativo.xls')
        });
    });
    //Essa flag é contrária então verifico tbm se é dev (OU no caso)
    var permissaoSomenteViewMonitoramentoConciliacaoVendas = 0;
</script>
<script src="/assets/js/js_views/conciliacao/monitoramento/listarAjax.js?14/03/2025-20250310v1.0"></script>
