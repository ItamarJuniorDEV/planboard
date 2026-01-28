# Backlog Item #13.1: Ordenação em Tasks

## Contexto de negócio

As tasks precisam poder ser exibidas em ordens diferentes no frontend, como por prioridade, status, título ou data de criação.

## User Story

Como usuário da API, eu quero ordenar as tasks de um projeto por diferentes campos para visualizar os dados da forma que faz mais sentido pra mim.

## Endpoint

- `GET /projects/{projectId}/tasks`

## Critérios de aceite

- `GET /projects/{projectId}/tasks?order_by=priority&direction=asc` → 200
- `GET /projects/{projectId}/tasks?order_by=created_at&direction=desc` → 200
- se `direction` não for enviado → padrão é `asc`
- se `order_by` não for enviado → padrão é `created_at`

## Definição técnica do tech lead

### TaskController@index

Validações:
- `order_by` — nullable, string, in:created_at,priority,status,title
- `direction` — nullable, string, in:asc,desc

## Regras

- a ordenação funciona junto com os filtros já existentes
- o `order_by` só aceita campos definidos explicitamente via `in:`
- `direction` padrão é `asc`
- `order_by` padrão é `created_at`
- a resposta continua paginada

## O que deve ser feito

- manter a rota já existente
- ajustar apenas o método `index`
- validar `order_by`
- validar `direction`
- definir os valores padrão quando não vierem na requisição
- aplicar `orderBy` na query antes da paginação
- manter os filtros já existentes funcionando junto com a ordenação

# Backlog Item #13.2: Ordenação em Projects

## Contexto de negócio

Os projetos precisam poder ser exibidos em ordens diferentes no frontend, como por deadline, orçamento, título ou data de criação.

## User Story

Como usuário da API, eu quero ordenar os projetos por diferentes campos para visualizar os dados da forma que faz mais sentido pra mim.

## Endpoint

- `GET /projects`

## Critérios de aceite

- `GET /projects?order_by=deadline&direction=asc` → 200
- `GET /projects?order_by=created_at&direction=desc` → 200
- se `direction` não for enviado → padrão é `desc`
- se `order_by` não for enviado → padrão é `created_at`

## Definição técnica do tech lead

### ProjectController@index

Validações:
- `order_by` — nullable, string, in:created_at,title,deadline,budget
- `direction` — nullable, string, in:asc,desc

## Regras

- a ordenação funciona junto com os filtros já existentes
- o `order_by` só aceita campos definidos explicitamente via `in:`
- `direction` padrão é `desc`
- `order_by` padrão é `created_at`
- a resposta continua paginada

## O que deve ser feito

- manter a rota já existente
- ajustar apenas o método `index`
- validar `order_by`
- validar `direction`
- definir os valores padrão quando não vierem na requisição
- aplicar `orderBy` na query antes da paginação
- manter os filtros já existentes funcionando junto com a ordenação

# Backlog Item #13.3: Ordenação em Milestones

## Contexto de negócio

As milestones precisam poder ser exibidas em ordens diferentes no frontend, como por data de entrega, título ou data de criação.

## User Story

Como usuário da API, eu quero ordenar as milestones de um projeto por diferentes campos para visualizar os dados da forma que faz mais sentido pra mim.

## Endpoint

- `GET /projects/{projectId}/milestones`

## Critérios de aceite

- `GET /projects/{projectId}/milestones?order_by=due_date&direction=asc` → 200
- `GET /projects/{projectId}/milestones?order_by=created_at&direction=desc` → 200
- se `direction` não for enviado → padrão é `asc`
- se `order_by` não for enviado → padrão é `created_at`

## Definição técnica do tech lead

### MilestoneController@index

Validações:
- `order_by` — nullable, string, in:created_at,due_date,title
- `direction` — nullable, string, in:asc,desc

## Regras
- a ordenação funciona junto com os filtros já existentes
- o `order_by` só aceita campos definidos explicitamente via `in:`
- `direction` padrão é `asc`
- `order_by` padrão é `created_at`
- a resposta continua paginada

## O que deve ser feito
- manter a rota já existente
- ajustar apenas o método `index`
- validar `order_by`
- validar `direction`
- definir os valores padrão quando não vierem na requisição
- aplicar `orderBy` na query antes da paginação
- manter os filtros já existentes funcionando junto com a ordenação