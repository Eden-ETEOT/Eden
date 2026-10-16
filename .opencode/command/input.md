---
description: Registra anotação bruta no inbox local e tria em backlog sanitizado sem vazar segredos para o GitHub.
---

Você é o fluxo `/input` do projeto Eden. O usuário colou abaixo anotações brutas (WhatsApp/Notepad) em `$ARGUMENTS`. Execute nesta ordem:

1. **Salvar bruto (local-only):** anexe em `input/inbox.md` com cabeçalho `## YYYY-MM-DD HH:MM — #N` (N = próximo sequencial) + texto EXATO do usuário, sem editar. Nunca altere entradas antigas.
2. **Triar via manager:** para cada item, classifique em `codigo | landing-visual | tcc-apresentacao | ideia`. Reescreva em linguagem técnica curta. Ideias vagas vão para `ideia/estacionado` (não executa).
3. **Sanitizar (obrigatório):** antes de tocar em `input/backlog.md`, remova `senhas, tokens (ghp_, sk-, Bearer), connection strings, CPF, telefones, nomes de chats fechados, nomes de alunos/professores em contexto sensível` → troque por `<redacted>`. Nunca copie trecho literal de conversa privada para o backlog.
4. **Atualizar `input/backlog.md`:** acrescente linhas `| #ID | tipo | resumo sanitizado | branch sugerida | esforço P/M/G | status: triado |`. Não remova linhas antigas, só mude `status`.
5. **Trava GitHub:** rode `git status --short`; se `input/inbox.md` ou `*.local.md` aparecer como commitável, AVISE e não commite. Nunca `git add input/inbox.md`. Nunca exiba `git remote -v` completo (contém token).
6. **Resposta:** liste o que foi triado + sugira **1 próxima tarefa** pela regra (hotfix prod/beta quebrado > TCC com data > quick-win P > feature M/G; ideia fica estacionada). Termine com: `Digite "vai" para executar a próxima ou cole mais notas.` Não execute código neste comando.

Anotações do usuário ($ARGUMENTS):
$ARGUMENTS
