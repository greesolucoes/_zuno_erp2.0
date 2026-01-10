# AGENTS.md

Instruções para agentes ao criar/alterar **Controllers**, **Services** e **Models** neste projeto (Laravel).

## Escopo
- Estas regras valem para:
  - `app/Http/Controllers/**` (Controllers)
  - `app/Services/**` (Services)
  - `app/Models/**` (Models)
  - `resources/views/**` (Blade / Front-end)
  - `public/js/**` (JS do front)
- Não inclua regras de Migration aqui.

## Backend (nunca esquecer)
- Multi-tenant: em consultas e gravações, respeitar `empresa_id` (padrão: `request()->empresa_id`/`$request->empresa_id`).
- Acesso ao registro: quando o módulo usar, validar com `__valida_objeto($item)` e negar com `abort(403)` antes de editar/remover/baixar.
- Validação: validar no início de `store()`/`update()`; não persistir dados sem `_validate(...)`.
- Transação: quando houver mais de 1 escrita relacionada, usar `DB::transaction(...)`.
- Erros: encapsular ações críticas em `try/catch`, manter `flash_sucesso/flash_erro` (web) e, quando existir no módulo, chamar `__saveLogError($e, request()->empresa_id)`.
- Paginação: quando for listagem, preferir `paginate(env("PAGINACAO"))` (padrão do projeto).
- Arquivos/diretórios: antes de gravar em `public_path(...)`, garantir pasta com `is_dir(...)` + `mkdir(..., 0777, true)` (padrão do projeto).
- API: retornar `response()->json(..., <status>)` e manter o padrão de status do módulo (ex.: `200`, `400/401`).
- Consistência: manter estilo do módulo (imports, view path, nomes) e não adicionar `error_reporting/ini_set('display_errors', ...)` novos.

## Local e namespace
- Controller “web” padrão: `app/Http/Controllers/<Nome>Controller.php` com `namespace App\Http\Controllers;`
- Controllers por módulo: criar dentro do diretório do módulo e ajustar namespace, ex.:
  - `app/Http/Controllers/Petshop/...` => `namespace App\Http\Controllers\Petshop;` (ou sub-namespace conforme a pasta)
  - `app/Http/Controllers/API/...` => `namespace App\Http\Controllers\API;`
- Sempre estender `App\Http\Controllers\Controller` (base do projeto).

## Nome do arquivo e da classe
- Nome do arquivo = nome da classe.
- Classe em **PascalCase** e sempre com sufixo `Controller`.
- Para rotas `Route::resource('clientes', 'ClienteController')`: manter o padrão atual do projeto (resource no plural, controller no singular).
- Para módulos que já usam plural (ex.: `VacinacoesController`), seguir o padrão já existente naquele módulo/diretório.

## Estrutura mínima de uma Controller (web)
Ordem sugerida (seguir padrão observado nas controllers existentes):
1) `use ...` (somente o que for usado)
2) `class ... extends Controller`
3) `__construct()` (apenas se precisar de middleware/DI/garantias de diretório)
4) Métodos públicos (rotas)
5) Métodos privados auxiliares (ex.: `_validate`)

## Métodos padrão (CRUD)
Quando a controller for “resource”, implementar (quando aplicável) os métodos:
- `index(Request $request)` (listagem; filtros com `$request`; paginação com `paginate(env("PAGINACAO"))` quando fizer sentido)
- `create()` (tela de cadastro)
- `store(Request $request)` (persistência)
- `edit($id)` (tela de edição)
- `update(Request $request, $id)` (atualização)
- `destroy(Request $request, $id)` (remoção)

Observações do padrão atual:
- Carregamento por id: preferir `Model::findOrFail($id)` em ações que exigem o registro.
- Multi-tenant/empresa: nas consultas, filtrar por `empresa_id` quando o padrão do módulo usar `request()->empresa_id`.
- Autorização do objeto: quando existir o helper no fluxo do módulo, validar com `__valida_objeto($item)` e, se falhar, `abort(403)`.

## Padrão para validação
- Centralizar validações em um método privado `private function _validate(Request $request)`.
- Chamar `_validate($request)` no início de `store()` e `update()`.
- Usar `$this->validate($request, $rules, $messages);` (padrão visto no projeto).
- Regras customizadas: usar `App\Rules\...` quando existir (ex.: `new ValidaDocumento`).

## Padrão para persistência e erro
- Em `store/update/destroy`:
  - Envolver persistência em `try/catch (\Exception $e)`.
  - Quando houver múltiplas operações, usar `DB::transaction(...)`.
  - Em sucesso: `session()->flash("flash_sucesso", "...");`
  - Em erro: `session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());` e registrar com `__saveLogError($e, request()->empresa_id)` quando o módulo usar log.
  - Finalizar com `return redirect()->route('...')` (ou `redirect()->back()` quando fizer sentido).

## Views e rotas (padrão do projeto)
- Views (web):
  - Módulos antigos usam paths com barra: `view('clientes/index')`, `view('clientes/create')`, `view('clientes/edit')`.
  - Petshop costuma usar dot notation: `view('petshop.config.index')`.
  - Regra: manter o padrão do diretório/módulo que você está mexendo (não “misturar” estilos sem necessidade).
- Rotas:
  - Se for `Route::resource`, garantir que os métodos do CRUD existam e os redirects usem `route('<resource>.<acao>')`.
  - Ações extras devem ser métodos em `camelCase` (ex.: `alterarStatus`, `baixarXml`, `downloadModelo`) e expostas por rotas explícitas com `->name('...')`.

## Convenções gerais
- Evitar lógica pesada de regra de negócio dentro da Controller: se já existir Service/Utils no projeto para o caso, usar/injetar.
- Evitar imports não utilizados.
- Não reformatar arquivos inteiros sem necessidade (manter estilo do arquivo/módulo).

## Fluxo padrão (Service + Controller + Model)
- Controller: valida request, aplica middleware/permissões/tenant, chama Service, e retorna `view/redirect/json` + `flash` quando web.
- Service: concentra regra de negócio e orquestra persistência (incluindo `DB::transaction` quando necessário).
- Model: concentra mapeamento/relacionamentos/casts/scopes/constants/accessors (sem dependência de `Request`/session/flash).

---

## Front-end (Blade)

## Layout e seções
- Padrão (admin/sistema): páginas web usam `@extends('default.layout', ['title' => '...'])` e `@section('content')`.
- CSS/JS por página: usar `@section('css')` e `@section('js')` (o layout faz `@yield('css')` e `@yield('js')`).

## Layouts do sistema (padrão atual)
- Admin/Sistema: `resources/views/default/layout.blade.php` => `@extends('default.layout', ['title' => '...'])`
  - Carrega scripts globais (toastr/flash, `jquery.mask`, select2, `public/js/main.js`).
  - Para máscaras, basta aplicar a classe (ex.: `moeda`, `cep`, etc); não adicionar lib novamente na página.
- Ecommerce (tema padrão): `resources/views/ecommerce/default.blade.php` => `@extends('ecommerce.default')`
- Ecommerce (One Tech): `resources/views/ecommerce_one_tech/default.blade.php` => `@extends('ecommerce_one_tech.default')`
- Relatórios/Impressão: `resources/views/relatorios/default.blade.php` => `@extends('relatorios.default')`

## Organização de views
- Pasta base: `resources/views/<recurso>/...`
  - Normalmente o nome da pasta segue o nome do recurso/rota em plural e em `snake_case` (ex.: `servicos`, `grupo_clientes`, `contas_bancarias`).
- Arquivos padrão do recurso: `index.blade.php`, `create.blade.php`, `edit.blade.php` (e `show.blade.php` quando existir).
- Parciais:
  - Formulário do recurso: `resources/views/<recurso>/_forms.blade.php` (padrão recorrente no projeto).
  - Parciais adicionais em `resources/views/<recurso>/partials/*.blade.php`.

## Forms (biblioteca do projeto)
- O projeto usa `netojose/laravel-bootstrap-4-forms` via facade `Form`.
- Padrão de uso:
  - `Form::open()->post()->route('<recurso>.store')` (create)
  - `Form::open()->fill($item)->put()->route('<recurso>.update', [$item->id])` (edit)
  - `->multipart()` quando houver upload.
- Campos seguem o builder fluente: `Form::text/tel/date/select/...()->required()->attrs([...])->value(...)`.

## Máscaras e helpers (padrão atual)
- Máscaras são aplicadas por classes CSS (via `public/js/main.js` + `jquery.mask`), ex.:
  - moeda: `class="moeda"` (usar `Form::tel(...)->attrs(['class' => 'moeda'])`)
  - outras classes existentes: `cep`, `data`, `perc`, `peso`, `ncm`, `placa`, etc (preferir reutilizar ao invés de criar máscara nova).
- Para valores monetários vindos do backend, preferir renderizar com `__moeda(...)` quando o padrão do formulário fizer isso.

## Rotas no Blade (padrão)
- Links e actions devem usar `route('<nome>')` (ex.: `route('servicos.index')`, `route('servicos.create')`) ao invés de URL “hardcoded”.
- Em telas de filtro, é comum `Form::open()->fill(request()->all())->get()` para manter parâmetros.

---

## Services

## Local e namespace
- Service padrão: `app/Services/<Nome>Service.php` com `namespace App\Services;`
- Services por módulo podem ficar em subpastas (ex.: `app/Services/Petshop/...`) com namespace equivalente.

## Nome do arquivo e da classe
- Nome do arquivo = nome da classe.
- Classe em **PascalCase** e sempre com sufixo `Service`.

## Estrutura mínima de um Service
Ordem sugerida:
1) `use ...` (somente o que for usado)
2) `class ...`
3) `__construct(...)` (injeções/config necessárias)
4) Métodos públicos (ações do domínio)
5) Métodos privados auxiliares

## Regras de responsabilidade
- Service NÃO deve retornar `view()`, `redirect()`, `session()->flash()` (isso é da Controller).
- Preferir receber tipos “do domínio” (ids, arrays validados, Models) ao invés do `Request`.
- Se ocorrer erro de regra de negócio, preferir lançar `\Exception` (ou retornar estrutura padronizada) e a Controller decide como responder.
- Operações com múltiplas escritas devem rodar em `DB::transaction(...)` (no Service ou na Controller; preferir no Service quando a regra é do domínio).

## Uso na Controller
- Sempre importar via `use App\Services\...;`.
- Preferir injeção no `__construct()` quando o Service não exige parâmetros dinâmicos.
- Quando o Service exigir config/parametros por requisição, instanciar explicitamente no método e manter a criação perto do uso.

---

## Models

## Local e namespace
- Model padrão: `app/Models/<Nome>.php` com `namespace App\Models;`
- Models por módulo: criar dentro do diretório do módulo e ajustar namespace, ex.:
  - `app/Models/Petshop/...` => `namespace App\Models\Petshop;` (ou sub-namespace conforme a pasta)

## Nome do arquivo e da classe
- Nome do arquivo = nome da classe.
- Classe em **PascalCase** (geralmente singular) e estender `Illuminate\Database\Eloquent\Model`.
- Se o nome da tabela não seguir a convenção do Laravel, definir `protected $table = '...'` (padrão já usado no projeto).

## Estrutura mínima de um Model
Ordem sugerida (manter o padrão do módulo/arquivo existente):
1) `use ...` (somente o que for usado)
2) `class ... extends Model`
3) Traits (`HasFactory`, `SoftDeletes`, traits do projeto)
4) `protected $table` (quando necessário)
5) `protected $fillable` (obrigatório)
6) `protected $casts` (quando necessário)
7) `protected $appends` + accessors (quando necessário)
8) Scopes (`scopeForCompany`, `scopeSearch`, etc) quando o módulo já usa
9) Relacionamentos (`belongsTo`, `hasMany`, etc)

## Traits (padrões do projeto)
- `HasFactory`: comum nos Models do módulo Petshop; manter o padrão do diretório/módulo (não adicionar/remover sem necessidade).
- `SoftDeletes`: usar quando a tabela tiver `deleted_at` (migration com `softDeletes()`); importar e aplicar o trait.
- Traits do projeto (ex.: `App\Traits\UppercaseFillable`): reutilizar quando o comportamento já existir (não reimplementar “na mão”).

## $fillable e $casts
- Sempre declarar `protected $fillable = [...]` com os campos graváveis.
- Incluir `empresa_id` quando o módulo for multi-tenant.
- Declarar `protected $casts` para campos que exigem tipo (padrões vistos no projeto):
  - `*_id` => `integer`
  - datas => `datetime`
  - JSON/arrays => `array`

## Scopes (quando aplicável)
- Se o módulo Petshop tiver padrão de escopo, preferir implementar/usar:
  - `scopeForCompany($query, int $empresaId)`/`scopeForCompany(Builder $query, int $companyId)` para filtrar por `empresa_id`.
  - `scopeSearch($query, ?string $term)` quando existir busca por termo.

## Relacionamentos
- Nomear métodos em `camelCase` e de forma descritiva (ex.: `salaAtendimento`, `scheduledByUser`).
- Declarar a chave estrangeira explicitamente quando não for a padrão do Laravel (padrão já usado: `belongsTo(Model::class, 'campo_id')`).
- Preferir `findOrFail`/validações de acesso na Controller; no Model manter relações, casts, scopes e helpers.
