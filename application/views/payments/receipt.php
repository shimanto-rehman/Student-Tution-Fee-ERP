<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-receipt"></i> Payment Receipt</h2>
    </div>
    <div class="col-md-6 text-end">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="bi bi-printer"></i> Print Receipt
        </button>
        <a href="<?= base_url('payments') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Payments
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body" id="receipt">
                <div class="text-center mb-4">
                    <h3>Pioneer Dental College</h3>
                    <p class="mb-1">Payment Receipt</p>
                    <p class="text-muted">Receipt #: <?= $payment->id ?></p>
                </div>

                <hr>

                <div class="row mb-4">
                    <div class="col-6">
                        <p><strong>Student Name:</strong><br><?= $payment->name ?></p>
                        <p><strong>Registration No:</strong><br><?= $payment->reg_no ?></p>
                        <p><strong>Phone:</strong><br><?= $payment->phone ?></p>
                    </div>
                    <div class="col-6 text-end">
                        <p><strong>Payment Date:</strong><br><?= date('d M Y', strtotime($payment->payment_date)) ?></p>
                        <p><strong>Transaction ID:</strong><br><?= $payment->transaction_id ?? 'N/A' ?></p>
                        <p><strong>Payment Mode:</strong><br><?= ucfirst($payment->payment_mode) ?></p>
                    </div>
                </div>

                <table class="table">
                    <thead class="table-light">
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
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
                            <th>Total Amount</th>
                            <th class="text-end">৳<?= number_format($payment->total_amount, 2) ?></th>
                        </tr>
                        <tr class="table-success">
                            <th>Amount Paid</th>
                            <th class="text-end">৳<?= number_format($payment->amount_paid, 2) ?></th>
                        </tr>
                    </tbody>
                </table>

                <?php if ($payment->remarks): ?>
                    <p><strong>Remarks:</strong> <?= $payment->remarks ?></p>
                <?php endif; ?>

                <div class="text-center mt-5">
                    <p class="text-muted mb-0">Thank you for your payment!</p>
                    <small class="text-muted">This is a computer-generated receipt.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style media="print">
    .navbar, .btn, footer { display: none; }
    .card { box-shadow: none; border: 1px solid #ddd; }
</style>