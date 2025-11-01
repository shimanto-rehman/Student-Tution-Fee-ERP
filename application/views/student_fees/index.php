<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-receipt-cutoff"></i> Student Fees Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#generateFeesModal">
            <i class="bi bi-calendar-check"></i> Generate Monthly Fees
        </button>
        <a href="<?= base_url('student-fees/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Fee
        </a>
    </div>
</div>

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
                                <td><?= $fee->id ?></td>
                                <td><?= $fee->name ?></td>
                                <td><?= $fee->reg_no ?></td>
                                <td><?= date('M Y', strtotime($fee->month_year . '-01')) ?></td>
                                <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                                <td>৳<?= number_format($fee->base_amount, 2) ?></td>
                                <td class="<?= $fee->late_fee > 0 ? 'text-danger fw-bold' : '' ?>">
                                    ৳<?= number_format($fee->late_fee, 2) ?>
                                </td>
                                <td>৳<?= number_format($fee->total_amount, 2) ?></td>
                                <td>
                                    <?php if ($fee->payment_status === 'Paid'): ?>
                                        <span class="badge badge-paid">Paid</span>
                                    <?php elseif ($fee->payment_status === 'Partial'): ?>
                                        <span class="badge badge-partial">Partial</span>
                                    <?php else: ?>
                                        <span class="badge badge-unpaid">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-sm btn-success" title="Make Payment">
                                        <i class="bi bi-credit-card"></i>
                                    </a>
                                    <a href="<?= base_url('student-fees/edit/' . $fee->id) ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No fees found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Generate Monthly Fees Confirmation Modal -->
<div class="modal fade" id="generateFeesModal" tabindex="-1" aria-labelledby="generateFeesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="generateFeesModalLabel">
                    <i class="bi bi-calendar-check"></i> Generate Monthly Fees
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Generate fees for:</strong>
                    <span id="currentMonthYear"></span>
                </div>

                <p>This will automatically create monthly fee records for all active students.</p>

                <ul class="mb-0">
                    <li>Bills will be created for students who don't have a bill for this month</li>
                    <li>Students who already have a bill will be skipped (no duplicates)</li>
                    <li>Due date will be set to the 20th of the month</li>
                </ul>

                <div class="mt-3">
                    <strong>Are you sure you want to proceed?</strong>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmGenerateBtn">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="generateSpinner"></span>
                    <i class="bi bi-check-circle" id="generateIcon"></i>
                    <span id="generateBtnText">Yes, Generate Fees</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Set current month/year in modal
document.addEventListener('DOMContentLoaded', function() {
    const months = ['January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'];
    const now = new Date();
    const monthYear = months[now.getMonth()] + ' ' + now.getFullYear();
    document.getElementById('currentMonthYear').textContent = monthYear;
});

// Handle fee generation
document.getElementById('confirmGenerateBtn').addEventListener('click', function() {
    const btn = this;
    const spinner = document.getElementById('generateSpinner');
    const icon = document.getElementById('generateIcon');
    const btnText = document.getElementById('generateBtnText');

    // Disable button and show loading
    btn.disabled = true;
    spinner.classList.remove('d-none');
    icon.classList.add('d-none');
    btnText.textContent = 'Generating...';

    // Send AJAX request
    fetch('<?= base_url('student-fees/generate-all') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('generateFeesModal'));
        modal.hide();

        // Show success/error message
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${data.success ? 'success' : 'danger'} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            <i class="bi bi-${data.success ? 'check-circle' : 'exclamation-triangle'}"></i>
            ${data.message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert alert at the top of the page
        const container = document.querySelector('.row.mb-4');
        container.parentNode.insertBefore(alertDiv, container);

        // Reload page after 2 seconds to show updated data
        setTimeout(() => {
            window.location.reload();
        }, 2000);
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
            <i class="bi bi-exclamation-triangle"></i>
            An error occurred while generating fees. Please try again.
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
