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
        $add_data=$_POST['arr'];
        $userId=Session::get('uinfo')['userId'];
        db('user')->where('userId',$userId)->update($add_data);
    }
    public function getInformation(){
       $userId=Session::get('uinfo')['userId'];
       $data=db('user')->where('userId',$userId)->select();
       echo json_encode($data);
    }
}