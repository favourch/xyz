var disc = angular.module('disciplinary', []);


disc.controller('pageCtrl', function ($scope, $http) {
    
    $scope.session = session;
    $scope.disciplinary = disciplinary;
    $scope.loading = false;
    $scope.student = '';
    $scope.selecetedItem = '';
    
    $scope.getStudent = function(seed){
        $scope.loading = true; 
        $http({
                method : "POST",
                url : "api/index.php?action=students",
                data: seed, 
            }).then(function mySucces(response) {
                $scope.student = response.data;
                $scope.loading = false; 
            }, function myError(response) {
                $scope.student = response.statusText;
                
            });
        
    };
    
    $scope.getToPaySession = function(from, to){
        $scope.loading = true;
        $scope.sessions = '';
        $http({
            method: "GET",
            url: "api/index.php?action=session_to_pay&from="+from+"&to="+to
           
        }).then(function mySucces(response) {
            $scope.sessions = response.data;
            $scope.loading = false;
        }, function myError(response) {
            $scope.session = response.statusText;
            $scope.loading = false;
        });
    }
    
    $scope.setSelectedItem = function(item){
        $scope.sessions = '';
       $scope.selecetedItem = item; 
       
    }

});
