# Backlog Item #13: CRUD de Colunas do Board

## Contexto de negócio

Dentro de cada Board, o time precisa organizar tarefas em colunas tipo Kanban. Exemplo: o projeto "Loja Virtual", board "Sprint 1", com colunas "To Do", "Doing", "Done".

## User Story

Como gestor de projetos, eu quero gerenciar colunas dentro de cada board para organizar o fluxo das tarefas.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/boards/{boardId}/columns` | index | 200 ou 404 |
| `GET /projects/{projectId}/boards/{boardId}/columns/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/boards/{boardId}/columns` | store | 201 ou 404 |
| `PUT /projects/{projectId}/boards/{boardId}/columns/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/boards/{boardId}/columns/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `columns`

- `id` — inteiro, auto increment, chave primária
- `board_id` — inteiro, obrigatório, **chave estrangeira referenciando `boards.id`**
- `name` — string, obrigatório, máximo 100 caracteres
- `position` — inteiro, obrigatório, mínimo 1
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `name` — obrigatório, string, máximo 100
- `position` — obrigatório, integer, mínimo 1

### Relacionamentos

- No Model `Column`: uma column **pertence a** um board (`belongsTo`)
- No Model `Board`: um board **tem muitas** columns (`hasMany`)

### Observação

O `board_id` vem pela URL, não pelo body. Precisa validar a cadeia: projeto existe? Board existe dentro do projeto? Só depois mexe na coluna.
