# #2 — CRUD de Tasks

> Como gestor, eu quero gerenciar as tarefas de cada projeto para acompanhar as entregas.

## Endpoints

- `GET /projects/{projectId}/tasks` → 200 ou 404
- `GET /projects/{projectId}/tasks/{id}` → 200 ou 404
- `POST /projects/{projectId}/tasks` → 201 ou 404
- `PUT /projects/{projectId}/tasks/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/tasks/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| project_id | FK | referencia projects.id, cascade delete |
| title | string | obrigatório, máx 255 |
| description | text | opcional |
| priority | string | obrigatório, máx 20, default 'medium' |

## Critérios de aceite

- validar se o projeto existe antes de qualquer operação
- `project_id` vem pela URL, não pelo body
