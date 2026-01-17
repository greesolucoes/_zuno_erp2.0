import { FinishedOS } from "../types"

export const makeOsTable = (data: FinishedOS[]) => {
    return (
        `
            <div class="os-finalizadas-lista">
                <div class="table-responsive">
                    <table id="os-finalizadas-table" class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">N° O.S</th>
                                <th class="text-center">Cliente</th>
                                <th class="text-center">Data Finalizada</th>
                                <th class="text-center">Responsável</th>
                                <th class="text-center">Valor Total</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${
                                data.length === 0
                                    ? `<tr><td colspan="7" class="py-5 text-center text-muted" style="font-size:1.1rem">Nenhuma OS finalizada encontrada.</td></tr>`
                                    : data
                                        .map(
                                            (os) => `
                                        <tr>
                                            <td class="text-center" style="font-weight:500;">${os.codigo_sequencial}</td>
                                            <td class="text-center" style="white-space:nowrap; font-weight:600;">${os.cliente}</td>
                                            <td class="text-center">${new Date(os.data_finalizada).toLocaleDateString('pt-BR')}</td>
                                            <td class="text-center">${os.funcionario}</td>
                                            <td class="text-center monetary text-green" style="font-weight:600;">
                                                ${os.valor_total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}
                                            </td>
                                            <td class="text-center">
                                                <button class="btn-pagar-os" data-os='${JSON.stringify(os)}'>Pagar</button>
                                            </td>
                                        </tr>
                                    `,
                                        )
                                        .join('')
                            }
                        </tbody>
                    </table>
                </div>
            </div>
        `
    )
}