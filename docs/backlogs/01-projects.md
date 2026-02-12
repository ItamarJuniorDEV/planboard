# #1 — CRUD de Projetos

> Como usuário, eu quero gerenciar projetos para acompanhar o trabalho da empresa.

## Endpoints

- `GET /projects` → 200
- `GET /projects/{id}` → 200 ou 404
- `POST /projects` → 201
- `PUT /projects/{id}` → 200 ou 404
- `DELETE /projects/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| title | string | obrigatório, máx 200 |
| description | text | opcional |
| budget | decimal(12,2) | obrigatório, mín 0 |
| status | string | obrigatório, máx 30, default 'draft' |
| deadline | date | opcional |
