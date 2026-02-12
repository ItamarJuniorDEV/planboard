# #6 — CRUD de Milestones

> Como gestor, eu quero definir marcos importantes dentro de cada projeto para acompanhar entregas-chave.

## Endpoints

- `GET /projects/{projectId}/milestones` → 200 ou 404
- `GET /projects/{projectId}/milestones/{id}` → 200 ou 404
- `POST /projects/{projectId}/milestones` → 201 ou 404
- `PUT /projects/{projectId}/milestones/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/milestones/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| project_id | FK | referencia projects.id, cascade delete |
| title | string | obrigatório, máx 120 |
| due_date | date | opcional |

## Critérios de aceite

- validar se o projeto existe antes de qualquer operação
- `project_id` vem pela URL, não pelo body
