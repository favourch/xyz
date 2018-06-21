<?php

require_once('../../path.php');

$url_part = '';

if (isset($_GET['action'])) {
    $url_part = $_GET['action'];
}

$post = '';
if (isset($_POST)) {
    $post = json_decode(file_get_contents('php://input'), TRUE);
}

$data = array();

switch ($url_part) {

    case 'result' :
        $query = sprintf("SELECT * FROM result "
                . "WHERE stdid = %s "
                . "AND csid = %s "
                . "AND sesid = %s", 
                GetSQLValueString($post['student'], 'text'),
                GetSQLValueString($post['course'], 'text'), 
                GetSQLValueString($post['session'], 'int'));
        $result = mysql_query($query, $tams) or die(mysql_error());
        $row_result = mysql_fetch_assoc($result);
        $totalRows_result = mysql_num_rows($result);

        if ($totalRows_result > 0) {
            $data['status'] = 'success';
            $data['rs'] = $row_result;
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
        echo json_encode($data);

        break;

    case 'course' :
        $query = sprintf("SELECT csid, csname FROM course "
                . "WHERE  csid = %s ",
                GetSQLValueString($post['course'], 'text'));
        $result = mysql_query($query, $tams) or die(mysql_error());
        $row_result = mysql_fetch_assoc($result);
        $totalRows_result = mysql_num_rows($result);

        if ($totalRows_result > 0) {
            $data['status'] = 'success';
            $data['rs'] = $row_result;
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }
        echo json_encode($data);
        break;

    default :
        break;
}