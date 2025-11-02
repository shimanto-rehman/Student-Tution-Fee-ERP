<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AutoFeeHook {

    public function checkMonthlyFee()
    {
        $CI =& get_instance();
        $CI->load->model('Student_fee_model');

        $today = date('d');

        // For testing, you can change to `if (true)` temporarily
/*         if ($today == '02') {
            log_message('info', 'Auto Fee Insert triggered on 1st of month');
            $result = $CI->Student_fee_model->generate_monthly_fees();
            log_message('info', 'Auto Fee Insert Result: ' . json_encode($result));

            if ($result['success']) {
                log_message('info', "Hook: Generated {$result['generated']} fees, skipped {$result['skipped']}");
            } else {
                log_message('error', "Hook: Failed to generate fees - {$result['message']}");
            }
        } */
    }
}
