<?php


if (!isset($_SESSION)) {
    session_start();
}

require_once('../path.php');



$MM_authorizedUsers = "10";

$MM_donotCheckaccess = "false";

fillAccomDetails($site_root, $tams);

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

$MM_restrictGoTo = "../index.php";

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

if(isset($_GET['check_status'])) {
    $ord_id = $_GET['check_status'];
    $table = $_GET['check_type'];
    $response = checkPaymentStatus($ord_id, $merchant_id, $table, $tams); 
    $notification->set_notification($response['msg'], $response['status']);
}

$page_title = "Tasued";

$msg = null;
$search_term = null;
$type = 'reg';
$pType = 'schfee';

$payType = array(
    "schfee" => "School Fees",
    "accfee" => "Acceptance Fees",
    "appfee" => "Application Fees",
    "jmbfee" => "Jamb Regul. Fees",
    "clrfee" => "Clearance Fees",
    "repfee" => "Reparation Fees",
    "olevelfee" => "O'level Fees"
);


function query_param($type, $pType) {
    
    $param['table'] = 'schfee_transactions';
        
    switch($type) {
        case 'pros':
            $param['extra_filter'] = "JOIN session ses ON ses.sesid = s.sesid";
            $param['column'] = "ses.sesname,";
            
            if ($pType == 'accfee') {
                $param['table'] = 'accfee_transactions';
            } elseif ($pType == 'appfee') {
                $param['table'] = 'appfee_transactions';
            } elseif ($pType == 'olevelfee') {
                $param['table'] = 'olevelverifee_transactions';
                $param['$extra_filter'] = '';
                $param['column'] = '';
            }
            break;
            
        case 'reg':

            $param['field'] = 'matric_no';
            $param['select'] = 'c.level';
            $param['condition'] = 'c.sesid';

            if ($pType == 'jmbfee') {
                $param['table'] = 'jambregul_transactions';
            } elseif ($pType == 'clrfee') {
                $param['table'] = 'clearance_transactions';
            } elseif ($pType == 'repfee') {
                $param['table'] = 'reparation_transactions';
            } elseif ($pType == 'olevelfee') {
                $param['table'] = 'olevelverifee_transactions';
                $param['field'] = 'can_no';
                $param['select'] = 'NULL';
                $param['condition'] = 's.sesid';
            }
    }
    
    return $param;
}

/*
function curl_request($merchant_id, $xml) {
     $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://196.46.20.33:5444/Exec");
    curl_setopt($ch, CURLOPT_VERBOSE, '1');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5000);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '1');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, '1');
    curl_setopt($ch, CURLOPT_CAINFO, CERTPATH . '/CAcert.crt');
    curl_setopt($ch, CURLOPT_SSLCERT, CERTPATH . "/{$merchant_id}.pem");
    curl_setopt($ch, CURLOPT_SSLKEY, CERTPATH . "/{$merchant_id}.key");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: text/xml']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    
    $response = curl_exec($ch);
    if (curl_errno($ch) > 0) {
        throw new Exception(curl_error($ch));
    }
    
    curl_close($ch);
    
    return $response;
}

function get_payload($merchant_id, $order_id, $session_id) {
    
    return sprintf("<?xml version='1.0' encoding='UTF-8'?>
        <TKKPG>
            <Request>
                <Operation>GetOrderStatus</Operation>
                <Language>EN</Language>
                <Order>
                    <Merchant>%s</Merchant>
                    <OrderID>%s</OrderID>
                </Order>
                <SessionID>%s</SessionID>
            </Request>
        </TKKPG>", $merchant_id, $order_id, $session_id);
}

function check_response($response) {
     $parsedxml = simplexml_load_string($response);

     $order_id = '';
     $order_status = '';
     $status = '';
     
    foreach($parsedxml->children() as $root_node) {
        foreach($root_node->children() as $response_node) {
            if ($response_node->getName() == "Status") {
                $status = $response_node;
            }

            foreach($response_node->children() as $child) {
                if ($child->getName() === "OrderID") {
                    $order_id = $child;
                }

                if ($child->getName() == "OrderStatus") {
                    $order_status = $child;
                }
            }
        }
    }
    
    if($status == '00' && in_array($order_status, ['APPROVED', 'DECLINED', 'CANCELED'])) {
        return ['order_id' => $order_id, 'order_status' => $order_status];
    }
    
    throw new Exception('Order status is the same or request was not successful!');
}
*/

if (isset($_GET['search'])) { // && $_GET['search'] != NULL) {
    $query = $_GET['search'];
    $search_term = "%{$query}%";
    $type = isset($_GET['type']) ? $_GET['type'] : $type;
    $pType = isset($_GET['ptype']) ? $_GET['ptype'] : $pType;
    $params = query_param($type, $pType);
        
    if ($_GET['search'] != '') {
        
        switch ($type) {
            case 'pros':
                $query_rsstdnt = sprintf("SELECT s.ordid, s.sessionid, %s '-' AS level, '-' AS sesname, s.amt, "
                                        . "s.date_time, s.status, p.jambregid as stdid, p.lname, p.fname, s.percentPaid, "
                                        . "%s, TIMESTAMP(date_time) as timestamp_date "
                                        . "FROM %s s "
                                        . "LEFT JOIN prospective p ON p.jambregid = s.can_no %s "
                                        . "WHERE ordid IS NOT NULL "
                                        . "AND (p.lname LIKE %s "
                                        . "OR p.fname LIKE %s "
                                        . "OR s.ordid LIKE %s "                                  
                                        . "OR s.reference LIKE %s "
                                        . "OR p.jambregid LIKE %s)", 
                                        GetSQLValueString($params['column'], "defined", $params['column']),
                                        GetSQLValueString("date_param", "defined", "STR_TO_DATE(date_time, '%d/%m/%Y') as converted_date"), 
                                        GetSQLValueString($params['table'], "defined", $params['table']), 
                                        GetSQLValueString($params['extra_filter'], "defined", $params['extra_filter']), 
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"), 
                                        GetSQLValueString($search_term, "text"));
                break;

            case 'reg':            	
                $query_rsstdnt = sprintf("SELECT c.ordid, c.sessionid, c.amt, ses.sesname, c.date_time, c.status, "
                                        . "s.stdid, s.lname, s.fname, c.percentPaid, %s as level, "
                                        . "%s, TIMESTAMP(date_time) as timestamp_date "
                                        . "FROM %s c "                                        
                                        . "LEFT JOIN student s ON s.stdid = c.%s JOIN session ses ON ses.sesid = %s "                                        
                                        . "AND (s.lname LIKE %s "
                                        . "OR c.ordid LIKE %s "                                        
                                        . "OR c.reference LIKE %s "
                                        . "OR s.fname LIKE %s "
                                        . "OR s.stdid LIKE %s)"
                                        . "WHERE ordid IS NOT NULL ", 
                                        GetSQLValueString($params['select'], "defined", $params['select']),
                                        GetSQLValueString("date_param", "defined", "STR_TO_DATE(date_time, '%d/%m/%Y') as converted_date"),
                                        GetSQLValueString($params['table'], "defined", $params['table']), 
                                        GetSQLValueString($params['field'], "defined", $params['field']), 
                                        GetSQLValueString($params['condition'], "defined", $params['condition']), 
                                        GetSQLValueString($search_term, "text"),                                        
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"), 
                                        GetSQLValueString($search_term, "text")); 
                break;


            default:
                break;
        }
        
        if (isset($_GET['action']) && $_GET['action'] == 'requery') {
            $query_status = $query_rsstdnt . " AND (STR_TO_DATE(date_time, '%d/%m/%Y') > '2016-12-12' OR TIMESTAMP(date_time) > '2016-12-12')";
            $status = mysql_query($query_status, $tams) or die(mysql_error());            
            $totalRows_status = mysql_num_rows($status);
            
            $update_param = ['column' => '`status` = CASE ', 'order_ids' => []];
            
            if ($totalRows_status > 0) {
                for (; $row_status = mysql_fetch_assoc($status);) {
                    $payload = get_payload($merchant_id, $row_status['ordid'], $row_status['sessionid']);

                    try {
                        $response = curl_request($merchant_id, $payload);
                        $updated_order = check_response($response);
                        $update_param['column'] .= "WHEN `ordid` = '{$updated_order[order_id]}' THEN '{$updated_order[order_status]}' ";
                        $update_param['order_ids'][] = $updated_order['order_id'];
                    } catch (Exception $ex) {

                    }
                }    
            
                $query_update = sprintf("UPDATE schfee_transactions SET %s WHERE ordid IN ('%s')", 
                        $update_param['column']." END", 
                        implode("','", $update_param['order_ids']));
                $update = mysql_query($query_update, $tams) or die(mysql_error());                    
            }
        }

        $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());
        $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
        $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
                
    }else {
        $msg = 'You did not enter a search term!';
        $notification->set_notification($msg, '');
    }
    header("Location:__pay_history.php");
    die();
}


$query_feehistory = sprintf('SELECT s.matric_no, s.ordid, s.status, s.reference, s.amt, s.date_time, l.levelname, ss.sesname, s.stamped, s.pcount, s.reg_fee, TIMESTAMP(date_time) as timestamp_date  '
                            . 'FROM schfee_transactions s, payschedule p, session ss, level_name l '
                            . 'WHERE matric_no = %s AND s.scheduleid = p.scheduleid AND p.sesid = ss.sesid  AND p.level = l.levelid '
                            . 'ORDER BY l.levelname ASC', GetSQLValueString($_SESSION['MM_Username'], "text"));
$feehistory = mysql_query($query_feehistory, $tams) or die(mysql_error());
$row_feehistory = mysql_fetch_assoc($feehistory);
$totalRows_feehistory = mysql_num_rows($feehistory);



?>

<!doctype html>
<html>
    <?php include INCPATH."/header.php" ?>

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
                                    <h3><i class="icon-reorder"></i>
                                       Payment History
                                    </h3>
                                </div>
                                <div class="box-content">                                   
                                    <div class="row-fluid">
                                        <table class="table table-condensed table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>S/N</th>
                                                    <th>Reference</th>
                                                    <th>Amount</th>
                                                    <th>Session</th>
                                                    <th>Level</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                   
                                                    <th>&nbsp;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                
                                                if ($totalRows_feehistory > 0) {
                                                    for ($idx = 0; $idx < $totalRows_feehistory; $idx++, $row_feehistory = mysql_fetch_assoc($feehistory)) {
                                                        $date = isset($row_feehistory['converted_date'])? $row_feehistory['converted_date']: $row_feehistory['timestamp_date'];
                                                        ?>
                                                        <tr>
                                                            <td><?php echo $idx + 1 ?></td>
                                                            <td align="center"><?php echo $row_feehistory['reference'] ?></td>
                                                            <td align="center"><?php echo $row_feehistory['amt'] ?></td>
                                                            <td align="center"><?php echo $row_feehistory['sesname'] ?></td>
                                                            <td align="center"><?php echo $row_feehistory['levelname'] ?></td>
                                                            <td><?php echo $row_feehistory['status'] ?></td>
                                                            <td align="center"><?php echo $row_feehistory['date_time'] ?></td> 
                                                            <td>
                                                                <?php if ($row_feehistory['status'] == 'APPROVED') { ?>
                                                                    <a class="btn btn-small btn-blue" target="_blank" href="receipt.php?no=<?php echo $row_feehistory['ordid'] ?>">Print Receipt</a>
                                                                <?php }else { ?>
                                                                    <a class="btn btn-small btn-blue" href="?check_status=<?php echo $row_feehistory['ordid'] ?>&check_type=schfee">Check Status</a>
                                                                <?php }?>
                                                                <?php if ($row_feehistory['reg_fee'] == 'TRUE') { ?>
                                                                    <a class="btn btn-small btn-grey"target="_blank" href="reg_fee_receipt.php?no=<?php echo $row_feehistory['ordid'] ?>">Print Reg.Fee Receipt</a>
                                                                <?php } ?>
                                                            </td>
                                                        </tr>
                                                    <?php }
                                                    
                                                }
                                                else { ?>
                                                    <tr>
                                                        <td colspan="8">
                                                            <div class="alert alert-danger">
                                                                You have not made any payment yet! 
                                                            </div>
                                                        </td>
                                                    </tr>
                                        <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <p>&nbsp;</p>
                        </div>
                    </div>
                </div>          
            </div>
            <?php include INCPATH."/footer.php" ?>
    </body>
</html>