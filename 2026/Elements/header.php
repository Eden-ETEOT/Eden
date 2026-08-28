<?php
// Header Component
// Este arquivo é importado nas páginas principais do dashboard
?>

<header class="dashboard-header">
    <div class="header-left">
        <div class="search-container">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" class="search-input" placeholder="Pesquisar...">
        </div>
    </div>

    <div class="header-right">
        <div class="notification-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <div class="notification-badge"></div>
        </div>

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
