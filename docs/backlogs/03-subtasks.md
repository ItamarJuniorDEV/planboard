# #3 — CRUD de Subtasks

> Como gestor, eu quero quebrar tarefas grandes em subtarefas menores para facilitar o acompanhamento.

## Endpoints

- `GET /projects/{projectId}/tasks/{taskId}/subtasks` → 200 ou 404
- `GET /projects/{projectId}/tasks/{taskId}/subtasks/{id}` → 200 ou 404
- `POST /projects/{projectId}/tasks/{taskId}/subtasks` → 201 ou 404
- `PUT /projects/{projectId}/tasks/{taskId}/subtasks/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/tasks/{taskId}/subtasks/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| task_id | FK | referencia tasks.id, cascade delete |
| title | string | obrigatório, máx 255 |
| done | boolean | obrigatório, default false |

## Critérios de aceite

- validar a cadeia completa: projeto existe → task pertence ao projeto → subtask pertence à task
- `task_id` vem pela URL, não pelo body
