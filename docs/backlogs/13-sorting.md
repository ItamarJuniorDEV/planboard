# Backlog Item #13: Ordenação dos Resultados

## Contexto de negócio

O frontend precisa exibir os dados em ordens diferentes dependendo do contexto: tarefas por prioridade, projetos por deadline, milestones por data de entrega.

## User Story

Como usuário da API, eu quero ordenar os resultados por diferentes campos para visualizar os dados da forma que faz mais sentido pra mim.

## Critérios de aceite

- `GET /projects/{projectId}/tasks?order_by=priority&direction=asc` → 200
- `GET /projects/{projectId}/tasks?order_by=created_at&direction=desc` → 200
- `GET /projects?order_by=deadline&direction=asc` → 200
- `GET /projects/{projectId}/milestones?order_by=due_date&direction=asc` → 200
- se `direction` não for enviado → padrão é `asc`
- se `order_by` não for enviado → padrão é `created_at`

## Definição técnica do tech lead

### Validações (em cada controller que aplicar)

- `order_by` — nullable, string, in: (campos permitidos por resource)
- `direction` — nullable, string, in:asc,desc

### Campos permitidos por resource

**Tasks:**
- `created_at`, `priority`, `status`, `title`

**Projects:**
- `created_at`, `title`, `deadline`, `budget`

**Milestones:**
- `created_at`, `due_date`, `title`

### Implementação

```php
$orderBy = $validate['order_by'] ?? 'created_at';
$direction = $validate['direction'] ?? 'asc';

$query->orderBy($orderBy, $direction);
```

## Regras

- funciona junto com os filtros já existentes
- o `order_by` só aceita campos definidos explicitamente via `in:` (nunca aceitar qualquer string)
- `direction` padrão é `asc`
- a resposta continua paginada

## O que é novo aqui

- uso de `orderBy` dinâmico na query
- combinar ordenação com filtros e paginação no mesmo endpoint
- importância de validar `order_by` com `in:` para evitar SQL injection
