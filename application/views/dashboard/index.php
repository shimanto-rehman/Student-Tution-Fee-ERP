<!-- application/views/dashboard/index.php -->
<div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="page-title">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard Overview
            </h2>
            <p class="page-subtitle">Welcome back! Here's what's happening with your fees today.</p>
        </div>
        <div class="current-date-badge">
            <i class="bi bi-calendar-check me-2"></i>
            <?= date('l, F d, Y') ?>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <!-- Total Students -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon-wrapper">
                <div class="stat-icon bg-primary">
                    <i class="bi bi-people"></i>
                    </div>
                <div class="stat-icon-bg"></div>
                    </div>
            <div class="stat-content">
                <h6 class="stat-label">Total Students</h6>
                <h2 class="stat-value"><?= number_format($total_students) ?></h2>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> Active
                </div>
            </div>
            <div class="stat-trend-line"></div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-success">
            <div class="stat-icon-wrapper">
                <div class="stat-icon bg-success">
                    <i class="bi bi-cash-stack"></i>
                    </div>
                <div class="stat-icon-bg"></div>
                    </div>
            <div class="stat-content">
                <h6 class="stat-label">Total Revenue</h6>
                <h2 class="stat-value">৳<?= number_format($total_revenue, 2) ?></h2>
                <div class="stat-change positive">
                    <i class="bi bi-arrow-up"></i> Collected
                </div>
            </div>
            <div class="stat-trend-line"></div>
        </div>
    </div>

    <!-- Outstanding Amount -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-danger">
            <div class="stat-icon-wrapper">
                <div class="stat-icon bg-danger">
                    <i class="bi bi-exclamation-triangle"></i>
                    </div>
                <div class="stat-icon-bg"></div>
                    </div>
            <div class="stat-content">
                <h6 class="stat-label">Outstanding</h6>
                <h2 class="stat-value">৳<?= number_format($outstanding_amount, 2) ?></h2>
                <div class="stat-change negative">
                    <i class="bi bi-arrow-down"></i> Pending
                </div>
            </div>
            <div class="stat-trend-line"></div>
        </div>
    </div>

    <!-- Overdue Fees -->
    <div class="col-md-6 col-lg-3">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon-wrapper">
                <div class="stat-icon bg-warning">
                    <i class="bi bi-clock-history"></i>
                    </div>
                <div class="stat-icon-bg"></div>
                    </div>
            <div class="stat-content">
                <h6 class="stat-label">Overdue Fees</h6>
                <h2 class="stat-value"><?= number_format($overdue_fees) ?></h2>
                <div class="stat-change negative">
                    <i class="bi bi-exclamation-circle"></i> Urgent
                </div>
            </div>
            <div class="stat-trend-line"></div>
        </div>
    </div>
</div>

<!-- Fee Status Overview -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="modern-card">
            <div class="card-header-modern">
                <div>
                    <h5 class="card-title-modern">
                        <i class="bi bi-bar-chart-line me-2"></i>
                        Monthly Collections (Last 6 Months)
                    </h5>
                    <p class="card-subtitle">Track your revenue over time</p>
                </div>
                <div class="chart-controls">
                    <button class="btn-icon active" data-period="6m">
                        <i class="bi bi-calendar-range"></i>
                    </button>
                </div>
            </div>
            <div class="card-body-modern">
                <canvas id="monthlyCollectionChart" height="90"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="modern-card">
            <div class="card-header-modern">
                <div>
                    <h5 class="card-title-modern">
                        <i class="bi bi-pie-chart me-2"></i>
                        Payment Status
                    </h5>
                    <p class="card-subtitle">Breakdown of fee status</p>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="chart-container">
                <canvas id="paymentStatusChart"></canvas>
                </div>
                
                <div class="status-legend mt-4">
                    <div class="legend-item">
                        <div class="legend-color paid"></div>
                        <div class="legend-content">
                            <span class="legend-label">Paid</span>
                            <span class="legend-value"><?= number_format($paid_fees) ?></span>
                        </div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color unpaid"></div>
                        <div class="legend-content">
                            <span class="legend-label">Unpaid</span>
                            <span class="legend-value"><?= number_format($unpaid_fees) ?></span>
                        </div>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color partial"></div>
                        <div class="legend-content">
                            <span class="legend-label">Partial</span>
                            <span class="legend-value"><?= number_format($partial_fees) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row g-4 mb-4">
    <!-- Recent Fees -->
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="card-header-modern">
                <div>
                    <h5 class="card-title-modern">
                        <i class="bi bi-receipt me-2"></i>
                        Recent Fees
                    </h5>
                    <p class="card-subtitle">Latest fee entries</p>
                </div>
                <a href="<?= base_url('student-fees') ?>" class="btn-action">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body-modern p-0">
                <div class="table-modern">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Month</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_fees)): ?>
                                <?php foreach ($recent_fees as $fee): ?>
                                    <tr>
                                        <td>
                                            <div class="student-info">
                                                <div class="student-avatar">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                            <div class="fw-bold"><?= $fee->name ?></div>
                                                    <small class="text-muted">Reg: <?= $fee->reg_no ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="month-badge"><?= date('M Y', strtotime($fee->month_year . '-01')) ?></span>
                                        </td>
                                        <td class="amount-cell">৳<?= number_format($fee->total_amount, 2) ?></td>
                                        <td>
                                            <?php if ($fee->payment_status === 'Paid'): ?>
                                                <span class="status-badge paid">
                                                    <i class="bi bi-check-circle me-1"></i> Paid
                                                </span>
                                            <?php elseif ($fee->payment_status === 'Partial'): ?>
                                                <span class="status-badge partial">
                                                    <i class="bi bi-clock-history me-1"></i> Partial
                                                </span>
                                            <?php else: ?>
                                                <span class="status-badge unpaid">
                                                    <i class="bi bi-x-circle me-1"></i> Unpaid
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No fees found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-lg-6">
        <div class="modern-card">
            <div class="card-header-modern">
                <div>
                    <h5 class="card-title-modern">
                        <i class="bi bi-credit-card me-2"></i>
                        Recent Payments
                    </h5>
                    <p class="card-subtitle">Latest payment transactions</p>
                </div>
                <a href="<?= base_url('payments') ?>" class="btn-action">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body-modern p-0">
                <div class="table-modern">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Mode</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($recent_payments)): ?>
                                <?php foreach ($recent_payments as $payment): ?>
                                    <tr>
                                        <td>
                                            <div class="student-info">
                                                <div class="student-avatar">
                                                    <i class="bi bi-person-circle"></i>
                                                </div>
                                                <div>
                                            <div class="fw-bold"><?= $payment->name ?></div>
                                            <small class="text-muted"><?= date('M Y', strtotime($payment->month_year . '-01')) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="date-badge">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <?= date('d M Y', strtotime($payment->payment_date)) ?>
                                            </span>
                                        </td>
                                        <td class="amount-cell success">
                                            <i class="bi bi-arrow-down-circle me-1"></i>
                                            ৳<?= number_format($payment->amount_paid, 2) ?>
                                        </td>
                                        <td>
                                            <span class="mode-badge">
                                                <i class="bi bi-wallet2 me-1"></i>
                                                <?= ucfirst($payment->payment_mode) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No payments found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="modern-card">
            <div class="card-header-modern">
                <div>
                    <h5 class="card-title-modern">
                        <i class="bi bi-lightning-charge me-2"></i>
                        Quick Actions
                    </h5>
                    <p class="card-subtitle">Frequently used functions</p>
                </div>
            </div>
            <div class="card-body-modern">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('student-fees/create') ?>" class="quick-action-card action-primary">
                            <div class="action-icon">
                                <i class="bi bi-plus-circle"></i>
                            </div>
                            <h6>Create New Fee</h6>
                            <p>Add a new fee entry</p>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('student-fees') ?>" class="quick-action-card action-info">
                            <div class="action-icon">
                                <i class="bi bi-receipt-cutoff"></i>
                            </div>
                            <h6>Manage Fees</h6>
                            <p>View and edit fees</p>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <a href="<?= base_url('payments') ?>" class="quick-action-card action-success">
                            <div class="action-icon">
                                <i class="bi bi-cash-coin"></i>
                            </div>
                            <h6>View Payments</h6>
                            <p>Payment history</p>
                        </a>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <button onclick="window.open('<?= base_url('test_due_bills_api.html') ?>', '_blank')" class="quick-action-card action-secondary w-100 border-0">
                            <div class="action-icon">
                                <i class="bi bi-code-slash"></i>
                            </div>
                            <h6>Test API</h6>
                            <p>API testing</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert for Overdue Fees -->
<?php if ($overdue_fees > 0): ?>
<div class="row g-4">
    <div class="col-12">
        <div class="alert-modern alert-warning">
            <div class="alert-icon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div class="alert-content">
                <h5 class="alert-title">Attention Required!</h5>
                <p class="alert-text">
                        You have <strong><?= number_format($overdue_fees) ?></strong> overdue fee(s) that need immediate attention.
                        Late fees are being calculated automatically.
                    </p>
                </div>
            <div class="alert-action">
                <a href="<?= base_url('student-fees') ?>" class="btn btn-warning">
                    View Overdue Fees <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Chart.js Script -->
<script src="<?= base_url('assets/chart.umd.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Collection Chart
    const monthlyCtx = document.getElementById('monthlyCollectionChart');
    if (monthlyCtx) {
        const monthlyData = <?= json_encode($monthly_collections) ?>;
        
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(item => item.month),
                datasets: [{
                    label: 'Collection Amount (৳)',
                    data: monthlyData.map(item => parseFloat(item.amount)),
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return '৳' + context.parsed.y.toLocaleString('en-BD', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500'
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11,
                                weight: '500'
                            },
                            callback: function(value) {
                                return '৳' + value.toLocaleString('en-BD');
                            }
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeInOutQuart'
                }
            }
        });
    }

    // Payment Status Pie Chart
    const statusCtx = document.getElementById('paymentStatusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Unpaid', 'Partial'],
                datasets: [{
                    data: [
                        <?= $paid_fees ?>,
                        <?= $unpaid_fees ?>,
                        <?= $partial_fees ?>
                    ],
                    backgroundColor: [
                        'rgba(39, 174, 96, 0.9)',
                        'rgba(231, 76, 60, 0.9)',
                        'rgba(243, 156, 18, 0.9)'
                    ],
                    borderColor: '#ffffff',
                    borderWidth: 3,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return label + ': ' + value + ' (' + percentage + '%)';
                            }
                        }
                    }
                },
                cutout: '70%',
                animation: {
                    animateRotate: true,
                    duration: 1500
                }
            }
        });
    }
});
</script>