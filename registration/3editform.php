<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



/* -----------------------------------------------*
 * 
 * Logic of the College/index.php Page 
 *
 * *------------------------------------------------
 */

$MM_authorizedUsers = "1,2,3,6,10";
$MM_donotCheckaccess = "false";

// *** Restrict Access To Page: Grant or deny access to this page
function isAuthorized($strUsers, $strGroups, $UserName, $UserGroup) {
    // For security, start by assuming the visitor is NOT authorized. 
    $isValid = False;

    // When a visitor has logged into this site, the Session variable MM_Username set equal to their username. 
    // Therefore, we know that a user is NOT logged in if that Session variable is blank. 
    if (!empty($UserName)) {
        // Besides being logged in, you may restrict access to only certain users based on an ID established when they login. 
        // Parse the strings into arrays. 
        $arrUsers = Explode(",", $strUsers);
        $arrGroups = Explode(",", $strGroups);
        if (in_array($UserName, $arrUsers)) {
            $isValid = true;
        }
        // Or, you may restrict access to only certain users based on their username. 
        if (in_array($UserGroup, $arrGroups)) {
            $isValid = true;
        }
        if (($strUsers == "") && false) {
            $isValid = true;
        }
    }
    return $isValid;
}

$MM_restrictGoTo = "../login.php";
if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $MM_authorizedUsers, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
    $MM_qsChar = "?";
    $MM_referrer = $_SERVER['PHP_SELF'];
    if (strpos($MM_restrictGoTo, "?"))
        $MM_qsChar = "&";
    if (isset($_SERVER['QUERY_STRING']) && strlen($_SERVER['QUERY_STRING']) > 0)
        $MM_referrer .= "?" . $_SERVER['QUERY_STRING'];
    $MM_restrictGoTo = $MM_restrictGoTo . $MM_qsChar . "accesscheck=" . urlencode($MM_referrer);
    header("Location: " . $MM_restrictGoTo);
    exit;
}



mysql_select_db($database_tams, $tams);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
  $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

$location = '';
if(getAccess() == 6 && isset($_SERVER['HTTP_REFERER'])) {
    $location = $_SERVER['HTTP_REFERER'];
}
  
if(getAccess() == 10) {
    $location = 'registercourse.php';
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {
    
    $location =  "viewform.php?stid={$_POST['stid']}";
    
    if(isset($_POST["location"]) && $_POST["location"] != '') {
        $location = $_POST["location"];
    }
    
    if(isset($_POST['updated_entries']) && !empty($_POST['updated_entries'])) {
        $updatedCodes = array_unique($_POST['updated_entries']);
        $updatedEntries = implode('\',\'', $updatedCodes);
        
        $query_courChk = sprintf("SELECT csid FROM `teaching` "
                            . "WHERE upload = 'no' "
                            . "AND sesid = %s "
                            . "AND csid IN ('%s')",
                            GetSQLValueString($_POST['sid'], "int"),
                            GetSQLValueString($updatedEntries, "defined", $updatedEntries));        
        $courChk = mysql_query($query_courChk, $tams) or die(mysql_error());
        $row_courChk = mysql_fetch_assoc($courChk);
        $totalRows_courChk = mysql_num_rows($courChk);
        
        $deletedEntries = array();
        
        if($totalRows_courChk > 0) {
            for($idx = 0; $idx < $totalRows_courChk; $idx++, $row_courChk = mysql_fetch_assoc($courChk)) {
                $deletedEntries[] = "'{$row_courChk['csid']}'";
            }        
            $delete = implode(',', $deletedEntries);

            $updateSQL = sprintf("DELETE FROM result "
                                    . "WHERE stdid = %s "
                                    . "AND sesid = %s "
                                    . "AND csid IN (%s)",
                                   GetSQLValueString($_POST['stid'], "text"),
                                   GetSQLValueString($_POST['sid'], "int"),
                                   GetSQLValueString($delete, "defined", $delete));

            $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
        }
    }
    
    if(isset($_POST['approved_entries']) && !empty($_POST['approved_entries'])) {
        $approvedCodes = array_unique($_POST['approved_entries']);
        $approvedEntries = implode('\',\'', $approvedCodes);
        $updateSQL = sprintf("UPDATE result SET cleared = 'TRUE' "
                                . "WHERE stdid = %s "
                                . "AND sesid = %s "
                                . "AND csid IN ('%s')",
                               GetSQLValueString($_POST['stid'], "text"),
                               GetSQLValueString($_POST['sid'], "int"),
                               GetSQLValueString($approvedEntries, "defined", $approvedEntries));

        $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    }
    
    if(isset($_POST['courses']) && !empty($_POST['courses'])) {
        $uniqueCourses = array_unique($_POST['courses']);
        $registeredCourses = array();
        
        $query_courChk = sprintf("SELECT csid FROM `teaching` "
                            . "WHERE upload = 'no' "
                            . "AND sesid = %s "
                            . "AND csid IN ('%s')",
                            GetSQLValueString($_POST['sid'], "int"),
                            GetSQLValueString('defined', "defined", implode('\',\'', $uniqueCourses)));        
        $courChk = mysql_query($query_courChk, $tams) or die(mysql_error());
        $row_courChk = mysql_fetch_assoc($courChk);
        $totalRows_courChk = mysql_num_rows($courChk);
        
        if($totalRows_courChk > 0) {
            for($idx = 0; $idx < $totalRows_courChk; $idx++, $row_courChk = mysql_fetch_assoc($courChk)) {            
                $dbEntry = sprintf("(%s, %s, %s, %s)", 
                                GetSQLValueString($_POST['stid'], "text"),
                                GetSQLValueString(htmlentities($row_courChk['csid']), "text"), 
                                GetSQLValueString($_POST['sid'], "int"),
                                GetSQLValueString('TRUE', 'text'));

                array_push($registeredCourses, $dbEntry);
            }        

            $finalCourses = implode(',', $registeredCourses);
            $insertSQL = sprintf("INSERT INTO result (stdid, csid, sesid, cleared) VALUES %s;",
                                    GetSQLValueString($finalCourses, "defined", $finalCourses));

            $Result = mysql_query($insertSQL, $tams) or die(mysql_error());
        }
        
        $uniqueCourses = null;
        $registeredCourses = null;
        $finalCourses = null;
    }
	
    $updateSQL = sprintf("UPDATE registration SET course = %s WHERE stdid=%s AND sesid=%s",
                            GetSQLValueString("Registered", "text"), 
                            GetSQLValueString($_POST['stid'], "text"), 
                            GetSQLValueString($_POST['sid'], "int"));
    $Result = mysql_query($updateSQL, $tams) or die(mysql_error());
    header("Location: {$location}"); 
}

$query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$colname_stud = -1;

if(getAccess() == 10)
    $colname_stud = getSessionValue('stid');

if (isset($_GET['stid'])) {
  $colname_stud = $_GET['stid'];
}

$query_stud = sprintf("SELECT s.stdid, s.fname, s.lname, s.level, s.progid, p.progname, d.deptname "
                        . "FROM student s, programme p, department d "
                        . "WHERE s.progid = p.progid "
                        . "AND p.deptid = d.deptid "
                        . "AND stdid = %s", 
                        GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$levels = [];    
for($idx = 1; $idx <= $row_stud['level']; $idx++) {
    array_push($levels, $idx);
}

/**
    Used on the registration view
*/
$query_suggestion = sprintf("SELECT csid, status, csname, unit "
                        . "FROM course c "
                        . "WHERE (deptid = %s "
                        . "OR catid IN(3,4,5,8)) "
                        . "AND level IN (%s) "
                        . "AND csid NOT IN ( SELECT csid "
                        . "FROM result "
                        . "WHERE stdid = %s "
                        . "AND sesid = %s)",
                        GetSQLValueString(getSessionValue('did'), "int"),
                        GetSQLValueString(" ", "defined", implode(',', $levels)),
                        GetSQLValueString($colname_stud, "text"),
                        GetSQLValueString($row_sess['sesid'], "int"));
$suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
$row_suggestion = mysql_fetch_assoc($suggestion);
$totalRows_suggestion = mysql_num_rows($suggestion);

$initialSug = array();
for($idx = 0 ; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
    $row_suggestion['registered'] = false;
    $row_suggestion['selected'] = false;
    $row_suggestion['removed'] = false;
    $initialSug[] = $row_suggestion;
}

$query_registered = sprintf("SELECT r.csid, status, csname, unit, cleared "
                        . "FROM course c, result r "
                        . "WHERE c.csid = r.csid "
                        . "AND stdid = %s "
                        . "AND sesid = %s",
                        GetSQLValueString($colname_stud, "text"),
                        GetSQLValueString($row_sess['sesid'], "int"));
$registered = mysql_query($query_registered, $tams) or die(mysql_error());
$row_registered = mysql_fetch_assoc($registered);
$totalRows_registered = mysql_num_rows($registered);

$totalRegistered = 0;
$registeredCourses = array();
$uncleared = array();
for($idx = 0 ;  $idx < $totalRows_registered; $idx++, $row_registered = mysql_fetch_assoc($registered)) {    
    
    $row_registered['registered'] = true;
    $row_registered['selected'] = true;
    
    if($row_registered['cleared'] == 'FALSE') {
        $row_registered['removed'] = true;
        $uncleared[] = $row_registered;
    }else {
        $row_registered['removed'] = false;
        $totalRegistered += $row_registered['unit'];
        $registeredCourses[] = $row_registered;
    }
}

$registeredCourses = array_merge($registeredCourses, $uncleared);

$name = ( isset($row_stud['stdid']) ) ? "for ".$row_stud['lname']." "
        .$row_stud['fname']." - ".$row_sess['sesname']." Session": "";


$page_title = "Tasued";
?>
<!doctype html>
<html ng-app="tams">
<?php include INCPATH."/header.php" ?>
<script type="text/javascript" src="/<?= $site_root?>/js/typeahead.bundle.min.js"></script>
<script type="text/javascript" src="/<?= $site_root?>/js/angular/angular-typeahead.js"></script>

<style>
    /*
---------------------------    Typeahead.js styling    ------------------------------
*/


.tt-dropdown-menu,
.gist {
  text-align: left;
}

.ng-invalid {
  border-color: red !important; /* just for visual */
}

/* site theme */
/* ---------- */

.selected-true {
    display: none;
}

.typeahead,
.tt-query,
.tt-hint {
  width: 100%;
  height: 15px;
  padding: 8px 12px;
  font-size: 13px;
  line-height: 30px;
  border: 2px solid #ccc;
  -webkit-border-radius: 8px;
     -moz-border-radius: 8px;
          border-radius: 8px;
  outline: none;
}

.typeahead {
  background-color: #fff;
}

.typeahead:focus {
  border: 2px solid #0097cf;
}

.tt-query {
  -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
     -moz-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
          box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
}

.tt-hint {
  color: #999
}

.tt-dropdown-menu {
  width: 100%;
  margin-top: 12px;
  padding: 4px 0;
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
  padding: 3px 10px;
  font-size: 12px;
  line-height: 20px;
}

.tt-suggestion.tt-cursor {
  color: #fff;
  background-color: #0097cf;
}

.tt-suggestion p {
  margin: 0;
}
</style>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
<?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
<?php include INCPATH."/side_nav_bar.php" ?>

            <div id="main">
                <div class="container-fluid nav-fixed">
<?php include INCPATH."/page_header.php" ?>
                    <div class="breadcrumbs">
                        <ul>
                            <li>
                                <a href="index.php">Home</a> <i class="icon-angle-right"></i>
                            </li>
                            <li>
                                <a href="college.php">College</a>
                            </li>
                        </ul>
                        <div class="close-bread">
                            <a href="#"><i class="icon-remove"></i></a>
                        </div>
                    </div>
                    <br/>
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-calendar"></i>
                                        Add/Delete Course <?php echo $name?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <div class="row-fluid">
                                        <form action="<?php echo $editFormAction; ?>" ng-submit="submitAction($event)" 
                                              method="post" name="form" ng-controller="CourseController">
                                            <div class="row-fluid">
                                                <div class="span4 well">
                                                    Max Unit Allowed: <span id="max" ng-bind="data.max"></span><br/>
                                                    Min Unit Allowed: <span id="min" ng-bind="data.min"></span><br/>
                                                    Registered Units: <span id="reg" ng-bind="data.reg"></span><br/>
                                                    Remaining Units: <span id="rem" ng-bind="data.rem"></span><br/>
                                                </div>
                                            </div>
                                            
                                            <table class="table table-condensed">

                                                <tr>
                                                    <td colspan="3" valign="top">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" valign="top">
                                                        <div>
                                                            Enter Number of Courses to add to student's Course Form: 
                                                            <input type="text" size="5" ng-model="data.fields"/> 
                                                            <button type="button" ng-click="addFields()">Add</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" valign="top">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" valign="top">

                                                        <table id="courses" border="0"  class="table table-striped table-condensed" width="100%">
                                                            <tr ng-show="data.courses.length > 0">
                                                                <td colspan="4">
                                                                    <div class="alert alert-info">
                                                                        Type the Course Code or Title in the Textfield(s) 
                                                                        below and select from the SUGGESTIONS. 
                                                                        Use the <span style="color: red; font-weight: bolder">
                                                                        <i class="fa fa-trash-o"></i> </span> button to delete 
                                                                        courses or the <span style="color: red; font-weight: bolder">
                                                                        <i class="fa fa-plus"></i></span> button to 
                                                                        <?php echo getAccess() == 10 ? 're-add' : 're-approve' ?> courses as appropriate.
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr ng-show="data.courses.length > 0">
                                                                <td colspan="4">&nbsp;</td>
                                                            </tr>
                                                            <tr ng-repeat="course in data.courses track by $index">
                                                                <td valign="center" ng-bind="course.csid">
                                                                </td>
                                                                <td valign="center" >
                                                                    <span ng-bind="course.csname" ng-show="course.selected"></span>
                                                                    <div ng-if="!course.selected" style="margin-bottom: 0">
                                                                        <input class="typeahead" size="70" type="text" value="" sf-typeahead 
                                                                               options="coursesOptions" datasets="coursesDataset" 
                                                                               ng-focus="setIndex($index)"

                                                                               placeholder="Enter course code or name"/>
                                                                    </div>
                                                                    <input type="hidden" value="{{course.csid}}" 
                                                                           ng-disabled="course.registered || !course.selected" name="courses[]"/>
                                                                </td>
                                                                <td valign="center" 
                                                                    ng-bind-template="{{course.unit}}{{course.status | first}}">
                                                                </td>
                                                                <td valign="center" >
                                                                    <a style="color: red; font-style: normal; font-weight: bolder;" 
                                                                       ng-hide="course.removed" ng-click="removeField($index)" href="">
                                                                        <span style="color: red; font-weight: bolder">
                                                                            <i class="fa fa-trash-o"></i>
                                                                    </a>
                                                                    <a style="color: red; font-style: normal; font-weight: bolder;" 
                                                                       ng-show="course.removed" ng-click="reApprove($index)" href="">
                                                                        <span style="color: red; font-weight: bolder">
                                                                            <i class="fa fa-plus"></i></span>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" align="center">&nbsp;</td>
                                                </tr>
                                                <tr>
                                                    <td colspan="3" align="center">
                                                        <input class="btn btn-primary" type="submit" name="submit" value="Update Courses" 
                                                               ng-click="data.submit = true" ng-disabled="data.min > data.reg || data.reg > data.max"/>
                                                    </td>
                                                </tr>
                                            </table>

                                            <input name="stid" type="hidden" value="<?php echo $colname_stud ?>" />
                                            <input name="sid" type="hidden" value="<?php echo $row_sess['sesid'] ?>" />
                                            <input name="updated_entries[]" ng-repeat="d in data.updatedEntries track by $index" type="hidden" value="{{d}}" />
                                            <input name="approved_entries[]" ng-repeat="a in data.approvedEntries track by $index" type="hidden" value="{{a}}" />
                                            <input name="location" type="hidden" value="<?php echo $location ?>" />
                                            <input type="hidden" name="MM_insert" value="form2" />
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>
            </div>          
        </div>
    <?php include INCPATH."/footer.php" ?>
    </body>
    <script>
    var module = angular.module('tams', ['siyfion.sfTypeahead']);
    
    module.filter('first', function() {
        return function(input) {
            // input will be the string we pass in
            if (input)
                return input.substr(0, 1);
        }
    });
    
    module.controller('CourseController', function($scope, $timeout, $interpolate) {
        $scope.$on('typeahead:selected', function(evt, elem, datum, dataset) {
            $scope.processSelection(elem, datum);
        });
        
        $scope.$on('typeahead:autocompleted', function(evt, elem, datum, dataset) {
            $scope.processSelection(elem, datum);
        });
         
        $scope.data = {
            "selectedIndex": null,
            "fields": 0,
            "disabled": true,
            "pending": 0,
            "updatedEntries": [],
            "approvedEntries": [],
            "max": <?php echo $row_sess['tnumax'] ?>,
            "min": <?php echo $row_sess['tnumin'] ?>,
            "rem": <?php echo $row_sess['tnumax'] - $totalRegistered ?>,
            "reg": <?php echo $totalRegistered ?>,
            "courses": <?php echo json_encode($registeredCourses); ?>,
            "submit": false
        };
  
        // instantiate the bloodhound suggestion engine
        var courses = new Bloodhound({
            datumTokenizer: function(d) { 
                var keyName = Bloodhound.tokenizers.whitespace(d.csname);
                var keyCode = Bloodhound.tokenizers.whitespace(d.csid);
                return keyName.concat(keyCode); 
            },
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: 'suggestions.php?l=<?php echo $row_stud['level']; ?>&q=%QUERY',
            local: <?php echo json_encode($initialSug); ?>,
            limit: 10,
            dupDetector: function(remote, local) {
                return remote.csid === local.csid;
            }
        });

        
        // initialize the bloodhound suggestion engine
        courses.initialize();

        var interpolateFn = $interpolate('<p class="selected-{{selected}}"><strong>{{csid}}</strong> – {{csname}}</p>');
        
        $scope.coursesDataset = {
            displayKey: 'csname',
            source: courses.ttAdapter(),
            templates: {
                empty: [
                  '<div>There is no Course that contains that Code or Title or you are not allowed to take that Course. \n\
                        Please try another Code or Title!',
                  '</div>'
                ].join('\n'),
                suggestion: interpolateFn
            }
        };

        $scope.clearValue = function() {
            $scope.selectedNumber = null;
        };
        
        $scope.addValue = function(datum) {
            courses.add(datum);
        };

        // Typeahead options object
        $scope.coursesOptions = {
            highlight: true
        };
  
        $scope.processSelection = function(elem, datum) {
            datum.selected = true;
            $scope.data.pending--;
            $scope.data.courses[$scope.data.selectedIndex] = datum;
            $scope.adjustCalc(parseInt(datum.unit), true);
            $timeout(function(){elem.remove();}, 10);
        };
        
        $scope.adjustCalc = function(unit, inc) {
            if(inc) {
                if(unit > $scope.data.rem) {
                    alert("You cannot register this course. Allowed units exceeded!");
                    return;
                }

                $scope.data.reg += unit;
                $scope.data.rem -= unit;
            }else {
                $scope.data.reg -= unit;
                $scope.data.rem += unit;
            }
            
        };
        
        $scope.addFields = function() {
            
            if($scope.data.fields > 0) {
                $scope.data.pending = $scope.data.fields;
                if($scope.data.fields > 1) {
                    for( ; $scope.data.fields !== 0; $scope.data.fields--) {
                        var emptyObj = {
                            "csid": "", 
                            "csname": "", 
                            "unit": null, 
                            "status": "", 
                            "registered": false, 
                            "selected": false, 
                            "removed": false
                        };
                        $scope.data.courses.unshift(emptyObj);
                    }
                }else {
                    var emptyObj = {
                        "csid": "", 
                        "csname": "", 
                        "unit": null, 
                        "status": "", 
                        "registered": false, 
                        "selected": false, 
                        "removed": false
                    };
                    $scope.data.courses.unshift(emptyObj);
                }
                
                $scope.data.fields = 0; 
            }
        };
        
        $scope.removeField = function(index) {
            if(confirm("Are you sure you want to remove this course?")) {
                var removed = $scope.data.courses.splice(index, 1);
                var removedEntry = removed[0];
                
                var unit = (removedEntry.unit === null) ? 0 : parseInt(removedEntry.unit);
                
                if(removedEntry.registered) {
                    // Add removed course to list of courses to be upated on the server
                    $scope.data.updatedEntries.push(removedEntry.csid);
                    
                    // Remove course from the updatedEntries list
                    var loc = $scope.data.approvedEntries.indexOf(removedEntry.csid)
                    if(loc !== -1)
                        $scope.data.approvedEntries.splice(loc, 1);
                    
                    // Add removed course to bottom of the list
                    $scope.data.courses.push(removedEntry);
                }
                
                removedEntry.removed = true;
                $scope.adjustCalc(unit, false);
            }
        };
        
        $scope.reApprove = function(index) {
            if(confirm("Are you sure you want to <?php echo getAccess() == 10 ? 're-add' : 're-approve' ?> this course?")) {
                var entry = $scope.data.courses.splice(index, 1);
                var course = entry[0];
                var unit = (course.unit === null) ? 0 : parseInt(course.unit);
                
                $scope.data.courses.unshift(course);
                
                if(course.registered) {
                    // Remove course from the updatedEntries list
                    var loc = $scope.data.updatedEntries.indexOf(course.csid)
                    if(loc !== -1)
                        $scope.data.updatedEntries.splice(loc, 1);
                        
                    // Add course to approvedCourse list
                    $scope.data.approvedEntries.push(course.csid);
                }
                
//                if(!removedEntry.csid) {
//                    $scope.data.pending
//                }
                
                // Used to toggle displayed button "X" or "+"
                course.removed = false;
                $scope.adjustCalc(unit, true);
            }
        };
        
        $scope.setIndex = function(index) {
            $scope.data.selectedIndex = index;
        };
        
        $scope.submitAction = function(event) {
            if($scope.data.fields > 0){
                $scope.addFields();
                event.preventDefault();
            }
            
            
            if($scope.data.pending > 0) {
                if(!confirm('You have empty fields, do you want to submit your form?')) 
                    event.preventDefault();
            }
        };
    });

    </script>
</html>

