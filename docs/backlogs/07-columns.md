# #7 — CRUD de Columns

> Como gestor, eu quero organizar tarefas em colunas Kanban dentro de cada board.

## Endpoints

- `GET /projects/{projectId}/boards/{boardId}/columns` → 200 ou 404
- `GET /projects/{projectId}/boards/{boardId}/columns/{id}` → 200 ou 404
- `POST /projects/{projectId}/boards/{boardId}/columns` → 201 ou 404
- `PUT /projects/{projectId}/boards/{boardId}/columns/{id}` → 200 ou 404
- `DELETE /projects/{projectId}/boards/{boardId}/columns/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| board_id | FK | referencia boards.id, cascade delete |
| name | string | obrigatório, máx 100 |
| position | integer | obrigatório, mín 1 |

## Critérios de aceite

- validar a cadeia completa: projeto existe → board pertence ao projeto → column pertence ao board
- `board_id` vem pela URL, não pelo body
