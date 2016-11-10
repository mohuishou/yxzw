<?php
/**
 * Created by PhpStorm.
 * User: lxl
 * Date: 16-11-9
 * Time: 下午6:33
 */

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Sign\SignController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Mohuishou\Lib\YxzwSign;

class UserController extends Controller{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function login(Request $request){
        $this->validate($request,[
            "phone"=>"required|min:11|max:11",
            "password"=>"required"
        ]);

        $phone=$request->input("phone");
        $password=$request->input("password");

        //推荐人id
        $t_uid=0;
        if($request->has("t")){
            $t_uid=$request->input("t");
        }

        //验证密码是否错误
        try{
            $yxzw_obj=new YxzwSign();
            $res=$yxzw_obj->login($phone,$password);
            if(!$res){
                return $this->error("手机号或密码错误！");
            }
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }

        //保存密码
        $user=User::firstOrCreate(["phone"=>$phone]);
        $user->password=$password;
        $user->save();
        //新用户,首次登录的用户还没有获取沃贝
        if(!isset($user->wb)||empty($user->wb)){

            //新用户，初始化
            $sign_controller=new SignController();
            $sign_controller->sign($user,false);
            //如果有推荐人，给推荐人添加10次
            if($t_uid){
                $t_user=User::find($t_uid);
                $t_user->sign_num=$t_user->sign_num+10;
                $t_user->save();
            }
        }

        $token=$this->creatToken($user);

        return $this->success("登录成功",["token"=>$token]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function user(Request $request){
        $user=$request->user();
        $yxzw=$user->yxzw->where("month",(int)date("m"))->first();
        $sign_info=json_decode($yxzw->sign_info);
        $user=$user->toArray();
        $user["yxzw"]=$yxzw;
        $user["yxzw"]["sign_info"]=$sign_info;
        return $this->success("数据获取成功！",$user);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function updateEmail(Request $request){
        $this->validate($request,[
            "email"=>"required|email"
        ]);
        $email=$request->input("email");
        $user=$request->user();
        $user->email=$email;
        $user->save();

        return $this->success("修改成功！",$user);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function autoSwitch(Request $request){
        $this->validate($request,[
            "status"=>"required|max:1"
        ]);
        $status=$request->input("status");
        $user=$request->user();
        $user->auto_status=$status;
        $user->save();

        return $this->success("修改成功！",$user);
    }



    /**
     * 退出登录
     * @author mohuishou<1@lailin.xyz>
     */
    public function logout(Request $request){
        Cache::pull("user.token.".$request->user()->id);
        return $this->success("退出成功！");
    }

    /**
     * 创建token
     * @author mohuishou<1@lailin.xyz>
     * @param User $user
     * @return string
     */
    public function creatToken(User $user){
        $token_arr=[
            'uid'=>$user->id,
            'time'=>60*2, //有效时间2小时
            'start'=>time() //token创建时间
        ];
        $token_str=json_encode($token_arr);
        $token=encrypt($token_str);
        Cache::put('user.token.'.$user->id,$token,60*2);
        return $token;
    }

}