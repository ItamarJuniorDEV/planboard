# Politica de Seguranca

## Versoes com suporte

| Versao | Suporte |
| ------ | ------- |
| main   | sim     |

## Reportando uma vulnerabilidade

Se voce encontrou uma vulnerabilidade neste projeto, por favor nao abra uma issue publica. Use o canal privado do GitHub ("Report a vulnerability" na aba Security do repositorio) ou envie um email para cdajuniorf@gmail.com.

Resposta inicial em ate 7 dias. Patch costuma sair em 1 a 4 semanas dependendo da severidade. Divulgacao publica e feita apos o patch estar disponivel.

Ao reportar, inclua se possivel: descricao do problema, passos para reproduzir, versao ou commit testado, e qual o impacto observado. Prova de conceito ajuda mas nao e obrigatoria.

## Escopo

Esta politica cobre o codigo deste repositorio. Vulnerabilidades em dependencias devem ser reportadas aos respectivos mantenedores. Configuracoes inseguras em ambientes de deploy proprios (chaves expostas, `APP_DEBUG=true` em producao, banco sem senha) nao sao tratadas como vulnerabilidade do projeto.
