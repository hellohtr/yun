<?php
/**
 * Created by IntelliJ IDEA.
 * User: wb.huangturong
 * Date: 2018/4/26
 * Time: 16:35
 */
namespace app\index\controller;
use think\Controller;
use think\Session;
class Information extends Controller{
    public function index(){
        if(Session::get('uinfo')){
            return $this->fetch();
        }else return  $this->redirect('login/login');

    }
    public function alterInformation(){

    }

}