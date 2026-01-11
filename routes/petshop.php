<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Petshop\Public\HomeController as PlanoPublicHomeController;
use App\Http\Controllers\Petshop\Public\AgendamentosController as PlanoPublicAgendamentosController;
use App\Http\Controllers\Petshop\Public\PlanoController as PlanoPublicPlanoController;
use App\Http\Controllers\Petshop\Public\PerfilController as PlanoPublicPerfilController;
use App\Http\Controllers\Petshop\Planos\PlanoAuthController;
use App\Http\Controllers\Petshop\Planos\PlanoUserController;
use App\Http\Controllers\PetShop\Planos\PlanoResetPasswordController;
use App\Http\Controllers\Petshop\Planos\UsuarioAvulsoController;
use App\Http\Controllers\Petshop\Planos\PlanoController as PlanoGerenciarController;
use App\Http\Controllers\Petshop\Public\HistoricoController as PlanoPublicHistoricoController;
use App\Http\Controllers\Petshop\ConfiguracaoController;
use App\Http\Controllers\Petshop\Public\ClienteController as PublicClienteController;
use App\Http\Controllers\Petshop\Agendamento\AgendamentoController as PetshopAgendamentoController;
use App\Http\Controllers\Petshop\Vet\AgendaController as VetAgendaController;
use App\Http\Controllers\Petshop\Vet\AtendimentosController as VetAtendimentosController;
use App\Http\Controllers\Petshop\Vet\InternacoesController as VetInternacoesController;
use App\Http\Controllers\Petshop\Vet\ProntuariosController as VetProntuariosController;
use App\Http\Controllers\Petshop\Vet\ModelosAvaliacaoController as VetModelosAvaliacaoController;
use App\Http\Controllers\Petshop\Vet\PrescricoesController as VetPrescricoesController;
use App\Http\Controllers\Petshop\Vet\VacinacoesController as VetVacinacoesController;
use App\Http\Controllers\Petshop\Vet\MedicoController as VetMedicoController;
use App\Http\Controllers\Petshop\Vet\SalasAtendimentoController as VetSalasAtendimentoController;
use App\Http\Controllers\Petshop\Vet\CartaoVacinasController as VetVaccineCardsController;
use App\Http\Controllers\Petshop\Vet\SalasInternacaoController as VetSalasInternacaoController;
use App\Http\Controllers\Petshop\Vet\MedicamentosController as VetMedicamentosController;
use App\Http\Controllers\Petshop\Vet\ChecklistController as VetChecklistController;
use App\Http\Controllers\Petshop\Vet\AlergiasController as VetAlergiasController;
use App\Http\Controllers\Petshop\Vet\CondicoesCronicasController as VetCondicoesCronicasController;
use App\Http\Controllers\Petshop\Vet\ModelosPrescricaoController as VetModelosPrescricaoController;
use App\Http\Controllers\Petshop\Vet\ExamesController as VetExamesController;
use App\Http\Controllers\Petshop\Vet\InternacaoStatusController as VetInternacaoStatusController;
use App\Http\Controllers\Petshop\Vet\ModeloAtendimentoController;

Route::prefix('petshop/planos')->group(function () {
    Route::get('login', [PlanoAuthController::class, 'showLoginForm'])->name('petshop.planos.login');
    Route::post('login', [PlanoAuthController::class, 'login'])->name('petshop.planos.login.submit');
     Route::get('password/reset', function () {
        return view('petshop.planos.passwords.email');
    })->name('petshop.planos.password.request');
    Route::post('reset-pass', [PlanoResetPasswordController::class, 'reset'])->name('petshop.planos.reset.pass');
    Route::get('reset-pass/{token}', [PlanoResetPasswordController::class, 'resetForm'])->name('petshop.planos.password.reset');
    Route::post('password/{token}', [PlanoResetPasswordController::class, 'validateReset'])->name('petshop.planos.reset.password');
    Route::get('usuario-plano', [PlanoUserController::class, 'create'])->name('petshop.planos.usuario.create');
    Route::post('usuario-plano', [PlanoUserController::class, 'store'])->name('petshop.planos.usuario.store');

    Route::middleware('auth:plano,portal')->group(function () {
        Route::get('publico/{empresa?}', [PlanoPublicHomeController::class, 'index'])->name('petshop.planos.publico');
        Route::get('agendamentos', [PlanoPublicAgendamentosController::class, 'index'])->name('petshop.planos.agendamentos');
        Route::get('agendamentos/novo', [PlanoPublicAgendamentosController::class, 'create'])->name('petshop.planos.agendamentos.novo');
 Route::get('agendamentos/{agendamento}/reagendar', [PlanoPublicAgendamentosController::class, 'create'])
            ->whereNumber('agendamento')
            ->name('petshop.planos.agendamentos.reagendar');        Route::post('agendamentos/novo', [PlanoPublicAgendamentosController::class, 'store'])->name('petshop.planos.agendamentos.store');
        Route::get('historico', [PlanoPublicHistoricoController::class, 'index'])->name('petshop.planos.historico');
        Route::get('plano', [PlanoPublicPlanoController::class, 'index'])->name('petshop.planos.plano');
        Route::get('perfil', [PlanoPublicPerfilController::class, 'index'])->name('petshop.planos.perfil');
         Route::put('perfil/usuario', [PlanoPublicPerfilController::class, 'updateUser'])->name('petshop.planos.perfil.usuario.update');
        Route::put('perfil/animal/{animal}', [PlanoPublicPerfilController::class, 'updateAnimal'])->name('petshop.planos.perfil.animal.update');
        Route::post('logout', [PlanoAuthController::class, 'logout'])->name('petshop.planos.logout');
    });
});

Route::get('petshop/{empresa}/{filial}', [PublicClienteController::class, 'create'])
    ->name('petshop.public.form')
    ->where(['empresa' => '(?!planos).*', 'filial' => '(?!login).*']);
Route::post('petshop/{empresa}/{filial}', [PublicClienteController::class, 'store'])
    ->name('petshop.public.store')
    ->where(['empresa' => '(?!planos).*', 'filial' => '(?!login).*']);

Route::get('vet/cartoes-vacinacao/{card}/imprimir', [VetVaccineCardsController::class, 'print'])
    ->name('vet.vaccine-cards.print')
    ->withoutMiddleware(['validaEmpresa', 'verificaEmpresa', 'validaAcesso', 'verificaContratoAssinado', 'limiteArmazenamento']);

Route::middleware(['verificaEmpresa', 'validaAcesso', 'verificaContratoAssinado', 'limiteArmazenamento'])->group(function () {
  Route::prefix('animais')->group(function () {
    Route::prefix('pacientes')->group(function () {
      Route::get('', 'Petshop\\Animais\\AnimalPacienteController@index')->name('animais.pacientes.index');
      Route::get('cadastro', 'Petshop\\Animais\\AnimalPacienteController@create')->name('animais.pacientes.create');
      Route::post('store', 'Petshop\\Animais\\AnimalPacienteController@store')->name('animais.pacientes.store');
      Route::get('atualizar/{id}', 'Petshop\\Animais\\AnimalPacienteController@edit')->name('animais.pacientes.edit');
      Route::get('{id}/crm', 'Petshop\\Animais\\AnimalPacienteController@crm')->name('animais.pacientes.crm');
      Route::put('update/{id}', 'Petshop\\Animais\\AnimalPacienteController@update')->name('animais.pacientes.update');
      Route::get('import', 'Petshop\\Animais\\AnimalPacienteController@import')->name('animais.pacientes.import');
      Route::get('import-download', 'Petshop\\Animais\\AnimalPacienteController@downloadModelo')->name('animais.pacientes.import-download');
      Route::post('import-store', 'Petshop\\Animais\\AnimalPacienteController@storePlanilha')->name('animais.pacientes.import-store');
      Route::delete('destroy/{id}', 'Petshop\\Animais\\AnimalPacienteController@destroy')->name('animais.pacientes.destroy');
    });

    Route::prefix('pelagens')->group(function () {
      Route::get('', 'Petshop\\Animais\\AnimalPelagemController@index')->name('animais.pelagens.index');
      Route::get('cadastro', 'Petshop\\Animais\\AnimalPelagemController@create')->name('animais.pelagens.create');
      Route::post('store', 'Petshop\\Animais\\AnimalPelagemController@store')->name('animais.pelagens.store');
      Route::get('atualizar/{id}', 'Petshop\\Animais\\AnimalPelagemController@edit')->name('animais.pelagens.edit');
      Route::put('update/{id}', 'Petshop\\Animais\\AnimalPelagemController@update')->name('animais.pelagens.update');
      Route::delete('destroy/{id}', 'Petshop\\Animais\\AnimalPelagemController@destroy')->name('animais.pelagens.destroy');
    });

    Route::prefix('especies')->group(function () {
      Route::get('', 'Petshop\\Animais\\AnimalEspecieController@index')->name('animais.especies.index');
      Route::get('cadastro', 'Petshop\\Animais\\AnimalEspecieController@create')->name('animais.especies.create');
      Route::post('store', 'Petshop\\Animais\\AnimalEspecieController@store')->name('animais.especies.store');
      Route::get('atualizar/{id}', 'Petshop\\Animais\\AnimalEspecieController@edit')->name('animais.especies.edit');
      Route::put('update/{id}', 'Petshop\\Animais\\AnimalEspecieController@update')->name('animais.especies.update');
      Route::delete('destroy/{id}', 'Petshop\\Animais\\AnimalEspecieController@destroy')->name('animais.especies.destroy');
    });

    Route::prefix('racas')->group(function () {
      Route::get('', 'Petshop\\Animais\\AnimalRacaController@index')->name('animais.racas.index');
      Route::get('cadastro', 'Petshop\\Animais\\AnimalRacaController@create')->name('animais.racas.create');
      Route::post('store', 'Petshop\\Animais\\AnimalRacaController@store')->name('animais.racas.store');
      Route::get('atualizar/{id}', 'Petshop\\Animais\\AnimalRacaController@edit')->name('animais.racas.edit');
      Route::put('update/{id}', 'Petshop\\Animais\\AnimalRacaController@update')->name('animais.racas.update');
      Route::delete('destroy/{id}', 'Petshop\\Animais\\AnimalRacaController@destroy')->name('animais.racas.destroy');
    });

    Route::prefix('diagnosticos')->group(function () {
      Route::get('', 'Petshop\\Animais\\AnimalDiagnosticoController@index')->name('animais.diagnosticos.index');
      Route::get('imprimir-anamnese/{id}', 'Petshop\\Animais\\AnimalDiagnosticoController@imprimirAnamnese')->name('animais.diagnosticos.imprimir-anamnese');
      Route::get('cadastro', 'Petshop\\Animais\\AnimalDiagnosticoController@create')->name('animais.diagnosticos.create');
      Route::post('store', 'Petshop\\Animais\\AnimalDiagnosticoController@store')->name('animais.diagnosticos.store');
      Route::get('atualizar/{id}', 'Petshop\\Animais\\AnimalDiagnosticoController@edit')->name('animais.diagnosticos.edit');
      Route::put('update/{id}', 'Petshop\\Animais\\AnimalDiagnosticoController@update')->name('animais.diagnosticos.update');
      Route::delete('destroy/{id}', 'Petshop\\Animais\\AnimalDiagnosticoController@destroy')->name('animais.diagnosticos.destroy');
    });

    Route::prefix('exames')->group(function () {
      Route::get('', 'Petshop\\Animais\\AnimalExameController@index')->name('animais.exames.index');
      Route::get('cadastro', 'Petshop\\Animais\\AnimalExameController@create')->name('animais.exames.create');
      Route::post('store', 'Petshop\\Animais\\AnimalExameController@store')->name('animais.exames.store');
      Route::get('atualizar/{id}', 'Petshop\\Animais\\AnimalExameController@edit')->name('animais.exames.edit');
      Route::put('update/{id}', 'Petshop\\Animais\\AnimalExameController@update')->name('animais.exames.update');
      Route::delete('destroy/{id}', 'Petshop\\Animais\\AnimalExameController@destroy')->name('animais.exames.destroy');
    });

    Route::prefix('consultas')->group(function () {
      Route::get('', 'Petshop\\Consultas\\ConsultaController@index')->name('animais.consultas.index');
      Route::get('cadastro', 'Petshop\\Consultas\\ConsultaController@create')->name('animais.consultas.create');
      Route::post('store', 'Petshop\\Consultas\\ConsultaController@store')->name('animais.consultas.store');
      Route::get('atualizar/{id}', 'Petshop\\Consultas\\ConsultaController@edit')->name('animais.consultas.edit');
      Route::put('update/{id}', 'Petshop\\Consultas\\ConsultaController@update')->name('animais.consultas.update');
      Route::delete('destroy/{id}', 'Petshop\\Consultas\\ConsultaController@destroy')->name('animais.consultas.destroy');
    });
  });

      Route::prefix('petshop')->group(function () {
                Route::get('config', [ConfiguracaoController::class, 'index'])->name('petshop.config.index');
                Route::post('config', [ConfiguracaoController::class, 'store'])->name('petshop.config.store');

        Route::prefix('AgendaGeralPetShop')->name('petshop.agenda.')->group(function () {
            Route::get('', [PetshopAgendamentoController::class, 'index'])->name('index');
            Route::post('', [PetshopAgendamentoController::class, 'store'])->name('store');
            Route::get('{agendamento}', [PetshopAgendamentoController::class, 'show'])->whereNumber('agendamento')->name('show');
            Route::put('{agendamento}', [PetshopAgendamentoController::class, 'update'])->whereNumber('agendamento')->name('update');
            Route::put('{agendamento}/status', [PetshopAgendamentoController::class, 'updateStatus'])->whereNumber('agendamento')->name('update-status');
            Route::delete('{agendamento}', [PetshopAgendamentoController::class, 'destroy'])->whereNumber('agendamento')->name('destroy');
            Route::get('{agendamento}/pdv', [PetshopAgendamentoController::class, 'pdv'])->whereNumber('agendamento')->name('pdv');
        });

        Route::get('planos', [PlanoGerenciarController::class, 'index'])->name('petshop.gerenciar.planos');
        Route::get('planos/novo', [PlanoGerenciarController::class, 'create'])->name('petshop.criar.plano');
        Route::post('planos', [PlanoGerenciarController::class, 'store'])->name('petshop.planos.store');
        Route::get('planos/{plano}/editar', [PlanoGerenciarController::class, 'edit'])->name('petshop.planos.edit');
        Route::put('planos/{plano}', [PlanoGerenciarController::class, 'update'])->name('petshop.planos.update');
        Route::delete('planos/{plano}', [PlanoGerenciarController::class, 'destroy'])->name('petshop.planos.destroy');

        Route::prefix('planos')->group(function () {
            Route::get('usuarios-plano', [PlanoUserController::class, 'index'])->name('petshop.planos.usuario.index');
            Route::get('usuarios-plano/novo', [PlanoUserController::class, 'createInterno'])->name('petshop.planos.usuarios-plano.create');
            Route::post('usuarios-plano', [PlanoUserController::class, 'storeInterno'])->name('petshop.planos.usuarios-plano.store');
            Route::get('usuarios-plano/{planoUser}/editar', [PlanoUserController::class, 'edit'])->name('petshop.planos.usuarios-plano.edit');
            Route::put('usuarios-plano/{planoUser}', [PlanoUserController::class, 'update'])->name('petshop.planos.usuarios-plano.update');
            Route::delete('usuarios-plano/{planoUser}', [PlanoUserController::class, 'destroy'])->name('petshop.planos.usuarios-plano.destroy');
            Route::post('usuarios-plano/cancelar', [PlanoUserController::class, 'cancelarPlano'])->name('petshop.planos.usuarios-plano.cancelar');

            Route::post('usuarios-plano/renovar', [PlanoUserController::class, 'renovarPlano'])->name('petshop.planos.usuarios-plano.renovar');

            Route::post('usuarios-plano/reenviar', [PlanoUserController::class, 'reenviarCredenciais'])->name('petshop.planos.usuarios-plano.reenviar');
            
            Route::prefix('usuarios-avulso')->group(function () {
                Route::get('', [UsuarioAvulsoController::class, 'index'])->name('petshop.planos.usuarios-avulso.index');
                Route::get('{avulsoUser}/editar', [UsuarioAvulsoController::class, 'edit'])->name('petshop.planos.usuarios-avulso.edit');
                Route::put('{avulsoUser}', [UsuarioAvulsoController::class, 'update'])->name('petshop.planos.usuarios-avulso.update');
                Route::delete('{avulsoUser}', [UsuarioAvulsoController::class, 'destroy'])->name('petshop.planos.usuarios-avulso.destroy');
            });
        });
    });

        Route::prefix('vet')->group(function () {
          Route::get('salas-atendimento', [VetSalasAtendimentoController::class, 'index'])->name('vet.salas-atendimento.index');
            Route::get('salas-atendimento/nova', [VetSalasAtendimentoController::class, 'create'])->name('vet.salas-atendimento.create');
            Route::post('salas-atendimento', [VetSalasAtendimentoController::class, 'store'])->name('vet.salas-atendimento.store');
            Route::get('salas-atendimento/{salaAtendimento}/editar', [VetSalasAtendimentoController::class, 'edit'])->name('vet.salas-atendimento.edit');
            Route::put('salas-atendimento/{salaAtendimento}', [VetSalasAtendimentoController::class, 'update'])->name('vet.salas-atendimento.update');
            Route::delete('salas-atendimento/{salaAtendimento}', [VetSalasAtendimentoController::class, 'destroy'])->name('vet.salas-atendimento.destroy');
            Route::get('medicos', [VetMedicoController::class, 'index'])->name('vet.medicos.index');
            Route::get('medicos/cadastro', [VetMedicoController::class, 'create'])->name('vet.medicos.create');
            Route::post('medicos', [VetMedicoController::class, 'store'])->name('vet.medicos.store');
            Route::get('medicos/{medico}/editar', [VetMedicoController::class, 'edit'])->name('vet.medicos.edit');
            Route::put('medicos/{medico}', [VetMedicoController::class, 'update'])->name('vet.medicos.update');
            Route::delete('medicos/{medico}', [VetMedicoController::class, 'destroy'])->name('vet.medicos.destroy');            Route::get('agenda', [VetAgendaController::class, 'index'])->name('vet.agenda.index');
            Route::get('agenda/agendar', [VetAgendaController::class, 'create'])->name('vet.agenda.create');
            Route::get('atendimentos', [VetAtendimentosController::class, 'index'])->name('vet.atendimentos.index');
            Route::get('atendimentos/registrar', [VetAtendimentosController::class, 'create'])->name('vet.atendimentos.create');
            Route::post('atendimentos', [VetAtendimentosController::class, 'store'])->name('vet.atendimentos.store');
            Route::get('atendimentos/{atendimento}/historico', [VetAtendimentosController::class, 'history'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.history');
            Route::get('atendimentos/{atendimento}/editar', [VetAtendimentosController::class, 'edit'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.edit');
                 Route::get('atendimentos/{atendimento}/faturamento', [VetAtendimentosController::class, 'billing'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.billing');
                Route::post('atendimentos/{atendimento}/faturamento', [VetAtendimentosController::class, 'storeBilling'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.billing.store');
            Route::put('atendimentos/{atendimento}', [VetAtendimentosController::class, 'update'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.update');
            Route::put('atendimentos/{atendimento}/status', [VetAtendimentosController::class, 'updateStatus'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.status.update');
                Route::delete('atendimentos/{atendimento}', [VetAtendimentosController::class, 'destroy'])
                ->whereNumber('atendimento')
                ->name('vet.atendimentos.destroy');
            Route::get('atendimentos/opcoes/pacientes', [VetAtendimentosController::class, 'patientsOptions'])
                ->name('vet.atendimentos.destroy');            Route::get('atendimentos/opcoes/pacientes', [VetAtendimentosController::class, 'patientsOptions'])
                ->name('vet.atendimentos.patients-options');
            Route::get('atendimentos/pacientes/{animal}', [VetAtendimentosController::class, 'patientDetails'])
                ->whereNumber('animal')
                ->name('vet.atendimentos.patient-details');
            Route::post('atendimentos/anexos', [VetAtendimentosController::class, 'storeAttachment'])
                ->name('vet.atendimentos.attachments.store');
            Route::post('atendimentos/anexos/remover', [VetAtendimentosController::class, 'removeAttachment'])
                ->name('vet.atendimentos.attachments.remove');
            Route::get('internacoes', [VetInternacoesController::class, 'index'])->name('vet.hospitalizations.index');
            Route::get('internacoes/registrar', [VetInternacoesController::class, 'create'])->name('vet.hospitalizations.create');
            Route::post('internacoes', [VetInternacoesController::class, 'store'])->name('vet.hospitalizations.store');
            Route::get('internacoes/{internacao}/editar', [VetInternacoesController::class, 'edit'])
                ->whereNumber('internacao')
                ->name('vet.hospitalizations.edit');
            Route::put('internacoes/{internacao}', [VetInternacoesController::class, 'update'])
                ->whereNumber('internacao')
                ->name('vet.hospitalizations.update');
            Route::prefix('internacoes/{internacao}/status')
                ->whereNumber('internacao')
                ->name('vet.hospitalizations.status.')
                ->group(function () {
                    Route::get('', [VetInternacaoStatusController::class, 'index'])->name('index');
                    Route::get('novo', [VetInternacaoStatusController::class, 'create'])->name('create');
                    Route::post('', [VetInternacaoStatusController::class, 'store'])->name('store');
                    Route::get('{status}/editar', [VetInternacaoStatusController::class, 'edit'])
                        ->whereNumber('status')
                        ->name('edit');
                    Route::put('{status}', [VetInternacaoStatusController::class, 'update'])
                        ->whereNumber('status')
                        ->name('update');
                    Route::delete('{status}', [VetInternacaoStatusController::class, 'destroy'])
                        ->whereNumber('status')
                        ->name('destroy');
                });
            Route::get('internacoes', [VetInternacoesController::class, 'index'])->name('vet.hospitalizations.index');
                        Route::get('internacoes/internados', [VetInternacoesController::class, 'inpatients'])->name('vet.hospitalizations.inpatients');

            Route::get('internacoes/ocupacao', [VetInternacoesController::class, 'occupancy'])->name('vet.hospitalizations.occupancy');
            Route::get('prontuarios', [VetProntuariosController::class, 'index'])->name('vet.records.index');
            Route::get('prontuarios/fila', [VetProntuariosController::class, 'queue'])->name('vet.records.queue');
            Route::get('prontuarios/registrar', [VetProntuariosController::class, 'create'])->name('vet.records.create');
            Route::post('prontuarios', [VetProntuariosController::class, 'store'])->name('vet.records.store');
            Route::get('prontuarios/{prontuario}', [VetProntuariosController::class, 'show'])
                ->whereNumber('prontuario')
                ->name('vet.records.show');
            Route::get('prontuarios/{prontuario}/editar', [VetProntuariosController::class, 'edit'])
                ->whereNumber('prontuario')
                ->name('vet.records.edit');
            Route::put('prontuarios/{prontuario}', [VetProntuariosController::class, 'update'])
                ->whereNumber('prontuario')
                ->name('vet.records.update');
             Route::post('prontuarios/anexos', [VetProntuariosController::class, 'storeAttachment'])
                ->name('vet.records.attachments.store');
            Route::post('prontuarios/anexos/remover', [VetProntuariosController::class, 'removeAttachment'])
                ->name('vet.records.attachments.remove');    
            Route::delete('prontuarios/{prontuario}', [VetProntuariosController::class, 'destroy'])
                ->whereNumber('prontuario')
                ->name('vet.records.destroy');
            Route::get('exames', [VetExamesController::class, 'index'])->name('vet.exams.index');
            Route::get('exames/novo', [VetExamesController::class, 'create'])->name('vet.exams.create');
            Route::post('exames', [VetExamesController::class, 'store'])->name('vet.exams.store');
            Route::get('exames/{exam}/coleta', [VetExamesController::class, 'collect'])
                ->whereNumber('exam')
                ->name('vet.exams.collect');
             Route::get('exames/{exam}/laudo', [VetExamesController::class, 'report'])
                ->whereNumber('exam')
                ->name('vet.exams.report');
                  Route::put('exames/{exam}/laudo', [VetExamesController::class, 'updateReport'])
                ->whereNumber('exam')
                ->name('vet.exams.report.update');
            Route::get('exames/{exam}/anexos/{attachment}', [VetExamesController::class, 'streamAttachment'])
                ->whereNumber('exam')
                ->whereNumber('attachment')
                ->name('vet.exams.attachments.stream');
                Route::put('exames/{exam}', [VetExamesController::class, 'update'])
                ->whereNumber('exam')
                ->name('vet.exams.update');
            Route::delete('exames/{exam}', [VetExamesController::class, 'destroy'])
                ->whereNumber('exam')
                ->name('vet.exams.destroy');
            Route::get('exames/tipos', [VetExamesController::class, 'types'])->name('vet.exams.types');
            Route::post('exames/tipos', [VetExamesController::class, 'storeType'])->name('vet.exams.types.store');

            Route::resource('modelos-atendimento', ModeloAtendimentoController::class)->names('vet.modelos-atendimento');

            Route::get('prontuarios/modelos-avaliacao/{modeloAvaliacao}', [VetProntuariosController::class, 'fetchAssessmentModel'])
                ->name('vet.records.assessment-models.fetch');
            Route::get('modelos-avaliacao', [VetModelosAvaliacaoController::class, 'index'])->name('vet.assessment-models.index');
            Route::get('modelos-avaliacao/criar', [VetModelosAvaliacaoController::class, 'create'])->name('vet.assessment-models.create');
            Route::post('modelos-avaliacao', [VetModelosAvaliacaoController::class, 'store'])->name('vet.assessment-models.store');
            Route::get('modelos-prescricao', [VetModelosPrescricaoController::class, 'index'])->name('vet.prescription-models.index');
            Route::get('modelos-prescricao/criar', [VetModelosPrescricaoController::class, 'create'])->name('vet.prescription-models.create');
            Route::post('modelos-prescricao', [VetModelosPrescricaoController::class, 'store'])->name('vet.prescription-models.store');
            Route::get('modelos-prescricao/{modeloPrescricao}/editar', [VetModelosPrescricaoController::class, 'edit'])
                ->whereNumber('modeloPrescricao')
                ->name('vet.prescription-models.edit');
            Route::put('modelos-prescricao/{modeloPrescricao}', [VetModelosPrescricaoController::class, 'update'])
                ->whereNumber('modeloPrescricao')
                ->name('vet.prescription-models.update');
            Route::get('modelos-prescricao/{modeloPrescricao}', [VetModelosPrescricaoController::class, 'show'])
                ->whereNumber('modeloPrescricao')
                ->name('vet.prescription-models.show');
            Route::get('modelos-avaliacao/{modeloAvaliacao}/editar', [VetModelosAvaliacaoController::class, 'edit'])
                ->whereNumber('modeloAvaliacao')
                ->name('vet.assessment-models.edit');
            Route::put('modelos-avaliacao/{modeloAvaliacao}', [VetModelosAvaliacaoController::class, 'update'])
                ->whereNumber('modeloAvaliacao')
                ->name('vet.assessment-models.update');
            Route::get('modelos-avaliacao/{modeloAvaliacao}', [VetModelosAvaliacaoController::class, 'show'])
                ->whereNumber('modeloAvaliacao')
                ->name('vet.assessment-models.show');
            Route::post('modelos-avaliacao', [VetModelosAvaliacaoController::class, 'store'])->name('vet.assessment-models.store');
            Route::get('prescricoes', [VetPrescricoesController::class, 'index'])->name('vet.prescriptions.index');
            Route::get('prescricoes/emitir', [VetPrescricoesController::class, 'create'])->name('vet.prescriptions.create');
            Route::post('prescricoes', [VetPrescricoesController::class, 'store'])->name('vet.prescriptions.store');
             Route::post('prescricoes/anexos', [VetPrescricoesController::class, 'storeAttachment'])
                ->name('vet.prescriptions.attachments.store');
            Route::post('prescricoes/anexos/remover', [VetPrescricoesController::class, 'removeAttachment'])
                ->name('vet.prescriptions.attachments.remove');
            Route::get('prescricoes/{prescricao}/editar', [VetPrescricoesController::class, 'edit'])
                ->whereNumber('prescricao')
                ->name('vet.prescriptions.edit');
            Route::put('prescricoes/{prescricao}', [VetPrescricoesController::class, 'update'])
                ->whereNumber('prescricao')
                ->name('vet.prescriptions.update');
            Route::delete('prescricoes/{prescricao}', [VetPrescricoesController::class, 'destroy'])
                ->whereNumber('prescricao')
                ->name('vet.prescriptions.destroy');
            Route::get('vacinacoes/painel', [VetVacinacoesController::class, 'panel'])->name('vet.vaccinations.panel');
            Route::get('vacinacoes', [VetVacinacoesController::class, 'index'])->name('vet.vaccinations.index');
            Route::get('vacinacoes/aplicar', [VetVacinacoesController::class, 'scheduled'])->name('vet.vaccinations.scheduled');
            Route::get('vacinacoes/agendar', [VetVacinacoesController::class, 'create'])->name('vet.vaccinations.create');
            Route::post('vacinacoes', [VetVacinacoesController::class, 'store'])->name('vet.vaccinations.store');
            Route::get('vacinacoes/{vacinacao}/editar', [VetVacinacoesController::class, 'edit'])->name('vet.vaccinations.edit');
            Route::put('vacinacoes/{vacinacao}', [VetVacinacoesController::class, 'update'])->name('vet.vaccinations.update');
            Route::get('vacinacoes/{vacinacao}/aplicar', [VetVacinacoesController::class, 'applyForm'])->name('vet.vaccinations.apply');
            Route::post('vacinacoes/{vacinacao}/aplicar', [VetVacinacoesController::class, 'apply'])->name('vet.vaccinations.apply.store');
            Route::get('vacinacoes/opcoes/salas', [VetVacinacoesController::class, 'roomsOptions'])->name('vet.vaccinations.rooms-options');
            Route::get('vacinacoes/opcoes/veterinarios', [VetVacinacoesController::class, 'veterinariansOptions'])->name('vet.vaccinations.veterinarians-options');
            Route::get('cartoes-vacinacao', [VetVaccineCardsController::class, 'index'])->name('vet.vaccine-cards.index');
            Route::get('cartoes-vacinacao/criar', [VetVaccineCardsController::class, 'create'])->name('vet.vaccine-cards.create');
            Route::post('cartoes-vacinacao', [VetVaccineCardsController::class, 'store'])->name('vet.vaccine-cards.store');
            Route::get('salas-internacao', [VetSalasInternacaoController::class, 'index'])->name('vet.salas-internacao.index');
            Route::get('salas-internacao/nova', [VetSalasInternacaoController::class, 'create'])->name('vet.salas-internacao.create');
            Route::post('salas-internacao', [VetSalasInternacaoController::class, 'store'])->name('vet.salas-internacao.store');
            Route::get('salas-internacao/{salaInternacao}/editar', [VetSalasInternacaoController::class, 'edit'])->name('vet.salas-internacao.edit');
            Route::put('salas-internacao/{salaInternacao}', [VetSalasInternacaoController::class, 'update'])->name('vet.salas-internacao.update');
            Route::delete('salas-internacao/{salaInternacao}', [VetSalasInternacaoController::class, 'destroy'])->name('vet.salas-internacao.destroy');      
            Route::get('medicamentos', [VetMedicamentosController::class, 'index'])->name('vet.medicines.index');
            Route::get('medicamentos/cadastro', [VetMedicamentosController::class, 'create'])->name('vet.medicines.create');
            Route::post('medicamentos', [VetMedicamentosController::class, 'store'])->name('vet.medicines.store');
            Route::get('medicamentos/{medicamento}/editar', [VetMedicamentosController::class, 'edit'])
                ->whereNumber('medicamento')
                ->name('vet.medicines.edit');
            Route::put('medicamentos/{medicamento}', [VetMedicamentosController::class, 'update'])
                ->whereNumber('medicamento')
                ->name('vet.medicines.update');
            Route::delete('medicamentos/{medicamento}', [VetMedicamentosController::class, 'destroy'])
                ->whereNumber('medicamento')
                ->name('vet.medicines.destroy');
            Route::prefix('checklists')->name('vet.checklist.')->group(function () {
                Route::get('', [VetChecklistController::class, 'index'])->name('index');
                Route::get('cadastro', [VetChecklistController::class, 'create'])->name('create');
                Route::post('', [VetChecklistController::class, 'store'])->name('store');
                Route::get('{checklist}/editar', [VetChecklistController::class, 'edit'])
                    ->whereNumber('checklist')
                    ->name('edit');
                Route::put('{checklist}', [VetChecklistController::class, 'update'])
                    ->whereNumber('checklist')
                    ->name('update');
                Route::delete('{checklist}', [VetChecklistController::class, 'destroy'])
                    ->whereNumber('checklist')
                    ->name('destroy');
            });
            Route::prefix('alergias')->name('vet.allergies.')->group(function () {
                Route::get('', [VetAlergiasController::class, 'index'])->name('index');
                Route::get('cadastro', [VetAlergiasController::class, 'create'])->name('create');
                Route::post('', [VetAlergiasController::class, 'store'])->name('store');
                Route::get('{alergia}/editar', [VetAlergiasController::class, 'edit'])
                    ->whereNumber('alergia')
                    ->name('edit');
                Route::put('{alergia}', [VetAlergiasController::class, 'update'])
                    ->whereNumber('alergia')
                    ->name('update');
                Route::delete('{alergia}', [VetAlergiasController::class, 'destroy'])
                    ->whereNumber('alergia')
                    ->name('destroy');
            });
            Route::prefix('condicoes-cronicas')->name('vet.chronic-conditions.')->group(function () {
                Route::get('', [VetCondicoesCronicasController::class, 'index'])->name('index');
                Route::get('cadastro', [VetCondicoesCronicasController::class, 'create'])->name('create');
                Route::post('', [VetCondicoesCronicasController::class, 'store'])->name('store');
                Route::get('{condicaoCronica}/editar', [VetCondicoesCronicasController::class, 'edit'])
                    ->whereNumber('condicaoCronica')
                    ->name('edit');
                Route::put('{condicaoCronica}', [VetCondicoesCronicasController::class, 'update'])
                    ->whereNumber('condicaoCronica')
                    ->name('update');
                Route::delete('{condicaoCronica}', [VetCondicoesCronicasController::class, 'destroy'])
                    ->whereNumber('condicaoCronica')
                    ->name('destroy');
            });    
          });


  Route::prefix('vacina')->group(function () {
    Route::prefix('vacinas')->group(function () {
      Route::get('', 'Petshop\\Vacinas\\VacinaController@index')->name('vacina.vacinas.index');
      Route::get('cadastro', 'Petshop\\Vacinas\\VacinaController@create')->name('vacina.vacinas.create');
      Route::post('store', 'Petshop\\Vacinas\\VacinaController@store')->name('vacina.vacinas.store');
      Route::get('atualizar/{vacina}', 'Petshop\\Vacinas\\VacinaController@edit')
        ->whereNumber('vacina')
        ->name('vacina.vacinas.edit');
      Route::put('update/{vacina}', 'Petshop\\Vacinas\\VacinaController@update')
        ->whereNumber('vacina')
        ->name('vacina.vacinas.update');
      Route::delete('destroy/{vacina}', 'Petshop\\Vacinas\\VacinaController@destroy')
        ->whereNumber('vacina')
        ->name('vacina.vacinas.destroy');
    });

    Route::prefix('vacinacoes')->group(function () {
      Route::get('', 'Petshop\\Vacinas\\VacinacaoController@index')->name('vacina.vacinacoes.index');
      Route::get('cadastro', 'Petshop\\Vacinas\\VacinacaoController@create')->name('vacina.vacinacoes.create');
      Route::post('store', 'Petshop\\Vacinas\\VacinacaoController@store')->name('vacina.vacinacoes.store');
      Route::get('atualizar/{id}', 'Petshop\\Vacinas\\VacinacaoController@edit')->name('vacina.vacinacoes.edit');
      Route::put('update/{id}', 'Petshop\\Vacinas\\VacinacaoController@update')->name('vacina.vacinacoes.update');
      Route::delete('destroy/{id}', 'Petshop\\Vacinas\\VacinacaoController@destroy')->name('vacina.vacinacoes.destroy');
    });

    Route::prefix('grupos-vacinacao')->group(function () {
      Route::get('', 'Petshop\\Vacinas\\GrupoVacinacaoController@index')->name('vacina.grupos_vacinacao.index');
      Route::get('cadastro', 'Petshop\\Vacinas\\GrupoVacinacaoController@create')->name('vacina.grupos_vacinacao.create');
      Route::post('store', 'Petshop\\Vacinas\\GrupoVacinacaoController@store')->name('vacina.grupos_vacinacao.store');
      Route::get('atualizar/{id}', 'Petshop\\Vacinas\\GrupoVacinacaoController@edit')->name('vacina.grupos_vacinacao.edit');
      Route::put('update/{id}', 'Petshop\\Vacinas\\GrupoVacinacaoController@update')->name('vacina.grupos_vacinacao.update');
      Route::delete('destroy/{id}', 'Petshop\\Vacinas\\GrupoVacinacaoController@destroy')->name('vacina.grupos_vacinacao.destroy');
    });
  });

  Route::prefix('tele_entregas')->group(function () {
    Route::get('', 'Petshop\\TeleEntrega\\TeleEntregaController@index')->name('tele_entregas.index');
    Route::get('cadastro', 'Petshop\\TeleEntrega\\TeleEntregaController@create')->name('tele_entregas.create');
    Route::post('store', 'Petshop\\TeleEntrega\\TeleEntregaController@store')->name('tele_entregas.store');
    Route::get('atualizar/{id}', 'Petshop\\TeleEntrega\\TeleEntregaController@edit')->name('tele_entregas.edit');
    Route::put('update/{id}', 'Petshop\\TeleEntrega\\TeleEntregaController@update')->name('tele_entregas.update');
    Route::delete('destroy/{id}', 'Petshop\\TeleEntrega\\TeleEntregaController@destroy')->name('tele_entregas.destroy');

    Route::prefix('tipos')->group(function () {
      Route::get('', 'Petshop\\TeleEntrega\\TipoTeleEntregaController@index')->name('tipos_tele_entregas.index');
      Route::get('cadastro', 'Petshop\\TeleEntrega\\TipoTeleEntregaController@create')->name('tipos_tele_entregas.create');
      Route::post('store', 'Petshop\\TeleEntrega\\TipoTeleEntregaController@store')->name('tipos_tele_entregas.store');
      Route::get('atualizar/{id}', 'Petshop\\TeleEntrega\\TipoTeleEntregaController@edit')->name('tipos_tele_entregas.edit');
      Route::put('update/{id}', 'Petshop\\TeleEntrega\\TipoTeleEntregaController@update')->name('tipos_tele_entregas.update');
      Route::delete('destroy/{id}', 'Petshop\\TeleEntrega\\TipoTeleEntregaController@destroy')->name('tipos_tele_entregas.destroy');
    });
  });

  Route::prefix('hotel')->group(function () {

    Route::resource('hoteis', 'Petshop\\Hotel\\HotelController');

    Route::get('endereco-entrega/{id}', 'Petshop\\Hotel\\HotelController@printEnderecoEntrega')->name('hoteis.endereco_entrega');

        Route::put('hoteis/{hotel}/move', 'Petshop\\Hotel\\HotelController@move')->name('hoteis.move');

    Route::post('hoteis/{hotel}/servicos', 'Petshop\\Hotel\\HotelController@attachServicos')->name('hoteis.servicos.attach');
    Route::get('hoteis/{hotel}/checklist', 'Petshop\\Hotel\\HotelChecklistController@create')->name('hoteis.checklist.create');
    Route::post('hoteis/{hotel}/checklist', 'Petshop\\Hotel\\HotelChecklistController@store')->name('hoteis.checklist.store');
    Route::get('hoteis/{hotel}/checklist/imprimir', 'Petshop\\Hotel\\HotelChecklistController@imprimir')->name('hoteis.checklist.imprimir');
    // Rotas de eventos do quarto devem ser registradas antes do resource para evitar conflito com {quarto}
    Route::get('quartos/eventos', 'Petshop\Hotel\QuartoEventoController@index')->name('quartos.eventos.index');
    Route::get('quartos/eventos/create', 'Petshop\Hotel\QuartoEventoController@create')->name('quartos.eventos.create');
    Route::get('quartos/eventos/edit/{id}', 'Petshop\Hotel\QuartoEventoController@edit')->name('quartos.eventos.edit');
    Route::post('quartos/eventos', 'Petshop\Hotel\QuartoEventoController@store')->name('quartos.eventos.store');
    Route::put('quartos/eventos/{id}', 'Petshop\Hotel\QuartoEventoController@update')->name('quartos.eventos.update');
    Route::delete('quartos/eventos/{id}', 'Petshop\Hotel\QuartoEventoController@destroy')->name('quartos.eventos.destroy');
    Route::post('quartos/eventos/{evento}/finalizar', 'Petshop\Hotel\QuartoEventoController@finalizar')->name('quartos.eventos.finalizar');
    Route::resource('quartos', 'Petshop\\Hotel\\QuartoController');

    Route::prefix('monitoramento')->group(function () {
      Route::get('hotel', 'Petshop\\Hotel\\MonitoramentoHotelController@index')->name('hotel.monitoramento.hotel');
      Route::get('hotel/{quarto}', 'Petshop\\Hotel\\MonitoramentoHotelController@show')->name('hotel.monitoramento.hotel.show');
      Route::get('quartos', 'Petshop\\Hotel\\MonitoramentoQuartoController@index')->name('hotel.monitoramento.quartos');
    });
  });

  Route::prefix('creche')->group(function () {

    Route::resource('creches', 'Petshop\Creche\CrecheController');
    Route::get('endereco-entrega/{id}', 'Petshop\\Creche\\CrecheController@printEnderecoEntrega')->name('creches.endereco_entrega');
    Route::put('creches/{creche}/move', 'Petshop\Creche\CrecheController@move')->name('creches.move');
    Route::post('creches/{creche}/servicos', 'Petshop\Creche\CrecheController@attachServicos')->name('creches.servicos.attach');
    Route::get('creches/{creche}/checklist', 'Petshop\Creche\CrecheChecklistController@create')->name('creches.checklist.create');
    Route::get('creches/{creche}/imprimir', 'Petshop\Creche\CrecheChecklistController@imprimir')->name('creches.checklist.imprimir');
    Route::post('creches/{creche}/checklist', 'Petshop\Creche\CrecheChecklistController@store')->name('creches.checklist.store');
    Route::get('turmas/eventos', 'Petshop\Creche\TurmaEventoController@index')->name('turmas.eventos.index');
    Route::get('turmas/eventos/create', 'Petshop\Creche\TurmaEventoController@create')->name('turmas.eventos.create');
    Route::get('turmas/eventos/edit/{id}', 'Petshop\Creche\TurmaEventoController@edit')->name('turmas.eventos.edit');
    Route::post('turmas/eventos', 'Petshop\Creche\TurmaEventoController@store')->name('turmas.eventos.store');
    Route::put('turmas/eventos/{id}', 'Petshop\Creche\TurmaEventoController@update')->name('turmas.eventos.update');
    Route::delete('turmas/eventos/{id}', 'Petshop\Creche\TurmaEventoController@destroy')->name('turmas.eventos.destroy');
    Route::post('turmas/eventos/{evento}/finalizar', 'Petshop\Creche\TurmaEventoController@finalizar')->name('turmas.eventos.finalizar');
    Route::resource('turmas', 'Petshop\Creche\TurmaController');

	Route::prefix('monitoramento')->group(function () {
		Route::get('creche', 'Petshop\Creche\MonitoramentoCrecheController@index')->name('creche.monitoramento.creche');
		Route::get('creche/{turma}', 'Petshop\Creche\MonitoramentoCrecheController@show')->name('creche.monitoramento.creche.show');
		Route::get('salas', 'Petshop\Creche\MonitoramentoSalaController@index')->name('creche.monitoramento.salas');
	});
  });

  Route::prefix('estetica')->group(function () {
    Route::get('config', 'Petshop\\Estetica\\EsteticaConfigController@index')->name('estetica.config.index');
    Route::resource('esteticas', 'Petshop\\Estetica\\EsteticaController');
    Route::get('endereco-entrega/{id}', 'Petshop\\Estetica\\EsteticaController@printEnderecoEntrega')->name('esteticas.endereco_entrega');
    Route::post('estetica/agend', 'Petshop\\Estetica\\EsteticaController@agendstore')->name('estetica.esteticas.agendstore');

  });
   Route::prefix('esteticista')->group(function () {
    Route::get('agendamentos/pendente', 'Petshop\\Estetica\\EsteticaController@pendentes')->name('petshop.esteticista.agendamentos.pendente');
    Route::get('agendamentos/pendente-avulso', 'Petshop\\Estetica\\EsteticaController@pendentesAvulso')->name('petshop.esteticista.agendamentos.pendente-avulso');
    Route::post('agendamentos/{estetica}/aprovar', 'Petshop\\Estetica\\EsteticaController@aprovar')->name('petshop.esteticista.agendamentos.aprovar');
    Route::post('agendamentos/{estetica}/rejeitar', 'Petshop\\Estetica\\EsteticaController@rejeitar')->name('petshop.esteticista.agendamentos.rejeitar');
  });

});
