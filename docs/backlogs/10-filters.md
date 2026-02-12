# #10 — Filtros em Tasks e Projects

## #10.1 — Filtros em Tasks

> Como usuário, eu quero filtrar e buscar tarefas para encontrar o que preciso rapidamente.

### Endpoint

- `GET /projects/{projectId}/tasks` → 200

### Parâmetros aceitos

| Parâmetro | Tipo | Valores |
|---|---|---|
| status | string | todo, doing, done |
| priority | string | low, medium, high, urgent |
| search | string | busca no título |
| per_page | integer | mín 1, máx 50 |

---

## #10.2 — Filtros em Projects

> Como usuário, eu quero filtrar e buscar projetos para encontrar o que preciso rapidamente.

### Endpoint

- `GET /projects` → 200

### Parâmetros aceitos

| Parâmetro | Tipo | Valores |
|---|---|---|
| status | string | draft, planning, active, on_hold, completed, cancelled |
| search | string | busca no título |
| deadline_from | date | projetos com deadline >= data |
| deadline_to | date | projetos com deadline <= data |
| per_page | integer | mín 1, máx 50 |
