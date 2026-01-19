# Backlog Item #9: PATCH Parcial — Mover Task entre Colunas

## Contexto de negócio

No Kanban, o usuário arrasta uma tarefa de uma coluna pra outra. Na API, isso é um PATCH que só atualiza o vínculo da coluna, sem mexer no resto da task.

## User Story

Como usuário da API, eu quero mover uma tarefa de uma coluna para outra sem precisar atualizar todos os campos da tarefa.

## Critérios de aceite

- `PATCH /projects/{projectId}/boards/{boardId}/columns/{columnId}/tasks/{taskId}/move` → 200 ou 404

## Regras

- projectId — vem pela URL
- boardId — vem pela URL
- columnId — vem pela URL
- taskId — vem pela URL
- a task deve passar a pertencer à nova coluna
- só atualiza o campo de vínculo da coluna, não o resto da task

## Validações

- verificar se o projeto existe
- verificar se o board existe dentro do projeto
- verificar se a coluna existe dentro do board
- verificar se a task existe
- se qualquer um não existir → 404

## Observação

- esse endpoint não é CRUD puro
- é uma regra de negócio
