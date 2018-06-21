<?php

$hostname = "localhost";
$database = "tasueded_olevelservice_db";
$username = "tasueded_dayo";
$password = "!Opensecsemy2";




$con = @mysql_pconnect($hostname, $username, $password) or trigger_error(mysql_error(), E_USER_ERROR);

mysql_select_db($database, $con);

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

function getPayStatus($data){
    

    switch ($data) {
        case 'C00':
            $response = "CashEnvoy transaction successful.";
            break;
        
        case 'C01':
            $response = "User cancellation.";
            break;
        case 'C02':
            $response = "User cancellation by inactivity.";
            break;
        case 'C03':
            $response = "No transaction record.";
            break;
        case 'C04':
            $response = "Insufficient funds.";
            break;
        case 'C05':
            $response = "Transaction failed. Contact support@cashenvoy.com for more information.";
            break;
        
        case 'C07':
            $response = "Transaction refunded.";
            break;
        default:
            $response = '';
            break;
    }
    return $response;
}
