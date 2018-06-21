<?php
if (!isset($_SESSION)) {
    session_start();
}
require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

$locQS = '-1';
if(isset($_GET['loc'])){
    $locQS = $_GET['loc'];
}

$haveSumittedSQL = sprintf("SELECT * FROM accom_student_location "
                        . "WHERE stdid = %s ",
                        GetSQLValueString($_SESSION['uid'], 'text'));
$haveSumittedRS = mysql_query($haveSumittedSQL, $tams) or die(mysql_error());
$haveSumittedRow = mysql_fetch_assoc($haveSumittedRS);
$found = mysql_num_rows($haveSumittedRS);

if($found > 0){
    header(sprintf("location: %s", '/'.$site_root.'/student/profile.php'));
    die();
}


$locSQL = sprintf("SELECT * FROM accom_hostel_location ");
$loc = mysql_query($locSQL, $tams) or die(mysql_error());
//$row_loc = mysql_fetch_assoc($loc);

 $accomodationSQL = sprintf("SELECT * FROM accom_accomodation aa "
                        . "JOIN accom_building_type abt "
                        . "ON aa.building_type = abt.buidid "
                        . "JOIN accom_hostel_location ahl "
                        . "ON aa.location = ahl.locid "
                        . "WHERE location = %s ", GetSQLValueString($locQS, 'int'));

$accomodation = mysql_query($accomodationSQL, $tams) or die(mysql_error());
$row_accomodation = mysql_fetch_assoc($accomodation);

$accom = array();
do{
    $accomFeatSQL = sprintf("SELECT * FROM accom_accomodation_features aaf "
                         . "JOIN accom_features  af ON aaf.featid = af.featid "
                         . "WHERE aaf.accomid = %s ", GetSQLValueString($row_accomodation['accomid'], 'int'));
    $accomFeatRS = mysql_query($accomFeatSQL, $tams) or die(mysql_error());

    $feature = array();
    for(;$row_accom  = mysql_fetch_assoc($accomFeatRS);){
        $feature[] = $row_accom;
    }
    $row_accomodation['feat'] = $feature;
    $accom[] = $row_accomodation;
   
}while($row_accomodation = mysql_fetch_assoc($accomodation));
//var_dump($accom); die();
//echo json_encode($accom);die();

if(isset($_POST['submit'])){
   
    $saveSQL = sprintf("INSERT INTO accom_student_location "
                        . "(stdid, locid) VALUES(%s, %s)", 
                        GetSQLValueString($_SESSION['uid'], 'text'),
                        GetSQLValueString($_POST['accom'], 'int'));
    $saveRS = mysql_query($saveSQL, $tams) or die(mysql_error());
    
    header('location: ../student/profile.php');
    die();
}

?>
<!doctype html>
<html ng-app="plunker">
    <?php include INCPATH . "/header.php" ?>
    <style>
        .tt-query {
            -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        }

        .tt-hint {
            color: #999
        }

        .tt-menu {
            width: 422px;
            margin: 12px 0;
            padding: 8px 0;
            background-color: #fff;
            border: 1px solid #ccc;
            border: 1px solid rgba(0, 0, 0, 0.2);
            -webkit-border-radius: 8px;
            -moz-border-radius: 8px;
            border-radius: 8px;
            -webkit-box-shadow: 0 5px 10px rgba(0,0,0,.2);
            -moz-box-shadow: 0 5px 10px rgba(0,0,0,.2);
            box-shadow: 0 5px 10px rgba(0,0,0,.2);
        }

        .tt-suggestion {
            padding: 3px 20px;
            font-size: 18px;
            line-height: 24px;
        }

        .tt-suggestion:hover {
            cursor: pointer;
            color: #fff;
            background-color: #0097cf;
        }

        .tt-suggestion.tt-cursor {
            color: #fff;
            background-color: #0097cf;

        }

        .tt-suggestion p {
            margin: 0;
        }
    </style>
    <body data-layout-sidebar="fixed" data-layout-topbar="fixed" ng-controller="MainCtrl">
        <?php include INCPATH . "/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH . "/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH . "/page_header.php" ?>                                                                                                               
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-list"></i> Campus Accommodation Information form</h3>
                                </div>                                
                                <div class="box-content nopadding">
                                    <div class='form-horizontal form-column form-bordered'>
                                    <p style="color: red; font-size: 18px; text-align: center; margin-bottom: 5px;">Note: All students are obliged to provide the University with accurate information of their respective campus address.</p>
                                    
                                        <div class="control-group">
                                            <label for="textfield" class="control-label">Location</label>
                                            <div class="controls">
                                                <select name="location" id="select"  class='input-large'  onchange="locfilt(this)" required="">
                                                    <option value="">-- Select Location --</option>
                                                    <?php for (; $row_loc = mysql_fetch_assoc($loc);) { ?>
                                                        <option value="<?= $row_loc['locid'] ?>" <?= ($row_loc['locid'] == $locQS)? 'selected' : ''?>><?= $row_loc['locname'] ?></option>
                                                    <?php } ?>
                                                </select> 
                                            </div>
                                        </div>
                                    <?php if(isset($_GET['loc'])){?>
                                        <div class="control-group" >
                                            <label for="textfield" class="control-label">Building Name</label>
                                            <div class="controls">
                                                <input class='typeahead' type="text" sf-typeahead options="exampleOptions" datasets="numbersDataset" ng-model="selectedNumber" placeholder="e.g Fantasy Hall">        
                                            </div>
                                        </div>
                                    <?php }?>
                                        <div class="control-group" ng-if="selectedNumber.building_name">
                                            <label for="textfield" class="control-label">Building Info</label>
                                            <div class="controls">
                                                <div class="well well-small">
                                                    <table>
                                                        <tr>
                                                            <th>Building Name : </th>
                                                            <td>{{selectedNumber.building_name}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Building Address :</th>
                                                            <td>{{selectedNumber.building_address}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Location  :</th>
                                                            <td>{{selectedNumber.locname}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>Caretaker Details :</th>
                                                            <td>{{selectedNumber.caretaker_name}} {{selectedNumber.caretaker_phone}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>No. Of Rooms :</th>
                                                            <td>{{selectedNumber.no_of_rooms + ' Rooms'}}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>payment plan :</th>
                                                            <td>{{'NGN '+selectedNumber.pay_amount}} / {{selectedNumber.pay_mode}} </td>
                                                        </tr>
                                                        <tr>
                                                            <th>Accommodation type / Gender:</th>
                                                            <td>{{selectedNumber.name}} / {{selectedNumber.gender+" Hostel"}} </td>
                                                        </tr> 
                                                        <tr>
                                                            <th>Available Features :</th>
                                                            <td>
                                                                <div>
                                                                    <ul>
                                                                        <li ng-repeat="ft in selectedNumber.feat">{{ft.featname}}</li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                        </tr> 
                                                    </table>
                                                    <div class="alert alert-info">
                                                        Are you sure the above information about you hostel  is correct ? 
                                                        if Not click the "No update this" button to edit  
                                                    </div>
                                                    <form action="#" method="POST">
                                                        <div>
                                                            <input type="hidden" name="accom" value="{{selectedNumber.accomid}}">
                                                            <button class="btn btn-primary btn-small" type="submit" name="submit">YES, This is Where I Live</button>
                                                            <a href="edit.php?id={{selectedNumber.accomid}}" class="btn btn-warning btn-small" >NO, Update This Building Information</a>
                                                        </div>
                                                    </form>
                                                </div>  
                                            </div>
                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
        <?php include INCPATH . "/footer.php" ?>
        <script src="dist/angular-typeahead.js"></script>
        <script src="dist/typeahead.bundle.js"></script>
        
         
        <script>
                var app = angular.module('plunker', ['siyfion.sfTypeahead']);
                var accom = <?= json_encode($accom)?>;
                app.controller('MainCtrl', function ($scope, $compile) {
                    
                    $scope.selectedNumber = '';

                    // instantiate the bloodhound suggestion engine
                    var numbers = new Bloodhound({
                        datumTokenizer: function (d) {
                            
                            var name = Bloodhound.tokenizers.whitespace(d.building_name);
                            var agent = Bloodhound.tokenizers.whitespace(d.caretaker_name);
                            var address = Bloodhound.tokenizers.whitespace(d.building_address);
                            
                            return name.concat(agent).concat(address);
                        },
                        queryTokenizer: Bloodhound.tokenizers.whitespace,
                        local: accom
                    });

                    // initialize the bloodhound suggestion engine
                    numbers.initialize();
                    
                    var elem = angular.element("<p>No results were found... <a href='add_new_location.php'>Click</a> to add new Address</p>");
                    $compile(elem)($scope);
                    
                    
                    $scope.numbersDataset = {
                        displayKey: 'building_name',
                        source: numbers.ttAdapter(),
                        templates: {
                            empty: [
                                '<div class="tt-suggestion tt-empty-message">',
                                elem.html(),
                                '</div>'
                            ].join('\n')
                        }
                    };
                    
                    $scope.$on('typeahead:select', function(elem, datum) {
                        console.log(elem, datum);
                    });


                    // Typeahead options object
                    $scope.exampleOptions = {
                        displayKey: 'building_name'
                    };
                    
                });

        </script>
    </body>
</html>
<?php ?>
