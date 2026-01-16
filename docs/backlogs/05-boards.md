# Backlog Item #11: CRUD de Boards (Quadros)

## Contexto de negócio

O time precisa organizar as tarefas de cada projeto em quadros, tipo sprints. Exemplo: o projeto "Loja Virtual" pode ter os boards "Sprint 1", "Sprint 2", "Backlog".

## User Story

Como gestor de projetos, eu quero gerenciar quadros dentro de cada projeto para organizar o trabalho em sprints ou fases.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/boards` | index | 200 ou 404 |
| `GET /projects/{projectId}/boards/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/boards` | store | 201 ou 404 |
| `PUT /projects/{projectId}/boards/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/boards/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `boards`

- `id` — inteiro, auto increment, chave primária
- `project_id` — inteiro, obrigatório, **chave estrangeira referenciando `projects.id`**
- `name` — string, obrigatório, máximo 100 caracteres
- `status` — string, obrigatório, máximo 20 caracteres, default 'active'
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `name` — obrigatório, string, máximo 100
- `status` — obrigatório, string, máximo 20

### Relacionamentos

- No Model `Board`: um board **pertence a** um project (`belongsTo`)
- No Model `Project`: um project **tem muitos** boards (`hasMany`)

### Observação

O `project_id` vem pela URL, não pelo body.
