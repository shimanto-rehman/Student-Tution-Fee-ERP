<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AutoFeeHook {

    public function checkMonthlyFee()
    {
        $CI =& get_instance();
        $CI->load->model('Student_fee_model');

        $today = date('d');

        // For testing, you can change to `if (true)` temporarily
        if ($today == '01') {
            log_message('info', 'Auto Fee Insert triggered on 1st of month');
            $result = $CI->Student_fee_model->generate_monthly_fees();
            log_message('info', 'Auto Fee Insert Result: ' . json_encode($result));
        }
    }
}
