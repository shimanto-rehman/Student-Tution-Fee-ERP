<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Student_fee_model;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Run the monthly auto fee insert only on the 1st
        $this->autoInsertFeesIfNeeded();
    }

    private function autoInsertFeesIfNeeded()
    {
        $today = date('d');
        log_message('debug', "Auto fee check triggered on day: $today");

        if ($today == '01') {
            log_message('info', 'It is the 1st — running generate_monthly_fees...');
            $feeModel = new \App\Models\Student_fee_model();
            $result = $feeModel->generate_monthly_fees();
            log_message('info', "Monthly fee generation result: " . json_encode($result));
        }
    }

}
