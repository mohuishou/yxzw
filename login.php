<?php
header('Content-type: text/json');
session_start();
/**
 * Created by mohuishou<1@lailin.xyz>.
 * User: mohuishou<1@lailin.xyz>
 * Date: 2016/7/31 0031
 * Time: 17:05
 */
require_once "vendor/autoload.php";
include_once 'lib/bmob/BmobObject.class.php';
require_once "function.php";
require_once "sign.php";


if(!isset($_POST["phone"])||!isset($_POST["password"])){
    error("参数错误");
}
if(isset($_POST['t'])){
    $t_uid=$_POST['t'];//推荐人uid
}

$phone=$_POST['phone'];
$password=$_POST['password'];

if(!preg_match("/0?(13|14|15|18)[0-9]{9}/",$phone)){
    error("手机号码格式错误");
}

//验证账号密码是否正确
$yxzw=new \Mohuishou\Lib\YxzwSign();
$res_yxzw=$yxzw->login($phone,$password);

if(!$res_yxzw){
    error("手机号或者密码错误！");
}
try{
    $user_db=new BmobObject("user");
    $res=$user_db->get("",['where={"phone":"'.$phone.'"}']);
}catch (Exception $e){
    error($e);
}

//不存在该用户
if(empty($res->results)){
    $res_sign_num=$yxzw->signNum();

    //构造用户数据,并且写入数据库
    $user_data=[
        "phone"=>$phone,
        "password"=>$password,
        "wb"=>$res_sign_num['wb'],
        "sign_num"=>15, //初始赠送十五次
        "auto_status"=>1 //默认开启自动签到
    ];
    try{
        $res_user=$user_db->create($user_data);
    }catch (Exception $e){
        error($e);
    }

    if(!empty($t_uid)){
        $user_db->increment($t_uid,"sign_num",array(10)); //邀请用户自动添加10次
    }

    $_SESSION['uid']=$res_user->objectId;
    $res_init=sign($res_user->objectId);

    if($res_init['status']>0){
        success("注册成功！");
    }else{
        error("初始化失败！");
    }

}

$_SESSION['uid']=$res->results[0]->objectId;

//检查密码是否变更
if($password==$res->results[0]->password){
    success("登录成功！");
}else{
    try{
        $res=$user_db->update($_SESSION["uid"], array("password"=>$password));
        success("登录成功！");
    }catch (Exception $e){
        error($e);
    }
}








