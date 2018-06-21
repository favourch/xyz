<?php
require_once('../../path.php');
if (!isset($_SESSION)) {
    session_start();
}

$auth_users = "1,20,28";
check_auth($auth_users, $site_root.'/admin');

if(isset($_GET['check_status'])) {
    $ord_id = $_GET['check_status'];
    $table = $_GET['ptype']; 
    $response = checkPaymentStatus($ord_id, $merchant_id, $table, $tams); 
    $notification->set_notification($response['msg'], $response['status']);
}

$editFormAction = $_SERVER['PHP_SELF'];
if (isset($_SERVER['QUERY_STRING'])) {
    $editFormAction .= "?" . htmlentities($_SERVER['QUERY_STRING']);
}

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
    "olevelfee" => "O'level Fees",
    "regfee" => "Registration Fees"
);

function query_param($type, $pType) {
    
    $param['table'] = 'schfee_transactions';
    if ($pType != 'schfee') {
     $param['payschedule'] = '';
     $param['level'] = '';     
     } else {
     $param['payschedule'] = "JOIN payschedule ps ON ps.scheduleid = c.scheduleid ";
     $param['level'] = "ps.level as level,";}
     
     
    switch($type) {
        case 'pros':
            $param['extra_filter'] = "JOIN session ses ON ses.sesid = s.sesid";
            $param['column'] = "ses.sesname,";
            
            if ($pType == 'accfee') {
                $param['table'] = 'accfee_transactions';
            } elseif ($pType == 'appfee') {
                $param['table'] = 'appfee_transactions';
            } elseif ($pType == 'regfee') {
                $param['table'] = 'registration_transactions';
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
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, '0');
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
                                        . "s.stdid, s.lname, s.fname, c.percentPaid, %s "
                                        . "%s, TIMESTAMP(date_time) as timestamp_date "
                                        . "FROM %s c "                                        
                                        . "LEFT JOIN student s ON s.stdid = c.%s "
                                        . "JOIN session ses ON ses.sesid = %s "  
                                       // . "JOIN payschedule ps ON ps.scheduleid = c.scheduleid " 
                                        . "%s "
                                        . "AND (s.lname LIKE %s "
                                        . "OR c.ordid LIKE %s "                                        
                                        . "OR c.reference LIKE %s "
                                        . "OR s.fname LIKE %s "
                                        . "OR s.stdid LIKE %s)"
                                        . "WHERE ordid IS NOT NULL ", 
                                       // GetSQLValueString($params['select'], //"defined", $params['select']),
                                        GetSQLValueString($params['level'], "defined", $params['level']),
                                        GetSQLValueString("date_param", "defined", "STR_TO_DATE(date_time, '%d/%m/%Y') as converted_date"),
                                        GetSQLValueString($params['table'], "defined", $params['table']), 
                                        GetSQLValueString($params['field'], "defined", $params['field']), 
                                        GetSQLValueString($params['condition'], "defined", $params['condition']), 
                                        GetSQLValueString($params['payschedule'], "defined", $params['payschedule']), 
                                        GetSQLValueString($search_term, "text"),                                        
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"),
                                        GetSQLValueString($search_term, "text"), 
                                        GetSQLValueString($search_term, "text")); 
                break;

            default:
                break;
        }

        $rsstdnt = mysql_query($query_rsstdnt, $tams) or die(mysql_error());
        $row_rsstdnt = mysql_fetch_assoc($rsstdnt);
        $totalRows_rsstdnt = mysql_num_rows($rsstdnt);
                
    }else {
        $msg = 'You did not enter a search term!';
        $notification->set_notification($msg, '');
    }
}
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
                                    <h3><i class="icon-credit-card"></i>
                                        Edit Payment
                                    </h3>
                                </div>
                                <div class="box-content">
                                    <form class="form-vertical  form-validate" action="<?php echo $editFormAction; ?>" method="get">
                                        <div class="row-fluid">
                                            <div class="span5">
                                                <div class="control-group">
                                                    <div class="input-prepend">
                                                        <span class="add-on">User Type  </span>
                                                        <select name="type" required="" class="input-large">
                                                            <option value="reg" <?php if ($type == 'reg') echo 'selected' ?>>Regular</option>
                                                            <option value="pros" <?php if ($type == 'pros') echo 'selected' ?>>Prospective</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="span5">
                                                <div class="control-group">
                                                    <div class="input-prepend input-medium">
                                                        <span class="add-on">Payment Type</span>
                                                        <select name="ptype" required="" class="input-large">
                                                            <option value="">-- Choose --</option>
                                                            <?php foreach($payType as $value => $label) {?>
                                                            <option value="<?= $value?>" <?= ($pType == $value)? 'selected' : ''?>><?= $label?></option>
                                                            <?php }?>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                         <div class='row-fluid'>
                                            <div class="span10">
                                                <div class="control-group">
                                                    <div class="input-prepend input-medium">
                                                        <span class="add-on">Search by Student ID or Names or Ordid </span>
                                                        <input name="search" type='text' class="input-xxlarge"  required="" value="<?php echo $query?>">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='span2'>
                                                 <input type="submit" value="Search" class="btn btn-primary" >
                                            </div>
                                        </div>
                                    </form>
                                    <table class="table table-condensed table-condensed table-striped">
                                        <thead> 
                                            <tr>
                                                <th>S/n</th>
                                                <th>Matric</th>
                                                <th>Full Name</th>                
                                                <th>Order No.</th>
                                                <th>Amount</th>
                                                <th>Percentage Paid</th>
                                                <th>Status</th>
                                                <th>Session</th>
                                                <th>Level</th>
                                                <th>Date</th>
                                                <th>Query Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            if(!empty($row_rsstdnt)){
                                            $i = 1; 
                                            do { 
                                                $date = isset($row_rsstdnt['converted_date'])? $row_rsstdnt['converted_date']: $row_rsstdnt['timestamp_date'];
                                            ?>
                                            <tr>
                                                <td><?php echo $i++;?></td>	
                                                <td><?php echo $row_rsstdnt['stdid']?></td>
                                                <td><?php echo $row_rsstdnt['fname']." ".$row_rsstdnt['lname']?></td>
                                                <td><?php echo $row_rsstdnt['ordid']?></td>
                                                <td><?php echo $row_rsstdnt['amt']?></td>
                                                <td><?php echo '%'.$row_rsstdnt['percentPaid']?></td>
                                                <td><?php echo $row_rsstdnt['status']?></td>
                                                <td><?php echo $row_rsstdnt['sesname']?></td>
                                                <td><?php echo $row_rsstdnt['level']?></td>
                                                <td><?php echo $row_rsstdnt['date_time']?></td>
                                                <td>
                                                    <?php if ($row_rsstdnt['status'] != 'APPROVED') { ?>
                                                        <a class="btn btn-small btn-blue" href="search_payment.php?search=<?php echo $query?>&ptype=<?php echo $pType?>&type=<?php echo $type?>&check_status=<?php echo $row_rsstdnt['ordid'] ?>&action=requery">
                                                       Update Status
                                                            <!--<i class="icon-refresh"></i> -->
                                                        </a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                <?php if (in_array(getAccess(), [20, 28])) { ?>
                                                    <div class="btn-group">
                                                        <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i class="icon-cogs"></i><span class="caret"></span></a>
                                                        <ul class="dropdown-menu">
                                                            <li>
                                                                <a target="_blank" href="edit_payment.php?type=<?php echo $type?>&id=<?php echo $row_rsstdnt['ordid']?>&cat=<?php echo $pType?>">Edit Payments </a>
                                                            </li>
<!--                                                            <li>
                                                                <a href="#">Delete</a>
                                                            </li>-->
                                                        </ul>
                                                    </div>
                                                <?php } ?>
                                                </td>
                                            </tr> 
                                            <?php } while($row_rsstdnt = mysql_fetch_assoc($rsstdnt)); 
                                            
                                            }else{?>
                                            <tr>
                                                <td colspan="12">
                                                    <div class="alert alert-error"> No record available </div>
                                                </td>
                                            </tr>
                                            <?php }?>
                                        </tbody>

                                    </table>
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
</html>