<div class="row mb-4">
    <div class="col-md-6">
        <h2><i class="bi bi-receipt-cutoff"></i> Student Fees Management</h2>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?= base_url('student-fees/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Fee
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Student</th>
                        <th>Reg No</th>
                        <th>Month/Year</th>
                        <th>Due Date</th>
                        <th>Base Amount</th>
                        <th>Late Fee</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($fees)): ?>
                        <?php foreach ($fees as $fee): ?>
                            <tr>
                                <td><?= $fee->id ?></td>
                                <td><?= $fee->name ?></td>
                                <td><?= $fee->reg_no ?></td>
                                <td><?= date('M Y', strtotime($fee->month_year . '-01')) ?></td>
                                <td><?= date('d M Y', strtotime($fee->due_date)) ?></td>
                                <td>৳<?= number_format($fee->base_amount, 2) ?></td>
                                <td class="<?= $fee->late_fee > 0 ? 'text-danger fw-bold' : '' ?>">
                                    ৳<?= number_format($fee->late_fee, 2) ?>
                                </td>
                                <td>৳<?= number_format($fee->total_amount, 2) ?></td>
                                <td>
                                    <?php if ($fee->payment_status === 'Paid'): ?>
                                        <span class="badge badge-paid">Paid</span>
                                    <?php elseif ($fee->payment_status === 'Partial'): ?>
                                        <span class="badge badge-partial">Partial</span>
                                    <?php else: ?>
                                        <span class="badge badge-unpaid">Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= base_url('student-fees/view/' . $fee->id) ?>" class="btn btn-sm btn-info" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= base_url('payments/create/' . $fee->id) ?>" class="btn btn-sm btn-success" title="Make Payment">
                                        <i class="bi bi-credit-card"></i>
                                    </a>
                                    <a href="<?= base_url('student-fees/edit/' . $fee->id) ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No fees found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
