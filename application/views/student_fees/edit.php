<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-pencil-square text-primary"></i>
            </div>
            Edit Student Fee
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
        <div class="card mb-4 current-info-card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Current Fee Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Student:</strong> <?= $fee->name ?></p>
                        <p><strong>Reg No:</strong> <code><?= $fee->reg_no ?></code></p>
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
                            <div class="info-text">Cannot be changed after creation</div>
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

                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Current Late Fee Information</strong>
                        </div>
                        <div class="ms-4">
                            <div class="mb-2">
                                <strong>Current Late Fee:</strong> 
                                <span class="text-danger fw-bold">৳<?= number_format($fee->late_fee, 2) ?></span>
                            </div>
                            <small class="text-muted">This will be recalculated based on the new due date when you save.</small>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Important Notice</strong>
                        </div>
                        <div class="ms-4">
                            <div class="mb-1"><i class="bi bi-dot"></i> Changing the base amount or due date will affect the total amount</div>
                            <div class="mb-1"><i class="bi bi-dot"></i> Late fees will be automatically recalculated</div>
                            <div class="mb-0"><i class="bi bi-dot"></i> Changes may affect existing payment records</div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i> Update Fee
                        </button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>