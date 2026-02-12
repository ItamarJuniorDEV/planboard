# #18 — Proteger Rotas com Autenticação

> Como administrador, eu quero que apenas usuários autenticados acessem a API.

## Critérios de aceite

- todas as rotas exceto `POST /login` exigem token válido no header
- requisição sem token ou com token inválido → 401
- única rota pública: `POST /login`
