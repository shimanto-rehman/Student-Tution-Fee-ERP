<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-credit-card-2-front text-primary"></i>
            </div>
            Process Payment - MTB Gateway
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
        <!-- Fee Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Payment Details</h5>
            </div>
            <div class="card-body payment-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student Name:</strong><br><?= $fee->name ?></p>
                        <p><strong>Registration No:</strong><br><code><?= $fee->reg_no ?></code></p>
                        <p><strong>Phone:</strong><br><?= $fee->phone ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fee Month:</strong><br><?= date('F Y', strtotime($fee->month_year . '-01')) ?></p>
                        <p><strong>Due Date:</strong><br><?= date('d M Y', strtotime($fee->due_date)) ?></p>
                        <p><strong>Fee Type:</strong><br><?= $fee->fee_name ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Breakdown -->
        <div class="card mb-4">
            <div class="card-header bg-info">
                <h5 class="mb-0"><i class="bi bi-calculator me-2"></i>Amount Breakdown</h5>
            </div>
            <div class="card-body">
                <table class="table mb-0">
                    <tr>
                        <td>Base Amount:</td>
                        <td class="text-end"><strong>৳<?= number_format($fee->base_amount, 2) ?></strong></td>
                    </tr>
                    <?php if ($fee->late_fee > 0): ?>
                    <tr class="text-danger">
                        <td>Late Fee:</td>
                        <td class="text-end fw-bold">৳<?= number_format($fee->late_fee, 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-light">
                        <th>Total Amount:</th>
                        <th class="text-end fs-5 text-primary">৳<?= number_format($fee->total_amount, 2) ?></th>
                    </tr>
                    <?php if ($total_paid > 0): ?>
                    <tr class="text-success">
                        <td>Already Paid:</td>
                        <td class="text-end">৳<?= number_format($total_paid, 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-warning">
                        <th>Amount to Pay:</th>
                        <th class="text-end fs-4 text-danger">৳<?= number_format($balance, 2) ?></th>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Payment Form -->
        <div class="card mb-4">
            <div class="card-header bg-success">
                <h5 class="mb-0"><i class="bi bi-bank me-2"></i>MTB Payment Gateway</h5>
            </div>
            <div class="card-body">
                <?php if ($balance <= 0): ?>
                    <div class="alert alert-success">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
                            <div>
                                <strong>Payment Complete</strong>
                                <div class="mt-1">This fee has been fully paid.</div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <form id="mtbPaymentForm" method="post" action="<?= base_url('payments/initiate-mtb-payment') ?>">
                        <input type="hidden" name="student_fee_id" value="<?= $fee->id ?>">
                        <input type="hidden" name="amount" value="<?= $balance ?>">
                        
                        <div class="mb-4">
                            <label class="form-label">Amount to Pay *</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="payment_amount" id="payment_amount" 
                                       class="form-control form-control-lg" 
                                       value="<?= $balance ?>" 
                                       max="<?= $balance ?>" 
                                       min="1" 
                                       required>
                            </div>
                            <div class="info-text">Maximum payable amount: ৳<?= number_format($balance, 2) ?></div>
                        </div>

                        <div class="alert alert-info mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-info-circle me-2"></i>
                                <strong>Payment Information</strong>
                            </div>
                            <div class="ms-4">
                                <div class="mb-1"><i class="bi bi-dot"></i> You will be redirected to MTB's secure payment gateway</div>
                                <div class="mb-1"><i class="bi bi-dot"></i> Please keep your card/mobile banking details ready</div>
                                <div class="mb-1"><i class="bi bi-dot"></i> Transaction reference will be generated automatically</div>
                                <div class="mb-0"><i class="bi bi-dot"></i> You can pay partial or full amount</div>
                            </div>
                        </div>

                        <div class="alert alert-warning mb-4">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <strong>Important Notice</strong>
                            </div>
                            <div class="ms-4">
                                <div class="mb-1"><i class="bi bi-dot"></i> Do not close the browser during payment</div>
                                <div class="mb-1"><i class="bi bi-dot"></i> Wait for payment confirmation</div>
                                <div class="mb-0"><i class="bi bi-dot"></i> Keep your transaction reference number safe</div>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="payBtn">
                                <i class="bi bi-credit-card me-2"></i> Proceed to MTB Payment Gateway
                            </button>
                            <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-outline-primary">
                                <i class="bi bi-cash-coin me-2"></i> Pay Manually Instead
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="card security-card">
            <div class="card-body">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-shield-check text-primary me-2 fs-4"></i>
                    <h6 class="card-title mb-0 text-primary">Secure Payment Gateway</h6>
                </div>
                <p class="card-text text-muted mb-0">
                    This transaction is secured by Mutual Trust Bank (MTB) Payment Gateway. 
                    Your payment information is encrypted and protected. We do not store your card details.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment_amount').addEventListener('input', function() {
    const maxAmount = <?= $balance ?>;
    const currentValue = parseFloat(this.value);
    
    if (currentValue > maxAmount) {
        this.value = maxAmount;
    }
    if (currentValue < 0) {
        this.value = 0;
    }
});

document.getElementById('mtbPaymentForm').addEventListener('submit', function(e) {
    const payBtn = document.getElementById('payBtn');
    const amount = parseFloat(document.getElementById('payment_amount').value);
    
    if (amount <= 0) {
        e.preventDefault();
        alert('Please enter a valid payment amount.');
        return;
    }
    
    if (amount > <?= $balance ?>) {
        e.preventDefault();
        alert('Payment amount cannot exceed the balance.');
        return;
    }
    
    // Show loading state
    payBtn.disabled = true;
    payBtn.innerHTML = '<span class="loading-spinner me-2"></span>Processing Payment...';
    
    // Add a small delay to show the loading state
    setTimeout(() => {
        payBtn.innerHTML = '<span class="loading-spinner me-2"></span>Redirecting to MTB Gateway...';
    }, 1000);
});

// Set focus on amount input
document.addEventListener('DOMContentLoaded', function() {
    const amountInput = document.getElementById('payment_amount');
    if (amountInput) {
        amountInput.focus();
        amountInput.select();
    }
});
</script>