<div class="row mb-4">
    <div class="col-md-6">
        <h2 class="page-title" style="color: var(--primary);">
            <div class="icon-wrapper">
                <i class="bi bi-plus-circle text-primary"></i>
            </div>
            Create Student Fee
        </h2>
    </div>
    <div class="col-md-6 text-md-end">
        <a href="<?= base_url('student-fees') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Fees
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card">
            <div class="card-header-custom">
                <h4>Student Fee Information</h4>
            </div>
            <div class="card-body">
                <?= form_open('student-fees/create') ?>
                    <div class="mb-4">
                        <label class="form-label">Student Information *</label>
                        <div class="search-container">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="student_search" class="form-control search-input" placeholder="Search by student name or registration number" required>
                            <input type="hidden" name="student_id" id="student_id">
                        </div>
                        <div class="info-text">Start typing to search for students</div>
                        <?= form_error('student_id', '<small class="text-danger">', '</small>') ?>
                    </div>

                    <div class="section-divider"></div>

                    <div class="mb-4">
                        <label class="form-label">Fee Details</label>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Fee Type *</label>
                                <div class="search-container">
                                    <i class="bi bi-credit-card input-group-icon"></i>
                                    <select name="fee_id" class="form-select input-with-icon" required id="fee_type">
                                        <option value="">Select Fee Type</option>
                                        <?php foreach ($fee_types as $type): ?>
                                            <option value="<?= $type->id ?>" data-amount="<?= $type->fee_amount ?>">
                                                <?= $type->fee_name ?> - ৳<?= number_format($type->fee_amount, 2) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?= form_error('fee_id', '<small class="text-danger">', '</small>') ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Base Amount *</label>
                                <div class="search-container">
                                    <i class="bi bi-currency-dollar input-group-icon"></i>
                                    <input type="number" step="0.01" name="base_amount" class="form-control input-with-icon" id="base_amount" required>
                                </div>
                                <?= form_error('base_amount', '<small class="text-danger">', '</small>') ?>
                            </div>
                        </div>
                    </div>

                    <div class="section-divider"></div>

                    <div class="mb-4">
                        <label class="form-label">Payment Schedule</label>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Month/Year *</label>
                                <div class="search-container">
                                    <i class="bi bi-calendar input-group-icon"></i>
                                    <input type="month" name="month_year" class="form-control input-with-icon" required value="<?= date('Y-m') ?>">
                                </div>
                                <?= form_error('month_year', '<small class="text-danger">', '</small>') ?>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date *</label>
                                <div class="search-container">
                                    <i class="bi bi-calendar-check input-group-icon"></i>
                                    <input type="date" name="due_date" class="form-control input-with-icon" required value="<?= date('Y-m-20') ?>">
                                </div>
                                <?= form_error('due_date', '<small class="text-danger">', '</small>') ?>
                                <div class="info-text">Recommended: 20th of the month</div>
                            </div>
                        </div>
                    </div>

                    <div class="alert late-fee-alert mb-4">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-info-circle-fill text-warning me-2"></i>
                            <h5 class="mb-0">Late Fee Policy</h5>
                        </div>
                        <div class="ms-4">
                            <div class="mb-1"><i class="bi bi-dot"></i> ৳10 per day from 21st to 28th of the month</div>
                            <div class="mb-0"><i class="bi bi-dot"></i> ৳20 per day after the 28th of the month</div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-2"></i> Create Fee
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
            // Update the search field with the selected student's name
            $(this).val(ui.item.label);
            return false;
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