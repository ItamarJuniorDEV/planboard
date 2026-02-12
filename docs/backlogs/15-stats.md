# #15 — Estatísticas do Projeto

> Como usuário, eu quero consultar um resumo do progresso de um projeto para visualizar o andamento rapidamente.

## Endpoint

- `GET /projects/{projectId}/stats` → 200 ou 404

## Critérios de aceite

- retorna total de tasks agrupadas por `status` e `priority`
- retorna total de subtasks com contagem de concluídas e pendentes
- retorna total de milestones com contagem de vencidas (`due_date` anterior a hoje)
- retorna 404 se o projeto não existir
- resposta não é paginada
