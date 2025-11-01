<?php
// application/models/Student_model.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_model extends CI_Model {
    
    private $table = 'student';
    
    public function __construct() {
        parent::__construct();
        $this->load->database();
    }
    
    // Get all active students
    public function get_all($limit = 100, $offset = 0) {
        $this->db->where('status', 1);
        $this->db->limit($limit, $offset);
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }
    
    // Get student by ID
    public function get_by_id($id) {
        $this->db->where('id', $id);
        $this->db->where('status', 1);
        return $this->db->get($this->table)->row();
    }
    
    // Get student by registration number
    public function get_by_reg_no($reg_no) {
        $this->db->where('reg_no', $reg_no);
        $this->db->where('status', 1);
        return $this->db->get($this->table)->row();
    }
    
    // Search students
    public function search($keyword) {
        $this->db->group_start();
        $this->db->like('name', $keyword);
        $this->db->or_like('reg_no', $keyword);
        $this->db->or_like('phone', $keyword);
        $this->db->group_end();
        $this->db->where('status', 1);
        $this->db->order_by('name', 'ASC');
        return $this->db->get($this->table)->result();
    }

    public function search_students($term) {
        $this->db->like('name', $term);
        $this->db->or_like('reg_no', $term);
        $this->db->limit(10);
        return $this->db->get('student')->result();
    }
    
    // Get student with unpaid fees
    public function get_with_unpaid_fees($student_id) {
        $this->db->select('s.*, sf.id as fee_id, sf.month_year, sf.due_date, sf.total_amount, sf.payment_status, sf.late_fee');
        $this->db->from('student s');
        $this->db->join('student_fees sf', 's.id = sf.student_id');
        $this->db->where('s.id', $student_id);
        $this->db->where('sf.payment_status !=', 'Paid');
        $this->db->where('sf.status', '1');
        $this->db->order_by('sf.month_year', 'ASC');
        return $this->db->get()->result();
    }
    
    // Create new student
    public function create($data) {
        $data['created'] = date('Y-m-d H:i:s');
        $data['status'] = 1;
        return $this->db->insert($this->table, $data);
    }
    
    // Update student
    public function update($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    // Soft delete student
    public function delete($id) {
        $data = array('status' => 0);
        $this->db->where('id', $id);
        return $this->db->update($this->table, $data);
    }
    
    // Get total count
    public function count_all() {
        $this->db->where('status', 1);
        return $this->db->count_all_results($this->table);
    }
}