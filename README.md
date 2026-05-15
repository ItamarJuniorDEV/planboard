# Planboard API

![CI](https://github.com/ItamarJuniorDEV/planboard-api/actions/workflows/ci.yml/badge.svg)
![Security](https://github.com/ItamarJuniorDEV/planboard-api/actions/workflows/security.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

API REST em Laravel 12 para gestão de projetos em quadros kanban. Cobre a hierarquia de projeto, quadro, coluna, tarefa, subtarefa, comentário, marco e etiqueta, com autenticação via Sanctum, autorização por dono do recurso e algumas operações em lote.

## Motivação

Fiz esse projeto como exercício pessoal pra praticar Laravel 12 em algo um pouco maior que um CRUD de blog. Queria entender como modelar uma hierarquia razoável de recursos (projeto contendo quadros, quadros contendo colunas, colunas e tarefas no mesmo projeto) sem deixar o controle de acesso vazar pelas beiradas. A regra de "só o dono ou um admin mexe" é simples de falar e chata de garantir em rotas aninhadas, então foi um bom motivo pra estudar Policies a sério, Form Requests com `authorize()` real e route model binding com `scopeBindings()`.

A escolha do domínio kanban veio porque me dá vários ganchos para praticar coisas que normalmente fingem ser tutoriais: cache de query quente (estatísticas do projeto), operações em lote (mover várias tarefas de coluna de uma vez), validação cruzada de recursos (a coluna destino tem que ser do mesmo projeto da tarefa). E ainda permite gerar uma documentação OpenAPI decente sem ter que escrever annotation a annotation.

## Stack

- PHP 8.3
- Laravel 12
- Laravel Sanctum 4 (autenticação por token e por sessão SPA)
- MySQL 8 em produção, SQLite em memória nos testes
- PHPUnit 11.5
- dedoc/scramble para gerar OpenAPI a partir dos tipos PHP

## Como rodar

Com Docker (o `docker-compose.yml` sobe MySQL 8 e phpMyAdmin):

    git clone https://github.com/ItamarJuniorDEV/planboard-api.git
    cd planboard-api
    cp .env.example .env
    docker compose up -d
    composer install
    php artisan key:generate
    php artisan migrate --seed
    php artisan serve

O phpMyAdmin fica em `http://localhost:8888` com as credenciais do `.env`.

Sem Docker (basta um MySQL local ou SQLite):

    cp .env.example .env
    composer install
    php artisan key:generate
    php artisan migrate --seed
    php artisan serve

Pra usar SQLite ao invés de MySQL, é só deixar `DB_CONNECTION=sqlite` no `.env` e criar o arquivo `database/database.sqlite`.

## Decisões de arquitetura

Optei por Sanctum em vez de Passport. A API não expõe OAuth para clientes de terceiros, ela atende um frontend próprio (ou o Postman do dev). Sanctum cobre os dois cenários úteis aqui: token bearer para clientes mobile/CLI e cookie de sessão para SPA na mesma origem. Passport seria over-engineering pra esse caso.

A autorização mora em Policies por recurso, uma por modelo de domínio, com `Gate::before` no `AppServiceProvider` liberando admin em tudo, com uma exceção deliberada: admin não pode deletar `User` via API. Sem essa exceção, qualquer admin chamando `DELETE /api/users/me` apagaria a própria conta. As Form Requests por ação chamam `$this->user()->can('verb', $this->route('model'))` dentro de `authorize()`, então quem chega no controller já passou pela policy. Não tem `return true` mentiroso em Form Request.

Todas as rotas aninhadas usam `scopeBindings()`. Isso significa que `/api/projects/{project}/boards/{board}` só resolve o board se ele realmente pertencer àquele projeto. Sem `scopeBindings`, um usuário poderia mandar `/api/projects/1/boards/999` e o Laravel devolveria o board 999 mesmo que ele seja de outro projeto, abrindo a porta pra IDOR. Com `scopeBindings`, o 404 vem antes do controller.

Resposta sempre serializa via API Resource. Mesmo nos endpoints paginados o envelope `data` é preservado, porque a chamada é `Resource::collection($paginator)->resource = $paginator`, o que mantém `meta` e `links` da paginação no nível raiz e o array de recursos dentro de `data`.

Decidi não introduzir Repository pattern. O projeto é médio-pequeno, Eloquent direto no controller resolve, e criar interface pra uma implementação só não faz sentido aqui. Mesma coisa com Service ou Action: não tem lógica de negócio que mereça classe própria.

Também não subi infraestrutura de Queue. A API não manda email, não gera PDF, não chama webhook externo. Nada que precise rodar fora do request-response.

Existe um único ponto de cache: `project:{id}:stats` com TTL de 60 segundos. As estatísticas são a consulta mais cara do projeto (agrega tarefas por status, prioridade, progresso de subtarefas, marcos em atraso) e mudam pouco em relação à frequência com que a dashboard pede. A invalidação é explícita via Observer `InvalidatesProjectStats` registrado em Task, Subtask e Milestone, então qualquer save ou delete nessas entidades zera a chave correspondente.

Segurança transversal: rate limit de 60 por minuto no grupo `api` (chave por user id, ou IP quando não autenticado), 5 por minuto no `login` (chave por email + IP, suficiente pra travar brute force sem trancar legítimos). Middleware `SecurityHeaders` aplicado em todo o grupo API com `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` restritivo, `Content-Security-Policy: default-src 'none'; frame-ancestors 'none'` (a API só devolve JSON, então CSP pode ser bem fechado) e HSTS condicional a produção + HTTPS. CORS com origens explícitas em `config/cors.php`, sem wildcard, porque `supports_credentials=true`. O login usa `Timebox::call(..., 500_000)` e mensagem uniforme `Credenciais inválidas` pra não vazar a existência do email. Em desenvolvimento, `Model::preventLazyLoading()` está ligado pra estourar exceção se algum N+1 escapar pro controller.

## Documentação da API

A documentação OpenAPI é gerada pelo `dedoc/scramble` a partir dos tipos do PHP (Form Requests, Resources, type hints). Subindo o servidor com `php artisan serve`, a interface fica em:

    http://localhost:8000/docs/api

O JSON cru fica em `/docs/api.json`. O security scheme `bearerAuth` já vem configurado e aplicado automaticamente em todas as rotas protegidas por `auth:sanctum`.

## Testes

Rodam contra SQLite em memória, com `RefreshDatabase` por feature test. A cobertura inclui autenticação, autorização (admin vs dono vs outro usuário), CRUD de projetos e tarefas, controle de papel em `/api/users` e hash de senha. Pra executar:

    php artisan test

## Melhorias futuras

- Cobertura de testes nos recursos secundários (boards, columns, comments, subtasks, milestones, labels).
- Cenários negativos extras nos endpoints em lote.
- Matriz PHP 8.3 no GitHub Actions com Pint e validação do OpenAPI.
- Refresh token (hoje Sanctum tá com `expiration => null`).
- Cursor pagination nos endpoints de listagem.
- Filtros mais expressivos via Spatie Query Builder.
- Webhook de eventos de domínio (tarefa criada, movida, marco vencido). Aí entra Queue de verdade.

## Segurança

Reporte de vulnerabilidades e política de divulgação estão em [SECURITY.md](SECURITY.md).

## Licença

MIT.
