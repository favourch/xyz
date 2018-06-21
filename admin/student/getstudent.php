<?php 
if (!isset($_SESSION)) {
    session_start();
}

require_once('../../path.php');

$auth_users = "1,20,28";
check_auth($auth_users, $site_root.'/admin');

$id = null;
$status_msg = 'Invalid matric. no. submitted!';

if(isset($_GET['stid'])) {
    $id = $_GET['stid'];
    $status_msg = 'No registration entries found!';    
}


$query_reg = sprintf("SELECT st.progid as prog, p.progname, s.sesname, r.* "
                    . "FROM registration r "
                    . "JOIN programme p ON r.progid = p.progid "
                    . "JOIN session s ON r.sesid = s.sesid "
                    . "JOIN student st ON r.stdid = st.stdid "
                    . "WHERE r.stdid = %s", GetSQLValueString($id, "text"));
$reg = mysql_query($query_reg, $tams) or die(mysql_error());
$totalRows_reg = mysql_num_rows($reg);

if($totalRows_reg > 0) {
    $reg_data = [];
    
    for($idx = 0; $row_reg = mysql_fetch_assoc($reg); $idx++) {
        $row_reg['isNew'] = false;
        $row_reg['deleted'] = false;
        $row_reg['updated'] = false;
        $reg_data[] = $row_reg;
    }
    
    echo json_encode(["status" => true, "status_msg" => 'Registration entries found!', "entries" => $reg_data]);
    return;
}

echo json_encode(["status" => false, "status_msg" => $status_msg]);