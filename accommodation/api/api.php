<?php

require_once('../../path.php');

$accomodationSQL = sprintf("SELECT * FROM accom_accomodation ");
$accomodation = mysql_query($accomodationSQL, $tams) or die(mysql_error());
$row_accomodation = mysql_fetch_assoc($accomodation);

$accom = array();
for(;$row_accomodation = mysql_fetch_assoc($accomodation);):
    $accom[] = $row_accomodation;
endfor;


echo json_encode($accom);

$url_part = '';
if(isset($_GET['action'])){
    $url_part = $_GET['action'];
}

$post = '';
if(isset($_POST)){
    $post = json_decode(file_get_contents('php://input'), TRUE);
}

$data = array();
switch ($url_part) {
    case 'building' :
        $query = sprintf("SELECT * FROM accom_accomodation "
                        . "WHERE location = %s "
                        . "AND building_name LIKE %s ", 
                        GetSQLValueString($post['location'], 'int'),
                        GetSQLValueString($post['building_name'], 'text'));
        $accomodation = mysql_query($query, $tams) or die(mysql_error());
        $row_accom = mysql_fetch_assoc($accomodation);
        $totalRows_accom = mysql_num_rows($accomodation);
        
        if ($totalRows_accom > 0) {
            $data['status'] = 'success';
            $data['rs'] = $row_accom;
        } else {
            $data['status'] = 'error';
            $data['msg'] = 'No Record Found';
        }

        echo json_encode($data);



        break;


    default :

        break;
}