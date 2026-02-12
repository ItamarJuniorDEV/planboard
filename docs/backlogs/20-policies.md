# #20 — Policies de Autorização

> Como usuário, eu quero que apenas o dono de um recurso (ou um admin) possa editá-lo ou deletá-lo.

## Critérios de aceite

- usuário só pode editar/deletar recursos que criou
- admin pode editar/deletar qualquer recurso
- tentativa não autorizada → 403 Forbidden
- adicionar `user_id` nas tabelas dos recursos via nova migration
- setar `user_id` automaticamente no momento de criação do recurso
