<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
class Login extends Controller {


/**
 * 登录页
 * @return type
 */
    public function login() {
        if($this->request->isAjax()){
            $params=$this->request->param();
            if(empty($params['username'])){
                $return['status']=-1;
                $return['msg']='请填写用户名！';
                return $return;
            }
            if(empty($params['password'])){
                $return['status']=-1;
                $return['msg']='请填写密码！';
                return $return;
            }
            $userinfo=Db::name('User')->where(array('username'=>$params['username']))->find();
            if(empty($userinfo)){
                $return['status']=-1;
                $return['msg']='用户名或密码错误！';
                return $return;
            }
            if($userinfo['password']==md5(md5($params['password']).$userinfo['salt'])){
                //验证成功 写入缓存
                list($s_user['id'],$s_user['username'],$s_user['name'])=array($userinfo['id'],$userinfo['username'],$userinfo['name']);
                session('user',$s_user);
                $return['status']=1;
                $return['msg']='登录成功！';
                $return['url']=url('index/index');
                return $return;
            }else{
                $return['status']=-1;
                $return['msg']='用户名或密码错误！';
                return $return;
            }
            
        }else{
            return $this->fetch('login');
        }
        
    }

}
