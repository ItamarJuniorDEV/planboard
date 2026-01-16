Backlog Item #7: CRUD Completo de Projetos

Como usuário da API, eu quero gerenciar os projetos da empresa.

Critérios de aceite:
  - GET /projects index 200
  - GET /projects/{id} 200 ou 404
  - POST /projects store 201
  - PUT /projects/{id} update 200 ou 404
  - DELETE /projects/{id} destroy 200 ou 404

Tabela projects:
  id — inteiro, auto increment, chave primária
  title — string, obrigatório, máximo 200 caracteres
  description — texto, opcional
  budget — decimal (12 dígitos total, 2 casas decimais), obrigatório
  status — string, obrigatório, máximo 30 caracteres, default 'draft'
  deadline — date, opcional
  created_at e updated_at — timestamps padrão

Validações no store e update:
  title — obrigatório, string, máximo 200
  description — opcional, string
  budget — obrigatório, numérico, mínimo 0
  status — obrigatório, string, máximo 30
  deadline — opcional, formato date
