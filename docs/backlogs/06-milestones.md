# Backlog Item #12: CRUD de Milestones (Marcos)

## Contexto de negócio

O time precisa definir marcos importantes dentro de cada projeto para acompanhar entregas-chave. Exemplo: "API finalizada", "Deploy em produção", "Entrega do MVP".

## User Story

Como gestor de projetos, eu quero gerenciar marcos dentro de cada projeto para acompanhar as entregas principais.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/milestones` | index | 200 ou 404 |
| `GET /projects/{projectId}/milestones/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/milestones` | store | 201 ou 404 |
| `PUT /projects/{projectId}/milestones/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/milestones/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `milestones`

- `id` — inteiro, auto increment, chave primária
- `project_id` — inteiro, obrigatório, **chave estrangeira referenciando `projects.id`**
- `title` — string, obrigatório, máximo 120 caracteres
- `due_date` — date, obrigatório
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `title` — obrigatório, string, máximo 120
- `due_date` — obrigatório, date

### Relacionamentos

- No Model `Milestone`: um milestone **pertence a** um project (`belongsTo`)
- No Model `Project`: um project **tem muitos** milestones (`hasMany`)

### Observação

O `project_id` vem pela URL, não pelo body.
