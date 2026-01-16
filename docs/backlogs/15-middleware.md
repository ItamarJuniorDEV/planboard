# Backlog Item #21: Middleware de Verificação de Role

## Contexto de negócio

Nem todo usuário logado pode fazer tudo. Algumas ações são exclusivas de administradores, como criar usuários ou deletar projetos.

## User Story

Como administrador, eu quero que certas ações sejam restritas ao meu perfil para evitar que membros comuns alterem dados críticos.

## Critérios de aceite

- criar um middleware customizado que verifica o `role` do usuário
- se o usuário não tiver a role exigida → 403 Forbidden
- se o usuário tiver a role certa → passa normalmente

## Definição técnica do tech lead

### Criar o middleware

```bash
php artisan make:middleware CheckRole
```

### Lógica do middleware

- recebe o request e a role esperada
- pega o usuário autenticado
- compara `$user->role` com a role esperada
- se não bater → retorna 403
- se bater → `$next($request)`

### Uso nas rotas

```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});
```

### Resposta quando não autorizado (403)

```json
{
    "message": "Acesso não autorizado!"
}
```

### Registrar o middleware

Registrar o alias `role` no `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
    ]);
})
```

## Observação

- middleware é diferente de autenticação: autenticação verifica se tá logado, middleware verifica se tem permissão
- o middleware `auth:sanctum` vem antes do `role` na cadeia
