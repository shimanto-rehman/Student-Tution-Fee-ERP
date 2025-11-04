<?php
// application/config/routes.php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
*/

$route['default_controller'] = 'dashboard';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

/*
| -------------------------------------------------------------------------
| Custom Routes
| -------------------------------------------------------------------------
*/

// Dashboard
$route['dashboard'] = 'dashboard/index';

// Student Fees Routes
$route['student-fees'] = 'student_fees/index';
$route['student-fees/create'] = 'student_fees/create';
$route['student-fees/view/(:num)'] = 'student_fees/view/$1';
$route['student-fees/edit/(:num)'] = 'student_fees/edit/$1';
$route['student-fees/delete/(:num)'] = 'student_fees/delete/$1';
$route['student-fees/generate/(:num)'] = 'student_fees/generate_monthly_fees/$1';
$route['student-fees/generate-all'] = 'student_fees/generate_all_monthly_fees';
$route['student-fees/search_student'] = 'student_fees/search_student';

// Payments Routes
$route['payments'] = 'payments/index';
$route['payments/create/(:num)'] = 'payments/create/$1';
$route['payments/process/(:num)'] = 'payments/process/$1';
$route['payments/receipt/(:num)'] = 'payments/receipt/$1';
$route['payments/initiate-mtb-payment'] = 'payments/initiate_mtb_payment';

// API Authentication Routes (public - no JWT required)
$route['api/auth/login'] = 'api/auth/login';
$route['api/auth/validate'] = 'api/auth/validate';

// MTB API Routes (requires JWT Bearer token)
$route['api/mtb/billing-info'] = 'api/mtb_gateway/get_billing_info';
$route['api/mtb/payment-callback'] = 'api/mtb_gateway/payment_callback';
$route['api/mtb/payment-status/(:any)'] = 'api/mtb_gateway/check_payment_status/$1';

// Due Bills API Routes
$route['api/due-bills/check'] = 'api/due_bills/check';
$route['api/due-bills/get'] = 'api/due_bills/get';

// Paid Bills API Routes
$route['api/paid-bills/check'] = 'api/paid_bills/check';
$route['api/paid-bills/get'] = 'api/paid_bills/get';
// Payments API (JWT protected)
$route['api/payments/process'] = 'api/payments/process';
/*
|--------------------------------------------------------------------------
| API Routes for CronJob
|--------------------------------------------------------------------------
*/
$route['api/generate-monthly-fees'] = 'api/generate_bill/api_generate_monthly_fees';