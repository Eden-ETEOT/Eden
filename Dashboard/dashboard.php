<?php include '../elements/sidebar.php';
    
?>
    
    <div class="main-content">
        <?php include '../elements/headerDashboard.php'; ?>
        
        <div class="dashboard-container">
            <h2 class="page-title">Visão geral</h2>
            
            <div class="dashboard-grid">
                <!-- Saldo Mensal -->
                <?php include 'charts/monthly-balance.php'; ?>
                
                <!-- Gastos Anuais -->
                <?php include 'charts/annual-expenses.php'; ?>
                
                <!-- Satisfação Financeira -->
                <?php include 'charts/satisfaction.php'; ?>
            </div>
            
            <!-- Reservas -->
            <?php include 'sections/reservations.php'; ?>
            
            <!-- Cards de Estatísticas -->
            <div class="stats-grid">
                <?php include 'sections/financial-card.php'; ?>
                <?php include 'sections/visitors-card.php'; ?>
                <?php include 'sections/maintenance-card.php'; ?>
            </div>
            
            <!-- Listagem de Moradores -->
            <?php include 'sections/residents.php'; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/dashboard.js"></script>