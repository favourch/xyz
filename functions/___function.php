<?php

// *** Restrict Access To Page: Grant or deny access to this page
if (!function_exists("isAuthorized")) {
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
    //        if (($strUsers == "") && true) {
    //            $isValid = true;
    //        }
        }
        return $isValid;
    }
}

function check_auth($auth_users, $site_root) {
        
    $MM_restrictGoTo = "/$site_root/login.php";
    if (!((isset($_SESSION['MM_Username'])) && (isAuthorized("", $auth_users, $_SESSION['MM_Username'], $_SESSION['MM_UserGroup'])))) {
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
}

function checkPaymentPros($sesid, $jambregid, $expected, $type = 'app') {
    global $tams;
    $status['status'] = true; 
    $paid = 0.00;
    $expected = doubleval($expected);
     
    switch($type) {        
        case 'app':
            $table = 'appfee_transactions';
            break;
            
        case 'acc':
            $table = 'accfee_transactions';
            break;
        
        case 'sch':
            $table = 'schfee_transactions';
            break;
    }
    
    $query_payment = sprintf("SELECT amt  
                        FROM %s 
                        WHERE can_no = %s AND status = 'APPROVED'", 
                        GetSQLValueString($table, "defined", $table), 
                        GetSQLValueString($jambregid, "text")); 
    $payment = mysql_query($query_payment, $tams) or die(mysql_error());
    $totalRows_payment = mysql_num_rows($payment);

    for(; $row_payment = mysql_fetch_assoc($payment);) {
        $paid += doubleval(str_replace(['NGN', ',', 'N'], '', $row_payment['amt']));
    }
    
   $status['paid'] = $paid; 
    
    if($paid < $expected) {
        $status['status'] = false; //has not paid
        $status['owing'] = $expected - $paid;        
        $status['desc'] = $paid == 0.00? 'Pending': 'Incomplete';
    }
     
    return $status;    
}

function checkFees($sesid, $stdid) {
    global $tams;
    $amount = 0;

    $query_info = sprintf("SELECT *  
                        FROM student 
                        WHERE stdid = %s", GetSQLValueString($stdid, "text"));
    $info = mysql_query($query_info, $tams) or die(mysql_error());
    $row_info = mysql_fetch_assoc($info);
    $totalRows_info = mysql_num_rows($info);

    $status = $row_info['stid'] == 27 ? 'Indigene' : 'Nonindigene';

    $query_schedule = sprintf("SELECT * 
                            FROM payschedule 
                            WHERE sesid = %s 
                            AND level = %s 
                            AND status = %s 
                            AND admid = %s", GetSQLValueString($sesid, "int"), GetSQLValueString($row_info['level'], "text"), GetSQLValueString($status, "text"), GetSQLValueString($row_info['admid'], "text"));

    $schedule = mysql_query($query_schedule, $tams) or die(mysql_error());
    $row_schedule = mysql_fetch_assoc($schedule);
    $totalRows_schedule = mysql_num_rows($schedule);

    $query_curPay = sprintf("SELECT *  
                            FROM schfee_transactions 
                            WHERE scheduleid = %s 
                            AND matric_no = %s 
                            AND status = 'APPROVED'", GetSQLValueString($row_schedule['scheduleid'], "int"), GetSQLValueString($stdid, "text"));
    $curPay = mysql_query($query_curPay, $tams) or die(mysql_error());
    $totalRows_curPay = mysql_num_rows($curPay);

    for (; $row_curPay = mysql_fetch_assoc($curPay);) {
        $amount += doubleval(str_replace(',', '', substr($row_curPay['amt'], 3)));
    }

    if ($row_schedule['amount'] > $amount) {
        return false;
    }

    return true;
}

function migrate_details($row_ses, $ordid, $jamb_no, $tams, $type = 'new') {
    
    $sesid = $row_ses['sesid'];
    
    $query_details = sprintf("SELECT * "
            . "FROM prospective p "
            . "JOIN admissions a ON p.admid = a.admid "
            . "JOIN admission_type at ON a.typeid = at.typeid "
            . "WHERE jambregid = %s", 
            GetSQLValueString($jamb_no, "text"));
    $details =  mysql_query($query_details, $tams) or die(mysql_error());
    $row_details = mysql_fetch_assoc($details);
    
    $status = $row_details['stid'] == $indigene_state_id? 'Indigene': 'Nonindigene';
    $level = $row_details['entrylevel'] ;//== 'DE'? 2: 1;

    $query_curSchedule = sprintf("SELECT *  
                                FROM payschedule  
                                WHERE sesid = %s 
                                AND level = %s 
                                AND status = %s 
                                AND admid = %s",
                                GetSQLValueString($sesid, "int"),
                                GetSQLValueString($level, "text"),
                                GetSQLValueString($status, "text"),
                                GetSQLValueString($row_details['typeid'], "text"));
    $curSchedule = mysql_query($query_curSchedule, $tams) or die(mysql_error());
    $row_curSchedule = mysql_fetch_assoc($curSchedule);

    // Get matric generation details
    $query_matric = sprintf("UPDATE prog_matric SET currentno = currentno + 1 WHERE progid = %s AND sesid = %s;", 
                            GetSQLValueString($row_details['progoffered'], "int"), 
                            GetSQLValueString($sesid, "int"));
    $matric =  mysql_query($query_matric, $tams) or die(mysql_error());
    /*
    $query_last = sprintf("SELECT @last_num as last");
    $last =  mysql_query($query_last, $tams) or die(mysql_error());
    $row_last =  mysql_fetch_assoc($last) or die(mysql_error());
    */
    
    $query_last = sprintf("SELECT currentno as last FROM prog_matric WHERE progid = %s AND sesid = %s;", 
                            GetSQLValueString($row_details['progoffered'], "int"), 
                            GetSQLValueString($sesid, "int"));
    $last =  mysql_query($query_last, $tams) or die(mysql_error());
    $row_last =  mysql_fetch_assoc($last) or die(mysql_error());
    
    
    $query_info = sprintf("SELECT c.colcode, p.progcode, sesname "
                            . "FROM prog_matric pm JOIN programme p ON p.progid = pm.progid "
                            . "JOIN department d ON d.deptid = p.deptid "
                            . "JOIN college c ON c.colid = d.colid "
                            . "JOIN `session` s ON s.sesid = pm.sesid "
                            . "WHERE pm.progid = %s AND pm.sesid = %s", 
                            GetSQLValueString($row_details['progoffered'], "int"), 
                            GetSQLValueString($sesid, "int"));
    $info =  mysql_query($query_info, $tams) or die(mysql_error());
    $row_info =  mysql_fetch_assoc($info) or die(mysql_error());
    
    $paths = explode('/', $row_info['sesname']);
    $ses_year = trim($paths[0]);
    
    $gen_matric = $ses_year.$row_info['colcode'].$row_info['progcode'].str_pad($row_last['last'], 3, '0', STR_PAD_LEFT);
        
    // check that the student has no duplicate entry in student table due to paid.php refresh
    $query_check = sprintf("SELECT * FROM student WHERE jambregid = %s", 
                            GetSQLValueString($jamb_no, "text"));
    $check =  mysql_query($query_check, $tams) or die(mysql_error());
    $row_check = mysql_fetch_assoc($check);
    
    if($type == 'new') {
        
        $query = sprintf("INSERT INTO student (stdid, lname, fname, mname, progid, phone, email, addr, sex, "
                . "dob, sesid, level, admid, password, stid, jambregid, healthStatus, Religion, sponsorname, "
                . "sponsorphn, sponsoradrs) "
                . "VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
                                GetSQLValueString($gen_matric, "text"),
                                GetSQLValueString($row_details['lname'], "text"),
                                GetSQLValueString($row_details['fname'], "text"),
                                GetSQLValueString($row_details['mname'], "text"),
                                GetSQLValueString($row_details['progoffered'], "int"),
                                GetSQLValueString($row_details['phone'], "text"),
                                GetSQLValueString($row_details['email'], "text"),
                                GetSQLValueString($row_details['address'], "text"),
                                GetSQLValueString(substr($row_details['Sex'], 0, 1), "text"),
                                GetSQLValueString($row_details['DoB'], "text"),
                                GetSQLValueString($sesid = $row_details['sesid'], "int"),
                                GetSQLValueString($level, "int"),
                                GetSQLValueString($row_details['typeid'], "text"),
                                GetSQLValueString(md5(strtolower($row_details['lname'])), "text"),
                                GetSQLValueString($row_details['stid'], "int"),
                                GetSQLValueString($jamb_no, "text"),                
                                GetSQLValueString($row_details['healthStatus'], "text"),
                                GetSQLValueString($row_details['Religion'], "text"),
                                GetSQLValueString($row_details['sponsorname'], "text"),
                                GetSQLValueString($row_details['sponsorphn'], "text"),
                                GetSQLValueString($row_details['sponsoradrs'], "text"));
        
    } else {
        $query = sprintf("Update student SET stdid = %s WHERE jambregid = %s",
                                GetSQLValueString($gen_matric, "text"),
                                GetSQLValueString($jamb_no, "text"));
    }    
        
    $result =  mysql_query($query, $tams) or die(mysql_error());
    
    if($result) {
        $query = sprintf("INSERT INTO registration VALUES(%s, %s, %s, %s, %s, %s)",
                        GetSQLValueString($gen_matric, "text"),
                        GetSQLValueString($sesid, "int"),
                        GetSQLValueString('Unregistered', "text"),
                        GetSQLValueString('Unregistered', "text"),
                        GetSQLValueString('FALSE', "text"),
                        GetSQLValueString($level, "int"));
        mysql_query($query, $tams);
                
        // update matric in the schfee_transaction table - for query of all paid students   
        $query_update = sprintf("UPDATE schfee_transactions SET matric_no = %s, scheduleid = %s WHERE ordid = %s",
                                 GetSQLValueString($gen_matric, "text"),
                                 GetSQLValueString($row_curSchedule['scheduleid'], "int"),
                                 GetSQLValueString($ordid, "text"));
        $update = mysql_query($query_update, $tams) or die(mysql_error());
    }else {
        $gen_matric = false;
    }
    
    return $gen_matric;
}

function audit_log($params) {
    global $tams;

    $params['status'] = isset($params['status']) ? $params['status'] : 'succeeded';
    $params['init'] = getSessionValue('stfid');
    $insertSQL = sprintf("INSERT INTO audit_log (initiator, entityid, entitytype, action, content, status) "
            . "VALUES (%s, %s, %s, %s, %s, %s)", GetSQLValueString($params['init'], "text"), GetSQLValueString($params['entid'], "text"), GetSQLValueString($params['enttype'], "text"), GetSQLValueString($params['action'], "text"), GetSQLValueString($params['cont'], "text"), GetSQLValueString($params['status'], "text"));
    mysql_query($insertSQL, $tams);
}

function getDeGrade($id) {
    $gradename = '';
    switch ($id) {
        case '1':
            $gradename = 'Distinction';
            break;
        case '2':
            $gradename = 'Upper-Credit';
            break;
        case '3':
            $gradename = 'Lower-Credit';
            break;
        case '4':
            $gradename = 'Merit';
            break;
        case '5':
            $gradename = 'Pass';
            break;
        default:
            $gradename = 'Fail';
            break;
    }

    return $gradename;
}

function getSessionValue($key) {
    if (array_key_exists($key, $_SESSION)) {
        return $_SESSION[$key];
    }
    return NULL;
}

function getName() {

    $name = "";
    if (isset($_SESSION['MM_Username']))
        $name = $_SESSION['fname'] . " " . $_SESSION['lname'];

    return $name;
}

function getStatusAlpha($status) {
    if ($status == "Compulsory")
        return "C";
    elseif ($status == "Elective")
        return "E";
    else
        return "R";
}

function getUploadState($state) {
    if (strtolower($state) == "no")
        return "Not Submitted";
    else
        return "Submitted";
}

function getApproveState($state) {
    if (strtolower($state) == "no")
        return "Not Approved";
    else
        return "Approved";
}

function getScore($test, $exam) {

    if ($test == "" && $exam == "") {
        return "-";
    }

    if ($test == "") {

        return $exam;
    }

    if ($exam == "") {

        return $test;
    }

    return $test + $exam;
}

function scoreValue($scoreValue) {
    $value;
    $value = ( $scoreValue == "" || $scoreValue == NULL ) ? "-" : $scoreValue;
    return $value;
}

function getRemark($score, $grade = 40) {

    if ($score == "-") {
        return "-";
    }

    $grade = ($score >= $grade) ? "P" : "F";
    return $grade;
}

function getRef($studid, $ses, $sem, $filter, $courses, $tams, $colid) {

    $query_rsrefs = sprintf("SELECT DISTINCT r.csid "
            . "FROM `result` r, department_course d, student s, grading g, course c "
            . "WHERE d.csid = r.csid "
            . "AND r.stdid = s.stdid "
            . "AND d.progid = s.progid "
            . "AND c.csid = r.csid "
            . "AND ((d.status = 'Required' AND tscore+escore < 30) "
            . "OR (d.status = 'Compulsory' AND tscore+escore <= g.gradeF) "
            . "OR (d.status = 'Elective' AND tscore IS NULL AND escore IS NULL) "
            . "OR (tscore IS NULL AND escore IS NULL)) "
            . "AND g.sesid = r.sesid "
            . "AND g.colid = %s "
            . "AND r.stdid = %s "
            . "AND (%s) "
            . "AND r.csid "
            . "NOT IN ("
            . "SELECT csid "
            . "FROM result "
            . "WHERE stdid = %s "
            . "AND sesid < %s AND tscore+escore > 39) "
            . "ORDER BY r.sesid ASC", GetSQLValueString($colid, "int"), GetSQLValueString($studid, "text"), GetSQLValueString($filter, "defined", $filter), GetSQLValueString($studid, "text"), GetSQLValueString($ses, "int"));
    $rsrefs = mysql_query($query_rsrefs, $tams) or die(mysql_error());
    $row_rsrefs = mysql_fetch_assoc($rsrefs);
    $totalRows_rsrefs = mysql_num_rows($rsrefs);

    $refs = [];

    for ($i = 0; $i < $totalRows_rsrefs; $i++, $row_rsrefs = mysql_fetch_assoc($rsrefs)) {
        $refs[$i] = $row_rsrefs['csid'];
    }

    $result['refs'] = implode(', ', $refs);

    return $result;
}

function statusMsg() {

    if (isset($_GET['success']))
        echo "<div class='alert alert-success'>The action completed successfully!\n </div>";
    elseif (isset($_GET['error']))
        echo "<div class='alert alert-danger'>The action could not be completed!\n</div>";
}

function getSex($char) {
    if (strtolower($char) == "m")
        return "Male";
    else
        return "Female";
}

function getLevel($student, $db) {

    $query = sprintf("SELECT level from student WHERE stdid=%s", GetSQLValueString($student, "text"));
    $query_level = mysql_query($query, $db) or die(mysql_error());
    $query_result = mysql_fetch_assoc($query_level);

    return $query_result['level'];
}

function getSemester($scode) {
    if (strtolower($scode) == "s")
        return "Second";
    else
        return "First";
}

function createFilter($type) {
    $filterQuery = "";
    if ($type == "lect") {
        if (isset($_GET['did']))
            $filterQuery = sprintf("SELECT title, lectid, fname, lname, mname, email FROM lecturer WHERE deptid = %s", GetSQLValueString($_GET['did'], "int"));
        elseif (isset($_GET['cid']))
            $filterQuery = sprintf("SELECT title, lectid, fname, lname, mname, email FROM lecturer, department WHERE lecturer.deptid = department.deptid AND department.colid = %s", GetSQLValueString($_GET['cid'], "int"));
    }

    if ($type == "stud") {
        if (isset($_GET['lvl'])) {
            $filterQuery = sprintf("SELECT * FROM student WHERE status='Undergrad' AND stdid=%s", GetSQLValueString("0", "int"));

            if (isset($_GET['pid']))
                $filterQuery = sprintf("SELECT s.stdid, s.fname, s.lname, p.deptid FROM student s, programme p WHERE s.status='Undergrad' AND p.progid = s.progid AND p.progid = %s AND level=%s ORDER BY stdid ASC", GetSQLValueString($_GET['pid'], "int"), GetSQLValueString($_GET['lvl'], "int"));
        }
        elseif (isset($_GET['pid'])) {
            $filterQuery = sprintf("SELECT * FROM student s, programme p WHERE s.status='Undergrad' AND p.progid = s.progid AND p.progid = %s", GetSQLValueString($_GET['pid'], "int"));
        }
        /* elseif( isset($_GET['cid']) )
          $filterQuery = sprintf("SELECT fname, lname FROM lecturer, department WHERE lecturer.deptid = department.deptid AND department.colid = %s", GetSQLValueString($_GET['cid'], "int")); */
    }

    if ($type == "course") {
        if (isset($_GET['did']))
            $filterQuery = sprintf("SELECT csid, csname, catname FROM course, category WHERE course.catid = category.catid AND deptid = %s", GetSQLValueString($_GET['did'], "int"));
        elseif (isset($_GET['cid']))
        /* $filterQuery = sprintf("SELECT csid, csname, catname FROM course, category, department WHERE course.catid = category.catid AND course.deptid = department.deptid AND department.colid = %s", GetSQLValueString($_GET['cid'], "int")) */
            ;
    }


    return $filterQuery;
}

function getLogin() {

    $login = false;
    if (isset($_SESSION['MM_Username'])) {
        $login = true;
    }

    return $login;
}

function getAccess() {

    $access = "";
    if (getLogin())
        $access = $_SESSION['MM_UserGroup'];

    return $access;
}

function doLogout($site_root) {

    //to fully log out a visitor we need to clear the session varialbles
    $_SESSION['MM_Username'] = NULL;
    $_SESSION['MM_UserGroup'] = NULL;
    $_SESSION['PrevUrl'] = NULL;
    $_SESSION['Username'] = NULL;
    $_SESSION['Access'] = NULL;
    session_destroy();
    $logoutGoTo = ( getAccess() == 1) ? "/" . $site_root . "/admin/index.php" : "/" . $site_root . "/index.php";
    if ($logoutGoTo) {
        header("Location: $logoutGoTo");
        exit;
    }
}

//Performs login for all types of users
function doLogin($user, $loginUsername, $password) {
    global $tams;
    $password = ($user == 1) ? $password : md5($password);
    $MM_redirectLoginSuccess = ( $user > 2 ) ? "staff/profile.php" : "student/profile.php";
    $MM_redirectLoginFailed = "login.php";
    $LoginRS__query = "";

    //Prospective students login logic
    if ($user == 1) {
        $LoginRS__query = sprintf("SELECT formnum, pstdid, fname, lname, mname, jambregid, access, formsubmit, "
                . "formpayment, progid1, progid2, progoffered, sn.sesid, sn.sesname, at.*, regtype, "
                . "a.admid "
                . "FROM prospective p "
                . "LEFT JOIN admissions a ON a.admid = p.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                . "LEFT JOIN session sn ON a.sesid = sn.sesid "
                . "LEFT JOIN programme pr ON p.progoffered = pr.progid "
                . "LEFT JOIN department d ON pr.deptid = d.deptid "
                . "LEFT JOIN college c ON c.colid = d.colid "
                . "WHERE jambregid = %s "
                . "AND lname = %s", GetSQLValueString($loginUsername, "text"), 
                GetSQLValueString($password, "text"));
    }

    //Returning students login logic
    elseif ($user == 2) {

        $LoginRS__query = sprintf("SELECT stdid, fname, lname, mname, phone, level, password, s.progid, "
                . "p.deptid, d.colid, c.special, access, sn.sesid, sn.sesname, at.*, a.admid "
                . "FROM student s "
                . "LEFT JOIN admissions a ON a.admid = s.admid "
                . "LEFT JOIN admission_type at ON a.typeid = at.typeid "
                . "LEFT JOIN session sn ON s.sesid = sn.sesid "
                . "LEFT JOIN programme p ON s.progid = p.progid "
                . "LEFT JOIN department d ON p.deptid = d.deptid "
                . "LEFT JOIN college c ON c.colid = d.colid "
                . "WHERE stdid = %s "
                . "AND password = %s", GetSQLValueString($loginUsername, "text"), 
                GetSQLValueString($password, "text"));
    }

    //Staff and admin login logic.
    // $user = 4: refers to admin user logging in from the admin dedicated login interface.
    elseif ($user == 3 || $user == 4) {
        $admin = "";
        
        if ($user == 4)
            $admin = "AND access = 1";
        
        $LoginRS__query = sprintf("SELECT lectid, fname, phone, lname, mname, password, access, c.colid, "
                . "special, l.deptid "
                . "FROM lecturer l "
                . "LEFT JOIN department d ON l.deptid = d.deptid "
                . "LEFT JOIN college c ON d.colid = c.colid "
                . "WHERE lectid = %s "
                . "AND password = %s %s", GetSQLValueString($loginUsername, "text"), 
                GetSQLValueString($password, "text"), 
                GetSQLValueString($admin, "defined", $admin));
    }
    
    elseif ($user == 5) {
        $LoginRS__query = sprintf("SELECT * "
                . "FROM ictstaff "
                . "WHERE stfid = %s "
                . "AND password = %s", GetSQLValueString($loginUsername, "text"), 
                GetSQLValueString($password, "text"));
    }

    $LoginRS = mysql_query($LoginRS__query, $tams) or die(mysql_error());
    $loginFoundUser = mysql_num_rows($LoginRS);
    if ($loginFoundUser) {

        $loginStrGroup = mysql_result($LoginRS, 0, 'access');
        $fname = mysql_result($LoginRS, 0, 'fname');
        $lname = mysql_result($LoginRS, 0, 'lname');
        $mname = mysql_result($LoginRS, 0, 'mname');
        $phone = ($user > 1) ? mysql_result($LoginRS, 0, 'phone') : "";
        
        $_SESSION['admid'] = NULL;
        $_SESSION['admname'] = NULL;
        
        $ses_query = sprintf("SELECT * "
                . "FROM session "
                . "WHERE status = 'TRUE' "
                . "OR admission = 'TRUE'");
        $session = mysql_query($ses_query, $tams);
        $totalRows_session = mysql_num_rows($session);

        if($totalRows_session > 0) {
            while($row_session = mysql_fetch_assoc($session)) {
                if($row_session['status'] == 'TRUE') {
                    $_SESSION['sesid'] = $row_session['sesid'];
                    $_SESSION['sesname'] = $row_session['sesname'];
                    $_SESSION['epass'] = $row_session['epass'];
                }

                if($row_session['admission'] == 'TRUE') {
                    $_SESSION['admid'] = $row_session['sesid'];
                    $_SESSION['admname'] = $row_session['sesname'];
                }
            }
        }
 
//        $set_query = sprintf("SELECT * "
//                . "FROM settings "
//                . "WHERE name IN ('active')");
//        $setting = mysql_query($set_query, $tams);
//        $totalRows_setting = mysql_num_rows($setting);
//        
//        if($totalRows_setting > 0) {
//            
//        }
        
        //Session values based on "who"
        switch ($user) {
            case 1:
                $MM_redirectLoginSuccess = 'admission/termsandcon.php';
                $_SESSION['stid'] = $_SESSION['uid'] = mysql_result($LoginRS, 0, 'jambregid');
                $_SESSION['admid'] = $_SESSION['stdses'] = mysql_result($LoginRS, 0, 'sesid');
                $_SESSION['admname'] = $_SESSION['stdsesname'] = mysql_result($LoginRS, 0, 'sesname');
                $_SESSION['pid'] = mysql_result($LoginRS, 0, 'progoffered');
                $_SESSION['pid1'] = mysql_result($LoginRS, 0, 'progid1');
                $_SESSION['pid2'] = mysql_result($LoginRS, 0, 'progid2');
                $_SESSION['admode'] = mysql_result($LoginRS, 0, 'typename');
                $_SESSION['regmode'] = mysql_result($LoginRS, 0, 'regtype');
                $_SESSION['admtype'] = mysql_result($LoginRS, 0, 'typeid');
                $_SESSION['form_submit'] = mysql_result($LoginRS, 0, 'formpayment');
                $_SESSION['form_payment'] = mysql_result($LoginRS, 0, 'formsubmit');
                $_SESSION['accttype'] = 'pros';
                
                if(strtolower($_SESSION['form_payment']) == 'yes') {
                    $MM_redirectLoginSuccess = 'admission/status.php';
                }
                
                break;
            case 2:
                $_SESSION['stid'] = $_SESSION['uid'] = mysql_result($LoginRS, 0, 'stdid');
                $_SESSION['stdses'] = mysql_result($LoginRS, 0, 'sesid');
                $_SESSION['stdsesname'] = mysql_result($LoginRS, 0, 'sesname');
                $_SESSION['pid'] = mysql_result($LoginRS, 0, 'progid');
                $_SESSION['did'] = mysql_result($LoginRS, 0, 'deptid');
                $_SESSION['cid'] = mysql_result($LoginRS, 0, 'colid');
                $_SESSION['level'] = mysql_result($LoginRS, 0, 'level');
                $_SESSION['admode'] = mysql_result($LoginRS, 0, 'typename');
                $_SESSION['admtype'] = mysql_result($LoginRS, 0, 'typeid');
                $_SESSION['accttype'] = 'stud';
                break;
            case 3:
                $_SESSION['lectid'] = $_SESSION['uid'] = mysql_result($LoginRS, 0, 'lectid');
                $_SESSION['did'] = mysql_result($LoginRS, 0, 'deptid');
                $_SESSION['cid'] = mysql_result($LoginRS, 0, 'colid');
                $_SESSION['accttype'] = 'staff';
                break;
            case 4:
                $_SESSION['accttype'] = 'admin';
                break;
            
            case 5:
                $_SESSION['uid'] = mysql_result($LoginRS, 0, 'stfid');
                $_SESSION['accttype'] = 'admin';
                
                $MM_redirectLoginSuccess = "dashboard.php";
                
                switch($loginStrGroup) {
                	case '21': 
                		break;
                		
                	
                	case '22': 
                		break;
                		
                	
                	case '23': 
                		$MM_redirectLoginSuccess = "bursary/index.php";
                		break;
                	
                	case '24': 
                		$MM_redirectLoginSuccess = "admission/index.php";
                		break;
                }

                
        }

        if (PHP_VERSION >= 5.1) {
            session_regenerate_id(true);
        } else {
            session_regenerate_id();
        }

        // Store user information in session
        $_SESSION['MM_Username'] = $loginUsername;
        $_SESSION['MM_UserGroup'] = $loginStrGroup;
        $_SESSION['fname'] = $fname;
        $_SESSION['lname'] = $lname;
        $_SESSION['mname'] = $mname;
        $_SESSION['phone'] = $phone;

        if (isset($_GET['accesscheck'])) {
            $MM_redirectLoginSuccess = urldecode($_GET['accesscheck']);
        }
        
        header("Location: " . $MM_redirectLoginSuccess);
        exit;
    } else {
        return true;
    }
}

/**
 * this function fetch the relative part of a given image 
 * 
 * @param string $image_name
 * @param string $dir
 * @return string relative url 
 */
function get_pics($image_name, $dir, $url = TRUE) {
   
    $image_url = IMGPATH."/user/profile.png";

    $image = [
                "{$dir}/" . strtoupper($image_name) . ".jpg",
                "{$dir}/" . strtolower($image_name) . ".jpg",
                "{$dir}/" . strtoupper($image_name) . ".JPG",
                "{$dir}/" . strtolower($image_name) . ".JPG",
                "{$dir}/" . strtoupper($image_name) . ".png",
                "{$dir}/" . strtolower($image_name) . ".png",
                "{$dir}/" . strtoupper($image_name) . ".PNG",
                "{$dir}/" . strtolower($image_name) . ".PNG",
                "{$dir}/" . strtoupper($image_name) . ".gif",
                "{$dir}/" . strtolower($image_name) . ".gif",
                "{$dir}/" . strtoupper($image_name) . ".GIF",
                "{$dir}/" . strtolower($image_name) . ".GIF"
            ];

    for ($idx = 0; $idx < count($image); $idx++) {
        if (realpath($image[$idx])) {
            $image_url = $image[$idx];            
            break;
        }
    }
        
    if ($url){        
        $folder_path = explode('/', substr($image_url, strpos($image_url, 'img')));
        unset($folder_path[0]);
        $image_url = SITEIMGURL . '/' . implode('/', $folder_path);
    }
    
    return $image_url;
}


function uploadFile($location, $type, $size, $id = "") {

    $result = "";
    // replace any spaces in original filename with underscores
    $file = "";
    $ext = strtolower(substr($_FILES['filename']['name'], strrpos($_FILES['filename']['name'], '.')));
    if ($type == "upload") {
        $file = $_FILES['filename']['name'];
    } elseif ($type == "news") {
        $file = "news_" . $id . $ext;
    } elseif ($type == "student") {
        $file = (!isset($_GET['stid']) ) ? $_SESSION['uid'] . $ext : $_GET['stid'] . $ext;
    } elseif ($type == "prospective") {
        $file = !isset($_GET['jambregid']) ? strtoupper($_SESSION['uid']) . $ext : strtoupper($_GET['jambregid']) . $ext;
    } else {
        $file = (!isset($_GET['lid']) ) ? $_SESSION['lid'] . $ext : $_GET['lid'] . $ext;
    }
    
    // create an array of permitted image MIME types
    $permittedImage = ['image/gif', 'image/jpeg', 'image/png'];

    // upload if file is OK
    if ((in_array($_FILES['filename']['type'], $permittedImage) || $type == 'upload') && 
            $_FILES['filename']['size'] > 0 && $_FILES['filename']['size'] <= $size) {
        switch ($_FILES['filename']['error']) {
            case 0: {
                    // move the file to the upload folder and rename it
                     $success = move_uploaded_file($_FILES['filename']['tmp_name'], $location . $file);
                    if ($type != 'upload') {
                        $resizeObj = new resize($location . $file);
                        $resizeObj->resizeImage(150, 150, 'auto');
                        $success = $resizeObj->saveImage($location . $file, 100);
                    }
//$success = move_uploaded_file($_FILES['filename']['tmp_name'],$location.$file);
                }

                if ($success) {
                    $result = "$file uploaded successfully.";
                } else {
                    $result = "Error uploading $file. Please try again.";
                }

                break;
            case 3:
            case 6:
            case 7:
            case 8:
                $result = "Error uploading $file. Please try again.";
                break;
            case 4:
                $result = "You didn't select a file to be uploaded.";
        }
    } else {
        $result = "$file is either too big or not an appropriate file type.";
    }
    return $result;
}

/* function uploadImage($file, $loc){
  $resizeObj = new resize($file);
  $resizeObj -> resizeImage(200, 200, 'crop');
  $resizeObj -> saveImage($loc."ade.jpeg", 100);

  } */
# ========================================================================#
#
   #  Author:    Jarrod Oberto
#  Version:	 1.0
#  Date:      17-Jan-10
#  Purpose:   Resizes and saves image
#  Requires : Requires PHP5, GD library.
#  Usage Example:
#                     include("classes/resize_class.php");
#                     $resizeObj = new resize('images/cars/large/input.jpg');
#                     $resizeObj -> resizeImage(150, 100, 0);
#                     $resizeObj -> saveImage('images/cars/large/output.jpg', 100);
#
   #
   # ========================================================================#

class resize {

    // *** Class variables
    private $image;
    private $width;
    private $height;
    private $imageResized;

    function __construct($fileName) {
        // *** Open up the file
        $this->image = $this->openImage($fileName);

        // *** Get width and height
        $this->width = imagesx($this->image);
        $this->height = imagesy($this->image);
    }

    ## --------------------------------------------------------

    private function openImage($file) {
        // *** Get extension
        $extension = strtolower(strrchr($file, '.'));

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                $img = imagecreatefromjpeg($file);
                break;
            case '.gif':
                $img = imagecreatefromgif($file);
                break;
            case '.png':
                $img = imagecreatefrompng($file);
                break;
            default:
                $img = false;
                break;
        }
        return $img;
    }

    ## --------------------------------------------------------

    public function resizeImage($newWidth, $newHeight, $option = "auto") {
        // *** Get optimal width and height - based on $option
        $optionArray = $this->getDimensions($newWidth, $newHeight, $option);

        $optimalWidth = $optionArray['optimalWidth'];
        $optimalHeight = $optionArray['optimalHeight'];


        // *** Resample - create image canvas of x, y size
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);


        // *** if option is 'crop', then crop too
        if ($option == 'crop') {
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
        }
    }

    ## --------------------------------------------------------

    private function getDimensions($newWidth, $newHeight, $option) {

        switch ($option) {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    ## --------------------------------------------------------

    private function getSizeByFixedHeight($newHeight) {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }

    private function getSizeByFixedWidth($newWidth) {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }

    private function getSizeByAuto($newWidth, $newHeight) {
        if ($this->height < $this->width) {
        // *** Image to be resized is wider (landscape)
            $optimalWidth = $newWidth;
            $optimalHeight = $this->getSizeByFixedWidth($newWidth);
        } elseif ($this->height > $this->width) {
        // *** Image to be resized is taller (portrait)
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight = $newHeight;
        } else {
        // *** Image to be resizerd is a square
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight = $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight = $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight = $newHeight;
            }
        }

        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    ## --------------------------------------------------------

    private function getOptimalCrop($newWidth, $newHeight) {

        $heightRatio = $this->height / $newHeight;
        $widthRatio = $this->width / $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth = $this->width / $optimalRatio;

        return ['optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight];
    }

    ## --------------------------------------------------------

    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight) {
        // *** Find center - this will be used for the crop
        $cropStartX = ( $optimalWidth / 2) - ( $newWidth / 2 );
        $cropStartY = ( $optimalHeight / 2) - ( $newHeight / 2 );

        $crop = $this->imageResized;
        //imagedestroy($this->imageResized);
        // *** Now crop from center to exact requested size
        $this->imageResized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($this->imageResized, $crop, 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight, $newWidth, $newHeight);
    }

    ## --------------------------------------------------------

    public function saveImage($savePath, $imageQuality = "100") {
        // *** Get extension
        $extension = strrchr($savePath, '.');
        $extension = strtolower($extension);

        switch ($extension) {
            case '.jpg':
            case '.jpeg':
                if (imagetypes() & IMG_JPG) {
                    imagejpeg($this->imageResized, $savePath, $imageQuality);
                }
                return true;
                break;

            case '.gif':
                if (imagetypes() & IMG_GIF) {
                    imagegif($this->imageResized, $savePath);
                }
                return true;
                break;

            case '.png':
                // *** Scale quality from 0-100 to 0-9
                $scaleQuality = round(($imageQuality / 100) * 9);

                // *** Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - $scaleQuality;

                if (imagetypes() & IMG_PNG) {
                    imagepng($this->imageResized, $savePath, $invertScaleQuality);
                }
                return true;
                break;

            // ... etc

            default:
                // *** No extension - No save.
                return false;
                break;
        }

        imagedestroy($this->imageResized);
    }

}

if (!function_exists("GetSQLValueString")) {

    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
  if (PHP_VERSION < 6) {
    $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
  }

  $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

  switch ($theType) {
    case "text":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;    
    case "long":
    case "int":
      $theValue = ($theValue != "") ? intval($theValue) : "NULL";
      break;
    case "double":
      $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
      break;
    case "date":
      $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
      break;
    case "defined":
      $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
      break;
  }
  return $theValue;
}
}


/**
 * This class Handle all Notification messages
 */
class Notification {
    
     private $messages;
   
    
    private function set_message($message, $status){
        
       //unset($this->messages);
       
       $this->messages['msg'][] = $message;
       $this->messages['status'] = $status;
       
      
       
    }
    
    private function get_message(){
        
        return $this->messages;
    }
    
    
    function set_notification($msg, $status ){
        if($msg){
            $this->set_message($msg,$status);
        }
        
    }
    
    function get_notification(){
        
        $final_message = "";
        
        $msg =  $this->get_message();
        
        if(is_array($msg['msg'])){
            foreach($msg['msg'] AS $m){
                $final_message .= "<li>{$m}</li>";
            }  
        }
        else{
            $final_message = $msg;
        }
        
        $final['msg'] = $final_message;
        $final['type'] = $msg['status'];
        
        return $final;
    }
}

$notification = new Notification();