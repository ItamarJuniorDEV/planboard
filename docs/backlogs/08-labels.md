# Backlog Item #14: CRUD de Labels (Etiquetas)

## Contexto de negócio

O time precisa categorizar tarefas e itens dentro de cada projeto usando etiquetas coloridas. Exemplo: "bug" (vermelho), "feature" (verde), "urgente" (laranja).

## User Story

Como usuário da API, eu quero gerenciar as etiquetas de um projeto.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `GET /projects/{projectId}/labels` | index | 200 ou 404 |
| `GET /projects/{projectId}/labels/{id}` | show | 200 ou 404 |
| `POST /projects/{projectId}/labels` | store | 201 ou 404 |
| `PUT /projects/{projectId}/labels/{id}` | update | 200 ou 404 |
| `DELETE /projects/{projectId}/labels/{id}` | destroy | 200 ou 404 |

## Definição técnica do tech lead

### Tabela `labels`

- `id` — inteiro, auto increment, chave primária
- `project_id` — inteiro, obrigatório, **chave estrangeira referenciando `projects.id`**
- `name` — string, obrigatório, máximo 50 caracteres
- `color` — string, obrigatório, máximo 30 caracteres
- `created_at` e `updated_at` — timestamps padrão

### Validações no store e update

- `name` — obrigatório, string, máximo 50
- `color` — obrigatório, string, máximo 30

### Relacionamentos

- No Model `Label`: uma label **pertence a** um project (`belongsTo`)
- No Model `Project`: um project **tem muitas** labels (`hasMany`)

### Observação

O `project_id` vem pela URL, não pelo body.
