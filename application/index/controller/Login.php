<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/3/13
 * Time: 12:19
 */
namespace app\index\controller;
use think\Controller;
use \think\captcha\Captcha;
use \think\Session;

class Login extends Controller{
    private $salt='er45wi6HRI21U42Eolkj';
    public function register(){
        if(Session::get('uinfo')){
            $this->redirect('index/index');
        }
        else return  $this->fetch();
    }
    public function doreg(){

        $save_data = input();
        // 进行验证码校验
        $captcha = new Captcha();
        if (!$captcha->check(input('yzm'))) {
            $this->error('验证码输入不正确！');
            exit();
        }
        unset($save_data['yzm']);
        // 在后台程序还需校验非空、校验字段合法性

        $user_vali = validate('user');

        // !非false
        if (!$user_vali->check($save_data)) {
            $this->error($user_vali->getError());
        }
        $save_data['createtime'] = date('Y-m-d H:i:s');

        $user_vali = validate('user');
        // 键释放掉
        unset($save_data['repassword']);
        unset($save_data['yzm']);
        $password=$save_data['password'];
        $save_data['password']=md5($password.$this->salt);

        db('user')->insert($save_data);
        $this->success('注册成功','login/login');
    }
    public function login(){
        if(Session::get('uinfo')){
              $this->redirect('index/index');
        }
        else return  $this->fetch();
    }

    public function dologin(){
        $username  = input('username');
        $password  = md5(input('password').$this->salt);
        //查询用户表有没有这些信息的用户

        $info =  db('user')->where("username='$username' and password='$password'")->find();
        if (empty($info)) {
            $this->error('用户名或密码不正确！');
        }else{
            Session::set('uinfo',$info);
            $this->success('登陆成功','index/index');

        }
    }

    public function logout(){
        Session::delete('uinfo');
        $this->redirect('login/login');
    }
}