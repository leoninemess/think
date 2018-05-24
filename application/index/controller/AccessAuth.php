<?php

namespace app\index\controller;

use think\Config;
use think\Db;
use think\exception\HttpResponseException;
use think\Request;
use think\View;
use think\Controller;
use \think\cache\driver\Redis;

/**
 * 访问权限管理
 * Class AccessAuth
 * @package hook
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/05/12 11:59
 */
class AccessAuth extends Controller {

    /**
     * 当前请求对象
     * @var Request
     */
    protected $request;

    /**
     * 行为入口
     * @param $params
     */
    public function run() {
        $user['id']=1;
        $user['name']='name';
        session('user',$user);
        //$this->request = $this->request;
        list($module, $controller, $action) = [$this->request->module(), $this->request->controller(), $this->request->action()];
        //首先判定这个action是否需要授权
        $node = strtolower($module . '/' . $controller . '/' . $action);
        $redis=new Redis();
        $nodeInfo = $redis->get($node);
        if (empty($node)) {
            //查询并将结果写入
            $nodeInfo = Db::name('Auth')->where(array('module' => $module, 'controller' => $controller, 'action' => $action))->find();
            if (empty($nodeInfo)) {
                $this->response('抱歉，您没有访问该模块的权限！', 0);
            } else {
                $nodeInfo = json_encode($nodeInfo);
                $redis->set($node, $nodeInfo, 600);
            }
        }
        //根据nodeInfo来判定是否需要授权
        $nodeInfo = json_decode($nodeInfo, true);
        if ($nodeInfo['is_auth'] == -1) {
            //不需要授权
            return true;
        }
        // 用户登录状态检查
        if (!session('user')) {
            throw new HttpResponseException(redirect('login/login'));
        }
        //存在用户登录信息，查询用户是否有权限
        $auth = $redis->get('auth_' . session('user.id'));
        if (empty($auth)) {
            $auth = $this->getUserAuth(session('user.id'));
            $auth= json_encode($auth);
            $redis->set('auth_'. session('user.id'),$auth,86400);
        }
        $auth= json_decode($auth,true);
        if (in_array($nodeInfo['id'], $auth)) {
            return true;
        }
        
        // 权限正常, 默认赋值
        $view = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $view->assign('classuri', strtolower("{$module}/{$controller}"));
    }

    /**
     * 返回消息对象
     * @param string $msg 消息内容
     * @param int $code 返回状态码
     * @param string $url 跳转URL地址
     * @param array $data 数据内容
     * @param int $wait
     */
    protected function response($msg, $code = 0, $url = '', $data = [], $wait = 3) {
        $result = ['code' => $code, 'msg' => $msg, 'data' => $data, 'url' => $url, 'wait' => $wait];
        throw new HttpResponseException(json($result));
    }

    public function getUserAuth($userid = '1') {
        if (!empty($userid)) {
            $userInfo = Db::name('user')->where('id', $userid)->find();
            //$groups= explode(',', $userInfo['group_id']);
            $authGroup = Db::name('user_group')->where(array('id' => $userInfo['group_id']))->column('auth');
            $auths = explode(',', $authGroup[0]);
            $allAuth = array();
            foreach ($auths as $value) {
                //获取所有权限组的子权限集
                $auth = Db::name('auth_group')->where(array('id' => $value))->column('auth_id');
                $auth = explode(',', $auth[0]);
                var_dump($auth);
                $allAuth = array_merge($allAuth, $auth);
            }
            $allAuth = array_unique($allAuth);
            var_dump($allAuth);
        }
        return $allAuth;
    }

}
