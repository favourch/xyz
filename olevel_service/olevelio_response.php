<?php
require_once('../path.php');

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");         

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}



if(isset($_POST)){
    $post = file_get_contents('php://input');
}

$post_array = json_decode($post, TRUE);

//print_r($post_array['result']);
//die();

$result_json = $post;
$exam_name = $post_array['result']['exam_name'];
$exam_number = $post_array['result']['exam_number'];
$exam_type = $post_array['result']['exam_type'];
$exam_year = $post_array['result']['exam_year'];
$card_sn = $post_array['result']['card_sn'];
$card_pin = $post_array['result']['card_pin'];
$result_id = $post_array['result_id'];


$olevelResultSQL  = sprintf("UPDATE olevel_veri_data "
                          . "SET exam_name = %s, exam_no = %s,  "
                          . "exam_type = %s, exam_year = %s, "
                          . "result_plain = %s,  card_no = %s, card_pin = %s, approve = 'Yes', date_fetched = NOW() WHERE id = %s ", 
                          GetSQLValueString($exam_name, 'text'),
                          GetSQLValueString($exam_number, 'text'),
                          GetSQLValueString($exam_type, 'text'),
                          GetSQLValueString($exam_year, 'text'),
                          GetSQLValueString($result_json, 'text'),
                          GetSQLValueString($card_sn, 'text'),
                          GetSQLValueString($card_pin, 'text'),
                          GetSQLValueString($result_id, 'text')); 
mysql_query($olevelResultSQL, $tams) or die(mysql_error());

echo true;

?>