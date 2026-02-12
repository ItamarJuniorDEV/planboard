# #11 — Operações em Lote nas Tasks

> Como usuário, eu quero executar ações em várias tarefas ao mesmo tempo para ganhar produtividade.

## Endpoints

- `PATCH /projects/{projectId}/tasks/bulk-move` → 200 ou 404
- `POST /projects/{projectId}/tasks/bulk-delete` → 200 ou 404

## #11.1 — Bulk Move

Move várias tasks para uma coluna de destino.

**Body:**
```json
{ "task_ids": [1, 3, 5], "column_id": 2 }
```

**Critérios de aceite:**
- validar se o projeto e a coluna existem e se a coluna pertence ao projeto
- tasks não encontradas são listadas em `not_found`
- retorna quantidade de tasks movidas

## #11.2 — Bulk Delete

Deleta várias tasks de uma vez.

**Body:**
```json
{ "task_ids": [2, 4, 6] }
```

**Critérios de aceite:**
- validar se o projeto existe
- tasks não encontradas são listadas em `not_found`
- retorna quantidade de tasks deletadas
