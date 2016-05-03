<?php
/**
* 
*/
class login
{
	private $_login_cookie;
    private $_code_cookie;
	
	public function __construct($user_name,$password)
	{

        do{
            /*--------获取验证码图片--------*/
            $code=$this->getCode();
            if(!$this->checkCode($code)) continue;
            $url="http://www.169ol.com/Mall/User/submitLogin";
            $data="user_name={$user_name}&user_password={$password}&Verification=".$code;
            $this->login($url,$data);
            if($this->sign()){

            }else{

            }
        }while(1);

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
     * @author mohuishou<1@lailin.xyz>
     * @param $url
     * @param $data
     */
	public function login($url,$data){
        $this->_login_cookie=dirname(__FILE__)."/login.cookie";
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
     * @param string $url
     * @return mixed
     */
	public function getContent($url="http://www.169ol.com/Mall/Personal/index") {
	    $ch = curl_init(); 
	    curl_setopt($ch, CURLOPT_URL, $url); 
	    curl_setopt($ch, CURLOPT_HEADER,0); 
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch,CURLOPT_COOKIEFILE,$this->_login_cookie); //读取cookie
	    $rs = curl_exec($ch); //执行cURL抓取页面内容
	    curl_close($ch); 
	    return $rs; 
	}

    /**
     * @author mohuishou<1@lailin.xyz>
     * @param string $url
     * @return mixed
     */
	public function sign($url="http://www.169ol.com/Mall/Sign/ajaxSign"){
		return $this->getContent($url);
        //{"status":1,"gotourl":0,"msg":327432}{"status":1,"gotourl":0,"msg":327432} 签到成功标识
        //签到失败标识"status": 0,"gotourl": 1,"msg": "亲，登录后才能成功签到"}
	}

    /**
     * @author mohuishou<1@lailin.xyz>
     * @param $url
     * @param $data
     * @return mixed
     */
	public function  post($url,$data){
		$ch = curl_init ();
		curl_setopt ( $ch, CURLOPT_URL, $url );
		curl_setopt ( $ch, CURLOPT_POST, 1 );
		curl_setopt ( $ch, CURLOPT_HEADER, 0 );
		curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
        curl_setopt($ch,CURLOPT_COOKIEFILE, $this->_code_cookie);
		$rs = curl_exec ( $ch );
		curl_close ( $ch );
//		echo $rs;
		return $rs;
	}

    /**
     * @author mohuishou<1@lailin.xyz>
     * @param string $url
     * @param string $fileName
     * @return string
     */
    function getCode($url = 'http://www.169ol.com/Mall/Code/getCode', $fileName = './img/code.png')
    {

        $this->_code_cookie = dirname(__FILE__)."/pic.cookie";

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
        require_once "../ImageOCR/Image.class.php";
        $image=new \ImageOCR\Image("./img/code.png");
        $a=$image->find();
        $code=implode("",$a);
        $image=null;

        return $code;

    }



}
	

	
	
	
	
	
