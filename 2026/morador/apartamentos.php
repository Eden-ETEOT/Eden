<?php
require_once __DIR__ . '/_guard.php';

$pageTitle = 'Apartamentos';
$menuAtivo = 'apartamentos';

$stmt = $conexao->prepare(
    "SELECT un.idUnidade, un.numResid, un.bloco, un.andar, un.metragem, un.ativo
     FROM moradorunidade mu
     JOIN unidade un ON un.idUnidade = mu.Unidade_idUnidade
     WHERE mu.Morador_idMorador = :m AND mu.dataFim IS NULL
     ORDER BY un.bloco, un.numResid"
);
$stmt->execute(['m' => $idMorador]);
$unidades = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php pageHead('Apartamentos - Eden Systems', ['../CSS/tabelas.css', '../CSS/morador.css'], ['https://unpkg.com/lucide@latest'], '..'); ?>
<body>
<div class="dashboard-wrapper">
<?php include __DIR__ . '/sidebar.php'; ?>
<div class="main-content">
<?php include __DIR__ . '/header.php'; ?>
<div class="dashboard-content">
                <h1 class="page-title">Meu Apartamento</h1>

                <div class="mor-panel">
                    <div class="mor-panel-head">
                        <div>
                            <h2 class="mor-panel-title">Apartamento vinculado</h2>
                            <p class="mor-panel-sub">Informações disponíveis para o morador</p>
                        </div>
                    </div>
                    <div style="overflow-x:auto">
                        <table class="issues-table">
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
                                <?php if (empty($unidades)): ?>
                                <tr><td colspan="6"><div class="mor-empty">Nenhuma unidade vinculada.</div></td></tr>
                                <?php else: ?>
                                <?php foreach ($unidades as $u): ?>
                                <tr>
                                    <td>#<?= (int) $u['idUnidade'] ?></td>
                                    <td><?= htmlspecialchars($u['numResid']) ?></td>
                                    <td><?= htmlspecialchars($u['bloco']) ?></td>
                                    <td><?= htmlspecialchars($user_name) ?></td>
                                    <td><?= $u['metragem'] !== null ? htmlspecialchars(number_format((float) $u['metragem'], 0, ',', '.') . ' m²') : '—' ?></td>
                                    <td>
                                        <button type="button" class="tbl-action" title="Visualizar" onclick='morVerApto(<?= json_encode($u, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'><i data-lucide="eye"></i></button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mor-modal-overlay" id="morAptoModal">
        <div class="mor-modal">
            <div class="mor-modal-header">
                <h2>Detalhes do Apartamento</h2>
                <button type="button" class="mor-modal-close" onclick="morFecharModal('morAptoModal')"><i data-lucide="x"></i></button>
            </div>
            <div class="mor-modal-body">
                <h3 id="maTitle" style="font-size:17px;color:#294633;margin-bottom:18px"></h3>
                <div class="mor-info-grid">
                    <div class="mor-info-box"><span>Apartamento</span><strong id="maNum"></strong></div>
                    <div class="mor-info-box"><span>Bloco</span><strong id="maBloco"></strong></div>
                </div>
                <div class="mor-info-grid" style="margin-top:14px">
                    <div class="mor-info-box"><span>Área</span><strong id="maArea"></strong></div>
                    <div class="mor-info-box"><span>Status</span><strong id="maStatus"></strong></div>
                </div>
                <div class="mor-info-box" style="margin-top:14px"><span>Morador</span><strong><?= htmlspecialchars($user_name) ?></strong></div>
            </div>
        </div>
    </div>

    <script src="<?= assetUrl('../js/app.js') ?>"></script>
    <script>
        if (window.lucide) lucide.createIcons();
        function morAbrirModal(id) {
            document.getElementById(id).classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function morFecharModal(id) {
            document.getElementById(id).classList.remove('active');
            document.body.style.overflow = '';
        }
        document.querySelectorAll('.mor-modal-overlay').forEach(m => m.addEventListener('click', e => { if (e.target === m) morFecharModal(m.id); }));
        function morVerApto(u) {
            document.getElementById('maTitle').textContent = 'Apartamento #' + u.idUnidade;
            document.getElementById('maNum').textContent = u.numResid;
            document.getElementById('maBloco').textContent = u.bloco;
            document.getElementById('maArea').textContent = u.metragem !== null ? String(u.metragem).replace('.', ',') + ' m²' : '—';
            document.getElementById('maStatus').textContent = parseInt(u.ativo, 10) === 1 ? 'Ativo' : 'Inativo';
            morAbrirModal('morAptoModal');
        }
    </script>
</body>
</html>
