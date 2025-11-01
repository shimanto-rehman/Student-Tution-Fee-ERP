<?php
// application/controllers/Dashboard.php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model(['Student_model', 'Student_fee_model', 'Payment_model']);
        $this->load->helper('url');
        $this->load->library('session');
    }
    
    public function index() {
        $data['page_title'] = 'Dashboard - Pioneer Dental College';
        
        // Update all late fees before showing statistics
        $this->Student_fee_model->update_all_late_fees();
        
        // Get statistics
        // Student table uses status=1 for active records
        $this->db->where('status', 1);
        $data['total_students'] = $this->db->count_all_results('student');
        
        // Student_fees uses status='0' for active records
        $this->db->where('status', '0');
        $data['total_fees'] = $this->db->count_all_results('student_fees');
        
        // Count unpaid, paid, and partial fees
        // Note: student_fees uses status='0' for active records
        $this->db->where('payment_status', 'Unpaid');
        $this->db->where('status', '0');
        $data['unpaid_fees'] = $this->db->count_all_results('student_fees');
        
        $this->db->where('payment_status', 'Paid');
        $this->db->where('status', '0');
        $data['paid_fees'] = $this->db->count_all_results('student_fees');
        
        $this->db->where('payment_status', 'Partial');
        $this->db->where('status', '0');
        $data['partial_fees'] = $this->db->count_all_results('student_fees');
        
        // Total revenue (all paid amounts)
        $revenue_query = $this->db->select_sum('amount_paid')
                                  ->where('status', '1')
                                  ->get('payments');
        $data['total_revenue'] = $revenue_query->row()->amount_paid ?? 0;
        
        // Outstanding amount (unpaid + partial)
        // Note: student_fees uses status='0' for active records
        $this->db->select('SUM(total_amount) as outstanding');
        $this->db->where_in('payment_status', ['Unpaid', 'Partial']);
        $this->db->where('status', '0');
        $outstanding_query = $this->db->get('student_fees');
        $total_outstanding = $outstanding_query->row()->outstanding ?? 0;
        
        // Subtract already paid amounts for partial payments
        $partial_paid_query = $this->db->select('SUM(p.amount_paid) as partial_paid')
                                       ->from('payments p')
                                       ->join('student_fees sf', 'p.student_fee_id = sf.id')
                                       ->where('sf.payment_status', 'Partial')
                                       ->where('p.status', '1')
                                       ->get();
        $partial_paid = $partial_paid_query->row()->partial_paid ?? 0;
        
        $data['outstanding_amount'] = $total_outstanding - $partial_paid;
        
        // Recent fees (last 10)
        $data['recent_fees'] = $this->Student_fee_model->get_all(10, 0);
        
        // Recent payments (last 10)
        $data['recent_payments'] = $this->db->select('p.*, sf.month_year, s.name, s.reg_no')
                                            ->from('payments p')
                                            ->join('student_fees sf', 'p.student_fee_id = sf.id')
                                            ->join('student s', 'sf.student_id = s.id')
                                            ->where('p.status', '1')
                                            ->order_by('p.created', 'DESC')
                                            ->limit(10)
                                            ->get()
                                            ->result();
        
        // Monthly collection data (last 6 months)
        $data['monthly_collections'] = $this->get_monthly_collections(6);
        
        // Overdue fees count
        // Note: student_fees uses status='0' for active records
        $this->db->where('payment_status !=', 'Paid');
        $this->db->where('due_date <', date('Y-m-d'));
        $this->db->where('status', '0');
        $data['overdue_fees'] = $this->db->count_all_results('student_fees');
        
        // Load views
        $this->load->view('templates/header', $data);
        $this->load->view('dashboard/index', $data);
        $this->load->view('templates/footer');
    }
    
    private function get_monthly_collections($months = 6) {
        $collections = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $month_date = date('Y-m', strtotime("-$i months"));
            $month_name = date('M Y', strtotime("-$i months"));
            
            $query = $this->db->select_sum('amount_paid')
                              ->where("DATE_FORMAT(payment_date, '%Y-%m') = '$month_date'")
                              ->where('status', '1')
                              ->get('payments');
            
            $collections[] = [
                'month' => $month_name,
                'amount' => $query->row()->amount_paid ?? 0
            ];
        }
        
        return $collections;
    }
}