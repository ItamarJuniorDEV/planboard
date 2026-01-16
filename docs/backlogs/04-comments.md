# Backlog Item #10: CRUD de Comentários da Tarefa

## Contexto de negócio

O time de gestão precisa registrar observações, atualizações e alinhamentos dentro de cada tarefa. Exemplo: na tarefa "Criar layout", pode existir comentário como "Aguardar aprovação do cliente" ou "Header já finalizado".

## User Story

Como gestor de projetos, eu quero gerenciar comentários dentro de cada tarefa para registrar informações importantes sobre o andamento do trabalho.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/tasks/{taskId}/comments` | index | 200 ou 404 |
| `GET /projects/{projectId}/tasks/{taskId}/comments/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/tasks/{taskId}/comments` | store | 201 ou 404 |
| `PUT /projects/{projectId}/tasks/{taskId}/comments/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/tasks/{taskId}/comments/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `comments`

- `id` — inteiro, auto increment, chave primária
- `task_id` — inteiro, obrigatório, **chave estrangeira referenciando `tasks.id`**
- `content` — texto, obrigatório
- `author` — string, obrigatório, máximo 100 caracteres
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `content` — obrigatório, string
- `author` — obrigatório, string, máximo 100

### Relacionamentos

- No Model `Comment`: um comentário **pertence a** uma task (`belongsTo`)
- No Model `Task`: uma task **tem muitos** comentários (`hasMany`)

### Observação

O `task_id` vem pela URL, não pelo body.

**IMPORTANTE:** mesmo o comentário sendo filho da task, tu precisa validar a **cadeia inteira**: o projeto existe? A task existe DENTRO desse projeto? Só depois mexe no comentário.
