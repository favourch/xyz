
var payMgt = angular.module('tams-app', []);



payMgt.controller('PageController', function($scope, $http){
    
    

    
    
    /**
     * Handle for Disability and disablity description
     */
    $scope.unit = 1;
    $scope.$watch('unit', function(value) {
        
        $scope.generate();
    });
    
    
    $scope.generate = function() {
        
        var dt = new Array();
        
        for(i = 0; i < $scope.unit; i++){
            dt[i] = 'we'+i;
        }
        
       $scope.dt = dt;
    };// End of disability Handle
    
    
    
    
});
        
    


