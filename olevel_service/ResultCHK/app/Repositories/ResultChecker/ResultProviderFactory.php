<?php
namespace Repositories\ResultChecker;


/**
 * Description of ResultFactory
 *
 * @author Suleodu
 */
class ResultProviderFactory {
    
    public static function build($provider_name, $http) {
        $resultProvider = '\Repositories\Providers\\'.ucwords(strtolower($provider_name)). 'Provider\\' . ucwords(strtolower($provider_name)). "ResultProvider";
        
    try {
            return new $resultProvider($http);
        } catch (Exception $exc) {
            return $exc->getTraceAsString();
            
        }
    }
}
