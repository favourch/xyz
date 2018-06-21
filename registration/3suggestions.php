<?php  ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}

require_once('../path.php');


if (!function_exists("GetSQLValueString")) {
function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") 
{
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

if(isset($_GET['q']) && isset($_GET['l'])) {
    
    mysql_select_db($database_tams, $tams);

    $query_sess = "SELECT * FROM `session` ORDER BY sesname DESC LIMIT 0,1";
    $sess = mysql_query($query_sess, $tams) or die(mysql_error());
    $row_sess = mysql_fetch_assoc($sess);
    $totalRows_sess = mysql_num_rows($sess);

    $colname_stud = "-1";
    if (isset($_SESSION['stid'])) {
      $colname_stud = $_SESSION['stid'];
    }

    if (isset($_GET['stid'])) {
      $colname_stud = $_GET['stid'];
    }

    $levels = [];
    
    for($idx = 1; $idx <= $_GET['l']; $idx++) {
        array_push($levels, $idx);
    }
        
    
    $query = "%".urldecode($_GET['q'])."%";
    /**
        Used to generate suggestions
    */
    $query_suggestion = sprintf("SELECT csid, status, csname, unit "
                            . "FROM course c "
                            . "WHERE (deptid <> %s OR catid NOT IN(3,4,5,8)) "
                            . "AND (csid LIKE %s OR csname LIKE %s) "            
                            . "AND level IN(%s)"
                            . "AND csid NOT IN ( SELECT csid "
                            . "FROM result "
                            . "WHERE stdid = %s "
                            . "AND sesid = %s LIMIT 10)",
                            GetSQLValueString(getSessionValue('did'), "int"),
                            GetSQLValueString($query, "text"),
                            GetSQLValueString($query, "text"),                            
                            GetSQLValueString(" ", "defined", implode(',', $levels)),
                            GetSQLValueString($colname_stud, "text"),
                            GetSQLValueString($row_sess['sesid'], "int"));
    $suggestion = mysql_query($query_suggestion, $tams) or die(mysql_error());
    $row_suggestion = mysql_fetch_assoc($suggestion);
    $totalRows_suggestion = mysql_num_rows($suggestion);

    $suggested = array();
    for($idx = 0 ; $totalRows_suggestion > $idx; $idx++, $row_suggestion = mysql_fetch_assoc($suggestion)) {
        $row_suggestion['registered'] = false;
        $row_suggestion['selected'] = false;
        $row_suggestion['removed'] = false;
        $suggested[] = $row_suggestion;
    }
    echo json_encode($suggested);
    
}else {
    echo json_encode(array());
}



