<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-pencil-square"></i> Edit Student Fee</h2>
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
                <h5 class="mb-0">Current Fee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student:</strong> <?= $fee->name ?></p>
                        <p><strong>Reg No:</strong> <?= $fee->reg_no ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Fee Type:</strong> <?= $fee->fee_name ?></p>
                        <p><strong>Month/Year:</strong> <?= date('F Y', strtotime($fee->month_year . '-01')) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <?= form_open('student-fees/edit/' . $fee->id) ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Month/Year *</label>
                            <input type="month" name="month_year" class="form-control" required 
                                   value="<?= $fee->month_year ?>" readonly>
                            <small class="text-muted">Cannot be changed after creation</small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" required 
                                   value="<?= $fee->due_date ?>">
                            <?= form_error('due_date', '<small class="text-danger">', '</small>') ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base Amount *</label>
                        <input type="number" step="0.01" name="base_amount" class="form-control" required 
                               value="<?= $fee->base_amount ?>">
                        <?= form_error('base_amount', '<small class="text-danger">', '</small>') ?>
                    </div>

                    <div class="alert alert-info">
                        <strong><i class="bi bi-info-circle"></i> Current Late Fee:</strong> 
                        ৳<?= number_format($fee->late_fee, 2) ?>
                        <br><small>This will be recalculated based on the new due date when you save.</small>
                    </div>

                    <div class="alert alert-warning">
                        <strong><i class="bi bi-exclamation-triangle"></i> Warning:</strong><br>
                        <small>
                            • Changing the base amount or due date will affect the total amount<br>
                            • Late fees will be automatically recalculated<br>
                            • Changes may affect existing payment records
                        </small>
                    </div>

                    <div class="text-end">
                        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-secondary">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Fee
                        </button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>