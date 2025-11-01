<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\Student_fee_model;

class BaseController extends Controller
{
    protected $helpers = ['url', 'form'];

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        // Run the monthly auto fee insert only on the 30th
        $this->autoInsertFeesIfNeeded();
    }

    private function autoInsertFeesIfNeeded()
    {
        $today = date('d');
        log_message('debug', "Auto fee check triggered on day: $today");

        if ($today == '30') {
            log_message('debug', 'It is the 30th — running autoInsertMonthlyFees...');
            $feeModel = new \App\Models\Student_fee_model();
            $result = $feeModel->autoInsertMonthlyFees();
            log_message('debug', "Result: $result");
        }
    }

}
