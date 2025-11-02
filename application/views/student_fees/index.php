<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-receipt-cutoff text-primary"></i>
            </div>
            Student Fees Management
        </h2>
    </div>
    <div class="col-md-6 text-md-end">
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateFeesModal">
            <i class="bi bi-calendar-check me-2"></i> Generate Monthly Fees
        </button>
        <a href="<?= base_url('student-fees/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i> Add New Fee
        </a>
    </div>
</div>

<!-- Search and Filter Section -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" action="<?= base_url('student-fees') ?>" id="filterForm">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" 
                           placeholder="Student name or Reg No" 
                           value="<?= $this->input->get('search') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        <option value="Paid" <?= $this->input->get('status') == 'Paid' ? 'selected' : '' ?>>Paid</option>
                        <option value="Partial" <?= $this->input->get('status') == 'Partial' ? 'selected' : '' ?>>Partial</option>
                        <option value="Unpaid" <?= $this->input->get('status') == 'Unpaid' ? 'selected' : '' ?>>Unpaid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Month/Year</label>
                    <input type="month" class="form-control" name="month_year" 
                           value="<?= $this->input->get('month_year') ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per Page</label>
                    <select class="form-select" name="per_page">
                        <option value="25" <?= $this->input->get('per_page') == '25' ? 'selected' : '' ?>>25</option>
                        <option value="50" <?= ($this->input->get('per_page') == '50' || !$this->input->get('per_page')) ? 'selected' : '' ?>>50</option>
                        <option value="100" <?= $this->input->get('per_page') == '100' ? 'selected' : '' ?>>100</option>
                        <option value="200" <?= $this->input->get('per_page') == '200' ? 'selected' : '' ?>>200</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2 d-md-flex">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search me-2"></i> Filter
                        </button>
                        <a href="<?= base_url('student-fees') ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-2"></i> Clear
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Active Filters Display -->
<?php 
$has_filters = $this->input->get('search') || $this->input->get('status') || $this->input->get('month_year');
if ($has_filters): 
?>
<div class="alert alert-warning alert-dismissible fade show">
    <div class="d-flex align-items-center mb-2">
        <i class="bi bi-funnel me-2"></i>
        <strong>Active Filters:</strong>
    </div>
    <ul class="mb-0 mt-2">
        <?php if ($this->input->get('search')): ?>
            <li>Search: <strong><?= htmlspecialchars($this->input->get('search')) ?></strong></li>
        <?php endif; ?>
        <?php if ($this->input->get('status')): ?>
            <li>Status: <strong><?= htmlspecialchars($this->input->get('status')) ?></strong></li>
        <?php endif; ?>
        <?php if ($this->input->get('month_year')): ?>
            <li>Month/Year: <strong><?= date('M Y', strtotime($this->input->get('month_year') . '-01')) ?></strong></li>
        <?php endif; ?>
    </ul>
    <a href="<?= base_url('student-fees') ?>" class="btn btn-sm btn-secondary mt-2">
        <i class="bi bi-x-circle me-2"></i> Clear All Filters
    </a>
</div>
<?php endif; ?>

<!-- Results Summary -->
<?php if ($total_rows > 0): ?>
<div class="alert alert-info">
    <div class="d-flex align-items-center">
        <i class="bi bi-info-circle me-2"></i>
        <div>
            Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_rows) ?> of <?= $total_rows ?> entries
            <?php if ($has_filters): ?>
                (filtered results)
            <?php endif; ?>
        </div>
    </div>
</div>
<?php elseif ($has_filters): ?>
<div class="alert alert-warning">
    <div class="d-flex align-items-center">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <div>
            No results found for your search criteria. <a href="<?= base_url('student-fees') ?>" class="alert-link">Clear filters</a> to see all records.
        </div>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Reg No</th>
                        <th>Month/Year</th>
                        <th>Due Date</th>
                        <th>Base Amount</th>
                        <th>Late Fee</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($fees)): ?>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><strong>#<?= $fee->id ?></strong></td>
                                <td><?= $fee->name ?></td>
                                <td><code><?= $fee->reg_no ?></code></td>
                                <td><?= date('M Y', strtotime($fee->month_year . '-01')) ?></td>
                                <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                                <td><strong>৳<?= number_format($fee->base_amount, 2) ?></strong></td>
                                <td class="<?= $fee->late_fee > 0 ? 'text-danger fw-bold' : '' ?>">
                                    ৳<?= number_format($fee->late_fee, 2) ?>
                                </td>
                                <td><strong class="text-primary">৳<?= number_format($fee->total_amount, 2) ?></strong></td>
                                <td>
                                    <?php if ($fee->payment_status === 'Paid'): ?>
                                        <span class="badge bg-success">Paid</span>
                                    <?php elseif ($fee->payment_status === 'Partial'): ?>
                                        <span class="badge bg-warning">Partial</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-info" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-success" title="Make Payment">
                                            <i class="bi bi-credit-card"></i>
                                        </a>
                                        <a href="<?= base_url('student-fees/edit/' . $fee->id) ?>" class="btn btn-warning" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="bi bi-receipt display-4 text-muted d-block mb-3"></i>
                                <h5 class="text-muted">No fees found</h5>
                                <p class="text-muted">Try adjusting your filters or add new fees</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_rows > $per_page): ?>
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted">
                Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_rows) ?> of <?= $total_rows ?> entries
            </div>
            <nav>
                <ul class="pagination mb-0">
                    <?php
                    $total_pages = ceil($total_rows / $per_page);
                    $current_page = floor($offset / $per_page) + 1;
                    
                    // Build query string for pagination
                    $query_params = $_GET;
                    unset($query_params['offset']);
                    $query_string = http_build_query($query_params);
                    $query_string = $query_string ? '&' . $query_string : '';
                    
                    // Previous button
                    if ($current_page > 1):
                    ?>
                        <li class="page-item">
                            <a class="page-link" href="?offset=<?= ($current_page - 2) * $per_page . $query_string ?>">
                                <i class="bi bi-chevron-left"></i> Previous
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link"><i class="bi bi-chevron-left"></i> Previous</span>
                        </li>
                    <?php endif; ?>
                    
                    <?php
                    // Page numbers with ellipsis
                    $range = 2; // Number of pages to show on each side
                    for ($i = 1; $i <= $total_pages; $i++):
                        if ($i == 1 || $i == $total_pages || abs($i - $current_page) <= $range):
                    ?>
                        <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?offset=<?= ($i - 1) * $per_page . $query_string ?>"><?= $i ?></a>
                        </li>
                    <?php
                        elseif (abs($i - $current_page) == $range + 1):
                    ?>
                        <li class="page-item disabled">
                            <span class="page-link">...</span>
                        </li>
                    <?php
                        endif;
                    endfor;
                    ?>
                    
                    <!-- Next button -->
                    <?php if ($current_page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?offset=<?= $current_page * $per_page . $query_string ?>">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="page-item disabled">
                            <span class="page-link">Next <i class="bi bi-chevron-right"></i></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Generate Monthly Fees Modal with Month/Year Selection -->
<div class="modal fade" id="generateFeesModal" tabindex="-1" aria-labelledby="generateFeesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="generateFeesModalLabel">
                    <i class="bi bi-calendar-check me-2"></i> Generate Monthly Fees
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Select month and year to generate fees</strong>
                    </div>
                </div>

                <form id="generateFeesForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="generateMonth" class="form-label">Month <span class="text-danger">*</span></label>
                            <select class="form-select" id="generateMonth" name="month" required>
                                <option value="">Select Month</option>
                                <option value="1">January</option>
                                <option value="2">February</option>
                                <option value="3">March</option>
                                <option value="4">April</option>
                                <option value="5">May</option>
                                <option value="6">June</option>
                                <option value="7">July</option>
                                <option value="8">August</option>
                                <option value="9">September</option>
                                <option value="10">October</option>
                                <option value="11">November</option>
                                <option value="12">December</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="generateYear" class="form-label">Year <span class="text-danger">*</span></label>
                            <select class="form-select" id="generateYear" name="year" required>
                                <option value="">Select Year</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="dueDay" class="form-label">Due Day of Month <span class="text-danger">*</span></label>
                        <select class="form-select" id="dueDay" name="due_day" required>
                            <option value="">Select Day</option>
                            <?php for ($d = 1; $d <= 31; $d++): ?>
                                <option value="<?= $d ?>" <?= $d == 20 ? 'selected' : '' ?>><?= $d ?></option>
                            <?php endfor; ?>
                        </select>
                        <small class="text-muted">Default: 20th of the month</small>
                    </div>

                    <div class="alert alert-warning">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Note:</strong>
                        </div>
                        <ul class="mb-0 mt-2">
                            <li>Bills will be created for students who don't have a bill for this month</li>
                            <li>Students who already have a bill will be skipped (no duplicates)</li>
                            <li>Only active students will be included</li>
                        </ul>
                    </div>

                    <div class="mt-3">
                        <strong>Are you sure you want to proceed?</strong>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmGenerateBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="generateSpinner"></span>
                    <i class="bi bi-check-circle me-2" id="generateIcon"></i>
                    <span id="generateBtnText">Yes, Generate Fees</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Populate year dropdown
document.addEventListener('DOMContentLoaded', function() {
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    const yearSelect = document.getElementById('generateYear');
    const monthSelect = document.getElementById('generateMonth');
    
    // Add years (current year - 1 to current year + 2)
    for (let year = currentYear - 1; year <= currentYear + 2; year++) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        if (year === currentYear) {
            option.selected = true;
        }
        yearSelect.appendChild(option);
    }
    
    // Set current month as default
    monthSelect.value = currentMonth;
});

// Form validation
function validateGenerateForm() {
    const month = document.getElementById('generateMonth').value;
    const year = document.getElementById('generateYear').value;
    const dueDay = document.getElementById('dueDay').value;
    
    if (!month || !year || !dueDay) {
        alert('Please select month, year, and due day');
        return false;
    }
    
    return true;
}

// Handle fee generation
document.getElementById('confirmGenerateBtn').addEventListener('click', function() {
    if (!validateGenerateForm()) {
        return;
    }
    
    const btn = this;
    const spinner = document.getElementById('generateSpinner');
    const icon = document.getElementById('generateIcon');
    const btnText = document.getElementById('generateBtnText');
    
    // Get form values
    const month = document.getElementById('generateMonth').value;
    const year = document.getElementById('generateYear').value;
    const dueDay = document.getElementById('dueDay').value;
    
    // Disable button and show loading
    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    btnText.textContent = 'Generating...';
    
    // Prepare data
    const requestData = {
        month: parseInt(month),
        year: parseInt(year),
        due_day: parseInt(dueDay)
    };
    
    // Send AJAX request
    fetch('<?= base_url('student-fees/generate-all') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateFeesModal'));
        modal.hide();
        
        // Reset form
        document.getElementById('generateFeesForm').reset();
        
        // Show success/error message
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${data.success ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                ${data.message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert alert at the top of the page
        const container = document.querySelector('.row.mb-4');
        container.parentNode.insertBefore(alertDiv, container);
        
        // Reload page after 2 seconds to show updated data
        if (data.success) {
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateFeesModal'));
        modal.hide();
        
        // Show error message
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger alert-dismissible fade show';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                An error occurred while generating fees. Please try again.
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        const container = document.querySelector('.row.mb-4');
        container.parentNode.insertBefore(alertDiv, container);
    })
    .finally(() => {
        // Reset button state
        btn.disabled = false;
        spinner.classList.add('d-none');
        icon.classList.remove('d-none');
        btnText.textContent = 'Yes, Generate Fees';
    });
});
</script>