<?php
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"

//$hostname_tams = "localhost";
//$database_tams = "tasueded_tams_demo";
//$username_tams = "tasueded_demo";
//$password_tams = "demo_user_1234";

$hostname_tams = "localhost";
$database_tams = "tams_db";
$username_tams = "root";
$password_tams = "!Opensecsemy2";

//$hostname_tams = "localhost";
//$database_tams = "dsadegbe_tams";
//$username_tams = "dsadegbe_dsitori";
//$password_tams = "!Crownbirth2015#";
$tams = @mysql_pconnect($hostname_tams, $username_tams, $password_tams) or trigger_error(mysql_error(),E_USER_ERROR); 


mysql_select_db($database_tams, $tams);
?>
