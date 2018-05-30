<?php

namespace app\index\controller;

use think\Controller;
use Sts\Request\V20150401 as Sts;

class Index extends Controller {

    public function index() {
        return '<style type="text/css">*{ padding: 0; margin: 0; } div{ padding: 4px 48px;} a{color:#2E5CD5;cursor: pointer;text-decoration: none} a:hover{text-decoration:underline; } body{ background: #fff; font-family: "Century Gothic","Microsoft yahei"; color: #333;font-size:18px;} h1{ font-size: 100px; font-weight: normal; margin-bottom: 12px; } p{ line-height: 1.6em; font-size: 42px }</style><div style="padding: 24px 48px;"> <h1>:) </h1><p> ThinkPHP V5.1<br/><span style="font-size:30px">12载初心不改（2006-2018） - 你值得信赖的PHP框架</span></p></div><script type="text/javascript" src="https://tajs.qq.com/stats?sId=64890268" charset="UTF-8"></script><script type="text/javascript" src="https://e.topthink.com/Public/static/client.js"></script><think id="eab4b9f840753f8e7"></think>';
    }

    public function hello($name = 'ThinkPHP5') {
        return 'hello,' . $name;
    }

    public function login() {
        session_start();
        var_dump($_COOKIE);
        return $this->fetch('login');
    }

    public function show_layout() {
        return $this->fetch('public/base');
    }

    public function show_layout_new() {
        return $this->fetch('public/base_new');
    }

    public function extend() {
        return $this->fetch('extend');
    }

    public function upload_file() {
        return $this->fetch();
    }

    public function get_token() {
        $data['AccessKeyId'] = 'LTAIbfEESNFaxGyq';
        $data['AccessKeySecret'] = 'AccessKeySecret';


        /*
         * 在您使用STS SDK前，请仔细阅读RAM使用指南中的角色管理部分，并阅读STS API文档
         *
         */
       include_once  ROOT_PATH.'vendor/aliyun-php-sdk-core/Config.php';
       // $this->env->load($this->rootPath  .'vendor/aliyun-php-sdk-core/Config.php');

        define("REGION_ID", "cn-shenzhen");
        define("ENDPOINT", "sts.cn-shenzhen.aliyuncs.com");
// 只允许子用户使用角色
        DefaultProfile::addEndpoint(REGION_ID, REGION_ID, "Sts", ENDPOINT);
        $iClientProfile = DefaultProfile::getProfile(REGION_ID, "LTAITWEiEMWy6f1H", "9o6oSuWPiPZJtwWbMy3wLzmckV3yki");
        $client = new DefaultAcsClient($iClientProfile);
// 角色资源描述符，在RAM的控制台的资源详情页上可以获取
        $roleArn = "tempuser";
// 在扮演角色(AssumeRole)时，可以附加一个授权策略，进一步限制角色的权限；
// 详情请参考《RAM使用指南》
// 此授权策略表示读取所有OSS的只读权限
        $policy = <<<POLICY
{
  "Statement": [
    {
      "Action": [
        "oss:Get*",
        "oss:List*"
      ],
      "Effect": "Allow",
      "Resource": "*"
    }
  ],
  "Version": "1"
}
POLICY;
        $request = new Sts\AssumeRoleRequest();
// RoleSessionName即临时身份的会话名称，用于区分不同的临时身份
// 您可以使用您的客户的ID作为会话名称
        $request->setRoleSessionName("client_name");
        $request->setRoleArn($roleArn);
        $request->setPolicy($policy);
        $request->setDurationSeconds(3600);
        try {
            $response = $client->getAcsResponse($request);
            print_r($response);
        } catch (ServerException $e) {
            print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
        } catch (ClientException $e) {
            print "Error: " . $e->getErrorCode() . " Message: " . $e->getMessage() . "\n";
        }
    }

}
