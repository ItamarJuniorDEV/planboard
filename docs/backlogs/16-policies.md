# Backlog Item #22: Policies — Quem Pode Editar/Deletar o Quê

## Contexto de negócio

O middleware de role resolve quem é admin ou member, mas não resolve quem é dono do quê. Exemplo: um member pode editar os próprios projetos, mas não os de outro member. Policies resolvem isso.

## User Story

Como usuário, eu quero que apenas o dono de um recurso (ou um admin) possa editar ou deletar esse recurso.

## Critérios de aceite

- um usuário só pode editar/deletar projetos que ele criou
- um admin pode editar/deletar qualquer projeto
- um usuário não pode editar/deletar projetos de outro usuário
- se não autorizado → 403 Forbidden
- mesma lógica para tasks, boards e outros recursos que tenham dono

## Definição técnica do tech lead

### Criar a Policy

```bash
php artisan make:policy ProjectPolicy --model=Project
```

### Métodos da Policy

- `update(User $user, Project $project)` → retorna true se `$user->id === $project->user_id` ou `$user->role === 'admin'`
- `delete(User $user, Project $project)` → mesma lógica

### Uso no Controller

```php
public function update(Request $request, int $id)
{
    $project = Project::find($id);
    $this->authorize('update', $project);
    // ... resto do código
}
```

### Resposta quando não autorizado (403)

```json
{
    "message": "This action is unauthorized."
}
```

### Pré-requisito

- precisa adicionar `user_id` na tabela `projects` (migration nova)
- setar o `user_id` automaticamente no store: `$project->user_id = $request->user()->id`

## Observação

- Policies são a forma do Laravel de resolver autorização granular (por recurso)
- o middleware `role` resolve permissões por tipo de usuário (admin/member)
- as Policies resolvem permissões por dono do recurso
- as duas coisas se complementam
