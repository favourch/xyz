var research = angular.module('research', []);


research.controller('ResearchController', function ($scope) {
    $scope.research = res;
    $scope.research_area = res_areas;
    $scope.pub_type = pub_type;
console.log(res);
    $scope.selectedItem = [];



    $scope.setSelected = function (val) {
        $scope.selectedItem = val;  
    };
    
    
    $scope.num_author = 1;
    
    $scope.$watch('num_author', function(val){
        var we = new Array();
        for(var i = 0; i < val; i++){
            we[i] = 'wet-'+ i;
        }
        $scope.itrate = we;
        console.log($scope.itrate);
    });
    
    
});
