<!-- application/views/dashboard/index.php -->
<div class="row mb-4">
    <div class="col-12">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <p class="text-muted">Welcome to Pioneer Dental College Payment Management System</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <!-- Total Students -->
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Students</h6>
                        <h3 class="mb-0"><?= number_format($total_students) ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-people fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Revenue</h6>
                        <h3 class="mb-0 text-success">৳<?= number_format($total_revenue, 2) ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cash-stack fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Outstanding Amount -->
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Outstanding</h6>
                        <h3 class="mb-0 text-danger">৳<?= number_format($outstanding_amount, 2) ?></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 p-3 rounded">
                        <i class="bi bi-exclamation-triangle fs-2 text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overdue Fees -->
    <div class="col-md-3 mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Overdue Fees</h6>
                        <h3 class="mb-0 text-warning"><?= number_format($overdue_fees) ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded">
                        <i class="bi bi-clock-history fs-2 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fee Status Overview -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="bi bi-bar-chart"></i> Monthly Collections (Last 6 Months)</h5>
            </div>
            <div class="card-body">
                <canvas id="monthlyCollectionChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Payment Status</h5>
            </div>
            <div class="card-body">
                <canvas id="paymentStatusChart"></canvas>
                
                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-circle-fill text-success"></i> Paid</span>
                        <strong><?= number_format($paid_fees) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><i class="bi bi-circle-fill text-danger"></i> Unpaid</span>
                        <strong><?= number_format($unpaid_fees) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-circle-fill text-warning"></i> Partial</span>
                        <strong><?= number_format($partial_fees) ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="row mb-4">
    <!-- Recent Fees -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-receipt"></i> Recent Fees</h5>
                <a href="<?= base_url('student-fees') ?>" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                            <div class="fw-bold"><?= $fee->name ?></div>
                                            <small class="text-muted"><?= $fee->reg_no ?></small>
                                        </td>
                                        <td><?= date('M Y', strtotime($fee->month_year . '-01')) ?></td>
                                        <td>৳<?= number_format($fee->total_amount, 2) ?></td>
                                        <td>
                                            <?php if ($fee->payment_status === 'Paid'): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif ($fee->payment_status === 'Partial'): ?>
                                                <span class="badge bg-warning">Partial</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Unpaid</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No fees found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Payments -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-credit-card"></i> Recent Payments</h5>
                <a href="<?= base_url('payments') ?>" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
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
                                            <div class="fw-bold"><?= $payment->name ?></div>
                                            <small class="text-muted"><?= date('M Y', strtotime($payment->month_year . '-01')) ?></small>
                                        </td>
                                        <td><?= date('d M Y', strtotime($payment->payment_date)) ?></td>
                                        <td class="text-success fw-bold">৳<?= number_format($payment->amount_paid, 2) ?></td>
                                        <td>
                                            <span class="badge bg-info"><?= ucfirst($payment->payment_mode) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-muted">No payments found</td>
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
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0"><i class="bi bi-lightning"></i> Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('student-fees/create') ?>" class="btn btn-outline-primary w-100 py-3">
                            <i class="bi bi-plus-circle fs-4 d-block mb-2"></i>
                            Create New Fee
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('student-fees') ?>" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-receipt-cutoff fs-4 d-block mb-2"></i>
                            Manage Fees
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <a href="<?= base_url('payments') ?>" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-cash-coin fs-4 d-block mb-2"></i>
                            View Payments
                        </a>
                    </div>
                    <div class="col-md-3 mb-3">
                        <button onclick="window.open('<?= base_url('api_testing.html') ?>', '_blank')" class="btn btn-outline-secondary w-100 py-3">
                            <i class="bi bi-code-slash fs-4 d-block mb-2"></i>
                            Test MTB API
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alert for Overdue Fees -->
<?php if ($overdue_fees > 0): ?>
<div class="row">
    <div class="col-12">
        <div class="alert alert-warning border-0 shadow-sm">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-3 me-3"></i>
                <div class="flex-grow-1">
                    <h5 class="alert-heading mb-1">Attention Required!</h5>
                    <p class="mb-0">
                        You have <strong><?= number_format($overdue_fees) ?></strong> overdue fee(s) that need immediate attention.
                        Late fees are being calculated automatically.
                        <a href="<?= base_url('student-fees') ?>" class="alert-link">View overdue fees →</a>
                    </p>
                </div>
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
                    data: monthlyData.map(item => item.amount),
                    backgroundColor: 'rgba(52, 152, 219, 0.8)',
                    borderColor: 'rgba(52, 152, 219, 1)',
                    borderWidth: 2,
                    borderRadius: 5
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
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + value.toLocaleString('en-BD');
                            }
                        }
                    }
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
                        'rgba(39, 174, 96, 0.8)',
                        'rgba(231, 76, 60, 0.8)',
                        'rgba(243, 156, 18, 0.8)'
                    ],
                    borderColor: [
                        'rgba(39, 174, 96, 1)',
                        'rgba(231, 76, 60, 1)',
                        'rgba(243, 156, 18, 1)'
                    ],
                    borderWidth: 2
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
                }
            }
        });
    }
});
</script>

<style>
.card {
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
}

.table tbody tr {
    transition: background-color 0.2s;
}

.table tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}

.btn-outline-primary:hover, .btn-outline-info:hover, 
.btn-outline-success:hover, .btn-outline-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
</style>s