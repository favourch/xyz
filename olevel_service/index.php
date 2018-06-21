<?php
if (!isset($_SESSION)){
    session_start();
}
require_once('../path.php');

$auth_users = "1, 20,22, 23, 24, 26,28, 10, 11";
check_auth($auth_users, $site_root . '/admin');

if(in_array(getSessionValue('accttype'), ['stud', 'pros'])){
    header('Location: client/index.php');
    die();
}else{
    header('Location: admin/index.php');
    die();
}
