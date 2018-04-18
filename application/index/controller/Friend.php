<?php
/**
 * Created by IntelliJ IDEA.
 * User: wb.huangturong
 * Date: 2018/4/12
 * Time: 15:27
 */
namespace app\app\index\controller;
use think\Controller;
use think\Session;

class Friend extends Controller{
    public function friend(){

        return $this->fetch();
    }
    public function loadFriend(){
        $userId=Session::get('uinfo')['userId'];
        $list=db('friend')->where('userId1',$userId)->whereOr('userId2',$userId);
    }
    public function addFriend(){
        $userId1=Session::get('uinfo')['userId'];
        $userId2=$_POST('userid');
        $createtime=date(date('Y-m-d H:i:s'));
        $list=db('friend')->where(['userId1'=>$userId1,'userId2'=>$userId2])->whereOr(['userId1'=>$userId2,'userId2'=>$userId1])->select();
        if($list){
            db('friend')->insert(['userId1'=>$userId1,'userId2'=>$userId2,'createtime'=>$createtime]);
        }
    }
    public function deleteFriend(){
        
    }
    public function sendMessage(){

    }
    public function loadMessage(){

    }
}