# Backlog Item #12.1: Filtros em Boards

## Contexto de negócio

Boards também precisam de busca e filtragem para facilitar o uso da API sem precisar retornar tudo de uma vez.

## User Story

Como usuário da API, eu quero filtrar e buscar boards de um projeto para encontrar o que preciso com mais facilidade.

## Endpoint

- `GET /projects/{projectId}/boards`

## Critérios de aceite

- `GET /projects/{projectId}/boards?status=active` → 200
- `GET /projects/{projectId}/boards?search=sprint` → 200
- `GET /projects/{projectId}/boards?status=active&search=sprint` → 200

## Definição técnica do tech lead

### BoardController@index

Validações:
- `status` — nullable, string, in:active,archived
- `search` — nullable, string (busca no campo `name`)
- `per_page` — nullable, integer, min:1, max:20

## Regras

- todos os filtros são opcionais
- os filtros podem funcionar juntos
- a resposta continua paginada
- o padrão de busca é `LIKE '%valor%'`

## O que deve ser feito

- manter a rota já existente
- ajustar apenas o método `index`
- aplicar filtro por `status` quando vier na requisição
- aplicar busca por `name` quando vier `search`
- manter paginação com `per_page`

# Backlog Item #12.2: Filtros em Milestones

## Contexto de negócio

Milestones também precisam de busca e filtragem para facilitar o uso da API sem precisar retornar tudo de uma vez.

## User Story

Como usuário da API, eu quero filtrar e buscar milestones de um projeto para encontrar o que preciso com mais facilidade.

## Endpoint

- `GET /projects/{projectId}/milestones`

## Critérios de aceite

- `GET /projects/{projectId}/milestones?search=v1` → 200
- `GET /projects/{projectId}/milestones?due_from=2025-01-01` → 200
- `GET /projects/{projectId}/milestones?due_to=2025-12-31` → 200
- `GET /projects/{projectId}/milestones?search=v1&due_from=2025-01-01&due_to=2025-12-31` → 200

## Definição técnica do tech lead

### MilestoneController@index

Validações:
- `search` — nullable, string (busca no campo `title`)
- `due_from` — nullable, date (milestones com `due_date >= data`)
- `due_to` — nullable, date (milestones com `due_date <= data`)
- `per_page` — nullable, integer, min:1, max:20

## Regras

- todos os filtros são opcionais
- os filtros podem funcionar juntos
- a resposta continua paginada
- o padrão de busca é `LIKE '%valor%'`

## O que deve ser feito

- manter a rota já existente
- ajustar apenas o método `index`
- aplicar busca por `title` quando vier `search`
- aplicar filtro `due_date >= due_from` quando vier `due_from`
- aplicar filtro `due_date <= due_to` quando vier `due_to`
- manter paginação com `per_page`

# Backlog Item #12.3: Filtros em Labels

## Contexto de negócio

Labels também precisam de busca e filtragem para facilitar o uso da API sem precisar retornar tudo de uma vez.

## User Story

Como usuário da API, eu quero buscar labels de um projeto para encontrar o que preciso com mais facilidade.

## Endpoint

- `GET /projects/{projectId}/labels`

## Critérios de aceite

- `GET /projects/{projectId}/labels?search=bug` → 200
- `GET /projects/{projectId}/labels?search=bug&per_page=10` → 200

## Definição técnica do tech lead

### LabelController@index

Validações:
- `search` — nullable, string (busca no campo `name`)
- `per_page` — nullable, integer, min:1, max:30

## Regras

- todos os filtros são opcionais
- os filtros podem funcionar juntos
- a resposta continua paginada
- o padrão de busca é `LIKE '%valor%'`

## O que deve ser feito

- manter a rota já existente
- ajustar apenas o método `index`
- aplicar busca por `name` quando vier `search`
- manter paginação com `per_page`