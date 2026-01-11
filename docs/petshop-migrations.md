# Pet Shop — Trilho de aprendizado (tabelas, relações e plano de migrations)

Este arquivo é para o time entender **o que existe hoje** (dump), **como vamos padronizar** e **qual a sequência de migrations**.

## 1) Fonte e contexto (onde veio isso)
- Dump completo com Pet Shop: `wwdipr_erp (10).sql` (lá existem `petshop_*`, `petshop_vet_*` e `animais_*`).
- Base atual do sistema: `bd.sql` (lá **não** aparecem as tabelas Pet Shop).

## 2) Decisões já tomadas (padrão do projeto)
- **Nomes de tabelas**: `snake_case`, plural, pt-BR.
- **Prefixo do domínio Pets**: usar `petshop_animais_*` (DECIDIDO ✅).
- **Sem misturar** `pet_shop_*` com `petshop_*` (vamos tratar `pet_shop_*` como legado do dump).

## 3) Padrões técnicos (alinhado ao `bd.sql`)
Esta seção define as regras de modelagem/migrations para manter 100% o padrão do projeto.

### 3.1) Tipos de PK/FK e convenção de colunas
- PK: seguir o padrão do projeto com `increments('id')` (INT) nas tabelas do Pet Shop.
- FKs: seguir `unsignedInteger('<coluna>')` (ou equivalente) para manter compatível com PK `increments`.
- Coluna de FK: `<tabela_singular>_id` (ex.: `cliente_id`, `animal_id`, `empresa_id`, `filial_id`).
- Datas e soft delete:
  - `timestamps()` em tabelas “principais”.
  - `softDeletes()` quando o histórico for importante (tabelas operacionais e cadastros).

### 3.2) Escopo multi-empresa e filial (como o sistema opera)
- `empresa_id`: sempre que o dado for “da empresa” (cadastros e configurações).
- `filial_id`: sempre que o dado precisar ser segregado por filial (regras/planos por filial, configuração por filial, atendimentos/vacinação por filial).
- Regra geral:
  - “Cadastro global da empresa” → `empresa_id`.
  - “Operação do dia-a-dia / agenda / atendimento” → `empresa_id` + `filial_id` (quando aplicável).

### 3.1) “Filial” (importantíssimo)
- No seu sistema: **`local_id` = `FILIAL_ID`** (você já sinalizou isso).
- No sistema existe tabela `filials` (migration `database/migrations/2013_11_17_155930_create_filials_table.php`).
- No dump do Pet Shop aparecem colunas `local_id`/`localizacao_id` apontando para `localizacaos`.

Decisão (FECHADO ✅, seguindo 100% o `bd.sql`):
- Padronizar Pet Shop em **`filial_id` → `filials.id`**.
- Renomear `local_id`/`localizacao_id` → `filial_id`.
- Ignorar/remover o conceito de `localizacaos` do módulo.

### 3.3) Deletes e integridade referencial (FKs)
- Padrão recomendado (evita “dados órfãos” e segue o estilo do projeto):
  - Tabelas filhas/pivot (ex.: doses, anexos, itens) → `onDelete('cascade')`.
  - Entidades “históricas” com vínculo forte (ex.: evoluções do prontuário) → `cascade` quando o “pai” for removido.
  - Relações opcionais (se existirem) → `nullable()` + `nullOnDelete()` (usar só quando fizer sentido no fluxo).

### 3.4) Índices (performance e filtros padrão)
- Criar índices para colunas de filtro/relacionamento:
  - `empresa_id`, `filial_id`, `cliente_id`, `animal_id`, `veterinario_id/medico_id`, `atendimento_id`, `status`, `datahora_*`.
- Uniques quando necessário (exemplos a confirmar no módulo):
  - `petshop_planos`: `unique(['empresa_id','filial_id','slug'])` (ou `empresa_id + slug` se slug for global na empresa).

### 3.5) Auditoria (usuários que criam/alteram)
- Quando existir `created_by/updated_by` no módulo Vet, a referência deve seguir o padrão do sistema (normalmente `users.id`).
- Regra: usar `unsignedInteger` e FK para a tabela “oficial” de usuários do projeto (confirmar se é `users` em produção).

### 3.6) Soft delete (onde usar)
- Recomendado manter `softDeletes()` em:
  - Cadastros (pets, espécies, raças, pelagens, diagnósticos, exames).
  - Operacionais (atendimentos, prontuários, prescrições, vacinação, internações), se o sistema precisa histórico.
- Pivots e tabelas de “itens/anexos” geralmente podem ser hard delete (mas podem herdar histórico por cascata).

### 3.7) Tabelas pivot (nomenclatura e timestamps)
- Nomear pivots com os dois lados no plural e prefixo do domínio:
  - `petshop_vet_vacina_especies`, `petshop_vet_medicamento_especies` (mantém como já está no módulo).
- `timestamps()` na pivot: usar quando houver auditoria de quando associou (ex.: protocolos/espécies); caso contrário, pode omitir.

### 3.8) Estratégia de migração (como sair do dump pro padrão)
- Preferência do projeto:
  1) `rename table` quando a base já existe e a mudança for “só nome”, minimizando risco e downtime.
  2) Caso o banco/ambiente não permita rename seguro: criar tabela nova + copiar dados + trocar FKs (mais trabalhoso).
- Legado `pet_shop_*`:
  - Se houver uso/dados: migration de **migração de dados** `pet_shop_*` → `petshop_*` e depois drop.

## 4) Mapa por domínio (o que o módulo usa)

### 4.1) Domínio “Pets” (cadastro e consultas)
Objetivo do rename (DECIDIDO ✅):
- `animais_*` → `petshop_animais_*`

Tabelas (hoje → alvo) e relações principais:
- `animais` → `petshop_animais`
  - FKs: `empresa_id` → `empresas.id`, `cliente_id` → `clientes.id`
  - FKs: `especie_id` → `petshop_animais_especies.id`, `raca_id` → `petshop_animais_racas.id`, `pelagem_id` → `petshop_animais_pelagens.id`
- `animais_especies` → `petshop_animais_especies` (FK: `empresa_id` → `empresas.id`)
- `animais_racas` → `petshop_animais_racas` (FKs: `empresa_id` → `empresas.id`, `especie_id` → `petshop_animais_especies.id`)
- `animais_pelagens` → `petshop_animais_pelagens` (FK: `empresa_id` → `empresas.id`)
- `animais_diagnosticos` → `petshop_animais_diagnosticos`
  - FKs: `empresa_id` → `empresas.id`, `animal_id` → `petshop_animais.id`, `funcionario_id` → `funcionarios.id`
- `animais_exames` → `petshop_animais_exames` (FK: `empresa_id` → `empresas.id`)
- `animais_consultas` → `petshop_animais_consultas`
  - FKs: `empresa_id` → `empresas.id`, `animal_id` → `petshop_animais.id`
  - FKs: `diagnostico_id` → `petshop_animais_diagnosticos.id`, `exame_id` → `petshop_animais_exames.id`

### 4.2) Domínio “Planos e assinaturas”
Tabelas e relações:
- `petshop_planos`
  - FKs: `empresa_id` → `empresas.id`
  - FK do local (dump legado): `local_id` → `localizacaos.id` (ver decisão “Filial” acima)
- `petshop_plano_versoes` (FK: `plano_id` → `petshop_planos.id`)
- `petshop_plano_servicos` (FKs: `plano_versao_id` → `petshop_plano_versoes.id`, `servico_id` → `servicos.id`)
- `petshop_plano_produtos` (FKs: `plano_versao_id` → `petshop_plano_versoes.id`, `produto_id` → `produtos.id`, `variacao_id` → `produto_variacaos.id`)
- `petshop_assinaturas` (FKs: `plano_id` → `petshop_planos.id`, `plano_versao_id` → `petshop_plano_versoes.id`)
- `petshop_consumos_servicos` (FKs: `assinatura_id` → `petshop_assinaturas.id`, `servico_id` → `servicos.id`)
- `petshop_consumos_produtos` (FKs: `assinatura_id` → `petshop_assinaturas.id`, `produto_id` → `produtos.id`)

Legado no dump (fora do padrão):
- `pet_shop_planos`
- `pet_shop_plano_servico`

Plano:
- Tratar `petshop_*` como oficial.
- Se essas tabelas legadas tiverem dados/uso: migration de **migração de dados** `pet_shop_*` → `petshop_*` e depois drop.

### 4.3) Domínio “Configuração / Agenda / Suporte”
Tabelas e relações:
- `petshop_configs`
  - FKs: `empresa_id` → `empresas.id`
  - FK do local (dump legado): `localizacao_id` → `localizacaos.id` (ver decisão “Filial” acima)
- `petshop_horarios_alternativos` (FK: `config_id` → `petshop_configs.id`)
- `petshop_salas_atendimento` (FK: `empresa_id` → `empresas.id`)
- `petshop_salas_internacao` (FK: `empresa_id` → `empresas.id`)
- `petshop_medicos` (FKs: `empresa_id` → `empresas.id`, `funcionario_id` → `funcionarios.id`)

### 4.4) Domínio “Vacinação”
Tabelas e relações:
- `petshop_vacinacoes`
  - FKs: `empresa_id` → `empresas.id`
  - FKs: `animal_id` → `petshop_animais.id`, `cliente_id` → `clientes.id`
  - FKs: `medico_id` → `petshop_medicos.id`, `protocolo_id` → `petshop_vet_vacinas.id`
  - FKs: `sala_atendimento_id` → `petshop_salas_atendimento.id`, `attendance_id` → `petshop_vet_atendimentos.id`
- `petshop_vacinacao_doses` (FKs: `vacinacao_id` → `petshop_vacinacoes.id`, `vacina_id` → `petshop_vet_vacinas.id`)
- `petshop_vacinacao_sessoes` (FK: `vacinacao_id` → `petshop_vacinacoes.id`)
- `petshop_vacinacao_sessao_doses` (FKs: `sessao_id` → `petshop_vacinacao_sessoes.id`, `dose_planejada_id` → `petshop_vacinacao_doses.id`, `responsavel_id` → `users.id`)
- `petshop_vacinacao_eventos` (FKs: `vacinacao_id` → `petshop_vacinacoes.id`, `registrado_por` → `users.id`)

### 4.5) Domínio “Veterinário (Vet)”
Núcleo do atendimento:
- `petshop_vet_atendimentos`
  - FKs: `empresa_id` → `empresas.id`
  - FKs: `animal_id` → `petshop_animais.id`, `tutor_id` → `clientes.id`
  - FKs: `veterinario_id` → `petshop_medicos.id`, `sala_id` → `petshop_salas_atendimento.id`, `servico_id` → `servicos.id`
- `petshop_vet_atendimento_anexos` (FK: `atendimento_id` → `petshop_vet_atendimentos.id`)
- `petshop_vet_atendimento_faturamentos` (FKs: `empresa_id` → `empresas.id`, `atendimento_id` → `petshop_vet_atendimentos.id`)
- `petshop_vet_atendimento_faturamento_produtos` (FKs: `faturamento_id` → `petshop_vet_atendimento_faturamentos.id`, `produto_id` → `produtos.id`)
- `petshop_vet_atendimento_faturamento_servicos` (FKs: `faturamento_id` → `petshop_vet_atendimento_faturamentos.id`, `servico_id` → `servicos.id`)

Prontuário / Prescrição / Exames:
- `petshop_vet_prontuarios` (FKs: `empresa_id` → `empresas.id`, `atendimento_id` → `petshop_vet_atendimentos.id`, `animal_id` → `petshop_animais.id`, `tutor_id` → `clientes.id`, `veterinario_id` → `petshop_medicos.id`, `modelo_avaliacao_id` → `petshop_vet_modelos_avaliacao.id`, `created_by/updated_by` → `users.id`)
- `petshop_vet_prontuario_evolucoes` (FKs: `prontuario_id` → `petshop_vet_prontuarios.id`, `autor_id` → `petshop_medicos.id`)
- `petshop_vet_prescricoes` (FKs: `empresa_id` → `empresas.id`, `animal_id` → `petshop_animais.id`, `veterinario_id` → `petshop_medicos.id`, `atendimento_id` → `petshop_vet_atendimentos.id`, `prontuario_id` → `petshop_vet_prontuarios.id`, `modelo_prescricao_id` → `petshop_vet_modelos_prescricao.id`, `created_by/updated_by` → `users.id`)
- `petshop_vet_prescricao_medicamentos` (FKs: `prescricao_id` → `petshop_vet_prescricoes.id`, `medicamento_id` → `petshop_vet_medicamentos.id`)
- `petshop_vet_prescricao_canais` (FK: `prescricao_id` → `petshop_vet_prescricoes.id`)
- `petshop_vet_prescricao_alergia` (FKs: `prescricao_id` → `petshop_vet_prescricoes.id`, `alergia_id` → `petshop_vet_alergias.id`)
- `petshop_vet_prescricao_condicao_cronica` (FKs: `prescricao_id` → `petshop_vet_prescricoes.id`, `condicao_cronica_id` → `petshop_vet_condicoes_cronicas.id`)
- `petshop_vet_exames` (FKs: `empresa_id` → `empresas.id`, `atendimento_id` → `petshop_vet_atendimentos.id`, `animal_id` → `petshop_animais.id`, `medico_id` → `petshop_medicos.id`, `exame_id` → `petshop_animais_exames.id`)
- `petshop_vet_exame_analises` (FK: `exame_id` → `petshop_vet_exames.id`)
- `petshop_vet_exame_anexos` (FK: `exame_id` → `petshop_vet_exames.id`)

Internações:
- `petshop_vet_internacoes` (FKs: `empresa_id` → `empresas.id`, `animal_id` → `petshop_animais.id`, `tutor_id` → `clientes.id`, `atendimento_id` → `petshop_vet_atendimentos.id`, `veterinario_id` → `petshop_medicos.id`, `sala_internacao_id` → `petshop_salas_internacao.id`)
- `petshop_vet_internacao_status` (FKs: `empresa_id` → `empresas.id`, `internacao_id` → `petshop_vet_internacoes.id`)

Cadastros Vet:
- `petshop_vet_checklists` (FK: `empresa_id` → `empresas.id`)
- `petshop_vet_alergias` (FK: `empresa_id` → `empresas.id`)
- `petshop_vet_condicoes_cronicas` (FK: `empresa_id` → `empresas.id`)
- `petshop_vet_medicamentos` (FKs: `empresa_id` → `empresas.id`, `produto_id` → `produtos.id`)
- `petshop_vet_medicamento_especies` (FKs: `medicamento_id` → `petshop_vet_medicamentos.id`, `especie_id` → `petshop_animais_especies.id`)
- `petshop_vet_vacinas` (FK: `empresa_id` → `empresas.id`)
- `petshop_vet_vacina_especies` (FKs: `vacina_id` → `petshop_vet_vacinas.id`, `especie_id` → `petshop_animais_especies.id`)
- `petshop_vet_modelos_atendimento` (FKs: `empresa_id` → `empresas.id`, `created_by/updated_by` → `users.id`)
- `petshop_vet_modelos_avaliacao` (FKs: `empresa_id` → `empresas.id`, `created_by/updated_by` → `users.id`)
- `petshop_vet_modelos_prescricao` (FKs: `empresa_id` → `empresas.id`, `created_by/updated_by` → `users.id`)

### 4.6) Domínio “Hotel” (quartos e agenda de quarto)
Cadastros e eventos base do módulo Hotel:
- `quartos`
  - FKs: `empresa_id` → `empresas.id`
  - FKs: `colaborador_id` → `funcionarios.id` (nullable)
  - Campos: `nome`, `descricao`, `tipo`, `capacidade`, `status`
- `quarto_eventos`
  - FKs: `quarto_id` → `quartos.id`
  - FKs opcionais: `servico_id` → `servicos.id`, `prestador_id` → `funcionarios.id`, `fornecedor_id` → `fornecedores.id`
  - Campos: `inicio`, `fim`, `descricao`

## 5) Sequência de migrations (o “como vamos implementar”)
Pré-requisito:
- Fechar decisão de “Filial” (item 3.1).

Ordem sugerida:
1) **Renomes de tabelas** `animais_*` → `petshop_animais_*` (e ajustar FKs que apontam para `animais`/`animais_*`).
2) **Ajustar colunas de filial** (renome `local_id`/`localizacao_id` → `filial_id`) e criar FK para `filials`.
3) **Garantir FKs e índices** do módulo inteiro (planos, configs, vacinas, vet).
4) **Migração de legado** (`pet_shop_*` → `petshop_*`) se existir uso/dados.
5) **Atualizar Models** com `protected $table = '...'` (pós-migration).
