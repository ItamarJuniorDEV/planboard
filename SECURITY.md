# Política de Segurança

## Versões com suporte

| Versão | Suporte |
| ------ | ------- |
| main   | sim     |

## Reportando uma vulnerabilidade

Se você encontrou uma vulnerabilidade neste projeto, por favor não abra uma issue pública. Usa o canal privado do GitHub ("Report a vulnerability" na aba Security do repositório) ou manda um email pra cdajuniorf@gmail.com.

Resposta inicial em até 7 dias. Patch costuma sair em 1 a 4 semanas dependendo da severidade. Divulgação pública é feita depois do patch estar disponível.

Ao reportar, inclui se possível: descrição do problema, passos pra reproduzir, versão ou commit testado, e qual o impacto observado. Prova de conceito ajuda mas não é obrigatória.

## Escopo

Cobre o código que está aqui no repo. Vulnerabilidades em dependências devem ser reportadas aos respectivos mantenedores. Configurações inseguras em ambientes de deploy próprios (chaves expostas, `APP_DEBUG=true` em produção, banco sem senha) não conta como vulnerabilidade aqui.
