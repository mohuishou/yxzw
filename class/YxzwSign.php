<?php
namespace Mohuishou\Lib;
use Curl\Curl;
use MongoDB\BSON\ObjectID;

/**
* 优选在沃自动签到类
*/
class YxzwSign
{
    /**
     * 登陆的cookie
     * @var $_login_cookie
     */
    protected $_login_cookie;

    /**
     * 签到地址
     * @var $_sign_url
     */
    protected $_sign_url="http://169ol.com/Mall/Sign/ajaxSign";

    /**
     * 登陆地址
     * @var $_login_url
     */
    protected $_login_url="http://169ol.com/Mobile/User/submitLogin";

    /**
     * 用户签到界面
     * @var string
     */
    protected $_sign_res="http://169ol.com/Mall/Sign/h5";

    /**
     * 登陆参数
     * @var array
     */
    protected $_login_param=[
        "phone"=>0,//手机号
        "password"=>"",//密码
        "type"=>1,
        "backurl"=>"/Mobile/Personal/index"
    ];

    protected $_curl;



	public function __construct()
	{
		$this->_curl=new Curl();

	}

    /**
     * 登陆优选在沃
     * @author mohuishou<1@lailin.xyz>
     * @param $phone
     * @param $password
     * @return bool
     */
	public function login($phone,$password){
        $this->_login_param["phone"]=$phone;
        $this->_login_param["password"]=$password;
        $this->_login_cookie="../cookies/yxzw/".$phone.".cookie";
        $this->_curl->setCookieJar($this->_login_cookie);
        $this->_curl->post($this->_login_url,$this->_login_param);
        if ($this->_curl->error) {
            echo 'Error: ' . $this->_curl->errorCode . ': ' . $this->_curl->errorMessage;
            return false;
        }
        else {
            $res=json_decode($this->_curl->response);
//            print_r($res);
            if($res->code) return true;
            return false;
        }
	}

    /**
     * 签到
     * @author mohuishou<1@lailin.xyz>
     * @return bool
     */
    public function sign(){
        $this->_curl->get($this->_sign_url);
        if ($this->_curl->error) {
            echo 'Error: ' . $this->_curl->errorCode . ': ' . $this->_curl->errorMessage;
            return false;
        }
        else {
            $res=json_decode($this->_curl->response);
            if($res->status){
                return $res;//签到成功
            }else{
                $a=preg_match("/已经签到/",$res->msg);
                if($a){
                    $res->status=2;//已签到
                }
                return $res;
            }
        }
    }

    /**
     * 获取优选在沃官方信息
     * @author mohuishou<1@lailin.xyz>
     * @return bool
     */
    public function signNum(){
        $user_info_pattern="/userInfo\s+=\s+(.*})/";
        $sign_calendar_pattern="/signCalendar\s+=\s+(.*})/";
        $this->_curl->get($this->_sign_res);
        if ($this->_curl->error) {
            echo 'Error: ' . $this->_curl->errorCode . ': ' . $this->_curl->errorMessage;
            return false;
        }
        else {
            $res=$this->_curl->response;
//            echo $res;
            preg_match_all($user_info_pattern,$res,$user_info);
            $user_info=json_decode($user_info[1][0]);
            preg_match_all($sign_calendar_pattern,$res,$sign_calendar);
            $sign_calendar=json_decode($sign_calendar[1][0]);
            return [
                "sign_info"=>$sign_calendar,
                "wb"=>$user_info->wobei,
                "all_sign"=>$user_info->signCnt
            ];
        }
    }

    /**
     * 优选在沃自动签到主方法
     * @author mohuishou<1@lailin.xyz>
     * @param $phone
     * @param $password
     * @return array
     */
    public function index($phone,$password){
        $data=[
            "status"=>1,//签到状态，1成功，2已经签到，0签到失败,-1:登录失败
            "sign_info"=>'', //本月签到详细信息，对象
            "all_sign"=>0,  //本月签到总次数
            "wb"=>0 //剩余沃贝数目
        ];

        //登录
        $res_login=$this->login($phone,$password);
        if(!$res_login){
            $data['status']=-1;
            return $data;
        }

        //签到
        $res_sign=$this->sign();
        print_r($res_sign);
        if(!$res_sign->status){
            $data['status']=0;
            return $data;
        }
        $data['status']=$res_sign->status;

        //获取信息
        $res_info=$this->signNum();
        if($res_info){
            $data["all_sign"]=$res_info["all_sign"];
            $data["sign_info"]=$res_info["sign_info"];
            $data["wb"]=$res_info["wb"];
        }

        return $data;



    }
}