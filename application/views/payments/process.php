<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-credit-card-2-front"></i> Process Payment - MTB Gateway</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <!-- Fee Information -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-info-circle"></i> Payment Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student Name:</strong><br><?= $fee->name ?></p>
                        <p><strong>Registration No:</strong><br><?= $fee->reg_no ?></p>
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
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-calculator"></i> Amount Breakdown</h5>
            </div>
            <div class="card-body">
                <table class="table mb-0">
                    <tr>
                        <td>Base Amount:</td>
                        <td class="text-end">৳<?= number_format($fee->base_amount, 2) ?></td>
                    </tr>
                    <?php if ($fee->late_fee > 0): ?>
                    <tr class="text-danger">
                        <td>Late Fee:</td>
                        <td class="text-end fw-bold">৳<?= number_format($fee->late_fee, 2) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-light">
                        <th>Total Amount:</th>
                        <th class="text-end fs-5">৳<?= number_format($fee->total_amount, 2) ?></th>
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
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-bank"></i> MTB Payment Gateway</h5>
            </div>
            <div class="card-body">
                <?php if ($balance <= 0): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> This fee has been fully paid.
                    </div>
                <?php else: ?>
                    <form id="mtbPaymentForm" method="post" action="<?= base_url('payments/initiate-mtb-payment') ?>">
                        <input type="hidden" name="student_fee_id" value="<?= $fee->id ?>">
                        <input type="hidden" name="amount" value="<?= $balance ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Amount to Pay *</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" name="payment_amount" id="payment_amount" 
                                       class="form-control form-control-lg" 
                                       value="<?= $balance ?>" 
                                       max="<?= $balance ?>" 
                                       min="1" 
                                       required>
                            </div>
                            <small class="text-muted">Maximum payable: ৳<?= number_format($balance, 2) ?></small>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Payment Information:</h6>
                            <ul class="mb-0">
                                <li>You will be redirected to MTB's secure payment gateway</li>
                                <li>Please keep your card/mobile banking details ready</li>
                                <li>Transaction reference will be generated automatically</li>
                                <li>You can pay partial or full amount</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <strong><i class="bi bi-exclamation-triangle"></i> Important:</strong>
                            <ul class="mb-0">
                                <li>Do not close the browser during payment</li>
                                <li>Wait for payment confirmation</li>
                                <li>Keep your transaction reference number safe</li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg" id="payBtn">
                                <i class="bi bi-credit-card"></i> Proceed to MTB Payment Gateway
                            </button>
                            <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-outline-primary">
                                <i class="bi bi-cash-coin"></i> Pay Manually Instead
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="card border-primary">
            <div class="card-body">
                <h6 class="card-title"><i class="bi bi-shield-check"></i> Secure Payment</h6>
                <p class="card-text text-muted mb-0">
                    <small>
                        This transaction is secured by Mutual Trust Bank (MTB) Payment Gateway. 
                        Your payment information is encrypted and protected. We do not store your card details.
                    </small>
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('payment_amount').addEventListener('input', function() {
    const maxAmount = <?= $balance ?>;
    if (parseFloat(this.value) > maxAmount) {
        this.value = maxAmount;
    }
    if (parseFloat(this.value) < 0) {
        this.value = 0;
    }
});

document.getElementById('mtbPaymentForm').addEventListener('submit', function(e) {
    const payBtn = document.getElementById('payBtn');
    payBtn.disabled = true;
    payBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';
});
</script>