<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-cash-coin"></i> Make Payment</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Fee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student:</strong> <?= $fee->name ?></p>
                        <p><strong>Reg No:</strong> <?= $fee->reg_no ?></p>
                        <p><strong>Month:</strong> <?= date('F Y', strtotime($fee->month_year . '-01')) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Total Amount:</strong> ৳<?= number_format($fee->total_amount, 2) ?></p>
                        <p><strong>Already Paid:</strong> <span class="text-success">৳<?= number_format($total_paid, 2) ?></span></p>
                        <p><strong>Balance:</strong> <span class="text-danger fw-bold">৳<?= number_format($balance, 2) ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Payment Details</h5>
            </div>
            <div class="card-body">
                <?= form_open('payments/create/' . $fee->id) ?>
                    <div class="mb-3">
                        <label class="form-label">Payment Date *</label>
                        <input type="date" name="payment_date" class="form-control" required value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount to Pay *</label>
                        <input type="number" step="0.01" name="amount_paid" class="form-control" required 
                               max="<?= $balance ?>" value="<?= $balance ?>">
                        <small class="text-muted">Maximum: ৳<?= number_format($balance, 2) ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Mode *</label>
                        <select name="payment_mode" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank">Bank Transfer</option>
                            <option value="online" selected>Online Payment</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Method</label>
                        <input type="text" name="payment_method" class="form-control" placeholder="e.g., MTB Gateway, bKash, Nagad">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="Enter transaction reference">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="2" placeholder="Any additional notes"></textarea>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle"></i> Record Payment
                        </button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>