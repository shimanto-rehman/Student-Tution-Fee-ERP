<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-cash-coin text-primary"></i>
            </div>
            Make Payment
        </h2>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Details
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Fee Information</h5>
            </div>
            <div class="card-body fee-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student:</strong> <?= $fee->name ?></p>
                        <p><strong>Reg No:</strong> <code><?= $fee->reg_no ?></code></p>
                        <p><strong>Month:</strong> <?= date('F Y', strtotime($fee->month_year . '-01')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Amount:</strong> <span class="fw-bold">৳<?= number_format($fee->total_amount, 2) ?></span></p>
                        <p><strong>Already Paid:</strong> <span class="text-success">৳<?= number_format($total_paid, 2) ?></span></p>
                        <p><strong>Balance:</strong> <span class="text-danger fw-bold fs-5">৳<?= number_format($balance, 2) ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary">
                <h5 class="mb-0"><i class="bi bi-credit-card me-2"></i>Payment Details</h5>
            </div>
            <div class="card-body">
                <?= form_open('payments/create/' . $fee->id) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Amount to Pay *</label>
                            <div class="payment-amount-input">
                                <input type="number" step="0.01" name="amount_paid" class="form-control" required 
                                       max="<?= $balance ?>" value="<?= $balance ?>" 
                                       placeholder="Enter payment amount">
                                <span class="amount-max" onclick="document.querySelector('input[name=\"amount_paid\"]').value = '<?= $balance ?>'">
                                    MAX
                                </span>
                            </div>
                            <div class="info-text">Maximum payable amount: ৳<?= number_format($balance, 2) ?></div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Mode *</label>
                            <select name="payment_mode" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="online" selected>Online Payment</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Method</label>
                            <input type="text" name="payment_method" class="form-control" placeholder="e.g., MTB Gateway, bKash, Nagad">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction reference">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Payment Status</label>
                            <select name="mtb_payment_status" class="form-select">
                                <option value="success" selected>Success</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Any additional notes about this payment"></textarea>
                    </div>

                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle me-2"></i>
                            <div>
                                <strong>Payment Confirmation</strong>
                                <div class="mt-1">After recording the payment, the student's fee status will be updated automatically.</div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-secondary me-3">
                            <i class="bi bi-x-circle me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-check-circle me-2"></i> Record Payment
                        </button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.querySelector('input[name="amount_paid"]');
    const maxAmount = <?= $balance ?>;
    
    amountInput.addEventListener('input', function() {
        if (parseFloat(this.value) > maxAmount) {
            this.value = maxAmount;
        }
    });
    
    // Set today's date as default if not already set
    const dateInput = document.querySelector('input[name="payment_date"]');
    if (!dateInput.value) {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
});
</script>