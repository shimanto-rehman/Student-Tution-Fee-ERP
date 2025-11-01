<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-receipt text-primary"></i>
            </div>
            Fee Details
        </h2>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= base_url('student-fees') ?>" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left me-2"></i> Back to List
        </a>
        <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-success">
            <i class="bi bi-credit-card me-2"></i> Make Payment
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2"></i>Student Information</h5>
            </div>
            <div class="card-body student-info-card">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?= $fee->name ?></p>
                        <p><strong>Registration No:</strong> <code><?= $fee->reg_no ?></code></p>
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
            <div class="card-header bg-info">
                <h5 class="mb-0"><i class="bi bi-receipt me-2"></i>Fee Details</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <th width="40%">Month/Year:</th>
                        <td><strong><?= date('F Y', strtotime($fee->month_year . '-01')) ?></strong></td>
                    </tr>
                    <tr>
                        <th>Due Date:</th>
                        <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                    </tr>
                    <tr>
                        <th>Base Amount:</th>
                        <td><strong>৳<?= number_format($fee->base_amount, 2) ?></strong></td>
                    </tr>
                    <tr class="<?= $fee->late_fee > 0 ? 'table-warning' : '' ?>">
                        <th>Late Fee:</th>
                        <td class="<?= $fee->late_fee > 0 ? 'text-danger fw-bold' : '' ?>">
                            ৳<?= number_format($fee->late_fee, 2) ?>
                            <?php if ($fee->late_fee > 0): ?>
                                <div class="info-text">(৳10/day till 28th, then ৳20/day)</div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr class="table-light">
                        <th>Total Amount:</th>
                        <td class="fs-5 fw-bold text-primary">৳<?= number_format($fee->total_amount, 2) ?></td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            <?php if ($fee->payment_status === 'Paid'): ?>
                                <span class="badge-paid">Paid</span>
                            <?php elseif ($fee->payment_status === 'Partial'): ?>
                                <span class="badge-partial">Partial</span>
                            <?php else: ?>
                                <span class="badge-unpaid">Unpaid</span>
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
                <div class="card-header bg-success">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Payment History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
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
                                        <td><strong>৳<?= number_format($payment->amount_paid, 2) ?></strong></td>
                                        <td><span class="badge bg-light text-dark"><?= ucfirst($payment->payment_mode) ?></span></td>
                                        <td><code><?= $payment->transaction_id ?? 'N/A' ?></code></td>
                                        <td>
                                            <?php if ($payment->mtb_payment_status === 'success'): ?>
                                                <span class="badge bg-success">Success</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-secondary">
                <h5 class="mb-0"><i class="bi bi-cash-stack me-2"></i>Payment Summary</h5>
            </div>
            <div class="card-body payment-summary-card">
                <div class="payment-summary-item">
                    <small class="text-muted">Total Amount</small>
                    <h4 class="text-primary">৳<?= number_format($fee->total_amount, 2) ?></h4>
                </div>
                <div class="payment-summary-item">
                    <small class="text-muted">Paid Amount</small>
                    <h4 class="text-success">৳<?= number_format($total_paid, 2) ?></h4>
                </div>
                <div class="payment-summary-item">
                    <small class="text-muted">Balance</small>
                    <h4 class="<?= $balance > 0 ? 'text-danger' : 'text-success' ?>">
                        ৳<?= number_format($balance, 2) ?>
                    </h4>
                </div>
                
                <?php if ($balance > 0): ?>
                    <hr>
                    <div class="payment-actions">
                        <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-success w-100">
                            <i class="bi bi-cash-coin me-2"></i> Pay Now (Manual)
                        </a>
                        <a href="<?= base_url('payments/process/' . $fee->id) ?>" class="btn btn-primary w-100">
                            <i class="bi bi-credit-card-2-front me-2"></i> Pay via MTB Gateway
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-success mb-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Fully Paid</strong>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>