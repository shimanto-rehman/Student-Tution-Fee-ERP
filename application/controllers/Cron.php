<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Cron Controller
 *
 * This controller handles cron job operations.
 * All methods should be executed via CLI only for security.
 *
 * Usage from command line:
 *   php index.php cron generate_monthly_fees YOUR_SECRET_KEY
 *
 * Or via URL (if needed):
 *   http://yourdomain.com/cron/generate_monthly_fees?key=YOUR_SECRET_KEY
 */
class Cron extends CI_Controller {

    /**
     * Secret key for cron job authentication
     * Change this to a strong random key in production
     */
    private $cron_secret_key = 'CHANGE_THIS_SECRET_KEY_IN_PRODUCTION';

    public function __construct() {
        parent::__construct();
        $this->load->model('Student_fee_model');
        $this->load->library('session');

        // Enable logging for all cron operations
        log_message('info', 'Cron controller accessed from ' . $this->input->ip_address());
    }

    /**
     * Generate monthly fees for all active students
     *
     * Security: Requires API key authentication
     *
     * CLI Usage:
     *   php index.php cron generate_monthly_fees YOUR_SECRET_KEY
     *
     * URL Usage (if needed):
     *   GET /cron/generate_monthly_fees?key=YOUR_SECRET_KEY
     *
     * @param string $api_key Secret key for authentication
     */
    public function generate_monthly_fees($api_key = null) {
        $start_time = microtime(true);

        // Check if running from CLI
        $is_cli = is_cli();

        // Get API key from CLI argument or GET parameter
        if ($api_key === null) {
            $api_key = $this->input->get('key');
        }

        // Validate API key
        if ($api_key !== $this->cron_secret_key) {
            $error_response = [
                'success' => false,
                'error' => 'Invalid API key',
                'message' => 'Authentication failed',
                'timestamp' => date('Y-m-d H:i:s')
            ];

            log_message('error', 'Cron authentication failed - Invalid API key provided');

            if ($is_cli) {
                echo json_encode($error_response, JSON_PRETTY_PRINT) . "\n";
                exit(1);
            } else {
                $this->output
                    ->set_status_header(401)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($error_response));
                return;
            }
        }

        log_message('info', 'Monthly fee generation cron job started');

        // Execute monthly fee generation
        try {
            $result = $this->Student_fee_model->generate_monthly_fees();

            $execution_time = round(microtime(true) - $start_time, 2);

            $response = array_merge($result, [
                'total_execution_time' => $execution_time,
                'timestamp' => date('Y-m-d H:i:s'),
                'execution_mode' => $is_cli ? 'CLI' : 'Web'
            ]);

            // Log success
            if ($result['success']) {
                log_message('info', "Cron job completed successfully: {$result['generated']} fees generated, {$result['skipped']} skipped");
            } else {
                log_message('warning', "Cron job completed with issues: {$result['message']}");
            }

            // Output response
            if ($is_cli) {
                echo "\n=== MONTHLY FEE GENERATION REPORT ===\n";
                echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
                echo "=====================================\n\n";
                exit($result['success'] ? 0 : 1);
            } else {
                $this->output
                    ->set_status_header($result['success'] ? 200 : 500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response));
            }

        } catch (Exception $e) {
            $error_response = [
                'success' => false,
                'error' => 'Exception occurred',
                'message' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s'),
                'execution_time' => round(microtime(true) - $start_time, 2)
            ];

            log_message('error', 'Cron job exception: ' . $e->getMessage());

            if ($is_cli) {
                echo json_encode($error_response, JSON_PRETTY_PRINT) . "\n";
                exit(1);
            } else {
                $this->output
                    ->set_status_header(500)
                    ->set_content_type('application/json')
                    ->set_output(json_encode($error_response));
            }
        }
    }

    /**
     * Test endpoint to verify cron setup
     *
     * Usage:
     *   php index.php cron test YOUR_SECRET_KEY
     *   GET /cron/test?key=YOUR_SECRET_KEY
     */
    public function test($api_key = null) {
        // Get API key
        if ($api_key === null) {
            $api_key = $this->input->get('key');
        }

        // Validate API key
        if ($api_key !== $this->cron_secret_key) {
            $response = [
                'success' => false,
                'message' => 'Invalid API key'
            ];
        } else {
            $response = [
                'success' => true,
                'message' => 'Cron controller is working correctly',
                'timestamp' => date('Y-m-d H:i:s'),
                'current_day' => date('d'),
                'current_month' => date('m'),
                'current_year' => date('Y'),
                'execution_mode' => is_cli() ? 'CLI' : 'Web',
                'php_version' => PHP_VERSION,
                'codeigniter_version' => CI_VERSION
            ];
        }

        if (is_cli()) {
            echo json_encode($response, JSON_PRETTY_PRINT) . "\n";
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    /**
     * Index method - shows usage instructions
     */
    public function index() {
        $instructions = [
            'controller' => 'Cron Controller',
            'description' => 'Handles automated cron job operations',
            'available_methods' => [
                'generate_monthly_fees' => [
                    'description' => 'Generate monthly fees for all active students',
                    'cli_usage' => 'php index.php cron generate_monthly_fees YOUR_SECRET_KEY',
                    'web_usage' => 'GET /cron/generate_monthly_fees?key=YOUR_SECRET_KEY',
                    'schedule' => 'Run on 1st of each month at 3:00 AM'
                ],
                'test' => [
                    'description' => 'Test cron controller setup',
                    'cli_usage' => 'php index.php cron test YOUR_SECRET_KEY',
                    'web_usage' => 'GET /cron/test?key=YOUR_SECRET_KEY'
                ]
            ],
            'security_note' => 'All methods require API key authentication. Change the secret key in production!'
        ];

        if (is_cli()) {
            echo json_encode($instructions, JSON_PRETTY_PRINT) . "\n";
        } else {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($instructions));
        }
    }
}
