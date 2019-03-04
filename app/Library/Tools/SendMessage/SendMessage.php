<?php
namespace App\Library\Tools\SendMessage;

use Curder\LaravelAliyunSms\AliyunSms;
class SendMessage {

    public function send($flag, $phoneNumber, $code, $name)
    {
        switch ($flag) {
            case 1:
                $variable = [
                    'name' => $name
                ];
                $template = 'SMS_99830006';
                break;
            case 2:
                $variable = [
                    'name' => $name,
                    'number' => $code
                ];
                $template = 'SMS_99800002';
                break;
            case 3:
                $variable = [
                    'number' => $name,
                    'name' => $code
                ];
                $template = 'SMS_112465461';
                break;
            case 4:
                $variable = [
                    'name' => $name,
                    'number' => $code
                ];
                $template = 'SMS_105910050';
                break;
            case 5:
                $variable = [
                    'name' => $name,
                    'number' => $code
                ];
                $template = 'SMS_105820051';
                break;
            case 6:
                $variable = [
                    'name' => $name,
                    'number' => $code
                ];
                $template = 'SMS_116568290';
                break;
            case 7:
                $variable = [
                    'name' => $name,
                    'number' => $code
                ];
                $template = 'SMS_119077094';
                break;
            default:
                $variable = [];
                // 默认模版
                $template = 'SMS_99830006';
        }
        $send = new AliyunSms();
        $response = $send->send($phoneNumber, $template, $variable);
        $message = $response->Message;
        if ($message == 'OK') {
            app('log')->info($phoneNumber.'发送成功');
            return 1;// 成功
        } else {
            app('log')->error(date('Y-m-d H:i:s').':\''.$phoneNumber.'\'短信发送失败.失败原因是:'.$message);
            return 0;// 失败
        }
    }
}
