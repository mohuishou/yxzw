<?php
/**
 * Created by PhpStorm.
 * User: lxl
 * Date: 16-11-9
 * Time: 下午6:34
 */

namespace App\Http\Controllers\Sign;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Yxzw;
use Mohuishou\Lib\YxzwSign;

class SignController extends Controller{


    public function sign(User $user,$is_auto=true){

        $return_data=[
            "status"=>0,
            "msg"=>""
        ];

        $yxzw_model=Yxzw::firstOrCreate([
            "uid"=>$user->id,
            "month"=>(int)date("m")
        ]);

        //是否为自动签到，自动签到需检查
        if($is_auto){
            if($user->auto_status!=1)
                return $return_data;//自动签到已关闭

            if($user->sign_num<1){
                return $return_data;//自动签到次数不足
            }
        }


        //分状态处理
        $msg_status="签到成功！";
        $status="成功";

        //签到
        try{
            $yxzw_obj=new YxzwSign();
            $sign_res=$yxzw_obj->index($user->phone,$user->password);
        }catch (\Exception $e){
            $return_data["msg"]=$e->getMessage();
            $status="失败";
            return $return_data;
        }

        $return_data["status"]=$sign_res["status"];

        switch ($sign_res["status"]){
            case 1:
                $msg_status="签到成功！";
                $status="成功";
                break;
            case 2:
                $msg_status="今日已签到，不会扣除自动签到次数";
                $status="已签到";
                break;
            case 0:
                $status="失败";
                $msg_status="签到失败，请手动尝试！";
                break;
            case -1:
                $status="失败";
                $msg_status="登录失败，请检查您的手机号与密码！";
                break;
        }

        $return_data["msg"]=$msg_status;

        if($sign_res["status"]>0){
            //用户数据库数据更新
            $user->wb=$sign_res["wb"];
            if($sign_res["status"]==1&&$is_auto){
                $user->sign_num=$user->sign_num-1;
            }
            $user->save();

            //更新当月签到详情
            $yxzw_model->sign_info=json_encode($sign_res["sign_info"],true);
            $yxzw_model->all_sign=$sign_res["all_sign"];
            if($sign_res["status"]==1) {
                $yxzw_model->auto_sign = $yxzw_model->auto_sign + 1;
            }
            $yxzw_model->save();
        }


        if($is_auto){
            //发送邮件
            $auto_sign_num=$user->yxzw()->where("month",(int)date("m"))->first()->auto_sign;
            $msg="感谢您使用优选在沃在线签到系统，您的使用情况如下：<br />";
            $msg .="【手机号】：".$user->phone."<br />";
            $msg .="【签到情况】：".$msg_status."<br />";
            $msg .="【本月签到次数】：".$sign_res["all_sign"]."<br />";
            $msg .="【本月自动签到次数】：".$auto_sign_num."<br />";
            $msg .="【剩余自动签到次数】：".$user->sign_num."<br />";
            $msg .="【签到时间】：".date("Y-m-d H:i:s")."<br />";
            $msg .="详情请点击：http://y.lxl520.com 登录查看，感谢您的使用";
            if(isset($user->email)){
                $res=$this->sendMail($msg,$user->email,$status);
                print_r($res);
            }
        }

        return $return_data;
    }

    /**
     * @param $msg
     * @param $email
     * @param $status
     * @return string
     */
    protected function sendMail($msg,$email,$status){
        $url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
        $API_USER = 'mohuishou';
        $API_KEY = 'RV0PMH97plzLGvue';

        //不同于登录SendCloud站点的帐号，您需要登录后台创建发信子帐号，使用子帐号和密码才可以进行邮件的发送。
        $param = array(
            'api_user' => $API_USER,
            'api_key' => $API_KEY,
            'from' => 'test@lailin.xyz',
            'fromname' => 'mohuishou',
            'to' => $email,
            'subject' => '【'.$status.'】优选在沃自动签到结果',
            'html' => $msg,
            'resp_email_id' => 'true');

        $data = http_build_query($param);

        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => $data
            ));

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return $result;
    }

}