# #13 — Ordenação em Tasks, Projects e Milestones

## #13.1 — Ordenação em Tasks

> Como usuário, eu quero ordenar as tasks por diferentes campos.

**Endpoint:** `GET /projects/{projectId}/tasks`

| Parâmetro | Valores | Default |
|---|---|---|
| order_by | created_at, priority, status, title | created_at |
| direction | asc, desc | asc |

---

## #13.2 — Ordenação em Projects

> Como usuário, eu quero ordenar os projetos por diferentes campos.

**Endpoint:** `GET /projects`

| Parâmetro | Valores | Default |
|---|---|---|
| order_by | created_at, title, deadline, budget | created_at |
| direction | asc, desc | desc |

---

## #13.3 — Ordenação em Milestones

> Como usuário, eu quero ordenar as milestones por diferentes campos.

**Endpoint:** `GET /projects/{projectId}/milestones`

| Parâmetro | Valores | Default |
|---|---|---|
| order_by | created_at, due_date, title | created_at |
| direction | asc, desc | asc |

## Critérios de aceite (todos)

- ordenação funciona junto com os filtros já existentes
- resposta continua paginada
