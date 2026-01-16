# Backlog Item #8: CRUD de Tarefas do Projeto

## Contexto de negócio

O time de gestão precisa acompanhar as tarefas de cada projeto. Cada tarefa pertence a um projeto específico.

## User Story

Como gestor de projetos, eu quero gerenciar as tarefas vinculadas a cada projeto para acompanhar o andamento das entregas.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/tasks` | index | 200 ou 404 (projeto não existe) |
| `GET /projects/{projectId}/tasks/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/tasks` | store | 201 ou 404 |
| `PUT /projects/{projectId}/tasks/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/tasks/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `tasks`

- `id` — inteiro, auto increment, chave primária
- `project_id` — inteiro, obrigatório, **chave estrangeira referenciando `projects.id`**
- `title` — string, obrigatório, máximo 255 caracteres
- `description` — texto, opcional
- `priority` — string, obrigatório, máximo 20 caracteres, default 'medium'
- `completed` — boolean, obrigatório, default false
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `title` — obrigatório, string, máximo 255
- `description` — opcional, string
- `priority` — obrigatório, string, máximo 20
- `completed` — obrigatório, boolean

### Relacionamentos

- No Model `Task`: uma task **pertence a** um project (`belongsTo`)
- No Model `Project`: um project **tem muitas** tasks (`hasMany`)

### Observação

O `project_id` não vem no body do request. Ele vem pela URL (`/projects/{projectId}/tasks`). Tu precisa setar ele manualmente antes de salvar.

## O que é novo aqui

- **Chave estrangeira na migration** — `$table->foreignId('project_id')->constrained()->onDelete('cascade')`
- **`belongsTo` e `hasMany`** — relacionamentos nos Models
- **Rota aninhada** — a task sempre vem dentro de um projeto
- **Validar se o projeto existe** antes de listar/criar tasks
