# #17 — Login e Logout

> Como usuário, eu quero me autenticar na API para acessar os recursos de forma segura.

## Endpoints

- `POST /login` → 200 ou 401
- `POST /logout` → 200 (requer autenticação)

## Critérios de aceite

**Login:**
- recebe `email` e `password`
- credenciais válidas → retorna token Sanctum + dados do usuário
- credenciais inválidas → 401

**Logout:**
- requer token no header `Authorization: Bearer {token}`
- revoga o token atual do usuário
