# #14 — Operações em Lote nas Subtasks, Comments e Milestones

## #14.1 — Bulk Complete e Bulk Delete em Subtasks

> Como usuário, eu quero marcar ou deletar várias subtasks de uma vez.

**Endpoints:**
- `POST /projects/{projectId}/tasks/{taskId}/subtasks/bulk-complete` → 200 ou 404
- `POST /projects/{projectId}/tasks/{taskId}/subtasks/bulk-delete` → 200 ou 404

**Body:**
```json
{ "subtask_ids": [1, 2, 3] }
```

**Critérios de aceite:**
- validar se o projeto e a task existem
- bulk-complete: marca `done = true` em todas as encontradas
- bulk-delete: deleta todas as encontradas
- subtasks não encontradas são listadas em `not_found`

---

## #14.2 — Bulk Delete em Comments

> Como usuário, eu quero deletar vários comentários de uma vez.

**Endpoint:** `POST /projects/{projectId}/tasks/{taskId}/comments/bulk-delete` → 200 ou 404

**Body:**
```json
{ "comment_ids": [1, 2, 3] }
```

**Critérios de aceite:**
- validar se o projeto e a task existem
- comments não encontrados são listados em `not_found`
- retorna quantidade de comments deletados

---

## #14.3 — Bulk Delete em Milestones

> Como usuário, eu quero deletar várias milestones de uma vez.

**Endpoint:** `POST /projects/{projectId}/milestones/bulk-delete` → 200 ou 404

**Body:**
```json
{ "milestone_ids": [1, 2, 3] }
```

**Critérios de aceite:**
- validar se o projeto existe
- milestones não encontradas são listadas em `not_found`
- retorna quantidade de milestones deletadas
