<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function topUp($agentid, $operation, $amount){
    global $tams;
    $topup_query ="";
    
    mysql_query("BEGIN", $tams);
    
    switch ($operation) {
        case 'credit':
            
            $details = "Account Credited ";
            
            $topup_query = sprintf("UPDATE agents "
                            . "SET topup = topup + %s "
                            . "WHERE agtid = %s", 
                            GetSQLValueString($amount, "double"),
                            GetSQLValueString($agentid, "text"));
            

            break;
        case 'debit':
            
            $details = "Account Debited ";
            
            $topup_query = sprintf("UPDATE agents "
                            . "SET topup = topup - %s "
                            . "WHERE agtid = %s", 
                            GetSQLValueString($amount, "double"),
                            GetSQLValueString($agentid, "text"));
            

            break;

        default:
            break;
    }
    
    mysql_query($topup_query, $tams) or die(mysql_error());
    
    $query_rsagt = sprintf("SELECT topup "
                        . "FROM agents "
                        . "WHERE agtid = %s", 
                        GetSQLValueString($agentid, "text"));
    $rsagt = mysql_query($query_rsagt, $tams) or die(mysql_error());
    $row_rsagt = mysql_fetch_assoc($rsagt);
    
    $query_hist = sprintf("INSERT INTO agent_pay_hist (agentid, details, amount) "
                        . " VALUES (%s, %s, %s)",
                        GetSQLValueString($agentid, "text"),
                        GetSQLValueString($details, "text"),
                        GetSQLValueString($amount, "double"));
    $rsagt = mysql_query($query_hist, $tams) or die(mysql_error());
    $insert_id = mysql_insert_id();
    
    $update_hist = sprintf("UPDATE agent_pay_hist "
                        . "SET cur_ballance = %s "
                        . "WHERE histid =%s ",
                        GetSQLValueString($row_rsagt['topup'], "double"),
                        GetSQLValueString($insert_id, "int"));
    mysql_query($update_hist, $tams) or die(mysql_error());
    
    mysql_query("COMMIT", $tams);
    
//    header('location: ')
}

