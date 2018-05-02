<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/2
 * Time: 23:51
 */
namespace app\admin\controller;
use think\Controller;
use think\Session;
class Index extends Controller{
    public function index(){
       // if(Session::get('uinfo')){
            return $this->fetch();
    //    }else return redirect('../../index/login/login');
    }
}