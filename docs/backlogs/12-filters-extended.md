# #12 — Filtros em Boards, Milestones e Labels

## #12.1 — Filtros em Boards

> Como usuário, eu quero filtrar e buscar boards de um projeto.

**Endpoint:** `GET /projects/{projectId}/boards`

| Parâmetro | Tipo | Valores |
|---|---|---|
| status | string | active, archived |
| search | string | busca no campo name |
| per_page | integer | mín 1, máx 20 |

---

## #12.2 — Filtros em Milestones

> Como usuário, eu quero filtrar e buscar milestones de um projeto.

**Endpoint:** `GET /projects/{projectId}/milestones`

| Parâmetro | Tipo | Valores |
|---|---|---|
| search | string | busca no campo title |
| due_from | date | milestones com due_date >= data |
| due_to | date | milestones com due_date <= data |
| per_page | integer | mín 1, máx 20 |

---

## #12.3 — Filtros em Labels

> Como usuário, eu quero buscar labels de um projeto.

**Endpoint:** `GET /projects/{projectId}/labels`

| Parâmetro | Tipo | Valores |
|---|---|---|
| search | string | busca no campo name |
| per_page | integer | mín 1, máx 30 |
