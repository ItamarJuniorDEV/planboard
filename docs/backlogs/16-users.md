# #16 — CRUD de Usuários

> Como administrador, eu quero gerenciar os usuários do sistema para controlar quem tem acesso à plataforma.

## Endpoints

- `GET /users` → 200
- `GET /users/{id}` → 200 ou 404
- `POST /users` → 201
- `PUT /users/{id}` → 200 ou 404
- `DELETE /users/{id}` → 200 ou 404

## Schema

| Campo | Tipo | Regras |
|---|---|---|
| name | string | obrigatório, máx 255 |
| email | string | obrigatório, único, email válido |
| password | string | obrigatório no store, opcional no update, mín 8 |
| role | string | opcional, in: admin, member, default 'member' |

## Critérios de aceite

- senha salva com hash (bcrypt)
- senha nunca retornada nas respostas
- email deve ser único — no update, ignorar o próprio registro
- adicionar campo `role` via nova migration na tabela users
