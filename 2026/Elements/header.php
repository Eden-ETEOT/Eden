<?php
// Header Component
// Este arquivo é importado nas páginas principais do dashboard
?>

<header class="dashboard-header">
    <div class="header-left">
        <h1 class="header-title"><?= htmlspecialchars($pageTitle ?? 'Painel de Controle') ?></h1>
    </div>

    <div class="header-right">
        <button type="button" class="user-profile" id="profileBtn" aria-haspopup="menu" aria-expanded="false">
            <?php if (!empty($user_foto)): ?>
                <span class="user-avatar user-avatar-img" id="userAvatar" style="background-image: url('<?= htmlspecialchars($user_foto) ?>'); background-size: cover; background-position: center;"></span>
            <?php else: ?>
                <span class="user-avatar" id="userAvatar"><?= htmlspecialchars($user_avatar) ?></span>
            <?php endif; ?>
            <span class="user-info">
                <span class="user-name" id="userName"><?= htmlspecialchars($user_name) ?></span>
                <span class="user-type" id="userType"><?= htmlspecialchars($user_type) ?></span>
            </span>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>
        <nav class="profile-menu" id="profileMenu" role="menu" aria-label="Menu do usuário">
            <a href="./configuracoes.php" role="menuitem"><i data-lucide="user"></i>Meu perfil</a>
            <a href="./suporte.php" role="menuitem"><i data-lucide="life-buoy"></i>Suporte</a>
            <a href="./logout.php" role="menuitem" class="danger"><i data-lucide="log-out"></i>Sair</a>
        </nav>
    </div>
</header>
<script>
(function () {
    var btn = document.getElementById('profileBtn');
    var menu = document.getElementById('profileMenu');
    if (!btn || !menu) return;
    function close() {
        menu.classList.remove('open');
        btn.setAttribute('aria-expanded', 'false');
    }
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var open = menu.classList.toggle('open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target)) close();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });
})();
</script>