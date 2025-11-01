<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-plus-circle"></i> Create Student Fee</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('student-fees') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
</div>

<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-body">
                <?= form_open('student-fees/create') ?>
                    <div class="mb-3">
                        <label class="form-label">Student *</label>
                            <input type="text" id="student_search" class="form-control" placeholder="Search student by name or reg. no" required>
                            <input type="hidden" name="student_id" id="student_id">
                        <?= form_error('student_id', '<small class="text-danger">', '</small>') ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Fee Type *</label>
                        <select name="fee_id" class="form-select" required id="fee_type">
                            <option value="">Select Fee Type</option>
                            <?php foreach ($fee_types as $type): ?>
                                <option value="<?= $type->id ?>" data-amount="<?= $type->fee_amount ?>">
                                    <?= $type->fee_name ?> - ৳<?= number_format($type->fee_amount, 2) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?= form_error('fee_id', '<small class="text-danger">', '</small>') ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Month/Year *</label>
                            <input type="month" name="month_year" class="form-control" required value="<?= date('Y-m') ?>">
                            <?= form_error('month_year', '<small class="text-danger">', '</small>') ?>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Due Date *</label>
                            <input type="date" name="due_date" class="form-control" required value="<?= date('Y-m-20') ?>">
                            <?= form_error('due_date', '<small class="text-danger">', '</small>') ?>
                            <small class="text-muted">Recommended: 20th of the month</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base Amount *</label>
                        <input type="number" step="0.01" name="base_amount" class="form-control" id="base_amount" required>
                        <?= form_error('base_amount', '<small class="text-danger">', '</small>') ?>
                    </div>

                    <div class="alert late-fee-alert">
                        <strong><i class="bi bi-info-circle"></i> Late Fee Policy:</strong><br>
                        <small>
                            • ৳10 per day from 21st to 28th of the month<br>
                            • ৳20 per day after the 28th of the month
                        </small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Create Fee
                        </button>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<!-- jQuery & jQuery UI (if not already included) -->
<link rel="stylesheet" href="<?= base_url('assets/jquery-ui.css') ?>">
<script src="<?= base_url('assets/jquery-3.6.0.min.js') ?>"></script>
<script src="<?= base_url('assets/jquery-ui.min.js') ?>"></script>

<script>
$(function() {
    // Custom autocomplete with "show all" when focused
    $('#student_search').autocomplete({
        source: function(request, response) {
            $.getJSON('<?= base_url('student-fees/search_student') ?>', {
                term: request.term || '' // if blank, return all
            }, function(data) {
                response(data);
            });
        },
        minLength: 0, // allow empty search
        select: function(event, ui) {
            $('#student_id').val(ui.item.id);
        }
    }).focus(function() {
        // Trigger search on focus to show all results
        $(this).autocomplete('search', '');
    });
});
</script>


<script>
document.getElementById('fee_type').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const amount = selectedOption.getAttribute('data-amount');
    if (amount) {
        document.getElementById('base_amount').value = amount;
    }
});
</script>