<?php
require_once __DIR__ . '/_guard.php';

$canonicos = [
    [
        'nome' => 'Básico',
        'descricao' => 'Ideal para pequenos condomínios',
        'valor' => 167.00,
        'maxApartamentos' => 30,
        'funcionalidades' => 'Registro de ocorrências, Acompanhamento de status, Histórico dos últimos 3 meses, 1 administrador / síndico',
    ],
    [
        'nome' => 'Profissional',
        'descricao' => 'Perfeito para condomínios em crescimento',
        'valor' => 267.00,
        'maxApartamentos' => 100,
        'funcionalidades' => 'Upload de imagem, Chat por ocorrência, Relatórios semanais, 3 administradores, Notificações em tempo real',
    ],
    [
        'nome' => 'Empresarial',
        'descricao' => 'Para administradoras e grandes condomínios',
        'valor' => 367.00,
        'maxApartamentos' => 0,
        'funcionalidades' => 'Todos os recursos do Profissional, Funcionários e administradores ilimitados, Dashboard analítico de ocorrências, Exportação de relatórios em PDF',
    ],
];
$planos = [];
foreach ($canonicos as $c) {
    $stmt = $conexao->prepare("SELECT idPlano, nome, descricao, valor, maxApartamentos, funcionalidades FROM plano WHERE nome = :n AND ativo = 1 LIMIT 1");
    $stmt->execute(['n' => $c['nome']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $stmt = $conexao->prepare(
            "INSERT INTO plano (nome, descricao, valor, maxApartamentos, funcionalidades, ativo)
             VALUES (:n, :d, :v, :m, :f, 1)"
        );
        $stmt->execute([
            'n' => $c['nome'], 'd' => $c['descricao'], 'v' => $c['valor'],
            'm' => $c['maxApartamentos'], 'f' => $c['funcionalidades'],
        ]);
        $c['idPlano'] = (int) $conexao->lastInsertId();
        $row = $c;
    }
    $planos[] = $row;
}

$n = count($planos);

$stmt = $conexao->prepare("SELECT Plano_idPlano FROM condominio WHERE idCondominio = :c");
$stmt->execute(['c' => $filtroCondominio]);
$planoAtual = $stmt->fetchColumn();
$destaque = $n > 0 ? $planos[(int) floor($n / 2)]['idPlano'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planos - Eden Systems</title>
    <link rel="stylesheet" href="../CSS/planos.css?v=2">
    <?php include '../Elements/favicon.php'; ?>
</head>
<body>
    <header class="pl-topbar">
        <a class="pl-topback" href="../configuracoes.php">◀ Voltar</a>
        <img src="../assets/PNG/logobranca-laranja.png" alt="Éden Systems" class="pl-toplogo">
        <span></span>
    </header>

    <main class="pl-landing">
        <h1>Escolha o <span>plano ideal</span> para o seu condomínio</h1>
        <p class="pl-subtitle">Gerencie ocorrências, comunicação e histórico em um só lugar.</p>

        <div class="pl-perks">
            <span><span class="pl-check" aria-hidden="true">✓</span>Sem taxa de instalação</span>
            <span><span class="pl-check" aria-hidden="true">✓</span>Suporte Incluso</span>
            <span><span class="pl-check" aria-hidden="true">✓</span>Cancele quando quiser</span>
        </div>

        <div class="pl-cards">
            <?php foreach ($planos as $p): ?>
            <?php $ehDestaque = ((int) $p['idPlano'] === (int) $destaque); ?>
            <section class="pl-card<?= $ehDestaque ? ' featured' : '' ?><?= ((int) $p['idPlano'] === (int) $planoAtual) ? ' current' : '' ?>">
                <?php if ((int) $p['idPlano'] === (int) $planoAtual): ?>
                <span class="pl-current">Plano atual</span>
                <?php endif; ?>
                <div class="pl-card-head">
                    <h2><?= htmlspecialchars($p['nome']) ?></h2>
                    <p class="pl-card-price">R$ <strong><?= number_format((float) $p['valor'], 2, ',', '.') ?></strong> <small>/mês</small></p>
                    <p class="pl-card-desc"><?= htmlspecialchars($p['descricao']) ?></p>
                </div>
                <hr>
                <ul>
                    <li><?= ((int) $p['maxApartamentos'] > 0) ? 'Até ' . (int) $p['maxApartamentos'] . ' unidades' : 'Unidades ilimitadas' ?></li>
                    <?php foreach (array_filter(array_map('trim', explode(',', (string) $p['funcionalidades']))) as $f): ?>
                    <li><?= htmlspecialchars($f) ?></li>
                    <?php endforeach; ?>
                </ul>
                <a class="pl-btn" href="./forma-pagamento.php?plano=<?= (int) $p['idPlano'] ?>"><span class="pl-check" aria-hidden="true">✓</span>Assinar agora</a>
            </section>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
