# Backlog Item #14: Operações em Lote nas Subtasks

## Contexto de negócio

Ao concluir uma tarefa, o usuário pode querer marcar todas as subtarefas como feitas de uma vez, sem precisar atualizar uma por uma.

## User Story

Como usuário da API, eu quero executar ações em várias subtarefas ao mesmo tempo para ganhar produtividade.

## Critérios de aceite

- `POST /projects/{projectId}/tasks/{taskId}/subtasks/bulk-complete` → 200 ou 404
- `POST /projects/{projectId}/tasks/{taskId}/subtasks/bulk-delete` → 200 ou 404

## Endpoint 1: Bulk Complete (marcar várias subtasks como done)

### Body esperado

```json
{
    "subtask_ids": [1, 2, 3]
}
```

### Validações

- `subtask_ids` — required, array, min:1
- `subtask_ids.*` — required, integer

### Regras

- verificar se o projeto existe
- verificar se a task existe dentro do projeto
- buscar todas as subtasks de uma vez com `whereIn`
- calcular quais IDs não foram encontrados com `array_diff`
- atualizar `done = true` em todas as encontradas com um único `update`

### Resposta de sucesso (200)

```json
{
    "success": true,
    "message": "Operação concluída!",
    "completed": 3,
    "not_found": []
}
```

## Endpoint 2: Bulk Delete (deletar várias subtasks)

### Body esperado

```json
{
    "subtask_ids": [1, 2, 3]
}
```

### Validações

- `subtask_ids` — required, array, min:1
- `subtask_ids.*` — required, integer

### Regras

- verificar se o projeto existe
- verificar se a task existe dentro do projeto
- buscar todas as subtasks de uma vez com `whereIn`
- calcular quais IDs não foram encontrados com `array_diff`
- deletar todas as encontradas com um único `delete`

### Resposta de sucesso (200)

```json
{
    "success": true,
    "message": "Operação concluída!",
    "deleted": 2,
    "not_found": [99]
}
```

## Observação

- os dois métodos ficam no `SubtaskController`
- usar o mesmo padrão de `whereIn` já implementado no `TaskController`
- as rotas são aninhadas dentro de `/projects/{projectId}/tasks/{taskId}/subtasks/`

## O que é novo aqui

- aplicar o padrão `whereIn` aprendido nas bulk operations de tasks em outro controller
- operação de update em lote (`bulk update`) com `whereIn`
