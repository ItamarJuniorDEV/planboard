# Backlog Item #3: CRUD de Subtarefas da Tarefa

## Contexto de negócio

O time percebeu que algumas tarefas são grandes demais e precisam ser quebradas em subtarefas menores. Exemplo: a tarefa "Criar layout do site" pode ter subtarefas como "Desenhar header", "Desenhar footer", "Escolher paleta de cores".

## User Story

Como gestor de projetos, eu quero gerenciar subtarefas dentro de cada tarefa para quebrar entregas grandes em pedaços menores.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/tasks/{taskId}/subtasks` | index | 200 ou 404 |
| `GET /projects/{projectId}/tasks/{taskId}/subtasks/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/tasks/{taskId}/subtasks` | store | 201 ou 404 |
| `PUT /projects/{projectId}/tasks/{taskId}/subtasks/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/tasks/{taskId}/subtasks/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `subtasks`

- `id` — inteiro, auto increment, chave primária
- `task_id` — inteiro, obrigatório, **chave estrangeira referenciando `tasks.id`**
- `title` — string, obrigatório, máximo 255 caracteres
- `done` — boolean, obrigatório, default false
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `title` — obrigatório, string, máximo 255
- `done` — obrigatório, boolean

### Relacionamentos

- No Model `Subtask`: uma subtask **pertence a** uma task (`belongsTo`)
- No Model `Task`: uma task **tem muitas** subtasks (`hasMany`)

### Observação

O `task_id` vem pela URL, não pelo body.

**IMPORTANTE:** mesmo a subtask sendo filha da task, tu precisa validar a **cadeia inteira**: o projeto existe? A task existe DENTRO desse projeto? Só depois mexe na subtask.
