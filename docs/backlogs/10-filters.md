# Backlog Item #16: Filtros e Busca nas Tasks

## User Story

Como usuário da API, eu quero filtrar e buscar tarefas para encontrar mais rápido o que preciso.

## Critérios de aceite

- GET `/projects/{projectId}/tasks?status=doing` → 200
- GET `/projects/{projectId}/tasks?priority=high` → 200
- GET `/projects/{projectId}/tasks?status=doing&priority=high` → 200
- GET `/projects/{projectId}/tasks?search=login` → 200

## Campos de filtro

- `status` — opcional, string, in:todo,doing,done
- `priority` — opcional, string, max:20
- `search` — opcional, string
- `per_page` — opcional, inteiro, mínimo 1, máximo 50

## Regras

- os filtros devem funcionar juntos
- a busca pode ser feita no título da task
- a resposta continua paginada

## Validações

- `status` — nullable, string, in:todo,doing,done
- `priority` — nullable, string, max:20
- `search` — nullable, string
- `per_page` — nullable, integer, min:1, max:50

## Observação

- isso fica no `index()` do `TaskController`

---

# Backlog Item #16.1: Filtros e Busca nos Projects

## User Story

Como usuário da API, eu quero filtrar e buscar projetos para encontrar mais rápido o que preciso.

## Critérios de aceite

- GET `/projects?status=active` → 200
- GET `/projects?search=admin` → 200
- GET `/projects?deadline_from=2025-06-01` → 200
- GET `/projects?deadline_to=2025-09-30` → 200
- GET `/projects?deadline_from=2025-06-01&deadline_to=2025-09-30` → 200
- GET `/projects?status=active&search=app` → 200

## Campos de filtro

- `status` — opcional, string, max:30
- `search` — opcional, string
- `deadline_from` — opcional, date
- `deadline_to` — opcional, date
- `per_page` — opcional, inteiro, mínimo 1, máximo 50

## Regras

- os filtros devem funcionar juntos
- a busca pode ser feita no título do projeto
- `deadline_from` filtra projetos com deadline >= a data informada
- `deadline_to` filtra projetos com deadline <= a data informada
- a resposta continua paginada

## Observação

- isso fica no `index()` do `ProjectController`
- filtro com datas usa operadores `>=` e `<=` no where
