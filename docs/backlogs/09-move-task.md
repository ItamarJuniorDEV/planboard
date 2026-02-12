# #9 — Mover Task entre Colunas

> Como usuário, eu quero mover uma tarefa para outra coluna sem precisar atualizar todos os seus campos.

## Endpoint

- `PATCH /projects/{projectId}/boards/{boardId}/columns/{columnId}/tasks/{taskId}/move` → 200 ou 404

## Critérios de aceite

- atualiza apenas o vínculo da coluna da task
- retorna 404 se projeto, board, coluna ou task não existirem
- não aceita body (todos os dados vêm pela URL)
