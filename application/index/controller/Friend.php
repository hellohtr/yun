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
        $userId1=$_GET('friendid');
        db('friend')->where(['userId1'=>Session::get('uinfo')['userId'],'userId2'=>$userId1])->whereOr(['userId1'=>$userId1,'userId2'=>Session::get('uinfo')['userId']])->delete();

    }
    public function sendMessage(){
        $add_data['content']=$_POST('concent');
        $add_data['sender']=Session::get('uinfo')['userId'];
        $add_data['accepter']=$_POST('friend');
        $add_data['createtime']=date(date('Y-m-d H:i:s'));
        db('message')->insert($add_data);


    }
    public function loadMessage(){

    }
}