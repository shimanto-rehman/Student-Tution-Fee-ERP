<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-receipt"></i> Fee Details</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('student-fees') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-success">
            <i class="bi bi-credit-card"></i> Make Payment
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Student Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?= $fee->name ?></p>
                        <p><strong>Registration No:</strong> <?= $fee->reg_no ?></p>
                        <p><strong>Phone:</strong> <?= $fee->phone ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Address:</strong> <?= $fee->address ?></p>
                        <p><strong>Fee Type:</strong> <?= $fee->fee_name ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Fee Details</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="40%">Month/Year:</th>
                        <td><?= date('F Y', strtotime($fee->month_year . '-01')) ?></td>
                    </tr>
                    <tr>
                        <th>Due Date:</th>
                        <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                    </tr>
                    <tr>
                        <th>Base Amount:</th>
                        <td>৳<?= number_format($fee->base_amount, 2) ?></td>
                    </tr>
                    <tr class="<?= $fee->late_fee > 0 ? 'table-warning' : '' ?>">
                        <th>Late Fee:</th>
                        <td class="<?= $fee->late_fee > 0 ? 'text-danger fw-bold' : '' ?>">
                            ৳<?= number_format($fee->late_fee, 2) ?>
                            <?php if ($fee->late_fee > 0): ?>
                                <small class="text-muted">(৳10/day till 28th, then ৳20/day)</small>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="table-light">
                        <th>Total Amount:</th>
                        <td class="fs-5 fw-bold">৳<?= number_format($fee->total_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            <?php if ($fee->payment_status === 'Paid'): ?>
                                <span class="badge badge-paid fs-6">Paid</span>
                            <?php elseif ($fee->payment_status === 'Partial'): ?>
                                <span class="badge badge-partial fs-6">Partial</span>
                            <?php else: ?>
                                <span class="badge badge-unpaid fs-6">Unpaid</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php if ($fee->paid_date): ?>
                        <tr>
                            <th>Paid Date:</th>
                            <td><?= date('d M Y', strtotime($fee->paid_date)) ?></td>
                        </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>

        <?php if (!empty($payments)): ?>
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Mode</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($payments as $payment): ?>
                                <tr>
                                    <td><?= date('d M Y', strtotime($payment->payment_date)) ?></td>
                                    <td>৳<?= number_format($payment->amount_paid, 2) ?></td>
                                    <td><?= ucfirst($payment->payment_mode) ?></td>
                                    <td><?= $payment->transaction_id ?? 'N/A' ?></td>
                                    <td>
                                        <?php if ($payment->mtb_payment_status === 'success'): ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">Total Amount</small>
                    <h4>৳<?= number_format($fee->total_amount, 2) ?></h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Paid Amount</small>
                    <h4 class="text-success">৳<?= number_format($total_paid, 2) ?></h4>
                </div>
                <div class="mb-3">
                    <small class="text-muted">Balance</small>
                    <h4 class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                        ৳<?= number_format($balance, 2) ?>
                    </h4>
                </div>
                
                <?php if ($balance > 0): ?>
                    <hr>
                    <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-success w-100 mb-2">
                        <i class="bi bi-cash-coin"></i> Pay Now (Manual)
                    </a>
                    <a href="<?= base_url('payments/process/' . $fee->id) ?>" class="btn btn-primary w-100">
                        <i class="bi bi-credit-card-2-front"></i> Pay via MTB Gateway
                    </a>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle"></i> Fully Paid
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>