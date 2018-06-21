var app = angular.module("myApp", []);

app.controller('pageCtrl', function($scope, $http,  $window){
        $scope.actions = '';
        $scope.trans = trans_data;
        $scope.fetch_form = true;
        $scope.loader = false
        $scope.fetch_btn = true;

        
        $scope.result;
        $scope.getResult = function (v){
            $scope.result = JSON.parse(v.result_json);
        }
        
        
        $scope.current;
        $scope.setCurrent = function(v){
            $scope.current = v;
        }
        

        $scope.resetForm  = function(){
            $scope.fetch_form = true;
            $scope.loader = false
            $scope.fetch_btn = true;
        }
        
        
        $scope.saveResult = function(rs){
            
            let rs_data = {
                result: rs,
                trans_id: $scope.current.id,
                user_id :$scope.current.user_id,
                web_hook_url: $scope.current.return_url,
            };
            $scope.save(rs_data);
            
            $scope.stream(rs_data);
            
            
        }
        
        //save a local copy of result to olevelservice.io
        $scope.save = function(d){
            $scope.loader = true;
            $scope.actions = 'Saving result to Database';
            
            $http({
                method : "POST",
                url : "../api/ResultCHK/api.php?action=save_local",
                data : {
                    result: d.result,
                    trans_id: d.trans_id
                }
            }).then(function mySuccess(response) {
                if(response.data.status == 0){
                    alert(response.data.message);
                    $scope.actions = 'Result saved to Database';
                    $scope.loader = false;
                        
                }
            }, function myError(response) {
                console.log(response);
                $scope.loader = false
            });
        }
        
        
        //
        $scope.stream = function(d){
            $scope.loader = true;
            $scope.actions = 'Pushing result to '+ d.web_hook_url ;
            $http({
                method : "POST",
                url : d.web_hook_url,
                
                data : {
                    result: d.result,
                    trans_id: d.trans_id,
                    user_id: d.user_id
                }
            }).then(function mySuccess(response) {
                $scope.actions = 'Result pushed to '+ d.return_url ;
                   
                $scope.loader = false;
                $window.location.reload();
            }, function myError(response) {
                
                $scope.loader = false;
                $window.location.reload();
                
            });
        }
      
      
        $scope.fetchResult = function(data){
            $scope.loader = true;
            $scope.actions = 'Fetching Result from  '+ data.exam_name + ' Site ';
            $scope.fetch_form = false;
            $http({
                method : "POST",
                url : "../api/ResultCHK/api.php?action=fetch",
                data : data
            }).then(function mySuccess(response) {
                if(response.data.status == 0){
                    alert(response.data.message);
                    console.log(response.data);
                    $scope.loader = false;
                    $scope.fetch_form = true;
                }else{
                    console.log(response.data);
                    $scope.fetched_result = response.data;
                    $scope.loader = false;
                }
                
            }, function myError(response) {
                console.log(response);
                $scope.loader = false
            });
        }
      
});



//app.config(function($routeProvider) {
//    
//    $routeProvider
//    .when("/", {
//      templateUrl : "views/index.html"
//    })
//    .when("/pay", {
//      templateUrl : "views/esl.html"
//    })
//    .when("/green", {
//      templateUrl : "green.htm"
//    })
//    .when("/blue", {
//      templateUrl : "blue.htm"
//    });
//  });
//  
  
  
  function popitup(url) {
	newwindow=window.open(url,'olevelresult.io-cashenvoy','height=500,width=400,left=400');
	if (window.focus) {newwindow.focus()}
	return false;
}


