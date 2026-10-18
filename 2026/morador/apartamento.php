<?php
require_once __DIR__ . '/_guard.php';
require_once __DIR__ . '/_layout.php';

$stmt = $conexao->prepare(
    "SELECT u.idUnidade, u.numResid, u.bloco, u.andar, u.metragem, u.ativo, us.nome AS moradorNome
     FROM unidade u
     LEFT JOIN moradorunidade mu ON mu.Unidade_idUnidade = u.idUnidade AND mu.dataFim IS NULL
     LEFT JOIN morador m ON m.idMorador = mu.Morador_idMorador
     LEFT JOIN usuario us ON us.idUsuario = m.idUsuario
     WHERE u.idUnidade = :u AND u.Condominio_idCondominio = :c LIMIT 1"
);
$stmt->execute(['u' => $moradia['idUnidade'], 'c' => $filtroCondominio]);
$apt = $stmt->fetch(PDO::FETCH_ASSOC);

$rotuloTipo = ['proprietario' => 'Proprietário', 'inquilino' => 'Inquilino', 'dependente' => 'Dependente'];
$sub = ($rotuloTipo[$moradia['tipoMorador']] ?? 'Morador') . ' • Apto ' . $moradia['numResid'];

moradorHead('Apartamentos');
moradorSidebar('apartamento');
moradorHeader($user['nome'] ?? 'Morador', $sub, 'Apartamentos');
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
                        <?php if ($apt): ?>
                        <tr>
                            <td>#<?= (int) $apt['idUnidade'] ?></td>
                            <td><?= htmlspecialchars($apt['numResid']) ?></td>
                            <td><?= htmlspecialchars($apt['bloco']) ?></td>
                            <td><?= htmlspecialchars($apt['moradorNome'] ?? $user['nome'] ?? '—') ?></td>
                            <td><?= $apt['metragem'] !== null ? htmlspecialchars(number_format((float) $apt['metragem'], 0, ',', '.') . ' m²') : '—' ?></td>
                            <td>
                                <div class="actions"><i data-lucide="eye" data-open="detalhes"></i></div>
                            </td>
                        </tr>
                        <?php else: ?>
                        <tr><td colspan="6"><p class="sub">Unidade não encontrada.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($apt): ?>
            <div class="modal-overlay" id="detalhes">
                <div class="modal">
                    <div class="modal-title">
                        <h2>Detalhes do Apartamento</h2><i data-lucide="x" data-close style="cursor:pointer"></i></div>
                    <div class="modal-content">
                        <h3 style="margin-bottom:18px">Apartamento #<?= (int) $apt['idUnidade'] ?></h3>
                        <div class="apt">
                            <div><small>Apartamento</small><strong><?= htmlspecialchars($apt['numResid']) ?></strong></div>
                            <div><small>Bloco</small><strong><?= htmlspecialchars($apt['bloco']) ?></strong></div>
                        </div>
                        <div class="apt">
                            <div><small>Área</small><strong><?= $apt['metragem'] !== null ? htmlspecialchars(number_format((float) $apt['metragem'], 0, ',', '.') . ' m²') : '—' ?></strong></div>
                            <div><small>Status</small><strong><?= ((int) $apt['ativo']) === 1 ? 'Ativo' : 'Inativo' ?></strong></div>
                        </div>
                        <div style="background:#f5f7f5;padding:14px;border-radius:9px;margin-top:14px"><small>Morador</small><strong style="display:block;margin-top:4px"><?= htmlspecialchars($apt['moradorNome'] ?? $user['nome'] ?? '—') ?></strong></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
<?php moradorFoot(); ?>
