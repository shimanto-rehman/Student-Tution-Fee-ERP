<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-receipt text-primary"></i>
            </div>
            Payment Receipt
        </h2>
    </div>
    <div class="col-md-6 text-md-end">
        <button onclick="window.print()" class="btn btn-primary me-2">
            <i class="bi bi-printer me-2"></i> Print Receipt
        </button>
        <a href="<?= base_url('payments') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i> Back to Payments
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-body" id="receipt">
                <!-- Receipt Header -->
                <div class="receipt-header text-center">
                    <h3>🏥 Pioneer Dental College</h3>
                    <p class="subtitle mb-2">Official Payment Receipt</p>
                    <div class="receipt-number">
                        Receipt #: <?= $payment->id ?>
                    </div>
                </div>

                <!-- Student and Payment Information -->
                <div class="row mb-4 student-info">
                    <div class="col-md-6">
                        <p><strong>Student Name:</strong><br><?= $payment->name ?></p>
                        <p><strong>Registration No:</strong><br><code><?= $payment->reg_no ?></code></p>
                        <p><strong>Phone:</strong><br><?= $payment->phone ?></p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p><strong>Payment Date:</strong><br><?= date('d M Y', strtotime($payment->payment_date)) ?></p>
                        <p><strong>Transaction ID:</strong><br><?= $payment->transaction_id ?? 'N/A' ?></p>
                        <p><strong>Payment Mode:</strong><br><?= ucfirst($payment->payment_mode) ?></p>
                        <?php if (isset($payment->mtb_payment_status)): ?>
                            <p><strong>Status:</strong><br>
                                <?php if ($payment->mtb_payment_status === 'success'): ?>
                                    <span class="payment-status status-success">Success</span>
                                <?php elseif ($payment->mtb_payment_status === 'pending'): ?>
                                    <span class="payment-status status-pending">Pending</span>
                                <?php elseif ($payment->mtb_payment_status === 'failed'): ?>
                                    <span class="payment-status status-failed">Failed</span>
                                <?php else: ?>
                                    <span class="payment-status status-success">Completed</span>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Amount Breakdown -->
                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount (BDT)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Fee for <?= date('F Y', strtotime($payment->month_year . '-01')) ?></td>
                            <td class="text-end">৳<?= number_format($payment->base_amount, 2) ?></td>
                        </tr>
                        <?php if ($payment->late_fee > 0): ?>
                            <tr>
                                <td>Late Fee</td>
                                <td class="text-end text-danger">৳<?= number_format($payment->late_fee, 2) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="table-light">
                            <th>Total Amount Due</th>
                            <th class="text-end">৳<?= number_format($payment->total_amount, 2) ?></th>
                        </tr>
                        <tr class="table-success">
                            <th>Amount Paid</th>
                            <th class="text-end text-success">৳<?= number_format($payment->amount_paid, 2) ?></th>
                        </tr>
                    </tbody>
                </table>

                <!-- Remarks -->
                <?php if ($payment->remarks): ?>
                    <div class="remarks-box">
                        <strong>Remarks:</strong> <?= $payment->remarks ?>
                    </div>
                <?php endif; ?>

                <!-- Footer -->
                <div class="footer-note text-center">
                    <p class="thank-you">Thank you for your payment!</p>
                    <small class="generated-text">
                        This is a computer-generated receipt. No signature required.
                    </small>
                    <div class="mt-3">
                        <small class="text-muted">
                            Pioneer Dental College • <?= date('d M Y, h:i A') ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Add print functionality with better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add print button functionality
    const printButton = document.querySelector('button[onclick="window.print()"]');
    if (printButton) {
        printButton.addEventListener('click', function() {
            // Add a small delay to ensure the print dialog appears after any animations
            setTimeout(() => {
                window.print();
            }, 100);
        });
    }
    
    // Auto-format receipt for printing
    window.addEventListener('beforeprint', function() {
        document.body.classList.add('printing');
    });
    
    window.addEventListener('afterprint', function() {
        document.body.classList.remove('printing');
    });
});
</script>