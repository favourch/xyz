<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');

$auth_users = "10";
check_auth($auth_users, $site_root);

fillAccomDetails($site_root, $tams);

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

if (isset($_POST["MM_insert"]) && $_POST["MM_insert"] == "form1" && isset($_POST['sid'])) {

    $query_rCheck = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", 
                            GetSQLValueString($_POST['stid'], "text"), 
                            GetSQLValueString($_POST['sid'], "text"));
    $rCheck = mysql_query($query_rCheck, $tams) or die(mysql_error());
    $row_rCheck = mysql_fetch_assoc($rCheck);
    $totalRows_rCheck = mysql_num_rows($rCheck);

    if($totalRows_rCheck < 1) {
    
        $insertSQL = sprintf("INSERT INTO registration (stdid, sesid, status, course, level, progid) VALUES (%s, %s, %s, %s, %s, %s)", 
                GetSQLValueString($_POST['stid'], "text"), 
                GetSQLValueString($_POST['sid'], "int"), 
                GetSQLValueString("Registered", "text"), 
                GetSQLValueString("Unregistered", "text"), 
                GetSQLValueString($_POST['lvl'], "int"), 
                GetSQLValueString(getSessionValue('pid'), "int"));


        $Registration = mysql_query($insertSQL, $tams) or die(mysql_error());
    }else {
        $updateSQL = sprintf("UPDATE registration SET status = 'Registered' WHERE stdid = %s AND sesid = %s", 
                GetSQLValueString($_POST['stid'], "text"), 
                GetSQLValueString($_POST['sid'], "int"));
        $Registration = mysql_query($updateSQL, $tams) or die(mysql_error());
    }
}

if ((isset($_POST["MM_insert"])) && ($_POST["MM_insert"] == "form2")) {

    if (isset($_POST['deleted_entries']) && !empty($_POST['deleted_entries'])) {
        $deletedCodes = array_unique($_POST['deleted_entries']);
        //array_key;
        $deletedEntries = implode('\',\'', $deletedCodes);
        $deleteSQL = sprintf("DELETE FROM course_reg "
                . "WHERE stdid = %s "
                . "AND sesid = %s "
                . "AND csid IN ('%s')", GetSQLValueString($_POST['stid'], "text"), GetSQLValueString($_POST['sid'], "int"), GetSQLValueString($deletedEntries, "defined", $deletedEntries));

        $Registration = mysql_query($deleteSQL, $tams) or die(mysql_error());
    }

    $insertRegistration = false;
            
    if (isset($_POST['courses']) && !empty($_POST['courses'])) {
        $uniqueCourses = array_unique($_POST['courses']);
        $registeredCourses = array();
        
        mysql_query('START TRANSACTION', $tams);

        foreach ($uniqueCourses AS $course) {
            $course = htmlentities($course);
            $dbEntry = sprintf("(%s, %s, %s, %s)", GetSQLValueString($_POST['stid'], "text"), GetSQLValueString($course, "text"), GetSQLValueString($_POST['sid'], "int"), GetSQLValueString('TRUE', 'text'));

            array_push($registeredCourses, $dbEntry);
        }

        $finalCourses = implode(',', $registeredCourses);
        $insertSQL = sprintf("INSERT INTO course_reg (stdid, csid, sesid, cleared) VALUES %s;", GetSQLValueString($finalCourses, "defined", $finalCourses));

        $insertRegistration = mysql_query($insertSQL, $tams) or die(mysql_error());

        $uniqueCourses = null;
        $registeredCourses = null;
        $finalCourses = null;
    }

    $updateSQL = sprintf("UPDATE registration SET course = %s WHERE stdid = %s AND sesid = %s", GetSQLValueString("Registered", "text"), GetSQLValueString($_POST['stid'], "text"), GetSQLValueString($_POST['sid'], "int"));

    $updateRegistration = mysql_query($updateSQL, $tams) or die(mysql_error());

    if ($insertRegistration && $updateRegistration) {
        mysql_query('COMMIT', $tams);
    }
    else {
        mysql_query('ROLLBACK', $tams);
    }
}

$query_sess = "SELECT * FROM `session` WHERE registration = 'TRUE' ORDER BY sesname DESC LIMIT 0,1";
$sess = mysql_query($query_sess, $tams) or die(mysql_error());
$row_sess = mysql_fetch_assoc($sess);
$totalRows_sess = mysql_num_rows($sess);

$student = getSessionValue('stid');

$query_studpop = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", 
        GetSQLValueString($student, "text"), 
        GetSQLValueString(getSessionValue('sesid'), "int"));
$studpop = mysql_query($query_studpop, $tams) or die(mysql_error());
$row_studpop = mysql_fetch_assoc($studpop);
$totalRows_studpop = mysql_num_rows($studpop);

//determine minimum & maximum registration units for each student
$query_reg_unit = sprintf("SELECT min, max FROM `reg_unit` WHERE progid = %s AND level= %s",
                    GetSQLValueString($row_studpop['progid'], "int"),
                    GetSQLValueString($row_studpop['level'], "int")); 
$reg_unit = mysql_query($query_reg_unit, $tams) or die(mysql_error());
$row_reg_unit = mysql_fetch_assoc($reg_unit);
$totalRows_reg_unit = mysql_num_rows($reg_unit);


$studPop = false;
if ($totalRows_studpop > 0)
    $studPop = true;

$redirect_url = "../registration/course_reg_form.php?stid=$student";

$redirect_url2 = "../payments/index.php";

if(!checkFees($row_sess['sesid'], getSessionValue('stid'))) {	
    header('Location:'.$redirect_url2);
    exit;
}
$paid = true;

$colname_stud = "-1";
if(isset($_SESSION['stid'])) {
    $colname_stud = $_SESSION['stid'];
}

if(isset($_GET['stid'])) {
    $colname_stud = $_GET['stid'];
}

$query_stud = sprintf("SELECT s.stdid, s.level, s.disciplinary, s.fname, s.payment, s.lname, s.level, "
        . "s.progid, p.progname, d.deptname, s.curid "
        . "FROM student s, programme p, department d "
        . "WHERE s.progid = p.progid "
        . "AND p.deptid = d.deptid "
        . "AND stdid = %s", GetSQLValueString($colname_stud, "text"));
$stud = mysql_query($query_stud, $tams) or die(mysql_error());
$row_stud = mysql_fetch_assoc($stud);
$totalRows_stud = mysql_num_rows($stud);

$query_rsdisp = sprintf("SELECT * FROM disciplinary WHERE stdid = %s", GetSQLValueString($colname_stud, "text"));
$rsdisp = mysql_query($query_rsdisp, $tams) or die(mysql_error());
$row_rsdisp = mysql_fetch_assoc($rsdisp);
$totalRows_rsdisp = mysql_num_rows($rsdisp);

$colname_ref = "-1";
if (isset($_GET['stid'])) {
    $colname_ref = $_GET['stid'];
}

$colname_regStatus = "-1";
if (isset($colname_stud)) {
    $colname_regStatus = $colname_stud;
}

$colname_regStatus1 = "-1";
if (isset($row_sess['sesid'])) {
    $colname_regStatus1 = $row_sess['sesid'];
}

if (isset($_GET['sid'])) {
    $colname_regStatus1 = $_GET['sid'];
}

$query_regStatus = sprintf("SELECT * FROM registration WHERE stdid = %s AND sesid = %s", GetSQLValueString($colname_regStatus, "text"), GetSQLValueString($colname_regStatus1, "int"));
$regStatus = mysql_query($query_regStatus, $tams) or die(mysql_error());
$row_regStatus = mysql_fetch_assoc($regStatus);
$totalRows_regStatus = mysql_num_rows($regStatus);

$colname_regsess = "-1";
if (isset($row_sess['sesid'])) {
    $colname_regsess = $row_sess['sesid'];
}
if (isset($_GET['sid'])) {
    $colname_regsess = $_GET['sid'];
}

$query_regsess = sprintf("SELECT s.* "
        . "FROM session s, registration r "
        . "WHERE r.sesid = s.sesid "
        . "AND r.status=%s AND r.stdid=%s "
        . "ORDER BY sesname DESC", GetSQLValueString("Registered", "text"), GetSQLValueString($colname_stud, "text"));
$regsess = mysql_query($query_regsess, $tams) or die(mysql_error());
$row_regsess = mysql_fetch_assoc($regsess);
$totalRows_regsess = mysql_num_rows($regsess);

$query_course = sprintf("SELECT distinct(r.csid), c.semester, c.csname, dc.status, dc.unit "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s "
                        
                        . "UNION "
                        
                        . "SELECT distinct(r.csid), c.semester, c.csname, c.status, c.unit "
                        . "FROM course_reg r, course c "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid "
                        . "AND r.stdid = %s AND c.curid = %s "
                        . "AND r.sesid = %s "
                        . "AND r.csid NOT IN "
                        
                        . "(SELECT distinct(r.csid) "
                        . "FROM course_reg r, course c, department_course dc "
                        . "WHERE r.cleared = 'TRUE' "
                        . "AND c.csid = r.csid AND r.csid = dc.csid "
                        . "AND r.stdid = %s AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND dc.progid = %s "
                        . "AND r.sesid = %s ) ",
                        
                        GetSQLValueString($colname_regStatus, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString($row_stud['progid'], "int"),
                        GetSQLValueString($colname_regsess, "int"),
                        
                        GetSQLValueString($colname_regStatus, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString($colname_regsess, "int"),
                        
                        GetSQLValueString($colname_regStatus, "text"), 
                        GetSQLValueString($row_stud['curid'], "int"),
                        GetSQLValueString($row_stud['progid'], "int"),
                        GetSQLValueString($colname_regsess, "int")
                        
                        ); 
$course = mysql_query($query_course, $tams) or die(mysql_error());
$row_course = mysql_fetch_assoc($course);
$totalRows_course = mysql_num_rows($course);

$query_crslist = sprintf("SELECT csid FROM course_reg WHERE stdid = %s AND sesid = %s", GetSQLValueString($colname_regStatus, "text"), GetSQLValueString($colname_regsess, "int"));
$crslist = mysql_query($query_crslist, $tams) or die(mysql_error());
$row_crslist = mysql_fetch_assoc($crslist);
$totalRows_crslist = mysql_num_rows($crslist);

$regOpen = false;
if ($row_sess['registration'] == 'TRUE') {
    $regOpen = true;
}

$sesReg = false;
$row_regStatus['status'];
if ($row_regStatus['status'] == "Registered")
    $sesReg = true;

$crsReg = false;

// Ensure student is properly registered
if ($row_regStatus['course'] == "Registered" && $totalRows_crslist > 0) {
    $crsReg = true;
}
else {
    $updateSQL = sprintf("UPDATE registration SET course = %s WHERE stdid=%s AND sesid=%s", GetSQLValueString("Unregistered", "text"), GetSQLValueString($colname_regStatus, "text"), GetSQLValueString($colname_regsess, "int"));
    $Registration = mysql_query($updateSQL, $tams) or die(mysql_error());
    $row_regStatus['course'] = 'Unregistered';
}

$crsAppr = false;
if (isset($row_regStatus['approved']) && $row_regStatus['approved'] == "TRUE")
    $crsAppr = true;

$levels = [];
for ($idx = 1; $idx <= $row_stud['level']; $idx++) {
    array_push($levels, $idx);
}

/**
  Used on the registration view
 */
$query_suggestion = sprintf("SELECT csid, status, csname, unit "
        . "FROM course c "
        . "WHERE (deptid = %s "
        . "OR catid IN(3,4,5,8)) "
        . "AND level IN (%s)  AND c.curid = %s  "
        . "AND csid NOT IN ( SELECT csid "
        . "FROM course_reg "
        . "WHERE stdid = %s "
        . "AND sesid = %s "
        . "UNION "
        . "SELECT csid "
        . "FROM department_course "
        . "WHERE curid = %s AND level = %s AND progid = %s)", 
        GetSQLValueString(getSessionValue('did'), "int"), 
        GetSQLValueString(" ", "defined", implode(',', $levels)), 
        GetSQLValueString($row_stud['curid'], "int"),
        GetSQLValueString($colname_stud, "text"), 
        GetSQLValueString($row_sess['sesid'], "int"), 
        GetSQLValueString($row_stud['curid'], "int"),
        GetSQLValueString($row_stud['level'], "int"), 
        GetSQLValueString($row_stud['progid'], "int"));
$suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
$row_suggestion = mysql_fetch_assoc($suggestion);
$totalRows_suggestion = mysql_num_rows($suggestion);

$initialSug = array();
for ($idx = 0; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
    $row_suggestion['registered'] = false;
    $row_suggestion['selected'] = false;
    $initialSug[] = $row_suggestion;
}

$query_registered = sprintf("SELECT distinct r.csid, dc.status, c.csname, dc.unit, cleared "
                        . "FROM department_course dc, course_reg r, course c "
                        . "WHERE c.csid = r.csid AND dc.csid = c.csid "
                        . "AND c.curid = dc.curid AND dc.curid = %s "
                        . "AND r.stdid = %s "
                        . "AND r.sesid = %s",
                        GetSQLValueString($row_stud['curid'], "int"),
                            GetSQLValueString($colname_stud, "text"), 
                            GetSQLValueString($row_sess['sesid'], "int"));
$registered = mysql_query($query_registered, $tams) or die(mysql_error());
$row_registered = mysql_fetch_assoc($registered);
$totalRows_registered = mysql_num_rows($registered);

$totalRegistered = 0;
$registeredCourses = array();
for ($idx = 0; $idx < $totalRows_registered; $idx++, $row_registered = mysql_fetch_assoc($registered)) {
    $totalRegistered += $row_registered['unit'];
    $row_registered['registered'] = true;
    $row_registered['selected'] = true;
    $row_registered['prereg'] = false;
    $registeredCourses[] = $row_registered;
}

$query_registered = sprintf("SELECT d.csid, d.status, csname, d.unit "
                            . "FROM course c "
                            . "JOIN department_course d ON c.csid = d.csid "
                            . "WHERE c.curid = d.curid AND d.curid = %s "
                            . "AND d.progid = %s AND d.level = %s", 
                            GetSQLValueString($row_stud['curid'], "int"),
                            GetSQLValueString($row_stud['progid'], "int"), 
                            GetSQLValueString($row_stud['level'], "int"));
$registered = mysql_query($query_registered, $tams) or die(mysql_error());
$row_registered = mysql_fetch_assoc($registered);
$totalRows_registered = mysql_num_rows($registered);

for ($idx = 0; $idx < $totalRows_registered; $idx++, $row_registered = mysql_fetch_assoc($registered)) {
    $totalRegistered += $row_registered['unit'];
    $row_registered['registered'] = false;
    $row_registered['selected'] = true;    
    $row_registered['prereg'] = true;
    $registeredCourses[] = $row_registered;
}

$query_depts = sprintf("SELECT deptid, deptname "
        . "FROM department");
$depts = mysql_query($query_depts, $tams) or die(mysql_error());
$row_depts = mysql_fetch_assoc($depts);
$totalRows_depts = mysql_num_rows($depts);
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
      z-index: inherit;
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
      z-index: 10001;
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


                    <div class="row-fluid">
                        <div class="span12">
                            <div class="box box-bordered box-color">
                                <div class="box-title">
                                    <h3><i class="icon-user"></i>
                                        Course Registration
                                         <?php if( isset($_GET['stid']) )echo " for ".$row_stud['lname'].", ".$row_stud['fname'];?>
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <table class="table">
                                        <?php
                                        if ($regOpen) {
                                            if ($row_stud['disciplinary'] == 'False') {
                                                ?>
                                                <?php if (!$sesReg) { ?>
                                                    <form action="<?php echo $editFormAction; ?>" method="post" name="form1" id="form1"> 

                                                        <tr>
                                                            <td>Please register for the session to proceed with course registration!</td>
                                                        </tr>
                                                        <tr>
                                                            <td align="center"><input type="submit" name="submit" id="submit" value="Register" /></td>
                                                        </tr>
                                                        <input name="stid" type="hidden" value="<?php echo $colname_stud ?>" />
                                                        <input name="lvl" type="hidden" value="<?php echo $row_stud['level'] ?>" />
                                                        <input name="sid" type="hidden" value="<?php echo $row_sess['sesid'] ?>" />
                                                        <input type="hidden" name="MM_insert" value="form1" />
                                                    </form>
                                                <?php }
                                                elseif ($sesReg && !$crsReg && $studPop) { ?>
                                                    <tr>
                                                        <td>

                                                            <form action="<?php echo $editFormAction; ?>" ng-submit="submitAction($event)" 
                                                                  method="post" name="form" ng-controller="CourseController">
                                                                <div class="alert alert-info">
                                                                    Please check your Department Handbook for COURSES to register for the SESSION. 
                                                                    
                                                                </div>
                                                                <div class="row-fluid">
                                                                    <div class="span4 well">
                                                                        Max Unit Allowed: <span id="max" class="label label-green" ng-bind="data.max"></span><br/>
                                                                        Min Unit Allowed: <span id="min" class="label label-brown" ng-bind="data.min"></span><br/>
                                                                        Registered Units: <span id="reg"  class="label  label-purple" ng-bind="data.reg" ></span><br/>
                                                                        Remaining Units: <span id="rem" class="label  label-pink" ng-bind="data.rem"></span><br/>
                                                                    </div>
                                                                </div>
                                                                <table class="table table-condensed">
                                                                    <tr>
                                                                        <td colspan="3" valign="top">

                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" valign="top">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" valign="top">
                                                                            <div>
                                                                                <div class="input-append input-prepend">
                                                                                    <span class="add-on">Enter Number of Courses to take for the SESSION:</span>
                                                                                    <input type="number" ng-model="data.fields" class="input-medium">
                                                                                    <button type="button" class="btn" ng-click="addFields()">Add</button> 
                                                                                </div>
                                                                            </div>
                                                                            
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" valign="top">&nbsp;</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <td colspan="3" valign="top">

                                                                            <table id="courses" border="0"  class="table table-striped table-condensed" width="100%" ng-cloak>
                                                                                <tr ng-show="data.courses.length > 0">
                                                                                    <td colspan="4">
                                                                                        <div class="alert alert-success">
                                                                                            Type the Course Code or Title in the Textfield(s) 
                                                                                            below and select from the SUGGESTIONS. 
                                                                                            Use the <span style="color: red; font-weight: bolder">
                                                                                                <i class="fa fa-trash-o"></i>
                                                                                            </span> button 
                                                                                            to delete courses as appropriate. 
                                                                                            After REGISTRATION, see your Course Adviser to ADD/DELETE courses.
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
                                                                                        <input type="hidden" value="{{course.csid}}" ng-focus="course.focused"
                                                                                               ng-disabled="disableCourse($index)" name="courses[]"/>
                                                                                    </td>
                                                                                    <td valign="center" 
                                                                                        ng-bind-template="{{course.unit}}{{course.status | first}}">
                                                                                    </td>
                                                                                    <td valign="center">
                                                                                        <a style="color: red; font-style: normal; font-weight: bolder" 
                                                                                           ng-click="removeField($index)" href=""><i class="fa fa-trash-o"></i></a>
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
                                                                            <input type="submit" name="submit" value="Register Courses" class="btn btn-primary" ng-disabled="data.min > data.reg || data.reg > data.max"/>
                                                                        </td>
                                                                    </tr>
                                                                </table>

                                                                <input name="stid" type="hidden" value="<?php echo $colname_stud ?>" />
                                                                <input name="sid" type="hidden" value="<?php echo $row_sess['sesid'] ?>" />
                                                                <input name="deleted_entries[]" ng-repeat="d in data.deletedEntries track by $index" type="hidden" value="{{d}}" />
                                                                <input type="hidden" name="MM_insert" value="form2" />
                                                            </form>
                                                        </td>
                                                    </tr>
        <?php }
        elseif (($sesReg && $crsReg) || in_array(getAccess(), $acl)) { ?>      
                                                    <tr>
                                                        <td>
                                                            <table class="table">
                                                                <tr>
                                                                    <td colspan="2" align="right">
                                                                        <a href="editform.php" target="_new">Edit Form</a>&nbsp &nbsp|&nbsp
                                                                        <a href="courseformpdf.php?sid=<?=$colname_regsess?>&stid=<?php echo $colname_stud; ?>" target="_new">Print Form</a>&nbsp &nbsp
                                                                        <select name="sesid" onchange="sesfilt(this)">
                                                                            <?php
                                                                            do {
                                                                                ?>
                                                                                <option value="<?php echo $row_regsess['sesid'] ?>" 
                                                                                <?php
                                                                                        if (!(strcmp($row_regsess['sesid'], $colname_regsess))) {
                                                                                            echo "selected=\"selected\"";
                                                                                        }
                                                                                        ?>>
                                                                                <?php echo $row_regsess['sesname'] ?>
                                                                                </option>
                                                                                <?php
                                                                            }
                                                                            while ($row_regsess = mysql_fetch_assoc($regsess));
                                                                            $rows = mysql_num_rows($regsess);
                                                                            if ($rows > 0) {
                                                                                mysql_data_seek($regsess, 0);
                                                                                $row_regsess = mysql_fetch_assoc($regsess);
                                                                            }
                                                                            ?>
                                                                        </select>
                                                                    </td>
                                                                </tr> 
                                                                <tr>
                                                                    <td colspan="2" width="100" align="right">
                                                                    </td>
                                                                </tr>             
                                                                <tr>
                                                                    <td colspan="2">
                                                                        <table class="table table-hover table-condensed">
                                                                            <tr>
                                                                                <th width="100" align="center">COURSE CODE</th>
                                                                                <th width="410" align="center">COURSE NAME</th>
                                                                                <th width="80" align="center">STATUS</th>
                                                                                <th width="30">UNIT</th>
                                                                                <th width="70" align="center">SEMESTER</th>
                                                                            </tr>
                                                                            <?php
                                                                            $tunits = 0;
                                                                            if ($totalRows_course > 0) { // Show if recordset not empty 
                                                                                ?>
                <?php
                do {
                    ?>
                                                                                    <tr>
                                                                                        <td><div align="center"><?php echo $row_course['csid']; ?></div></td>
                                                                                        <td><?php echo $row_course['csname']; ?></td>
                                                                                        <td><div align="center"><?php echo $row_course['status']; ?></div></td>
                                                                                        <td>
                                                                                            <div align="center">
                                                                                                <?php echo $row_course['unit'];
                                                                                                $tunits += $row_course['unit']; ?>
                                                                                            </div>
                                                                                        </td>
                                                                                        <td>
                                                                                            <div align="center">
                                                                                    <?php echo (strtolower($row_course['semester']) == "f") ? "First" : "Second"; ?>
                                                                                            </div>
                                                                                        </td>
                                                                                    </tr>
                <?php }
                while ($row_course = mysql_fetch_assoc($course)); ?>

            <?php } // Show if recordset not empty  ?>
                                                                            <tr>
                                                                                <td colspan="3" align="right" >Total Units</td>
                                                                                <td align="center"><?php echo $tunits; ?></td>
                                                                                <td></td>
                                                                            </tr>
                                                                        </table></td>
                                                                </tr>
                                                                <tr>
                                                                    <td colspan="2">&nbsp;</td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                <?php }
                                                else {
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <div class="alert alert-info">
                                                                Your course form is awaiting approval by your course adviser. Check back later!
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php }
                                                ?>
                                                <?php
                                            }
                                            else {
                                                echo "You are on a disciplinary action<strong>  {$row_rsdisp['status']}</strong> as at <strong> "
                                                . "{$row_rsdisp['login']} "
                                                . "</strong> Kindlly  contact the Registrar's Office for advice and necessary action";
                                            }
                                        }
                                        else {
                                            echo "Registration for this session {$row_sess['sesname']} is closed!";
                                        }
                                        ?>
                                    </table>
                                </div>
                            </div>
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
            "deletedEntries": [],
            "max": <?php echo $row_reg_unit['max'] ?>,
            "min": <?php echo $row_reg_unit['min'] ?>,
            "rem": <?php echo $row_reg_unit['max'] - $totalRegistered ?>,
            "reg": <?php echo $totalRegistered ?>,
            "courses": <?php echo json_encode($registeredCourses); ?>,
            "submit": false
        };
  
        // instantiate the bloodhound suggestion engine
        var courses = new Bloodhound({
            datumTokenizer: function(d) { 
                var keyName = Bloodhound.tokenizers.whitespace(d.csname+" "+d.csid);
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
                            "removed": false,
                            "focused": false,
                            "prereg": false
                        };
                        
                        if($scope.data.fields === 1)
                            emptyObj.focused = true;
                        
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
                        "removed": false, 
                        "focused": true,
                        "prereg": false
                    };
                    $scope.data.courses.unshift(emptyObj);
                }
            }
            
            $scope.data.fields = 0; 
        };
        
        $scope.disableCourse = function(idx) {
            var crs = $scope.data.courses[idx];
            
            if(!crs.csid || crs.registered)
                return true;            
                
            return false;
        }
        
        $scope.removeField = function(index) {
            if(confirm("Are you sure you want to remove this course?")) {
                var removed = $scope.data.courses.splice(index, 1);
                var removedEntry = removed[0];
                var unit = (removedEntry.unit === null) ? 0 : parseInt(removedEntry.unit);
                
                if(removedEntry.registered || removedEntry.prereg) {
                    removedEntry.registered = false;
                    removedEntry.prereg = false;
                    $scope.data.deletedEntries.push(removedEntry.csid);
                    $scope.addValue(removedEntry);
                }
       
                if(!removedEntry.csid) {
                    $scope.data.pending--;
                }
                
                removedEntry.selected = false;
                $scope.adjustCalc(unit, false);
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