// Monthly Balance Chart
const monthlyBalanceCtx = document.getElementById('monthlyBalanceChart');
if (monthlyBalanceCtx) {
    new Chart(monthlyBalanceCtx, {
        type: 'bar',
        data: {
            labels: ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'],
            datasets: [{
                label: 'Saldo',
                data: [350, 150, 180, 180, 250, 280, 230, 270, 200, 320, 150, 280],
                backgroundColor: function(context) {
                    const index = context.dataIndex;
                    // Highlight specific bars with gradient
                    if ([1, 3, 5, 7, 10].includes(index)) {
                        const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 400);
                        gradient.addColorStop(0, '#52734C');
                        gradient.addColorStop(1, '#4F772D');
                        return gradient;
                    }
                    return '#D8E3CE';
                },
                borderRadius: 4,
                barThickness: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(49, 87, 44, 0.9)',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 400,
                    ticks: {
                        stepSize: 100,
                        display: false
                    },
                    grid: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        display: false
                    },
                    border: {
                        display: false
                    }
                }
            }
        }
    });
}

// Annual Expenses Chart
const annualExpensesCtx = document.getElementById('annualExpensesChart');
if (annualExpensesCtx) {
    new Chart(annualExpensesCtx, {
        type: 'line',
        data: {
            labels: ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN'],
            datasets: [
                {
                    label: 'Este ano',
                    data: [180, 220, 160, 250, 290, 320],
                    borderColor: '#31572C',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6
                },
                {
                    label: 'Ano passado',
                    data: [150, 190, 210, 200, 250, 260],
                    borderColor: '#E6E8EC',
                    backgroundColor: 'transparent',
                    borderWidth: 3,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(49, 87, 44, 0.9)',
                    padding: 12,
                    cornerRadius: 8
                }
            },
            scales: {
                y: {
                    display: false
                },
                x: {
                    display: false
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}

// Satisfaction Donut Chart
const satisfactionCtx = document.getElementById('satisfactionChart');
if (satisfactionCtx) {
    new Chart(satisfactionCtx, {
        type: 'doughnut',
        data: {
            labels: ['Insatisfeitos', 'Satisfeitos', 'Neutro'],
            datasets: [{
                data: [37, 33, 30],
                backgroundColor: [
                    '#132A13',
                    '#4F772D',
                    'rgba(222, 196, 132, 0.79)'
                ],
                borderWidth: 0,
                cutout: '65%'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(49, 87, 44, 0.9)',
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + context.parsed + '%';
                        }
                    }
                }
            }
        }
    });
}

// Sidebar menu interactions
document.querySelectorAll('.menu-item.expandable').forEach(item => {
    item.addEventListener('click', function() {
        this.classList.toggle('expanded');
    });
});

// Approval/Rejection actions
document.querySelectorAll('.btn-action').forEach(btn => {
    btn.addEventListener('click', function() {
        const action = this.textContent.trim();
        const row = this.closest('tr');
        
        if (action === 'Aprovar') {
            if (confirm('Deseja aprovar esta reserva?')) {
                const statusBadge = row.querySelector('.status-badge');
                statusBadge.className = 'status-badge approved';
                statusBadge.textContent = 'Aprovado';
                
                // Replace buttons with "Ver" button
                const actionsCell = this.closest('td');
                actionsCell.innerHTML = '<button class="btn-action view">Ver</button>';
            }
        } else if (action === 'Rejeitar') {
            if (confirm('Deseja rejeitar esta reserva?')) {
                const statusBadge = row.querySelector('.status-badge');
                statusBadge.className = 'status-badge rejected';
                statusBadge.textContent = 'Negado';
                
                // Replace buttons with "Ver" button
                const actionsCell = this.closest('td');
                actionsCell.innerHTML = '<button class="btn-action view">Ver</button>';
            }
        } else if (action === 'Ver') {
            alert('Visualizar detalhes da reserva');
        }
    });
});


