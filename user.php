<?php
header('Content-type: text/json');
session_start();
date_default_timezone_set("PRC");
require_once "function.php";
include_once 'lib/bmob/BmobObject.class.php';

if(!isset($_SESSION['uid'])||empty($_SESSION['uid'])){
    error("尚未登陆！","index.html");
};
$uid=$_SESSION['uid'];
$user_db=new BmobObject('user');
if(isset($_POST['action'])){
    $action=$_POST['action'];
    switch ($action){
        case "logout":
            logout();
            break;
        case "auto_sign":
            autoSwitch($user_db,$uid,$_POST['data']);
            break;
        case "email":
            updateEmail($user_db,$uid,$_POST['data']);
            break;

    }
}

try{
    $yxzw_db=new BmobObject("yxzw");
    $res_yxzw=$yxzw_db->get("",array('where={"month":'.(int)date("m").',"user":{"__type":"Pointer","className":"user","objectId":"'.$uid.'"}}','include=user'));
    success("获取成功","",$res_yxzw->results[0]);
}catch (Exception $e){
    error($e);
}

function updateEmail($user_db,$uid,$email){
    $data["email"]=$email;
    $user_db->update($uid,$data);
    success("修改成功");
}

function autoSwitch($user_db,$uid,$status){
    $data['auto_status']=(int)$status;
    $user_db->update($uid,$data);
    success("修改成功");
}

/**
 * 退出登录
 * @author mohuishou<1@lailin.xyz>
 */
function logout(){
    unset($_SESSION['uid']);
    success("退出成功","index.html");
}