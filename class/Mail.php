<?php
/**
 * 邮件发送函数
 * Created by mohuishou<1@lailin.xyz>.
 * User: mohuishou<1@lailin.xyz>
 * Date: 2016/5/2 0002
 * Time: 12:51
 */

/**
 * 利用sendcloud接口的邮件发送函数，专门的邮件服务商，对于邮件发送相对比较友好，并且可以不用引用相关文件
 * @author mohuishou<1@lailin.xyz>
 * @param $msg
 * @return string
 */
function sendMail($msg)
{
    $url = 'http://sendcloud.sohu.com/webapi/mail.send.json';
    $API_USER = 'MOHUIHSOU_test_lHbOTh';
    $API_KEY = 'GmKosJDHD8yLulsA';

    //不同于登录SendCloud站点的帐号，您需要登录后台创建发信子帐号，使用子帐号和密码才可以进行邮件的发送。
    $param = array(
        'api_user' => $API_USER,
        'api_key' => $API_KEY,
        'from' => 'test@lailin.xyz',
        'fromname' => 'mohuishou',
        'to' => '1@lailin.xyz',
        'subject' => '优选在沃自动签到结果',
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

/**
 * 基于phpMailer的SMTP的邮件发送函数
 * 暂时用不了了，因为测试的时候发送了太多邮件到qq邮箱被拉入了黑名单
 * @author mohuishou<1@lailin.xyz>
 * @param $msg
 * @throws phpmailerException
 */
//function mail($msg)
//{
//    require_once "./lib/PHPMailer/PHPMailerAutoload.php";
//    $mail = new PHPMailer;
//    $mail->isSMTP();
//    $mail->SMTPDebug = 2;
//    $mail->Debugoutput = 'html';
//    $mail->Host = "smtp.mxhichina.com";
//    $mail->Port = 25;
//    $mail->SMTPAuth = true;
//    $mail->Username = "no-replay@lxl520.com";
//    $mail->Password = "QWEqwe123";
//    $mail->setFrom('no-replay@lxl520.com', '莫回首');
//    $mail->addReplyTo('test@lailin.xyz', '测试');
//    $mail->addAddress('test@lailin.xyz', '123');
//    $mail->Subject = '【签到结果】';
//    $mail->msgHTML($msg);
//    $mail->AltBody = 'This is a plain-text message body';
//    if (!$mail->send()) {
//        echo "Mailer Error: " . $mail->ErrorInfo;
//    } else {
//        echo "Message sent!";
//    }
//
//}