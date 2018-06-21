<?php

$post = '';
if(isset($_POST)){
$post = json_decode(file_get_contents('php://input'), TRUE);

var_dump($post);

}

