<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Controller
 * 
 * Base Controller Class - Industry Standard Practice
 * All application controllers should extend this instead of CI_Controller
 * 
 * This provides shared functionality across all controllers
 */
class MY_Controller extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
    }
}

