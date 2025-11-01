<?php
// application/models/Payment_model.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Payment_model extends CI_Model {
    
    private $table = 'payments';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    // Get all payments
    public function get_all($limit = 100, $offset = 0) {
        $this->db->select('p.*, sf.month_year, s.name, s.reg_no');
        $this->db->from($this->table . ' p');
        $this->db->join('student_fees sf', 'sf.id = p.student_fee_id');
        $this->db->join('student s', 's.id = sf.student_id');
        $this->db->where('p.status', '1');
        $this->db->limit($limit, $offset);
        $this->db->order_by('p.created', 'DESC');
        return $this->db->get()->result();
    }
    
    // Get payment by ID
    public function get_by_id($id) {
        $this->db->select('p.*, sf.month_year, sf.base_amount, sf.late_fee, sf.total_amount, s.name, s.reg_no, s.phone, s.address');
        $this->db->from($this->table . ' p');
        $this->db->join('student_fees sf', 'sf.id = p.student_fee_id');
        $this->db->join('student s', 's.id = sf.student_id');
        $this->db->where('p.id', $id);
        $this->db->where('p.status', '1');
        return $this->db->get()->row();
    }
    
    // Get payments by student fee ID
    public function get_by_student_fee($student_fee_id) {
        $this->db->where('student_fee_id', $student_fee_id);
        $this->db->where('status', '1');
        $this->db->order_by('payment_date', 'DESC');
        return $this->db->get($this->table)->result();
    }
    
    // Get total paid amount for a student fee
    public function get_total_paid_for_fee($student_fee_id) {
        $this->db->select_sum('amount_paid');
        $this->db->where('student_fee_id', $student_fee_id);
        $this->db->where('status', '1');
        $this->db->where('mtb_payment_status', 'success');
        $result = $this->db->get($this->table)->row();
        return $result->amount_paid ?? 0;
    }
    
    // Create payment record
    public function create($data) {
        $data['created'] = date('Y-m-d H:i:s');
        $data['status'] = '1';
        
        $this->db->trans_start();
        
        // Insert payment
        $this->db->insert($this->table, $data);
        $payment_id = $this->db->insert_id();
        
        // Update student fee status
        if ($payment_id && isset($data['student_fee_id'])) {
            $this->update_student_fee_status($data['student_fee_id']);
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status() ? $payment_id : false;
    }
    
    // Update payment record
    public function update($id, $data) {
        $data['updated'] = date('Y-m-d H:i:s');
        
        $this->db->trans_start();
        
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        
        // Get student_fee_id to update status
        $payment = $this->db->where('id', $id)->get($this->table)->row();
        if ($payment) {
            $this->update_student_fee_status($payment->student_fee_id);
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    // Update student fee payment status
    private function update_student_fee_status($student_fee_id) {
        // Get student fee details
        $fee = $this->db->where('id', $student_fee_id)->get('student_fees')->row();
        
        if (!$fee) return false;
        
        // Get total paid amount
        $total_paid = $this->get_total_paid_for_fee($student_fee_id);
        
        // Determine payment status
        $payment_status = 'Unpaid';
        $paid_date = null;
        
        if ($total_paid >= $fee->total_amount) {
            $payment_status = 'Paid';
            $paid_date = date('Y-m-d');
        } elseif ($total_paid > 0) {
            $payment_status = 'Partial';
        }
        
        // Update student_fees table
        $update_data = array(
            'payment_status' => $payment_status,
            'paid_date' => $paid_date
        );
        
        $this->db->where('id', $student_fee_id);
        return $this->db->update('student_fees', $update_data);
    }
    
    // Get payment by MTB transaction reference
    public function get_by_mtb_ref($mtb_ref) {
        $this->db->where('mtb_transaction_ref', $mtb_ref);
        return $this->db->get($this->table)->row();
    }
    
    // Update MTB payment status
    public function update_mtb_status($payment_id, $status, $response = null) {
        $data = array(
            'mtb_payment_status' => $status,
            'updated' => date('Y-m-d H:i:s')
        );
        
        if ($response) {
            $data['mtb_response'] = json_encode($response);
        }
        
        return $this->update($payment_id, $data);
    }
    
    // Get payments by student ID
    public function get_by_student($student_id) {
        $this->db->select('p.*, sf.month_year, sf.base_amount, sf.late_fee, sf.total_amount');
        $this->db->from($this->table . ' p');
        $this->db->join('student_fees sf', 'sf.id = p.student_fee_id');
        $this->db->where('sf.student_id', $student_id);
        $this->db->where('p.status', '1');
        $this->db->order_by('p.payment_date', 'DESC');
        return $this->db->get()->result();
    }
    
    // Delete payment (soft delete)
    public function delete($id) {
        $this->db->trans_start();
        
        // Get payment details
        $payment = $this->db->where('id', $id)->get($this->table)->row();
        
        // Soft delete payment
        $this->db->where('id', $id);
        $this->db->update($this->table, ['status' => '0']);
        
        // Update student fee status
        if ($payment) {
            $this->update_student_fee_status($payment->student_fee_id);
        }
        
        $this->db->trans_complete();
        
        return $this->db->trans_status();
    }
    
    // Generate transaction ID
    public function generate_transaction_id() {
        return 'TXN' . date('YmdHis') . rand(1000, 9999);
    }
}