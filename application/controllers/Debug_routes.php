<?php
/**
 * CREATE THIS FILE: application/controllers/Debug_routes.php
 * Access it at: http://localhost/pioneer-dental/debug_routes
 * This will help diagnose your routing issues
 */

defined('BASEPATH') OR exit('No direct script access allowed');

class Debug_routes extends CI_Controller {
    
    public function index() {
        // Check if we're in development mode
        if (ENVIRONMENT !== 'development') {
            show_404();
            return;
        }
        
        echo '<html><head><title>Route Debugger</title>';
        echo '<style>
            body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
            .section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            h2 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
            .success { color: #28a745; font-weight: bold; }
            .error { color: #dc3545; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
            pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background: #007bff; color: white; }
        </style></head><body>';
        
        echo '<h1>🔍 Route & Configuration Debugger</h1>';
        
        // 1. Check Base URL
        echo '<div class="section">';
        echo '<h2>1. Base URL Configuration</h2>';
        $base_url = base_url();
        echo '<p><strong>Current Base URL:</strong> <code>' . $base_url . '</code></p>';
        
        if (strpos($base_url, 'localhost') !== false || strpos($base_url, '127.0.0.1') !== false) {
            echo '<p class="success">✓ Local development environment detected</p>';
        }
        
        echo '<p><strong>Recommendation:</strong> Make sure this matches your actual URL</p>';
        echo '<pre>$config[\'base_url\'] = \'http://localhost/pioneer-dental/\';</pre>';
        echo '</div>';
        
        // 2. Check Controllers
        echo '<div class="section">';
        echo '<h2>2. Controller Files Check</h2>';
        
        $controllers_to_check = [
            'Payments' => APPPATH . 'controllers/Payments.php',
            'Student_fees' => APPPATH . 'controllers/Student_fees.php',
            'Dashboard' => APPPATH . 'controllers/Dashboard.php',
            'Mtb_gateway' => APPPATH . 'controllers/api/Mtb_gateway.php'
        ];
        
        echo '<table>';
        echo '<tr><th>Controller</th><th>Path</th><th>Status</th></tr>';
        
        foreach ($controllers_to_check as $name => $path) {
            $exists = file_exists($path);
            $status = $exists ? '<span class="success">✓ Exists</span>' : '<span class="error">✗ Missing</span>';
            echo "<tr><td>$name</td><td><code>$path</code></td><td>$status</td></tr>";
        }
        
        echo '</table>';
        echo '</div>';
        
        // 3. Check Models
        echo '<div class="section">';
        echo '<h2>3. Model Files Check</h2>';
        
        $models_to_check = [
            'Student_model' => APPPATH . 'models/Student_model.php',
            'Student_fee_model' => APPPATH . 'models/Student_fee_model.php',
            'Payment_model' => APPPATH . 'models/Payment_model.php'
        ];
        
        echo '<table>';
        echo '<tr><th>Model</th><th>Path</th><th>Status</th></tr>';
        
        foreach ($models_to_check as $name => $path) {
            $exists = file_exists($path);
            $status = $exists ? '<span class="success">✓ Exists</span>' : '<span class="error">✗ Missing</span>';
            echo "<tr><td>$name</td><td><code>$path</code></td><td>$status</td></tr>";
        }
        
        echo '</table>';
        echo '</div>';
        
        // 4. Check Views
        echo '<div class="section">';
        echo '<h2>4. View Files Check</h2>';
        
        $views_to_check = [
            'payments/index' => APPPATH . 'views/payments/index.php',
            'payments/create' => APPPATH . 'views/payments/create.php',
            'payments/receipt' => APPPATH . 'views/payments/receipt.php',
            'student_fees/index' => APPPATH . 'views/student_fees/index.php',
            'student_fees/view' => APPPATH . 'views/student_fees/view.php',
            'dashboard/index' => APPPATH . 'views/dashboard/index.php',
            'templates/header' => APPPATH . 'views/templates/header.php',
            'templates/footer' => APPPATH . 'views/templates/footer.php'
        ];
        
        echo '<table>';
        echo '<tr><th>View</th><th>Path</th><th>Status</th></tr>';
        
        foreach ($views_to_check as $name => $path) {
            $exists = file_exists($path);
            $status = $exists ? '<span class="success">✓ Exists</span>' : '<span class="error">✗ Missing</span>';
            echo "<tr><td>$name</td><td><code>$path</code></td><td>$status</td></tr>";
        }
        
        echo '</table>';
        echo '</div>';
        
        // 5. Check Routes
        echo '<div class="section">';
        echo '<h2>5. Current Routes Configuration</h2>';
        
        $routes_file = APPPATH . 'config/routes.php';
        if (file_exists($routes_file)) {
            echo '<p class="success">✓ Routes file exists</p>';
            echo '<p><strong>File location:</strong> <code>' . $routes_file . '</code></p>';
            
            // Try to display routes
            echo '<h3>Defined Routes:</h3>';
            echo '<pre>';
            include $routes_file;
            
            // Show the routes array
            if (isset($route)) {
                foreach ($route as $pattern => $destination) {
                    echo htmlspecialchars("'$pattern' => '$destination'") . "\n";
                }
            }
            echo '</pre>';
        } else {
            echo '<p class="error">✗ Routes file not found</p>';
        }
        echo '</div>';
        
        // 6. Test URLs
        echo '<div class="section">';
        echo '<h2>6. Test Your URLs</h2>';
        echo '<p>Click these links to test if they work:</p>';
        echo '<ul>';
        echo '<li><a href="' . base_url() . '" target="_blank">Dashboard (Home)</a></li>';
        echo '<li><a href="' . base_url('student-fees') . '" target="_blank">Student Fees</a></li>';
        echo '<li><a href="' . base_url('payments') . '" target="_blank">Payments Index</a></li>';
        echo '<li><a href="' . base_url('payments/create/1') . '" target="_blank">Payment Create (ID: 1)</a></li>';
        echo '<li><a href="' . base_url('api/mtb/billing-info') . '" target="_blank">MTB API</a></li>';
        echo '</ul>';
        echo '</div>';
        
        // 7. .htaccess check
        echo '<div class="section">';
        echo '<h2>7. .htaccess Configuration</h2>';
        
        $htaccess_path = FCPATH . '.htaccess';
        if (file_exists($htaccess_path)) {
            echo '<p class="success">✓ .htaccess file exists</p>';
            echo '<h3>Current .htaccess content:</h3>';
            echo '<pre>' . htmlspecialchars(file_get_contents($htaccess_path)) . '</pre>';
        } else {
            echo '<p class="error">✗ .htaccess file not found</p>';
            echo '<p>Create this file in your root directory:</p>';
            echo '<pre>';
            echo htmlspecialchars('RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]');
            echo '</pre>';
        }
        echo '</div>';
        
        // 8. PHP Info
        echo '<div class="section">';
        echo '<h2>8. PHP & Server Information</h2>';
        echo '<table>';
        echo '<tr><td><strong>PHP Version:</strong></td><td>' . phpversion() . '</td></tr>';
        echo '<tr><td><strong>Server Software:</strong></td><td>' . $_SERVER['SERVER_SOFTWARE'] . '</td></tr>';
        echo '<tr><td><strong>Document Root:</strong></td><td>' . $_SERVER['DOCUMENT_ROOT'] . '</td></tr>';
        echo '<tr><td><strong>Script Filename:</strong></td><td>' . $_SERVER['SCRIPT_FILENAME'] . '</td></tr>';
        echo '<tr><td><strong>mod_rewrite:</strong></td><td>' . (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules()) ? '<span class="success">Enabled</span>' : '<span class="warning">Cannot detect (might still be enabled)</span>') . '</td></tr>';
        echo '</table>';
        echo '</div>';
        
        // 9. Quick Fixes
        echo '<div class="section">';
        echo '<h2>9. Common Solutions</h2>';
        echo '<h3>If you get 404 errors, try these:</h3>';
        echo '<ol>';
        echo '<li><strong>Check Base URL:</strong> Make sure <code>config/config.php</code> has correct base_url</li>';
        echo '<li><strong>Enable mod_rewrite:</strong> Ensure Apache mod_rewrite module is enabled</li>';
        echo '<li><strong>Check .htaccess:</strong> Make sure .htaccess file is in root directory</li>';
        echo '<li><strong>File permissions:</strong> Ensure proper read permissions on all files</li>';
        echo '<li><strong>Clear cache:</strong> Delete <code>application/cache/</code> contents</li>';
        echo '<li><strong>Check routes order:</strong> Specific routes should come before generic ones</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</body></html>';
    }
}