<?php
$menuAtivo = 'apartamentos';
$tituloPagina = 'Apartamentos';
include __DIR__ . '/_top.php';

$area = $moradia['metragem'] !== null ? str_replace('.', ',', (string) $moradia['metragem']) . ' m²' : '—';
?>
<section class="page-title">
    <h1>Meu Apartamento</h1>
    <p>Consulte as informações da unidade vinculada ao seu cadastro.</p>
</section>
<div class="panel table-panel">
    <div class="panel-head">
        <div>
            <h2 class="panel-title">Apartamento vinculado</h2>
            <p class="sub">Informações disponíveis para o morador</p>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Numeração</th>
                <th>Bloco</th>
                <th>Morador</th>
                <th>Área</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>#<?= (int) $moradia['idUnidade'] ?></td>
                <td><?= htmlspecialchars($moradia['numResid']) ?></td>
                <td><?= htmlspecialchars($moradia['bloco']) ?></td>
                <td><?= htmlspecialchars($nomeMorador) ?></td>
                <td><?= htmlspecialchars($area) ?></td>
                <td>
                    <div class="actions"><i data-lucide="eye" data-open="detalhes"></i></div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal-overlay" id="detalhes">
    <div class="modal">
        <div class="modal-title">
            <h2>Detalhes do Apartamento</h2><i data-lucide="x" data-close style="cursor:pointer"></i></div>
        <div class="modal-content">
            <h3 style="margin-bottom:18px">Apartamento #<?= (int) $moradia['idUnidade'] ?></h3>
            <div class="apt">
                <div><small>Apartamento</small><strong><?= htmlspecialchars($moradia['numResid']) ?></strong></div>
                <div><small>Bloco</small><strong><?= htmlspecialchars($moradia['bloco']) ?></strong></div>
            </div>
            <div class="apt">
                <div><small>Área</small><strong><?= htmlspecialchars($area) ?></strong></div>
                <div><small>Andar</small><strong><?= htmlspecialchars((string) $moradia['andar']) ?></strong></div>
            </div>
            <div style="background:#f5f7f5;padding:14px;border-radius:9px;margin-top:14px"><small>Morador</small><strong style="display:block;margin-top:4px"><?= htmlspecialchars($nomeMorador) ?></strong></div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/_bottom.php'; ?>
