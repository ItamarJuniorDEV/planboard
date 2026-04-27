# Instruções do projeto

Este arquivo é lido pelo Claude Code automaticamente em toda sessão. Define o contexto e as regras que valem para o projeto inteiro.

## Sobre o projeto

Projeto Laravel pessoal sendo polido para portfólio. A versão do Laravel é a que está no `composer.json` — não atualizar a menos que seja explicitamente pedido. O objetivo é que o repositório fique apresentável para recrutadores: código limpo, testado, documentado, com histórico git coerente, sem indícios de geração por IA.

## Regras de escrita (valem para código, commits e documentação)

Sem emojis em lugar nenhum. Não em README, não em commits, não em comentários, não em mensagens de PR, não em respostas no chat com o usuário ao longo do trabalho.

Sem buzzwords e sem tom de marketing. Termos proibidos em qualquer texto gerado: robust, comprehensive, seamless, leverage, elevate, delve, dive deep, unleash, supercharge, game-changer, cutting-edge, state-of-the-art, world-class, enterprise-grade, bank-level, military-grade, AI-powered, blazing-fast, rock-solid, battle-tested. Se precisar descrever uma qualidade, descrever o que foi feito ("usa cache de query para reduzir tempo de resposta") em vez de adjetivar ("blazing-fast queries").

Sem bullet points perfeitos em README. Texto corrido com parágrafos curtos é o padrão. Listas só quando o conteúdo for genuinamente uma lista (passos para rodar, dependências, etc.).

Sem comentários óbvios no código. `// loop através do array` não entra. Comentário só quando explica o porquê de uma decisão não-óbvia, nunca o que o código faz.

Português brasileiro no README e em mensagens voltadas ao usuário humano do projeto. Inglês em código (nomes de variáveis, métodos, classes, mensagens de log estruturado). Mensagens de commit em inglês curto e direto.

Voz na documentação: primeira pessoa quando explicar motivação ("Fiz esse projeto porque..."), terceira pessoa neutra quando descrever comportamento técnico ("A API expõe..."). Nunca "we" genérico tipo "we believe in clean code".

## Versão do Laravel

Detectar via `composer.json` antes de qualquer mudança. Adaptar sintaxe e estrutura à versão presente:
- Laravel 11/12: bootstrap/app.php, sem app/Http/Kernel.php, sem app/Console/Kernel.php.
- Laravel 10 e anteriores: Kernel.php tradicional.

Não migrar entre versões sem instrução explícita.

## Convenções Laravel a aplicar

Form Requests para validação. Não `$request->validate()` em controllers.

Policies para autorização. `authorize()` no Form Request chama `$this->user()->can(...)`.

API Resources para serialização de resposta. Não retornar Model direto.

Services para regra de negócio quando ela ultrapassa CRUD simples. Para CRUD simples, controller fino chamando Eloquent direto está ok.

Actions (classes invocáveis com um método `__invoke` ou `handle`) para operações isoladas com side effect (envio de email, processamento de pagamento, geração de relatório). Não exige pacote externo, basta classe POPO em `app/Actions`.

Repository pattern só se houver razão concreta (trocar de Eloquent para algo, ou abstrair fonte de dados externa). Em projeto pequeno, repository genérico em cima do Eloquent é overhead sem retorno.

Single Action Controllers (`__invoke`) para endpoints que têm uma única operação não-CRUD.

Queues para qualquer operação que demore mais que ~200ms e não precise de retorno síncrono: envio de email, processamento de upload, webhooks externos, geração de PDF/Excel, notificações. Driver: `database` é suficiente para portfólio, `redis` se já houver Redis no compose.

Cache em queries quentes que mudam pouco: `Cache::remember('chave', $ttl, fn () => ...)`. Invalidação explícita em eventos Eloquent (`saved`, `deleted`).

Eager loading sempre que houver `->with(...)` óbvio. Detectar N+1 com Laravel Telescope em dev ou `Model::preventLazyLoading()` no `AppServiceProvider::boot()` quando em ambiente local.

## Segurança (pilar transversal — todo subagente respeita)

`$fillable` explícito em todo modelo. `$guarded = []` é proibido.

Form Request com `authorize()` retornando decisão real, nunca `return true` literal.

Rotas aninhadas usam `->scopeBindings()` para evitar IDOR.

`DB::raw`, `whereRaw`, `orderByRaw` só com bindings parametrizados — nunca concatenação de input.

Cast `'encrypted'` em colunas com documento, token externo, dados pessoais sensíveis.

Rate limiting em login (5/min por email+IP), registro, recuperação de senha. Global em `/api/*` (60/min).

Headers de segurança via middleware: HSTS, X-Content-Type-Options, X-Frame-Options, Referrer-Policy, Permissions-Policy, CSP básico.

CORS sem wildcard quando `supports_credentials=true`.

`.env` nunca no git. `.env.example` com placeholders neutros, nunca valores que pareçam reais.

`APP_DEBUG=false` e `APP_ENV=production` documentados como obrigatórios em deploy.

Em respostas de login, registro e recovery: mensagem uniforme para email existente vs inexistente (evita user enumeration).

Comparação de tokens sensíveis: `hash_equals`, nunca `===`.

## Testes

Pest se o projeto já usa Pest. PHPUnit caso contrário. Não converter de um para outro sem motivo.

Feature tests cobrindo: autenticação, autorização (Policy), validação (Form Request), happy path, edge cases relevantes, rate limiting em endpoints sensíveis, IDOR em rotas aninhadas.

Unit tests em Services, Actions e qualquer classe com lógica não-trivial. Modelos Eloquent puros não precisam de unit test próprio (são cobertos por feature test).

Factories realistas — nomes plausíveis, emails plausíveis, dados coerentes entre si. Não `name => $faker->word()`.

Database: `RefreshDatabase` trait, SQLite em memória em CI.

## Documentação de API

Swagger/OpenAPI gerado automaticamente. Preferência: `dedoc/scramble` (não polui código com annotations). L5-Swagger só se o projeto já estiver com ele.

Documentar todos os response codes relevantes (200/201/204/401/403/404/422/429), não só o happy path.

Não documentar endpoints internos (admin debug, health detalhado) no Swagger público.

Security schemes definidos (bearerAuth para Sanctum) e aplicados nos endpoints protegidos.

## Git

Branches por feature: `feature/nome-curto`, `fix/nome-curto`, `refactor/nome-curto`, `docs/nome-curto`, `test/nome-curto`, `chore/nome-curto`.

Mensagens de commit em inglês, Conventional Commits sem rigidez: `feat:`, `fix:`, `refactor:`, `test:`, `docs:`, `chore:`, `perf:`, `style:`. Primeira linha até 72 caracteres, imperativa, sem ponto final. Corpo opcional explicando porquê.

Mensagens devem soar como dev real escrevendo rápido: ocasionalmente sem corpo, ocasionalmente em minúsculo após o tipo, ocasionalmente com erro de digitação não corrigido (raro, sem exagero). Nunca poéticas, nunca explicativas em excesso.

Não usar emoji em commit (nem gitmoji).

Não mencionar Claude, IA, agente, ou ferramenta de geração em nenhuma mensagem de commit, PR, comentário ou documentação. O projeto é do desenvolvedor.

## Como trabalhar

Antes de mexer em código, ler `composer.json` (versão do Laravel, dependências), `routes/api.php` e `routes/web.php` (superfície da API), `app/Models/` (domínio), `database/migrations/` (schema), README atual (intenção do projeto).

Mudanças grandes vão em branches separados e em commits separados — não amontoar refactor + feature + test no mesmo commit.

Quando houver dúvida sobre intenção do projeto (ex: "isso é um SaaS multitenant ou só um app simples?"), perguntar ao desenvolvedor antes de assumir.

Quando o subagente terminar uma fase, retornar relatório curto: o que foi feito, o que ficou pendente, o que precisa de decisão humana.
