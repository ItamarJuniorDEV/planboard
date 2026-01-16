# Backlog Item #19: Login e Logout com Sanctum

## Contexto de negócio

O sistema precisa de autenticação para que apenas usuários cadastrados acessem a API. O login gera um token que o usuário envia em cada requisição.

## User Story

Como usuário, eu quero fazer login e logout na API para acessar os recursos de forma segura.

## Critérios de aceite

| Endpoint | Método | Status |
|---|---|---|
| `POST /login` | login | 200 ou 401 |
| `POST /logout` | logout | 200 |

### Login

- recebe email e password
- valida as credenciais
- se válido → gera token com Sanctum e retorna
- se inválido → 401

### Logout

- recebe o token no header `Authorization: Bearer {token}`
- revoga o token atual
- retorna 200

## Definição técnica do tech lead

### Body do login

```json
{
    "email": "joao@email.com",
    "password": "12345678"
}
```

### Validações no login

- `email` — obrigatório, string, email
- `password` — obrigatório, string

### Resposta do login (200)

```json
{
    "message": "Login realizado com sucesso!",
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "João",
        "email": "joao@email.com",
        "role": "admin"
    }
}
```

### Resposta do login (401)

```json
{
    "message": "Credenciais inválidas!"
}
```

### Resposta do logout (200)

```json
{
    "message": "Logout realizado com sucesso!"
}
```

## Observação

- o Sanctum já está instalado (`php artisan install:api`)
- a tabela `personal_access_tokens` já existe
- usar `Auth::attempt()` pra validar credenciais
- usar `$user->createToken('auth-token')` pra gerar o token
- usar `$request->user()->currentAccessToken()->delete()` pra revogar no logout
