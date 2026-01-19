# Backlog Item #15: Endpoint de Estatísticas do Projeto

## Contexto de negócio

O dashboard precisa exibir um resumo rápido de cada projeto: quantas tasks existem, quantas estão concluídas, distribuição por prioridade, etc.

## User Story

Como usuário da API, eu quero consultar um resumo estatístico de um projeto para visualizar o progresso de forma rápida.

## Critérios de aceite

- `GET /projects/{projectId}/stats` → 200 ou 404

### Resposta de sucesso (200)

```json
{
    "success": true,
    "message": "Estatísticas carregadas com sucesso!",
    "data": {
        "tasks": {
            "total": 10,
            "by_status": {
                "todo": 4,
                "doing": 3,
                "done": 3
            },
            "by_priority": {
                "low": 1,
                "medium": 5,
                "high": 3,
                "urgent": 1
            }
        },
        "subtasks": {
            "total": 20,
            "done": 12,
            "pending": 8
        },
        "milestones": {
            "total": 3,
            "overdue": 1
        }
    }
}
```

## Definição técnica do tech lead

### Rota

```php
Route::get('/projects/{projectId}/stats', [ProjectController::class, 'stats']);
```

### Implementação sugerida

```php
// tasks por status
$tasksByStatus = $project->tasks()
    ->selectRaw('status, count(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

// subtasks
$subtasksTotal = $project->tasks()->withCount('subtasks')->get()->sum('subtasks_count');

// milestones vencidas
$overdue = $project->milestones()->where('due_date', '<', now())->count();
```

## Regras

- verificar se o projeto existe → 404 se não encontrar
- usar `groupBy` + `selectRaw` para contar por categoria
- não paginar (é um resumo, não uma lista)
- milestones `overdue` = due_date anterior à data de hoje

## O que é novo aqui

- uso de `selectRaw` e `groupBy` para agregar dados
- uso de `withCount` para contar relacionamentos
- montar uma resposta JSON com estrutura aninhada
- `now()` helper do Laravel para comparar datas
