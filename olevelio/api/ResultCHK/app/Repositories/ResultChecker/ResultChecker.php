<?php
namespace Repositories\ResultChecker;

use Repositories\ResultChecker\ResultProviderFactory;

/**
 * Result Checker Library 
 * 
 * 
 * @category   Libarary
 * @subpackage 
 * @author     Adedayo Sule-odu  <suleodu.adedayo@gmail.com>
 * @copyright  Copyright Â© 2014 Result Checker Library.
 * @version    1.0.0
 * @since      File available since Release 1.0.0
 */
class ResultChecker {
    private $http;
    
    function __construct() {
        $this->http = '';
    }
    
    
    public function getProvider($provider_name) {
        return ResultProviderFactory :: build($provider_name, $this->http);
    }
}


