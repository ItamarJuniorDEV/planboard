# #4 — CRUD de Comments

> Como gestor, eu quero registrar observações dentro de cada tarefa para documentar o andamento.

## Endpoints

- `GET /projects/{projectId}/tasks/{taskId}/comments` → 200 ou 404
- `GET /projects/{projectId}/tasks/{taskId}/comments/{id}` → 200 ou 404
- `POST /projects/{projectId}/tasks/{taskId}/comments` → 201 ou 404
- `PUT /projects/{projectId}/tasks/{taskId}/comments/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/tasks/{taskId}/comments/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| task_id | FK | referencia tasks.id, cascade delete |
| content | text | obrigatório |
| author | string | obrigatório, máx 100 |

## Critérios de aceite

- validar a cadeia completa: projeto existe → task pertence ao projeto → comment pertence à task
- `task_id` vem pela URL, não pelo body
