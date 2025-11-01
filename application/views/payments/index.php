<!-- application/views/payments/index.php -->
<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-credit-card"></i> Payment Records</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('student-fees') ?>" class="btn btn-primary">
            <i class="bi bi-receipt"></i> View Fees
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Payments</h6>
                        <h3 class="mb-0"><?= count($payments) ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded">
                        <i class="bi bi-receipt-cutoff fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Amount</h6>
                        <h3 class="mb-0 text-success">
                            ৳<?= number_format(array_sum(array_column($payments, 'amount_paid')), 2) ?>
                        </h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded">
                        <i class="bi bi-cash-stack fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">This Month</h6>
                        <h3 class="mb-0 text-info">
                            <?php 
                            $this_month = array_filter($payments, function($p) {
                                return date('Y-m', strtotime($p->payment_date)) === date('Y-m');
                            });
                            echo count($this_month);
                            ?>
                        </h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded">
                        <i class="bi bi-calendar-check fs-2 text-info"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('payments') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Student name or reg no" 
                       value="<?= $this->input->get('search') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment Mode</label>
                <select name="mode" class="form-select">
                    <option value="">All</option>
                    <option value="cash" <?= $this->input->get('mode') === 'cash' ? 'selected' : '' ?>>Cash</option>
                    <option value="bank" <?= $this->input->get('mode') === 'bank' ? 'selected' : '' ?>>Bank</option>
                    <option value="online" <?= $this->input->get('mode') === 'online' ? 'selected' : '' ?>>Online</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="<?= $this->input->get('from_date') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="<?= $this->input->get('to_date') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid gap-2 d-md-flex">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="<?= base_url('payments') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Reg No</th>
                        <th>Month/Year</th>
                        <th>Amount</th>
                        <th>Mode</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= $payment->id ?></td>
                                <td><?= date('d M Y', strtotime($payment->payment_date)) ?></td>
                                <td><?= $payment->name ?></td>
                                <td><?= $payment->reg_no ?></td>
                                <td><?= date('M Y', strtotime($payment->month_year . '-01')) ?></td>
                                <td class="text-success fw-bold">৳<?= number_format($payment->amount_paid, 2) ?></td>
                                <td>
                                    <span class="badge bg-info"><?= ucfirst($payment->payment_mode) ?></span>
                                </td>
                                <td>
                                    <small class="text-muted"><?= $payment->transaction_id ?? 'N/A' ?></small>
                                </td>
                                <td>
                                    <?php if (isset($payment->mtb_payment_status)): ?>
                                        <?php if ($payment->mtb_payment_status === 'success'): ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php elseif ($payment->mtb_payment_status === 'pending'): ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php elseif ($payment->mtb_payment_status === 'failed'): ?>
                                            <span class="badge bg-danger">Failed</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">N/A</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="badge bg-success">Completed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('payments/receipt/' . $payment->id) ?>" 
                                       class="btn btn-sm btn-success" title="View Receipt">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    <a href="<?= base_url('student-fees/view/' . $payment->student_fee_id) ?>" 
                                       class="btn btn-sm btn-info" title="View Fee">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    <p class="mb-0">No payment records found</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($payments) && count($payments) >= $limit): ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $offset + count($payments)) ?>
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <?php if ($offset > 0): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('payments?offset=' . max(0, $offset - $limit)) ?>">
                                    Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (count($payments) >= $limit): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('payments?offset=' . ($offset + $limit)) ?>">
                                    Next
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Export Options -->
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title mb-3">
            <i class="bi bi-download"></i> Export Options
        </h5>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success" onclick="exportToCSV()">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export to CSV
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportToPDF()">
                <i class="bi bi-file-earmark-pdf"></i> Export to PDF
            </button>
            <button type="button" class="btn btn-outline-info" onclick="window.print()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    // Simple CSV export
    let csv = 'ID,Date,Student,Reg No,Month,Amount,Mode,Transaction ID,Status\n';
    
    <?php foreach ($payments as $payment): ?>
    csv += '<?= $payment->id ?>,';
    csv += '<?= date('Y-m-d', strtotime($payment->payment_date)) ?>,';
    csv += '"<?= addslashes($payment->name) ?>",';
    csv += '<?= $payment->reg_no ?>,';
    csv += '<?= date('Y-m', strtotime($payment->month_year . '-01')) ?>,';
    csv += '<?= $payment->amount_paid ?>,';
    csv += '<?= $payment->payment_mode ?>,';
    csv += '"<?= $payment->transaction_id ?? '' ?>",';
    csv += '<?= $payment->mtb_payment_status ?? 'completed' ?>\n';
    <?php endforeach; ?>
    
    // Download
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'payments_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}

function exportToPDF() {
    // This would require a PDF library or server-side generation
    alert('PDF export functionality can be implemented using jsPDF or server-side PDF generation.');
}
</script>

<style media="print">
    .btn, .card-header, .pagination, .export-options { display: none; }
    .table { font-size: 12px; }
</style>