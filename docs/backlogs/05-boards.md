# #5 — CRUD de Boards

> Como gestor, eu quero organizar o trabalho de cada projeto em quadros (sprints/fases).

## Endpoints

- `GET /projects/{projectId}/boards` → 200 ou 404
- `GET /projects/{projectId}/boards/{id}` → 200 ou 404
- `POST /projects/{projectId}/boards` → 201 ou 404
- `PUT /projects/{projectId}/boards/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/boards/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| project_id | FK | referencia projects.id, cascade delete |
| name | string | obrigatório, máx 100 |
| status | string | obrigatório, máx 20, default 'active' |

## Critérios de aceite

- validar se o projeto existe antes de qualquer operação
- `project_id` vem pela URL, não pelo body
