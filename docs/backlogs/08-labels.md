# #8 — CRUD de Labels

> Como usuário, eu quero categorizar itens do projeto com etiquetas coloridas.

## Endpoints

- `GET /projects/{projectId}/labels` → 200 ou 404
- `GET /projects/{projectId}/labels/{id}` → 200 ou 404
- `POST /projects/{projectId}/labels` → 201 ou 404
- `PUT /projects/{projectId}/labels/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/labels/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| project_id | FK | referencia projects.id, cascade delete |
| name | string | obrigatório, máx 50 |
| color | string | obrigatório, máx 30 |

## Critérios de aceite

- validar se o projeto existe antes de qualquer operação
- `project_id` vem pela URL, não pelo body
