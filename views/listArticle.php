
<?php
include '../config/databasecnx.php';

session_start();

$_SESSION['user_id']=$user_id;
if(isset($_SESSION['user_id'])){
    $user_id=$_SESSION['user_id'];
    echo "user ID:".$user_id;
}







?>


















