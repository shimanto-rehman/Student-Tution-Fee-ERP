<?php
// application/models/Student_fee_model.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_fee_model extends CI_Model {
    
    protected $table = 'student_fees';
    protected $primaryKey = 'id';
    protected $allowedFields = ['student_id', 'month', 'year', 'amount', 'created_at'];
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    /**
     * IMPROVED: Generate monthly fees for all active students
     * Should be run on the 1st of every month via cron job
     * Generates bills for the CURRENT month with due date on the 20th
     *
     * @return array Detailed response with success status and statistics
     */
    public function generate_monthly_fees() {
        $start_time = microtime(true);

        // Generate for CURRENT month (running on 1st of month)
        $current_month = (int)date('m');
        $current_year = (int)date('Y');

        log_message('info', "Monthly fee generation started for {$current_year}-{$current_month}");

        // Get all active students
        $students = $this->db->where('status', 1)->get('student')->result();

        if (empty($students)) {
            log_message('warning', 'No active students found for fee generation');
            return [
                'success' => false,
                'message' => 'No active students found',
                'generated' => 0,
                'skipped' => 0,
                'total_students' => 0,
                'month' => $current_month,
                'year' => $current_year,
                'execution_time' => 0
            ];
        }

        // Get monthly fee from fees_master (query by fee_type for flexibility)
        $monthly_fee = $this->db
            ->select('id, fee_amount')
            ->where('fee_type', 'monthly')
            ->where('status', 1)
            ->get('fees_master')
            ->row();

        if (!$monthly_fee) {
            log_message('error', 'Monthly fee not configured in fees_master table');
            return [
                'success' => false,
                'message' => 'Monthly fee not configured in fees_master',
                'generated' => 0,
                'skipped' => 0,
                'total_students' => count($students),
                'month' => $current_month,
                'year' => $current_year,
                'execution_time' => 0
            ];
        }

        // Check for existing bills to avoid duplicates
        $existing_bills = $this->db
            ->select('student_id')
            ->where('bill_month', $current_month)
            ->where('bill_year', $current_year)
            ->where('fee_id', $monthly_fee->id)
            ->where('status', '1')
            ->get($this->table)
            ->result_array();

        $existing_student_ids = array_column($existing_bills, 'student_id');

        // Prepare batch data
        $batch_data = [];
        $skipped = 0;
        $current_timestamp = date('Y-m-d H:i:s');
        $due_date = sprintf('%04d-%02d-20', $current_year, $current_month); // 20th of current month
        $month_year = sprintf('%04d-%02d', $current_year, $current_month);

        foreach ($students as $student) {
            // Skip if bill already exists for this student
            if (in_array($student->id, $existing_student_ids)) {
                $skipped++;
                log_message('debug', "Skipped student ID {$student->id} - bill already exists");
                continue;
            }

            // Generate unique bill ID: YYYYMMSSSSSS format
            $bill_unique_id = $this->generate_bill_unique_id($student->id, $current_month, $current_year);

            $batch_data[] = [
                'student_id' => $student->id,
                'fee_id' => $monthly_fee->id,
                'month_year' => $month_year,
                'bill_month' => $current_month,
                'bill_year' => $current_year,
                'due_date' => $due_date,
                'payment_status' => 'Unpaid',
                'base_amount' => $monthly_fee->fee_amount,
                'late_fee' => 0.00,
                'total_amount' => $monthly_fee->fee_amount,
                'status' => '1',
                'created' => $current_timestamp,
                'bill_unique_id' => $bill_unique_id,
                'bank_transaction_id' => '',
                'comments' => 'Auto-generated monthly bill'
            ];
        }

        $generated = 0;

        // Insert in batches of 100 records at a time for performance
        if (!empty($batch_data)) {
            $batch_size = 100;
            $total_batches = ceil(count($batch_data) / $batch_size);

            log_message('info', "Inserting {count($batch_data)} bills in {$total_batches} batches");

            for ($i = 0; $i < $total_batches; $i++) {
                $batch = array_slice($batch_data, $i * $batch_size, $batch_size);

                if ($this->db->insert_batch($this->table, $batch)) {
                    $generated += count($batch);
                    log_message('debug', "Batch " . ($i + 1) . "/{$total_batches} inserted successfully");
                } else {
                    log_message('error', "Failed to insert batch " . ($i + 1) . "/{$total_batches}");
                }
            }
        }

        $execution_time = round(microtime(true) - $start_time, 2);
        $month_name = date('F', mktime(0, 0, 0, $current_month, 1));

        log_message('info', "Monthly fee generation completed: {$generated} generated, {$skipped} skipped in {$execution_time}s");

        return [
            'success' => true,
            'message' => "Successfully generated {$generated} bills for {$month_name} {$current_year}",
            'generated' => $generated,
            'skipped' => $skipped,
            'total_students' => count($students),
            'month' => $current_month,
            'year' => $current_year,
            'month_name' => $month_name,
            'due_date' => $due_date,
            'batch_size' => 100,
            'total_batches' => isset($total_batches) ? $total_batches : 0,
            'execution_time' => $execution_time
        ];
    }

    /**
     * OLD METHOD: Auto-generate monthly bills for all active students using BATCH INSERT
     * Should be run on the 30th of every month via cron job
     * Generates bills for the NEXT month
     *
     * @deprecated Use generate_monthly_fees() instead
     */

    public function autoInsertMonthlyFees()
    {
        $bill_month = date('m');
        $bill_year  = date('Y');

        // Check if already inserted for this month
        $exists = $this->db->where('bill_month', $bill_month)
                        ->where('bill_year', $bill_year)
                        ->count_all_results($this->table);

        if ($exists > 0) {
            return 'Already inserted for this month';
        }

        // ✅ Step 1: Get monthly fee info from fees_master
        $fee_info = $this->db->select('id, fee_amount')
                            ->where('fee_type', 'monthly')
                            ->where('status', 1)
                            ->get('fees_master')
                            ->row_array();

        if (empty($fee_info)) {
            return 'No monthly fee record found in fees_master.';
        }

        $fee_id      = $fee_info['id'];
        $base_amount = $fee_info['fee_amount'];

        // ✅ Step 2: Get all students
        $students = $this->db->select('id')->get('student')->result_array();

        if (empty($students)) {
            return 'No students found.';
        }

        // ✅ Step 3: Prepare data
        $data = [];
        foreach ($students as $stu) {
            $bill_unique_id = $bill_year . $bill_month . str_pad($stu['id'], 6, '0', STR_PAD_LEFT);

            $data[] = [
                'student_id'      => $stu['id'],
                'fee_id'          => $fee_id,
                'bill_month'      => $bill_month,
                'bill_year'       => $bill_year,
                'bill_unique_id'  => $bill_unique_id,
                'base_amount'     => $base_amount,
                'total_amount'    => $base_amount,
                'payment_status'  => 'unpaid',
                'due_date'        => date('Y-m-t'), // last day of current month
                'status'          => 1,
                'created'         => date('Y-m-d H:i:s')
            ];
        }

        // ✅ Step 4: Insert all at once
        $this->db->insert_batch($this->table, $data);

        return 'Inserted monthly fees for ' . count($students) . ' students.';
    }

    public function auto_generate_monthly_bills() {
        // Generate for NEXT month (since running on 30th)
        $next_month = date('Y-m', strtotime('+1 month'));
        $next_month_parts = explode('-', $next_month);
        $target_month = (int)$next_month_parts[1];
        $target_year = (int)$next_month_parts[0];
        
        // Get all active students
        $students = $this->db->where('status', 1)->get('student')->result();
        
        if (empty($students)) {
            return [
                'success' => false, 
                'message' => 'No active students found',
                'generated' => 0,
                'skipped' => 0,
                'total_students' => 0
            ];
        }
        
        // Get monthly fee from fees_master (fee_id = 2 for Monthly Fee)
        $monthly_fee = $this->db->where('id', 2)->get('fees_master')->row();
        
        if (!$monthly_fee) {
            return [
                'success' => false, 
                'message' => 'Monthly fee not configured in fees_master',
                'generated' => 0,
                'skipped' => 0,
                'total_students' => 0
            ];
        }
        
        // Get existing bills for target month to avoid duplicates
        $existing_bills = $this->db
            ->select('student_id')
            ->where('bill_month', $target_month)
            ->where('bill_year', $target_year)
            ->where('fee_id', 2)
            ->get($this->table)
            ->result_array();
        
        $existing_student_ids = array_column($existing_bills, 'student_id');
        
        // Prepare batch data
        $batch_data = [];
        $skipped = 0;
        $current_timestamp = date('Y-m-d H:i:s');
        $due_date = sprintf('%04d-%02d-20', $target_year, $target_month);
        $month_year = sprintf('%04d-%02d', $target_year, $target_month);
        
        foreach ($students as $student) {
            // Skip if bill already exists
            if (in_array($student->id, $existing_student_ids)) {
                $skipped++;
                continue;
            }
            
            // Generate unique bill ID
            $bill_unique_id = $this->generate_bill_unique_id($student->id, $target_month, $target_year);
            
            $batch_data[] = [
                'student_id' => $student->id,
                'fee_id' => 2, // Monthly fee
                'month_year' => $month_year,
                'due_date' => $due_date,
                'payment_status' => 'Unpaid',
                'base_amount' => $monthly_fee->amount,
                'late_fee' => 0.00,
                'total_amount' => $monthly_fee->amount,
                'status' => '1',
                'created' => $current_timestamp,
                'bill_month' => $target_month,
                'bill_year' => $target_year,
                'bill_unique_id' => $bill_unique_id,
                'bank_transaction_id' => '',
                'comments' => 'Auto-generated monthly bill'
            ];
        }
        
        $generated = 0;
        
        // Insert in batches of 100 records at a time
        if (!empty($batch_data)) {
            $batch_size = 100;
            $total_batches = ceil(count($batch_data) / $batch_size);
            
            for ($i = 0; $i < $total_batches; $i++) {
                $batch = array_slice($batch_data, $i * $batch_size, $batch_size);
                
                if ($this->db->insert_batch($this->table, $batch)) {
                    $generated += count($batch);
                }
            }
        }
        
        return [
            'success' => true,
            'generated' => $generated,
            'skipped' => $skipped,
            'total_students' => count($students),
            'month' => $target_month,
            'year' => $target_year,
            'month_name' => date('F', mktime(0, 0, 0, $target_month, 1)),
            'batch_size' => 100,
            'total_batches' => isset($total_batches) ? $total_batches : 0
        ];
    }
    
    /**
     * Generate unique bill ID
     * Format: YYYYMMSSSSSS (Year + Month + Student ID padded to 6 digits)
     * Example: Student ID 535 for Nov 2025 = 202511000535
     */
    private function generate_bill_unique_id($student_id, $month, $year) {
        return (int)($year . str_pad($month, 2, '0', STR_PAD_LEFT) . str_pad($student_id, 6, '0', STR_PAD_LEFT));
    }
    
    /**
     * Bulk update late fees using batch update
     */
    public function update_all_late_fees() {
        // Get all unpaid fees
        $unpaid_fees = $this->db
            ->where('payment_status !=', 'Paid')
            ->where('status', '1')
            ->get($this->table)
            ->result();
        
        if (empty($unpaid_fees)) {
            return 0;
        }
        
        $batch_update_data = [];
        $current_date = date('Y-m-d');
        
        foreach ($unpaid_fees as $fee) {
            $new_late_fee = $this->calculate_late_fee($fee->due_date);
            $new_total = $fee->base_amount + $new_late_fee;
            
            // Only update if late fee changed
            if ($new_late_fee != $fee->late_fee || $new_total != $fee->total_amount) {
                $batch_update_data[] = [
                    'id' => $fee->id,
                    'late_fee' => $new_late_fee,
                    'total_amount' => $new_total,
                    'updated' => $current_date
                ];
            }
        }
        
        $updated = 0;
        
        if (!empty($batch_update_data)) {
            // Update in batches of 100
            $batch_size = 100;
            $total_batches = ceil(count($batch_update_data) / $batch_size);
            
            for ($i = 0; $i < $total_batches; $i++) {
                $batch = array_slice($batch_update_data, $i * $batch_size, $batch_size);
                
                if ($this->db->update_batch($this->table, $batch, 'id')) {
                    $updated += count($batch);
                }
            }
        }
        
        return $updated;
    }
    
    /**
     * Calculate late fee based on due date
     * Rules:
     * - Days 1-8 after due date: 10 BDT per day
     * - Days 9+: 20 BDT per day
     */
    public function calculate_late_fee($due_date) {
        $current_date = date('Y-m-d');
        $due = new DateTime($due_date);
        $now = new DateTime($current_date);
        
        if ($now <= $due) {
            return 0;
        }
        
        $days_late = $now->diff($due)->days;
        $late_fee = 0;
        
        if ($days_late > 0) {
            // Days 1-8: 10 taka per day
            $days_10tk = min($days_late, 8);
            $late_fee += $days_10tk * 10;
            
            // Days 9+: 20 taka per day
            if ($days_late > 8) {
                $days_20tk = $days_late - 8;
                $late_fee += $days_20tk * 20;
            }
        }
        
        return $late_fee;
    }
    
    // ============= OTHER EXISTING METHODS =============
    
    public function get_bills_for_mtb($filters = []) {
        $this->db->select('
            sf.id,
            sf.student_id,
            sf.bill_unique_id,
            sf.bill_month,
            sf.bill_year,
            sf.month_year,
            sf.due_date,
            sf.base_amount,
            sf.late_fee,
            sf.total_amount,
            sf.payment_status,
            sf.bank_transaction_id,
            s.name,
            s.reg_no,
            s.phone,
            s.prt_phone,
            s.address,
            fm.fee_name
        ');
        $this->db->from($this->table . ' sf');
        $this->db->join('student s', 's.id = sf.student_id');
        $this->db->join('fees_master fm', 'fm.id = sf.fee_id');
        $this->db->where('sf.status', '1');
        
        if (!empty($filters['student_id'])) {
            $this->db->where('sf.student_id', $filters['student_id']);
        }
        if (!empty($filters['reg_no'])) {
            $this->db->where('s.reg_no', $filters['reg_no']);
        }
        if (!empty($filters['bill_month'])) {
            $this->db->where('sf.bill_month', $filters['bill_month']);
        }
        if (!empty($filters['bill_year'])) {
            $this->db->where('sf.bill_year', $filters['bill_year']);
        }
        if (!empty($filters['payment_status'])) {
            $this->db->where('sf.payment_status', $filters['payment_status']);
        }
        if (!empty($filters['bill_unique_id'])) {
            $this->db->where('sf.bill_unique_id', $filters['bill_unique_id']);
        }
        
        $this->db->order_by('sf.created', 'DESC');
        
        if (!empty($filters['limit'])) {
            $limit = (int)$filters['limit'];
            $offset = !empty($filters['offset']) ? (int)$filters['offset'] : 0;
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }
    
    public function get_bill_by_unique_id($bill_unique_id) {
        $this->db->select('sf.*, s.name, s.reg_no, s.phone, s.prt_phone, s.address, s.email, fm.fee_name');
        $this->db->from($this->table . ' sf');
        $this->db->join('student s', 's.id = sf.student_id');
        $this->db->join('fees_master fm', 'fm.id = sf.fee_id');
        $this->db->where('sf.bill_unique_id', $bill_unique_id);
        $this->db->where('sf.status', '1');
        return $this->db->get()->row();
    }
    
    public function update_payment_from_mtb($bill_unique_id, $bank_transaction_id, $payment_status = 'Paid', $comments = '') {
        $data = [
            'payment_status' => $payment_status,
            'bank_transaction_id' => $bank_transaction_id,
            'paid_date' => date('Y-m-d'),
            'comments' => $comments
        ];
        
        $this->db->where('bill_unique_id', $bill_unique_id);
        return $this->db->update($this->table, $data);
    }
    
    public function get_all($limit = 100, $offset = 0) {
        $this->db->select('sf.*, s.name, s.reg_no, s.phone, fm.fee_name');
        $this->db->from($this->table . ' sf');
        $this->db->join('student s', 's.id = sf.student_id');
        $this->db->join('fees_master fm', 'fm.id = sf.fee_id');
        $this->db->where('sf.status', '1');
        $this->db->limit($limit, $offset);
        $this->db->order_by('sf.month_year', 'DESC');
        return $this->db->get()->result();
    }
    
    public function get_by_id($id) {
        $this->db->select('sf.*, s.name, s.reg_no, s.phone, s.address, fm.fee_name');
        $this->db->from($this->table . ' sf');
        $this->db->join('student s', 's.id = sf.student_id');
        $this->db->join('fees_master fm', 'fm.id = sf.fee_id');
        $this->db->where('sf.id', $id);
        $this->db->where('sf.status', '1');
        return $this->db->get()->row();
    }
    
    public function create($data) {
        $data['created'] = date('Y-m-d H:i:s');
        $data['status'] = '1';

        if (isset($data['due_date'])) {
            $data['late_fee'] = $this->calculate_late_fee($data['due_date']);
        }



        if (!isset($data['bill_unique_id']) && isset($data['student_id'])) {
            $data['bill_unique_id'] = $this->generate_bill_unique_id(
                $data['student_id'],
                $data['bill_month'],
                $data['bill_year']
            );
        }

        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, ['status' => '0']);
    }
}
