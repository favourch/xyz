<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');


$auth_users = "1,2,3,20";
check_auth($auth_users, $site_root);

$filter = '';

if (getAccess() == 2) {
    $col = getSessionValue('cid');
    $filter = "WHERE c.colid = {$col}";
}

if (getAccess() == 3) {
    $dept = getSessionValue('did');
    $filter = "WHERE d.deptid = {$dept}";
}

$query_prog = sprintf("SELECT progid, progname, duration, p.deptid "
        . "FROM programme p "
        . "LEFT JOIN department d ON p.deptid = d.deptid "
        . "LEFT JOIN college c ON d.colid = c.colid "
        . "%s", 
        GetSQLValueString($filter, "defined", $filter));
$prog = mysql_query($query_prog, $tams) or die(mysql_error());
$row_prog = mysql_fetch_assoc($prog);
$totalRows_prog = mysql_num_rows($prog);

$progid = $row_prog['progid'];
if (isset($_GET['pid'])) {
    $progid = $_GET['pid'];
}

if (isset($_POST["progid"])) {
    $progid = $_POST["progid"];
}

$prog_list = [];

for(;$row_prog;) {
    $prog_list[$row_prog['progid']] = [$row_prog['progname'], $row_prog['duration'], $row_prog['deptid']];
    $row_prog = mysql_fetch_assoc($prog);
}

if(!in_array($progid, array_keys($prog_list))) {
    $progid = '';
    $notification->set_notification('You do not have the privilege to view the courses in this programme!');
}

$deptid = $prog_list[$progid][2];

if (isset($_POST["submit"]) && $progid != '') {
    if (isset($_POST['deleted'])) {
        $deleteSQL = sprintf("DELETE FROM department_course WHERE assignid IN ('%s')", 
                GetSQLValueString('text', "defined", implode("','", $_POST['deleted'])));
        $Result1 = mysql_query($deleteSQL, $tams);
        if(mysql_errno() != 0) {
            
        }
    }
    
    if (isset($_POST['inserted'])) {
        $insert_entries = [];
        
        foreach ($_POST['inserted'] as $values) {
            
            $insert_entries[] = sprintf("(%s, %s, %s, %s, %s)",
                                        GetSQLValueString($progid, "int"),
                                        GetSQLValueString($deptid, "int"),
                                        GetSQLValueString($values['status'], "text"),
                                        GetSQLValueString($values['unit'], "int"),
                                        GetSQLValueString($values['level'], "int"));
                      
        }
        
        $insertSQL = sprintf("INSERT INTO department_course (progid, deptid, status, unit, level) VALUES %s;", 
                GetSQLValueString('text', "defined", implode(",", $insert_entries)));
        $Result1 = mysql_query($insertSQL, $tams);
        
    }
    
    if (isset($_POST['courses'])) {
        
        $update_columns = [
                            'status' => '`status` = CASE ', 
                            'unit' => '`unit` = CASE ', 
                            'level' => '`level` = CASE '
                        ];
        $ids = [];
        
        foreach ($_POST['courses'] as $id => $values) {
            $update_columns['status'] .= sprintf("WHEN `assignid` = %s THEN %s ",
                    GetSQLValueString($id, "int"), 
                    GetSQLValueString($values['status'], "text"));

            $update_columns['level'] .= sprintf("WHEN `assignid` = %s THEN %s ",
                    GetSQLValueString($id, "int"), 
                    GetSQLValueString($values['level'], "int"));
            
            $update_columns['unit'] .= sprintf("WHEN `assignid` = %s THEN %s ", 
                    GetSQLValueString($id, "int"), 
                    GetSQLValueString($values['unit'], "int"));
            
            // Save all assignid
            $ids[] = $id;            
        }
        
        if(count($ids) > 0) {
            
            $update_columns['status'] .= 'END';
            $update_columns['unit'] .= 'END';
            $update_columns['level'] .= 'END';
            
            $where = sprintf(" WHERE `assignid` IN (%s)", 
                    GetSQLValueString("ids", "defined", implode(',', $ids)));

            $update_query = sprintf("UPDATE department_course SET %s %s", 
                    GetSQLValueString("update_columns", "defined", implode(',', $update_columns)), 
                    GetSQLValueString($where, "defined", $where));
            $rsupdate = mysql_query($update_query, $tams);
        }
    }
}

$query_courses = sprintf("SELECT dc.assignid, dc.status, dc.unit, dc.level, c.csid, c.csname "
        . "FROM department_course dc "
        . "JOIN course c ON dc.csid = c.csid "
        . "LEFT JOIN category ct ON c.catid = ct.catid "
        . "LEFT JOIN department d ON c.deptid = d.deptid "
        . "WHERE dc.progid = %s "
        . "ORDER BY c.csid", GetSQLValueString($progid, "int"));
$courses = mysql_query($query_courses, $tams) or die(mysql_error());
$totalRows_courses = mysql_num_rows($courses);

$course_list = [];
for(;$row_courses = mysql_fetch_assoc($courses);) {
    $row_courses['edit'] = false;
    $row_suggestion['selected'] = true;
    $course_list[] = $row_courses;
}

/**
    Generate initial suggestions
*/
$query_suggestion = sprintf("SELECT dc.status, dc.unit, dc.level, c.csid, csname "
                        . "FROM course c "
                        . "LEFT JOIN department_course dc ON c.csid = dc.csid AND c.deptid = dc.deptid AND dc.deptid = %s "
                        . "WHERE dc.assignid IS NULL "
                        . "AND (catid IN(3,4,5,8) OR c.deptid = %s)",
                        GetSQLValueString($deptid, "int"),
                        GetSQLValueString($deptid, "int"));
$suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
$row_suggestion = mysql_fetch_assoc($suggestion);
$totalRows_suggestion = mysql_num_rows($suggestion);

$initial_sug = [];
for($idx = 0 ; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
    $row_suggestion['selected'] = false;
//    $row_suggestion['status'] = 'Compulsory';
//    $row_suggestion['unit'] = 1;
//    $row_suggestion['level'] = 1;
    $initial_sug[] = $row_suggestion;
}

?>
<!doctype html>
<html ng-app="tams">
    <?php include INCPATH."/header.php" ?>

    <body data-layout-sidebar="fixed" data-layout-topbar="fixed">
        <?php include INCPATH."/top_nav_bar.php" ?>
        <div class="container-fluid" id="content">
            <?php include INCPATH."/side_nav_bar.php" ?>
            <div id="main">
                <div class="container-fluid nav-fixed">
                    <?php include INCPATH."/page_header.php" ?>

                    <div class="row-fluid" ng-controller="PageController"> 
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-reorder"></i>
                                        Course Status
                                    </h3>                                   
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <div class="span3">Course Status for:</div>
                                        <div class="span4">
                                            <div class="control-group">
                                                <div class="controls controls-row">
                                                    <select name="progid" onchange="progfilt(this)" class="chosen-select">
                                                        <?php foreach($prog_list as $id => $details) :?>
                                                        <option value="<?php echo $id?>" <?php echo $id == $progid? 'selected': ''?>>
                                                            <?php echo $details[0]?>
                                                        </option>
                                                        <?php endforeach;?>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>   
                                    
                                    <div class="row-fluid">
                                        <div class="span2">Add more courses:</div>
                                        <div class="span7">
                                            <div class="control-group">
                                                <div class="controls controls-row">
                                                    <input class="typeahead input-large" size="200" type="text" 
                                                           value="" sf-typeahead options="coursesOptions" 
                                                           datasets="coursesDataset" placeholder="Enter course code or name"/>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <form name="assignform" action='' method="post">
                                        <fieldset>
                                            <legend>Departmental Courses</legend>
                                            
                                            <table class="table table-striped">
                                                <thead>
                                                    <tr>
                                                        <th>Code</th>
                                                        <th>Name</th>
                                                        <th>Status</th>
                                                        <th>Level</th>
                                                        <th>Unit</th>
                                                        <th></th>
                                                    </tr>
                                                </thead>
                                                <tr ng-repeat="course in newCourses">
                                                    <td>{{course.csid}}</td>
                                                    <td>{{course.csname}}</td>
                                                    <td>
                                                        <select name="inserted[{{$index}}][status]" class="input-medium">
                                                            <option value="Compulsory">Compulsory</option>
                                                            <option value="Required">Required</option>
                                                            <option value="Elective">Elective</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="inserted[{{$index}}][level]" class="input-small">
                                                            <?php for ($idx = 1; $idx <= $prog_list[$progid][1]; $idx++) : ?>
                                                                <option value="<?php echo $idx ?>">
                                                                    <?php echo $idx . '00' ?>
                                                                </option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="inserted[{{$index}}][unit]" class="input-small">
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <span style="cursor: pointer" ng-click="delete($index, 'new')">
                                                            <i class="icon-trash"></i>
                                                        </span>
                                                    </td>
                                                </tr>
                                                
                                                <tr ng-repeat="course in courses">
                                                    <td>{{course.csid}}</td>
                                                    <td>{{course.csname}}</td>
                                                    <td>
                                                        <select name="courses[{{course.assignid}}][status]" ng-model="course.status" class="input-medium"
                                                                ng-disabled="!course.edit">
                                                            <option value="Compulsory">Compulsory</option>
                                                            <option value="Required">Required</option>
                                                            <option value="Elective">Elective</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="courses[{{course.assignid}}][level]" class="input-small" ng-disabled="!course.edit">
                                                            <?php for ($idx = 1; $idx <= $prog_list[$progid][1]; $idx++) :?>
                                                            <option value="<?php echo $idx ?>">
                                                            <?php echo $idx . '00' ?>
                                                            </option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="courses[{{course.assignid}}][unit]" ng-disabled="!course.edit" ng-model="course.unit" class="input-small">
                                                            <option value="1">1</option>
                                                            <option value="2">2</option>
                                                            <option value="3">3</option>
                                                            <option value="4">4</option>
                                                            <option value="5">5</option>
                                                            <option value="6">6</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" ng-model="course.edit"/>
                                                        <span style="cursor: pointer" ng-click="delete($index, 'old')">
                                                            <i class="icon-trash"></i>
                                                        </span>
                                                    </td>
                                                </tr>
                                                <tr ng-repeat="course in deletedCourses">
                                                    <td>{{course.csid}}</td>
                                                    <td>{{course.csname}}</td>
                                                    <td>{{course.status}}</td>
                                                    <td>{{course.level}}</td>
                                                    <td>{{course.unit}}</td>
                                                    <td>
                                                        <span style="cursor: pointer" ng-click="add($index)">
                                                            <i class="icon-plus"></i>
                                                        </span>
                                                    </td>
                                                    <input type="hidden" name="deleted[]" value="{{course.assignid}}"/>
                                                </tr>
                                            </table>
                                        </fieldset>
                                        <div class="row-fluid pad-3">
                                            <input type="submit" name="submit" value="Assign Courses" class='btn btn-primary'/>
                                        </div>
                                        <input type="hidden" name="progid" value="<?php echo $progid ?>" />
                                    </form>
                                            
                                </div>
                            </div>

                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>

            
        </div>
    </body>

    <script type="text/javascript" src="/<?php echo $site_root?>/js/typeahead.bundle.min.js"></script>
    <script type="text/javascript" src="/<?php echo $site_root?>/js/angular/angular-typeahead.js"></script>
    <script type="text/javascript">
        var app = angular.module('tams', ['siyfion.sfTypeahead']);
        
        app.controller('PageController', function($scope, $interpolate) {
            
            $scope.$on('typeahead:selected', function(evt, elem, datum, dataset) {
                $scope.processSelection(elem, datum);
                evt.preventDefault();
            });

            $scope.$on('typeahead:autocompleted', function(evt, elem, datum, dataset) {
                $scope.processSelection(elem, datum);
                evt.preventDefault();
            });
            
            $scope.courses = <?php echo json_encode($course_list) ?>;            
            $scope.newCourses = [];
            $scope.deletedCourses = [];
                   
             // instantiate the bloodhound suggestion engine
            var courses = new Bloodhound({
                datumTokenizer: function(d) { 
                    var keyName = Bloodhound.tokenizers.whitespace(d.csname);
                    var keyCode = Bloodhound.tokenizers.whitespace(d.csid);
                    return keyName.concat(keyCode); 
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                //remote: 'suggestions.php?q=%QUERY',
                local: <?php echo json_encode($initial_sug); ?>,
                limit: 10,
                dupDetector: function(remote, local) {
                    return remote.csid === local.csid;
                }
            });

            // initialize the bloodhound suggestion engine
            courses.initialize();

            var interpolateFn = $interpolate('<p class="selected-{{selected}}"><strong>{{csid}}</strong> – {{csname}}</p>');

            $scope.coursesDataset = {
                displayKey: '',
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
                if(!datum.selected) {
                    datum.selected = true;
                    $scope.$apply(function() {$scope.newCourses.push(datum);});
                }                
            };
        
            $scope.delete = function(idx, type) {
                switch(type) {
                    case 'new':
                        if(confirm("Are you sure you want to remove this course?")) {
                            var removed = $scope.newCourses.splice(idx, 1);
                            removed[0].selected = false;
                        }
                        break;
                        
                    case 'old':
                        if(confirm("Are you sure you want to remove this course?")) {
                            var removed = $scope.courses.splice(idx, 1);
                            $scope.deletedCourses.push(removed[0]);                            
                        }
                        break;
                }
            };
            
            $scope.add = function(idx) {
                if(confirm("Are you sure you want to undo this delete?")) {
                    var removed = $scope.deletedCourses.splice(idx, 1);
                    removed[0].selected = false;
                    $scope.courses.push(removed[0]);
                }
            };
        });
    </script>
</html>

    
