<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AutoFeeHook {

    public function checkMonthlyFee()
    {
        $CI =& get_instance();
        $CI->load->model('Student_fee_model');

        $today = date('d');

        // For testing, you can change to `if (true)` temporarily
        if ($today == '30') {
            $result = $CI->Student_fee_model->autoInsertMonthlyFees();
            log_message('debug', 'Auto Fee Insert: ' . $result);
        }
    }
}
