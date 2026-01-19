# Backlog Item #11: Operações em Lote

## User Story

Como usuário da API, eu quero executar ações em várias tarefas ao mesmo tempo para ganhar produtividade.

## Critérios de aceite

- PATCH `/projects/{projectId}/tasks/bulk-move` → 200 ou 404
- POST `/projects/{projectId}/tasks/bulk-delete` → 200 ou 404

## Endpoint 1: Bulk Move (mover várias tasks pra uma coluna)

### Body esperado

```json
{
    "task_ids": [1, 3, 5],
    "column_id": 2
}
```

### Validações

- `task_ids` — required, array, min:1
- `task_ids.*` — required, integer (cada item do array tem que ser inteiro)
- `column_id` — required, integer

### Regras

- verificar se o projeto existe
- verificar se a coluna existe e pertence ao projeto
- buscar todas as tasks de uma vez com `whereIn`
- calcular quais IDs não foram encontrados com `array_diff`
- atualizar todas as encontradas com um único `update`
- no final, retorna quantas foram movidas e quais IDs não foram encontrados

### Resposta de sucesso (200)

```json
{
    "message": "Operação concluída!",
    "moved": 3,
    "not_found": [7, 99]
}
```

## Endpoint 2: Bulk Delete (deletar várias tasks)

### Body esperado

```json
{
    "task_ids": [2, 4, 6]
}
```

### Validações

- `task_ids` — required, array, min:1
- `task_ids.*` — required, integer

### Regras

- verificar se o projeto existe
- buscar todas as tasks de uma vez com `whereIn`
- calcular quais IDs não foram encontrados com `array_diff`
- deletar todas as encontradas com um único `delete`
- no final, retorna quantas foram deletadas e quais IDs não foram encontrados

### Resposta de sucesso (200)

```json
{
    "message": "Operação concluída!",
    "deleted": 2,
    "not_found": [99]
}
```

## Observação

- os dois métodos ficam no `TaskController`
- as rotas são aninhadas dentro de `/projects/{projectId}/tasks/`
- usa try/catch como sempre
