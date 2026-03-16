# Planboard API

API RESTful para gerenciamento de projetos com quadros kanban.

A ideia era ter um backend completo pra organizar projetos em equipe — com autenticação, controle de papel, autorização por dono do recurso e operações em lote. O projeto serviu pra explorar esses padrões de forma mais próxima de como aparecem em sistemas reais.

---

## Funcionalidades

- Autenticação por token com Laravel Sanctum
- CRUD completo de projetos, quadros, colunas, tarefas, subtarefas, comentários, marcos e etiquetas
- Suporte a kanban: mover tarefas entre colunas, mover e excluir em lote
- Autorização por dono do recurso — só o criador ou um admin pode editar/deletar
- Controle de acesso por papel (admin / member)
- Filtros, busca e paginação nos endpoints de listagem
- Estatísticas do projeto: tarefas por status/prioridade, progresso de subtarefas e marcos em atraso

---

## Tecnologias

- PHP 8.3 / Laravel 12
- MySQL
- Laravel Sanctum (autenticação)
- PHPUnit (testes)
- Docker

---

## Instalação

**Pré-requisitos:** PHP 8.3+, Composer, MySQL

```bash
git clone https://github.com/ItamarJuniorDEV/planboard-api.git
cd planboard-api
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate
```

## Rodando

```bash
php artisan serve
```

---

## Autenticação

| Método | Endpoint | Descrição |
|--------|----------|-----------|
| POST | `/api/login` | Login e geração de token |
| POST | `/api/logout` | Revogar token |

Todos os outros endpoints exigem o header `Authorization: Bearer {token}`.

---

## Endpoints

### Projetos
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects` | Listar projetos |
| GET | `/api/projects/{id}` | Buscar projeto |
| POST | `/api/projects` | Criar projeto |
| PUT | `/api/projects/{id}` | Atualizar projeto |
| DELETE | `/api/projects/{id}` | Deletar projeto |
| GET | `/api/projects/{id}/stats` | Estatísticas do projeto |

### Quadros
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/boards` | Listar quadros |
| POST | `/api/projects/{projectId}/boards` | Criar quadro |
| PUT | `/api/projects/{projectId}/boards/{id}` | Atualizar quadro |
| DELETE | `/api/projects/{projectId}/boards/{id}` | Deletar quadro |

### Colunas
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/boards/{boardId}/columns` | Listar colunas |
| POST | `/api/projects/{projectId}/boards/{boardId}/columns` | Criar coluna |
| PUT | `/api/projects/{projectId}/boards/{boardId}/columns/{id}` | Atualizar coluna |
| DELETE | `/api/projects/{projectId}/boards/{boardId}/columns/{id}` | Deletar coluna |

### Tarefas
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/tasks` | Listar tarefas |
| POST | `/api/projects/{projectId}/tasks` | Criar tarefa |
| PUT | `/api/projects/{projectId}/tasks/{id}` | Atualizar tarefa |
| DELETE | `/api/projects/{projectId}/tasks/{id}` | Deletar tarefa |
| PATCH | `/api/projects/{projectId}/boards/{boardId}/columns/{columnId}/tasks/{taskId}/move` | Mover tarefa para coluna |
| PATCH | `/api/projects/{projectId}/tasks/bulk-move` | Mover tarefas em lote |
| POST | `/api/projects/{projectId}/tasks/bulk-delete` | Deletar tarefas em lote |

### Subtarefas
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/tasks/{taskId}/subtasks` | Listar subtarefas |
| POST | `/api/projects/{projectId}/tasks/{taskId}/subtasks` | Criar subtarefa |
| PUT | `/api/projects/{projectId}/tasks/{taskId}/subtasks/{id}` | Atualizar subtarefa |
| DELETE | `/api/projects/{projectId}/tasks/{taskId}/subtasks/{id}` | Deletar subtarefa |
| POST | `/api/projects/{projectId}/tasks/{taskId}/subtasks/bulk-complete` | Concluir em lote |
| POST | `/api/projects/{projectId}/tasks/{taskId}/subtasks/bulk-delete` | Deletar em lote |

### Comentários
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/tasks/{taskId}/comments` | Listar comentários |
| POST | `/api/projects/{projectId}/tasks/{taskId}/comments` | Criar comentário |
| PUT | `/api/projects/{projectId}/tasks/{taskId}/comments/{id}` | Atualizar comentário |
| DELETE | `/api/projects/{projectId}/tasks/{taskId}/comments/{id}` | Deletar comentário |

### Marcos
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/milestones` | Listar marcos |
| POST | `/api/projects/{projectId}/milestones` | Criar marco |
| PUT | `/api/projects/{projectId}/milestones/{id}` | Atualizar marco |
| DELETE | `/api/projects/{projectId}/milestones/{id}` | Deletar marco |

### Etiquetas
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/projects/{projectId}/labels` | Listar etiquetas |
| POST | `/api/projects/{projectId}/labels` | Criar etiqueta |
| PUT | `/api/projects/{projectId}/labels/{id}` | Atualizar etiqueta |
| DELETE | `/api/projects/{projectId}/labels/{id}` | Deletar etiqueta |

### Usuários *(somente admin)*
| Método | Endpoint | Descrição |
|--------|----------|-----------|
| GET | `/api/users` | Listar usuários |
| GET | `/api/users/{id}` | Buscar usuário |
| POST | `/api/users` | Criar usuário |
| PUT | `/api/users/{id}` | Atualizar usuário |
| DELETE | `/api/users/{id}` | Deletar usuário |

---

## Controle de Acesso

| Papel | Permissões |
|-------|------------|
| `admin` | Acesso total — pode editar e deletar qualquer recurso |
| `member` | Acesso aos próprios recursos — só pode editar/deletar o que criou |

---

## Testes

Os testes rodam com SQLite em memória, sem precisar do MySQL configurado.

```bash
php artisan test
```

---

## Licença

MIT
