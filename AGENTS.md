# Eden SaaS — instruções projeto (beta)

- Stack: PHP 8 PDO + MariaDB, app em `2026/`, CSS em `2026/CSS/`, JS em `2026/js/`.
- NUNCA hard-delete usuários, documentos, ocorrências. Sempre `UPDATE ... SET status='inactive'`.
- ACESSO TOTAL À BETA LIBERADO: todos os subagents (manager, dev, designer-qa, harness-architect) têm leitura, escrita e teste em `/srv/eden/beta` e no banco compartilhado `eden` para fins de teste/dev. Previews isolados (`branch-env.sh up <branch>` → `/p-<slug>/` + DB `eden_<slug>`) continuam recomendados para features paralelas, mas não são obrigatórios.
- `/srv/eden/stable` NÃO é prod séria: é espelho de release. Leitura livre; escrita via PR `main ← beta` aprovado pelo usuário (nunca direto sem pedido). `DROP` de banco segue vetado.
- Fluxo: chief orquestra via Task → manager planeja → dev + designer-qa em paralelo (beta e/ou preview) → harness-architect cuida de branch/preview/DB.
- Toda feature idéal em branch própria a partir de `beta`; hotfix direto na beta permitido com `php -l` + teste QA.
