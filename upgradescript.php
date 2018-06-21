<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once('path.php');

$query_upgradeble = sprintf("SELECT *, tscore + escore as total "
                            . "FROM `result` r "
                            . "JOIN registration g ON r.sesid = g.sesid AND r.stdid = g.stdid AND g.level = 3 "
                            . "WHERE `csid` LIKE 'Ent321' "
                            . "AND r.sesid = 11 "
                            . "AND tscore + escore BETWEEN 35 AND 44");
$upgradeble = mysql_query($query_upgradeble, $tams) or die(mysql_error());

for(;$row_upgradeble = mysql_fetch_assoc($upgradeble);) {
    $addition = 45 - $row_upgradeble['total'];
    
//    if($addition + $row_upgradeble['tscore'] > 30)
//        echo $row_upgradeble['resultid']. ' ';
    if(is_int($addition)) {
        $query_upgraded = sprintf("UPDATE result SET escore = %s "
                                    . "WHERE resultid = %s", 
                                    GetSQLValueString($addition + $row_upgradeble['escore'], "int"), 
                                    GetSQLValueString($row_upgradeble['resultid'], "int"));
        $upgraded = mysql_query($query_upgraded, $tams);
    }
}

echo mysql_num_rows($upgradeble);