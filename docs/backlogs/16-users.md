# Backlog Item #16: CRUD de Usuários

## Contexto de negócio

O sistema precisa de usuários cadastrados para controlar quem acessa a plataforma. Apenas administradores podem criar novos usuários.

## User Story

Como administrador, eu quero gerenciar os usuários do sistema para controlar quem tem acesso à plataforma.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /users` | index | 200 |
| `GET /users/{id}` | show | 200 ou 404 |
| `POST /users` | store | 201 |
| `PUT /users/{id}` | update | 200 ou 404 |
| `DELETE /users/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `users` (já existe no Laravel)

- `id` — inteiro, auto increment, chave primária
- `name` — string, obrigatório, máximo 255 caracteres
- `email` — string, obrigatório, único, máximo 255 caracteres
- `password` — string, obrigatório, mínimo 8 caracteres
- `role` — string, obrigatório, default 'member' (valores: admin, member)
- `created_at` e `updated_at` — timestamps padrão

### Validações no store

- `name` — obrigatório, string, máximo 255
- `email` — obrigatório, string, email, único na tabela users
- `password` — obrigatório, string, mínimo 8
- `role` — opcional, string, in:admin,member

### Validações no update

- `name` — obrigatório, string, máximo 255
- `email` — obrigatório, string, email, único ignorando o próprio id
- `password` — opcional, string, mínimo 8
- `role` — opcional, string, in:admin,member

### Regras

- a senha deve ser salva com hash (bcrypt)
- a senha nunca deve ser retornada nas respostas
- só admin pode criar usuários (implementar na fase de permissões)

### Observação

- precisa criar uma migration pra adicionar o campo `role` na tabela users
- usar `$hidden = ['password']` no Model pra não retornar a senha
