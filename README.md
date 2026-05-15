# Planboard API

![CI](https://github.com/ItamarJuniorDEV/planboard-api/actions/workflows/ci.yml/badge.svg)
![Security](https://github.com/ItamarJuniorDEV/planboard-api/actions/workflows/security.yml/badge.svg)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

API REST em Laravel 12 para gestao de projetos em quadros kanban. Cobre a hierarquia de projeto, quadro, coluna, tarefa, subtarefa, comentario, marco e etiqueta, com autenticacao via Sanctum, autorizacao por dono do recurso e algumas operacoes em lote.

## Motivacao

Fiz esse projeto como exercicio pessoal pra praticar Laravel 12 em algo um pouco maior que um CRUD de blog. Queria entender como modelar uma hierarquia razoavel de recursos (projeto contendo quadros, quadros contendo colunas, colunas e tarefas no mesmo projeto) sem deixar o controle de acesso vazar pelas beiradas. A regra de "so o dono ou um admin mexe" e simples de falar e chata de garantir em rotas aninhadas, entao foi um bom motivo pra mergulhar de cabeca em Policies, Form Requests com `authorize()` real e route model binding com `scopeBindings()`.

A escolha do dominio kanban veio porque me da varios ganchos para praticar coisas que normalmente fingem ser tutoriais: cache de query quente (estatisticas do projeto), operacoes em lote (mover varias tarefas de coluna de uma vez), validacao cruzada de recursos (a coluna destino tem que ser do mesmo projeto da tarefa). E ainda permite gerar uma documentacao OpenAPI decente sem ter que escrever annotation a annotation.

## Stack

- PHP 8.3
- Laravel 12
- Laravel Sanctum 4 (autenticacao por token e por sessao SPA)
- MySQL 8 em producao, SQLite em memoria nos testes
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

Para usar SQLite ao inves de MySQL, basta deixar `DB_CONNECTION=sqlite` no `.env` e criar o arquivo `database/database.sqlite`.

## Decisoes de arquitetura

Optei por Sanctum em vez de Passport. A API nao expoe OAuth para clientes de terceiros, ela atende a um frontend proprio (ou ao Postman do dev). Sanctum cobre os dois cenarios uteis aqui: token bearer para clientes mobile/CLI e cookie de sessao para SPA na mesma origem. Passport seria complexidade sem retorno.

A autorizacao mora em Policies por recurso, uma por modelo de dominio, com `Gate::before` no `AppServiceProvider` liberando admin em tudo, com uma excecao deliberada: admin nao pode deletar `User` via API. Sem essa excecao, qualquer admin chamando `DELETE /api/users/me` apagaria a propria conta. As Form Requests por acao chamam `$this->user()->can('verb', $this->route('model'))` dentro de `authorize()`, entao quem chega no controller ja passou pela policy. Nao tem `return true` mentiroso em Form Request.

Todas as rotas aninhadas usam `scopeBindings()`. Isso significa que `/api/projects/{project}/boards/{board}` so resolve o board se ele realmente pertencer aquele projeto. Sem `scopeBindings`, um usuario poderia mandar `/api/projects/1/boards/999` e o Laravel devolveria o board 999 mesmo que ele seja de outro projeto, abrindo a porta para IDOR. Com `scopeBindings`, o 404 vem antes do controller.

Resposta sempre serializa via API Resource. Mesmo nos endpoints paginados o envelope `data` e preservado, porque a chamada e `Resource::collection($paginator)->resource = $paginator`, o que mantem `meta` e `links` da paginacao no nivel raiz e o array de recursos dentro de `data`. Decidi nao introduzir Repository pattern: o projeto e medio-pequeno, Eloquent direto no controller resolve, e abstrair atras de uma interface com uma unica implementacao seria overhead sem motivo. Pelo mesmo principio, nao criei camada de Service ou Action: nao ha logica de negocio que extrapole CRUD a ponto de justificar uma classe a parte. Tambem nao subi infraestrutura de Queue. A API nao manda email, nao gera PDF, nao chama webhook externo, nao tem nada que precise rodar fora do request-response.

Existe um unico ponto de cache: `project:{id}:stats` com TTL de 60 segundos. As estatisticas sao a consulta mais cara do projeto (agrega tarefas por status, prioridade, progresso de subtarefas, marcos em atraso) e mudam pouco em relacao a frequencia com que a dashboard pede. A invalidacao e explicita via Observer `InvalidatesProjectStats` registrado em Task, Subtask e Milestone, entao qualquer save ou delete nessas entidades zera a chave correspondente.

Seguranca transversal: rate limit de 60 por minuto no grupo `api` (chave por user id, ou IP quando nao autenticado), 5 por minuto no `login` (chave por email + IP, suficiente pra travar brute force sem trancar legitimos). Middleware `SecurityHeaders` aplicado em todo o grupo API com `X-Content-Type-Options: nosniff`, `X-Frame-Options: DENY`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` restritivo, `Content-Security-Policy: default-src 'none'; frame-ancestors 'none'` (a API so devolve JSON, entao CSP pode ser bem fechado) e HSTS condicional a producao + HTTPS. CORS com origens explicitas em `config/cors.php`, sem wildcard, porque `supports_credentials=true`. O login usa `Timebox::call(..., 500_000)` e mensagem uniforme `Credenciais invalidas` para nao vazar a existencia do email. Em desenvolvimento, `Model::preventLazyLoading()` esta ligado pra estourar excecao se algum N+1 escapar pro controller.

## Documentacao da API

A documentacao OpenAPI e gerada pelo `dedoc/scramble` a partir dos tipos do PHP (Form Requests, Resources, type hints). Subindo o servidor com `php artisan serve`, a interface fica em:

    http://localhost:8000/docs/api

O JSON cru fica em `/docs/api.json`. O security scheme `bearerAuth` ja vem configurado e aplicado automaticamente em todas as rotas protegidas por `auth:sanctum`.

## Testes

Rodam contra SQLite em memoria, com `RefreshDatabase` por feature test. Cobertura inclui autenticacao, autorizacao (admin vs dono vs outro usuario), CRUD de projetos e tarefas, controle de papel em `/api/users` e hash de senha. Para executar:

    php artisan test

## Melhorias futuras

Cobertura de testes mais ampla nos recursos secundarios (boards, columns, comments, subtasks, milestones, labels) e cenarios negativos extras nos endpoints em lote.

Adicionar GitHub Actions com matriz para PHP 8.3 rodando o suite de testes, lint via Pint e validacao do OpenAPI exportado.

Refresh tokens para sessoes longas de SPA, hoje o Sanctum esta com `expiration => null`.

Filtros e ordenacao mais expressivos nos endpoints de listagem usando algo como Spatie Query Builder, com paginacao por cursor onde fizer sentido.

Webhook de eventos do dominio (tarefa criada, tarefa movida, marco vencido) para permitir integracao externa sem polling. Isso traria uma Queue de verdade pro projeto.

## Seguranca

Reporte de vulnerabilidades e politica de divulgacao estao em [SECURITY.md](SECURITY.md).

## Licenca

MIT.
