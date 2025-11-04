<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-credit-card text-primary"></i>
            </div>
            Payment Records
        </h2>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= base_url('student-fees') ?>" class="btn btn-primary">
            <i class="bi bi-receipt me-2"></i> View Fees
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card summary-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Payments</h6>
                        <h3 class="mb-0 text-primary"><?= count($payments) ?></h3>
                    </div>
                    <div class="summary-icon bg-opacity-10">
                        <i class="bi bi-receipt-cutoff fs-2 text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card summary-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Amount</h6>
                        <h3 class="mb-0 text-success">
                            ৳<?= number_format(array_sum(array_column($payments, 'amount_paid')), 2) ?>
                        </h3>
                    </div>
                    <div class="summary-icon bg-opacity-10">
                        <i class="bi bi-cash-stack fs-2 text-success"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card summary-card">
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
                    <div class="summary-icon bg-opacity-10">
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
                    <option value="">All Modes</option>
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
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="bi bi-search me-2"></i> Filter
                    </button>
                    <a href="<?= base_url('payments') ?>" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-2"></i> Clear
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
                                <td><strong>#<?= $payment->id ?></strong></td>
                                <td><?= date('d M Y', strtotime($payment->payment_date)) ?></td>
                                <td><?= $payment->name ?></td>
                                <td><code><?= $payment->reg_no ?></code></td>
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
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('payments/receipt/' . $payment->id) ?>" 
                                           class="btn btn-success" title="View Receipt">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                        <a href="<?= base_url('student-fees/view/' . $payment->student_fee_id) ?>" 
                                           class="btn btn-info" title="View Fee">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="bi bi-inbox display-4 d-block mb-3"></i>
                                    <h5>No payment records found</h5>
                                    <p class="mb-0">Try adjusting your filters or create new payments</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if (!empty($payments) && count($payments) >= $limit): ?>
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing <?= $offset + 1 ?> to <?= min($offset + $limit, $offset + count($payments)) ?> of <?= count($payments) ?> entries
                </div>
                <nav>
                    <ul class="pagination mb-0">
                        <?php if ($offset > 0): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('payments?offset=' . max(0, $offset - $limit)) ?>">
                                    <i class="bi bi-chevron-left me-1"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php if (count($payments) >= $limit): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?= base_url('payments?offset=' . ($offset + $limit)) ?>">
                                    Next <i class="bi bi-chevron-right ms-1"></i>
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
<div class="card mt-4 export-section">
    <div class="card-body">
        <h5 class="card-title mb-3">
            <i class="bi bi-download me-2"></i>Export Options
        </h5>
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-success" onclick="exportToCSV()">
                <i class="bi bi-file-earmark-spreadsheet me-2"></i> Export to CSV
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="exportToPDF()">
                <i class="bi bi-file-earmark-pdf me-2"></i> Export to PDF
            </button>
            <button type="button" class="btn btn-outline-info" onclick="window.print()">
                <i class="bi bi-printer me-2"></i> Print
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
    // Force Excel to treat date as text to avoid ####### display
    csv += '="<?= date('Y-m-d', strtotime($payment->payment_date)) ?>",';
    csv += '"<?= addslashes($payment->name) ?>",';
    csv += '<?= $payment->reg_no ?>,';
    csv += '<?= date('Y-m', strtotime($payment->month_year . '-01')) ?>,';
    csv += '<?= $payment->amount_paid ?>,';
    csv += '<?= isset($payment->payment_mode) ? $payment->payment_mode : (isset($payment->payment_method) ? $payment->payment_method : "") ?>,';
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
    ensurePdfLibs().then(function() {
        const jsPDFRef = window.jspdf || {};
        const DocCtor = jsPDFRef.jsPDF || window.jsPDF;
        if (!DocCtor || !window.jspdf || !window.jspdf.jsPDF || !window.jspdf.jsPDF.prototype) {
            alert('PDF library failed to load.');
            return;
        }
        const doc = new DocCtor({ orientation: 'landscape', unit: 'pt', format: 'A4' });
        const title = 'Payments Export';
        doc.setFontSize(14);
        doc.text(title, 40, 30);

        const headers = [[
            'ID','Date','Student','Reg No','Month','Amount','Mode','Transaction ID','Status'
        ]];

        const rows = [
            <?php foreach ($payments as $payment): ?>
            [
                '<?= $payment->id ?>',
                '<?= date('Y-m-d', strtotime($payment->payment_date)) ?>',
                '<?= addslashes($payment->name) ?>',
                '<?= $payment->reg_no ?>',
                '<?= date('Y-m', strtotime($payment->month_year . '-01')) ?>',
                '<?= number_format($payment->amount_paid, 2) ?>',
                '<?= isset($payment->payment_mode) ? ucfirst($payment->payment_mode) : (isset($payment->payment_method) ? ucfirst($payment->payment_method) : '') ?>',
                '<?= $payment->transaction_id ?? 'N/A' ?>',
                '<?= $payment->mtb_payment_status ?? 'completed' ?>'
            ],
            <?php endforeach; ?>
        ];

        if (doc.autoTable) {
            doc.autoTable({
                head: headers,
                body: rows,
                startY: 50,
                styles: { fontSize: 9 },
                headStyles: { fillColor: [52, 152, 219] }
            });
            doc.save('payments_' + new Date().toISOString().split('T')[0] + '.pdf');
        } else if (window.jspdf && window.jspdf.plugin && window.jspdf.plugin.autoTable) {
            // Some builds attach autoTable to plugin namespace
            window.jspdf.plugin.autoTable(doc, {
                head: headers,
                body: rows,
                startY: 50
            });
            doc.save('payments_' + new Date().toISOString().split('T')[0] + '.pdf');
        } else {
            alert('PDF table plugin not available.');
        }
    }).catch(function() {
        alert('Could not load PDF libraries. Check your network connection.');
    });
}

function loadScriptOnce(src, id) {
    return new Promise(function(resolve, reject) {
        if (id && document.getElementById(id)) return resolve();
        var s = document.createElement('script');
        if (id) s.id = id;
        s.src = src;
        s.async = true;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

function ensurePdfLibs() {
    var hasJsPDF = typeof window.jspdf !== 'undefined' || typeof window.jsPDF !== 'undefined';
    var autoTableLoaded = !!(window.jspdf && window.jspdf.jsPDF && window.jspdf.jsPDF.prototype && window.jspdf.jsPDF.prototype.autoTable) || !!(window.jspdf && window.jspdf.plugin && window.jspdf.plugin.autoTable);

    var jsPdfPromise = hasJsPDF ? Promise.resolve() : loadScriptOnce('https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', 'jspdf-umd');

    return jsPdfPromise.then(function() {
        if (autoTableLoaded) return;
        // after jsPDF, load autotable
        return loadScriptOnce('https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js', 'jspdf-autotable');
    }).then(function() {
        // small delay to allow UMD to initialize
        return new Promise(function(res){ setTimeout(res, 100); });
    });
}
</script>

<!-- jsPDF and autoTable CDN for client-side PDF export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-YkC7nq2l2yT5Hq0v5k6m0e5t3B5lE2z6q9vC2v3m5y6b7z8w9x0yZ6v1n8c2kO8N9yCwzM1kZP1HkB8oQkS8NA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js" integrity="sha512-3mY0Cw6T1uX7gTt3e9gL2kQ5m6a5xqJv2qgk6+v3WmE6P3n6bS7j8R3s9nL2xAqfCqz0J1M8Gv8nG0H5m7b9Mg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<style media="print">
    .btn, .card-header, .pagination, .export-section { display: none; }
    .table { font-size: 12px; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
</style>