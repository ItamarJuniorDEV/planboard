# #19 — Middleware de Role

> Como administrador, eu quero restringir certas ações a perfis específicos para proteger dados críticos.

## Critérios de aceite

- criar middleware customizado que verifica o `role` do usuário autenticado
- usuário sem a role exigida → 403 Forbidden
- aplicar nas rotas exclusivas de admin (ex: criação e exclusão de usuários)
