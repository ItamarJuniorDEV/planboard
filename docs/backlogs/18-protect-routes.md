# Backlog Item #18: Proteger Rotas com Autenticação

## Contexto de negócio

Agora que o sistema tem login, as rotas precisam ser protegidas. Só quem tá logado (com token válido) pode acessar os recursos da API.

## User Story

Como administrador do sistema, eu quero que apenas usuários autenticados acessem as rotas da API para garantir a segurança dos dados.

## Critérios de aceite

- todas as rotas da API (exceto login) devem exigir autenticação
- requisição sem token → 401 Unauthenticated
- requisição com token válido → acessa normalmente
- requisição com token inválido/expirado → 401

## Definição técnica do tech lead

### Como proteger

Agrupar as rotas dentro de um `Route::middleware('auth:sanctum')`:

```php
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/projects', [ProjectController::class, 'index']);
    // ... todas as outras rotas
});
```

### Header obrigatório em todas as requisições (exceto login)

```
Authorization: Bearer {token}
```

### Resposta quando não autenticado (401)

```json
{
    "message": "Unauthenticated."
}
```

## Observação

- o middleware `auth:sanctum` já vem pronto com o Sanctum
- a única rota pública é o `POST /login`
- o logout precisa estar dentro do grupo protegido (precisa do token pra revogar)
