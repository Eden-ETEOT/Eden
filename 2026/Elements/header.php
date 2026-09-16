<?php
// Header Component
// Este arquivo é importado nas páginas principais do dashboard
?>

<header class="dashboard-header">
    <div class="header-left">
        <h1 class="header-title"><?= htmlspecialchars($pageTitle ?? 'Painel de Controle') ?></h1>
    </div>

    <div class="header-right">
        <div class="user-profile">
            <?php if (!empty($user_foto)): ?>
                <div class="user-avatar user-avatar-img" id="userAvatar" style="background-image: url('<?= htmlspecialchars($user_foto) ?>'); background-size: cover; background-position: center;"></div>
            <?php else: ?>
                <div class="user-avatar" id="userAvatar"><?= htmlspecialchars($user_avatar) ?></div>
            <?php endif; ?>
            <div class="user-info">
                <span class="user-name" id="userName"><?= htmlspecialchars($user_name) ?></span>
                <span class="user-type" id="userType"><?= htmlspecialchars($user_type) ?></span>
            </div>
            <svg class="dropdown-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </div>
</header>