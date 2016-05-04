<?php

/**
 * 联通优选在沃自动签到程序
 * 使用ImageOCR自动识别图片验证码
 * Class login
 */
class AutoSign
{
	private $_login_cookie;
    private $_code_cookie;
	
	public function __construct($user_name,$password,$count=20,$is_mail=true)
	{
        $begin_time=time();
        $msg='感谢您使用优选在沃在线签到系统，您的使用情况如下：<br />';
        for($i=0;$i<$count;$i++){
            /*--------获取验证码图片并识别--------*/
            $code=$this->getCode();

            /*------------验证验证码是否正确------------*/
            if(!$this->checkCode($code)){
                $msg .="【验证码】：$code 验证失败 <br /> ";
                continue;
            }else{
                $msg .="【验证码】：$code 验证成功 <br /> ";
            }


            /*----------登陆---------*/
            $url="http://www.169ol.com/Mall/User/submitLogin";
            $data="user_name={$user_name}&user_password={$password}&Verification=".$code;
            $this->login($url,$data);

            /*-----------获取签到结果并进行解析-----------*/
            $sign_data=json_decode($this->sign(),true);
            if($sign_data['status']){
                echo "签到成功 <br />";
                break;
            }else{
                if($sign_data['gotourl']){
                    echo "尚未登陆 <br />";
                    continue;
                }else{
                    echo "已签到 <br />";
                    break;
                }
            }

        }

        /*---------------整理反馈数据---------------*/
        $msg .="【签到结果：】".$sign_data['msg'] ."<br />";
        $msg .= "【运行次数】：".($i+1)." 次 <br /> ";
        $msg .= "【开始时间】：".date("Y-m-d H:m:s",$begin_time);
        $end_time=time();
        $msg .="【运行时间】：".($end_time-$begin_time) ."秒";

        /*-----------是否通过邮件发送反馈数据-------------*/
        if($is_mail){
            require_once "Mail.inc.php";
            echo sendMail($msg);
        }else{
            echo $msg;
        }

	}

   

    /**
     * 检查验证码
     * @author mohuishou<1@lailin.xyz>
     * @param string $code 验证码
     * @return bool 1 or 0
     */
    public function checkCode($code){
        return $this->post('http://www.169ol.com/Mall/User/loginValiCode',"Verification=".$code);
    }


    /**
     * 登陆
     * @author mohuishou<1@lailin.xyz>
     * @param string $url 登陆地址
     * @param string $data 登陆需要post的数据
     */
	public function login($url,$data){
        $this->_login_cookie=dirname(__FILE__)."/cookies/login.cookie";
	    $ch = curl_init();
	    curl_setopt ($ch, CURLOPT_TIMEOUT, 100);
	    curl_setopt ($ch, CURLOPT_URL,$url);
	    curl_setopt ($ch, CURLOPT_HEADER,true);
	    curl_setopt($ch, CURLOPT_POST, 1);//post方式提交
	    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_COOKIEFILE, $this->_code_cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_login_cookie);
	    $re  = curl_exec($ch);
        curl_close($ch);
	}

    /**
     * @author mohuishou<1@lailin.xyz>
     * @param string $url get方法获取数据
     * @param string $cookie cookie文件的地址，默认为$this->_login_cookie
     * @return mixed
     */
	public function getContent($url,$cookie=null) {
        //$cookie赋初值
        !$cookie && $cookie=$this->_login_cookie;

        $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url); 
	    curl_setopt($ch, CURLOPT_HEADER,0); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch,CURLOPT_COOKIEFILE,$cookie); //读取cookie
	    $rs = curl_exec($ch); //执行cURL抓取页面内容
	    curl_close($ch);
	    return $rs; 
	}

    /**
     * 签到
     * @author mohuishou<1@lailin.xyz>
     * @param string $url 签到的地址
     * @return string $json 返回一个json数据
     */
	public function sign($url="http://www.169ol.com/Mall/Sign/ajaxSign"){
		return $this->getContent($url);
        //{"status":1,"gotourl":0,"msg":327432}{"status":1,"gotourl":0,"msg":327432} 签到成功标识
        //签到失败标识"status": 0,"gotourl": 1,"msg": "亲，登录后才能成功签到"}
	}

    /**
     * @author mohuishou<1@lailin.xyz>
     * @param string $url post的地址
     * @param mixed $data post的数据 可以是字符串也可以是数组
     * @param string $cookie cookie文件的地址，默认为$this->_login_cookie
     * @return mixed
     */
	public function  post($url,$data,$cookie=null){

        //$cookie赋初值
        !$cookie && $cookie=$this->_login_cookie;

		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch,CURLOPT_COOKIEFILE, $cookie);
		$rs = curl_exec ( $ch );
		curl_close ( $ch );
//		echo $rs;
		return $rs;
	}

    /**
     * 获取验证码并自动识别
     * @author mohuishou<1@lailin.xyz>
     * @param string $url
     * @param string $fileName
     * @return string 返回识别字符串
     */
    public function getCode($url = 'http://www.169ol.com/Mall/Code/getCode', $fileName = './img/code.png')
    {

        $this->_code_cookie = dirname(__FILE__)."/cookies/pic.cookie";

        /*---------设置header---------*/
		$header[]="User-Agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2633.3 Safari/537.36";
		$header[]="Host:www.169ol.com";
		$header[]="Accept:image/webp,image/*,*/*;q=0.8";
		$header[]="Referer:http://www.169ol.com/Mall/User/index";
		$header[]="Accept-Language:zh-CN,zh;q=0.8";


        $ch = curl_init();
        $fp = fopen($fileName, 'w+');
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_FILE,$fp);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->_code_cookie);
        curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        /*----------验证码识别--------*/
        require_once dirname(__FILE__)."/lib/ImageOCR/Image.class.php";
        $image=new \ImageOCR\Image("./img/code.png");
        $a=$image->find();
        $code=implode("",$a);
        $image=null;

        return $code;

    }



}
	

	
	
	
	
	
