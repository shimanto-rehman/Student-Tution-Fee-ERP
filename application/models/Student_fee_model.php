<?php
// application/models/Student_fee_model.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_fee_model extends CI_Model {
    
    private $table = 'student_fees';
    
    /**
     * Get all fees with filters, pagination, and search
     */
    public function get_all($limit = 50, $offset = 0, $filters = []) {
        $this->db->select('
            student_fees.*,
            student.name,
            student.reg_no,
            (student_fees.base_amount + student_fees.late_fee) as total_amount
        ');
        $this->db->from($this->table);
        $this->db->join('student', 'student.id = student_fees.student_id', 'left');
        
        // Apply filters
        $this->apply_filters($filters);
        
        // Order by most recent first
        $this->db->order_by('student_fees.bill_year', 'DESC');
        $this->db->order_by('student_fees.bill_month', 'DESC');
        $this->db->order_by('student_fees.id', 'DESC');
        
        // Apply pagination
        $this->db->limit($limit, $offset);
        
        $query = $this->db->get();
        
        // Debug: Log the query
        log_message('debug', 'Student Fees Query: ' . $this->db->last_query());
        
        return $query->result();
    }
    
    /**
     * Count all fees with filters (for pagination)
     */
    public function count_all($filters = []) {
        $this->db->from($this->table);
        $this->db->join('student', 'student.id = student_fees.student_id', 'left');
        
        // Apply filters
        $this->apply_filters($filters);
        
        $count = $this->db->count_all_results();
        
        // Debug: Log the count query
        log_message('debug', 'Student Fees Count Query: ' . $this->db->last_query());
        log_message('debug', 'Count Result: ' . $count);
        
        return $count;
    }
    
    /**
     * Apply filters to query
     */
    private function apply_filters($filters) {
        // Debug: Log filters
        log_message('debug', 'Applying filters: ' . json_encode($filters));
        
        // Search filter (student name or reg no or fee ID)
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $this->db->group_start();
            $this->db->like('student.name', $search);
            $this->db->or_like('student.reg_no', $search);
            $this->db->or_like('student_fees.id', $search);
            $this->db->group_end();
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $this->db->where('student_fees.payment_status', $filters['status']);
        }
        
        // Month/Year filter
        if (!empty($filters['month_year'])) {
            $this->db->where('student_fees.month_year', $filters['month_year']);
        }
    }
    
    /**
     * Get fee by ID with student details
     */
    public function get_by_id($id) {
        $this->db->select('
            student_fees.*,
            student.name,
            student.reg_no,
            student.phone,
            (student_fees.base_amount + student_fees.late_fee) as total_amount
        ');
        $this->db->from($this->table);
        $this->db->join('student', 'student.id = student_fees.student_id', 'left');
        $this->db->where('student_fees.id', $id);
        
        return $this->db->get()->row();
    }
    
    /**
     * Get fees by student ID
     */
    public function get_by_student($student_id, $limit = null) {
        $this->db->select('
            student_fees.*,
            (student_fees.base_amount + student_fees.late_fee) as total_amount
        ');
        $this->db->from($this->table);
        $this->db->where('student_id', $student_id);
        $this->db->order_by('bill_year', 'DESC');
        $this->db->order_by('bill_month', 'DESC');
        
        if ($limit) {
            $this->db->limit($limit);
        }
        
        return $this->db->get()->result();
    }
    
    /**
     * Create new fee record
     */
    public function create($data) {
        // Calculate total amount
        $data['late_fee'] = $data['late_fee'] ?? 0;
        
        return $this->db->insert($this->table, $data);
    }
    
    /**
     * Update fee record
     */
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    /**
     * Delete fee record
     */
    public function delete($id) {
        $this->db->where('id', $id);
        return $this->db->delete($this->table);
    }
    
    /**
     * Check if fee exists for student and month
     */
    public function fee_exists($student_id, $month_year) {
        $this->db->where('student_id', $student_id);
        $this->db->where('month_year', $month_year);
        $query = $this->db->get($this->table);
        
        return $query->num_rows() > 0;
    }
    
    /**
     * Generate monthly fees for all active students
     * @param int $month - Month number (1-12)
     * @param int $year - Year (e.g., 2024)
     * @param int $due_day - Day of month for due date (1-31)
     */
    public function generate_monthly_fees($month = null, $year = null, $due_day = 20) {
        // Use current month/year if not provided
        $month = $month ?? date('n');
        $year = $year ?? date('Y');
        
        // Format month_year as YYYY-MM
        $month_year = sprintf('%04d-%02d', $year, $month);
        
        // Calculate due date
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $due_day = min($due_day, $days_in_month); // Ensure due day doesn't exceed days in month
        $due_date = sprintf('%04d-%02d-%02d', $year, $month, $due_day);
        
        log_message('info', "Generating fees for: {$month_year}, Due Date: {$due_date}");
        
        // Get all active students
        $this->db->where('status', 1);
        $students = $this->db->get('student')->result();
        
        if (empty($students)) {
            return [
                'success' => false,
                'message' => 'No active students found',
                'generated' => 0,
                'skipped' => 0,
                'total_students' => 0
            ];
        }
        
        $generated = 0;
        $skipped = 0;
        $errors = [];
        
        foreach ($students as $student) {
            // Check if fee already exists for this month
            if ($this->fee_exists($student->id, $month_year)) {
                $skipped++;
                log_message('debug', "Skipping student ID {$student->id} - Fee already exists for {$month_year}");
                continue;
            }
            
            // Get student's default fee amount (from fees_master or student record)
            $fee_amount = isset($student->monthly_fee) && $student->monthly_fee > 0 
                ? $student->monthly_fee 
                : 1000; // Default to 1000 if not set
            
            // Create fee record
            $fee_data = [
                'student_id' => $student->id,
                'fee_id' => 1, // Default fee type, adjust as needed
                'month_year' => $month_year,
                'bill_month' => $month,
                'bill_year' => $year,
                'due_date' => $due_date,
                'base_amount' => $fee_amount,
                'late_fee' => 0,
                'payment_status' => 'Unpaid'
            ];
            
            if ($this->db->insert($this->table, $fee_data)) {
                $generated++;
                log_message('info', "Generated fee for student ID {$student->id} - Amount: {$fee_amount}");
            } else {
                $errors[] = "Failed to generate fee for student ID {$student->id}";
                log_message('error', "Failed to insert fee for student ID {$student->id}");
            }
        }
        
        $success_message = "Generated {$generated} fees for " . date('F Y', strtotime($month_year . '-01'));
        if ($skipped > 0) {
            $success_message .= ", skipped {$skipped} existing records";
        }
        
        return [
            'success' => true,
            'message' => $success_message,
            'generated' => $generated,
            'skipped' => $skipped,
            'total_students' => count($students),
            'month' => $month,
            'year' => $year,
            'errors' => $errors
        ];
    }
    
    /**
     * Calculate late fee based on due date
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
    
    /**
     * Update all late fees for unpaid/partial bills
     */
    public function update_all_late_fees() {
        $this->db->select('id, due_date');
        $this->db->where_in('payment_status', ['Unpaid', 'Partial']);
        $fees = $this->db->get($this->table)->result();
        
        foreach ($fees as $fee) {
            $late_fee = $this->calculate_late_fee($fee->due_date);
            
            if ($late_fee > 0) {
                $this->db->where('id', $fee->id);
                $this->db->update($this->table, ['late_fee' => $late_fee]);
            }
        }
    }
    
    /**
     * Get payment statistics
     */
    public function get_statistics($filters = []) {
        $this->db->select('
            COUNT(*) as total_fees,
            SUM(CASE WHEN payment_status = "Paid" THEN 1 ELSE 0 END) as paid_count,
            SUM(CASE WHEN payment_status = "Unpaid" THEN 1 ELSE 0 END) as unpaid_count,
            SUM(CASE WHEN payment_status = "Partial" THEN 1 ELSE 0 END) as partial_count,
            SUM(base_amount + late_fee) as total_amount,
            SUM(CASE WHEN payment_status = "Paid" THEN base_amount + late_fee ELSE 0 END) as paid_amount,
            SUM(CASE WHEN payment_status = "Unpaid" THEN base_amount + late_fee ELSE 0 END) as unpaid_amount
        ');
        $this->db->from($this->table);
        $this->db->join('student', 'student.id = student_fees.student_id', 'left');
        
        // Apply filters
        $this->apply_filters($filters);
        
        return $this->db->get()->row();
    }
}