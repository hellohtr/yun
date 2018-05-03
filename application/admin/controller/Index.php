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
    public function showUser(){
        $userlist=db('user')->where(['isadmin'=>0])->field('userId,username,phone,sex,age,mail')->select();
        echo json_encode($userlist);
    }
    public function deleteUser(){
        $user=$_POST['id'];
        db('bin')->where('userId',$user)->delete();
        db('share')->where('userId',$user)->delete();
        db('files')->where('userId',$user)->delete();
        db('folder')->where('userId',$user)->delete();
        db('user')->where('userId',$user)->delete();
        $path="../../upload/".$user;
        $this->delDir($path);
        $this->success('删除用户成功');

    }
    public function addUser(){
        $user=$_POST['user'];
        $list=db('user')->where('$userId',$user['userId'])->find();
        if($list){
            $this->error('用户已存在');
        }
        db('user')->insert($user);
           $this->success('添加用户成功');
    }
    public function alterUser(){
        $user=$_POST['user'];

            db('user')->where('userId',$user['userId'])->update($user);
            $this->success('修改用户成功');

    }
    public function deleteFile(){
        $file = $_POST['file'];
        foreach ($file as $value){
            $path=db('files')->where('fileid',$value['id'])->field('filepath')->find();
            unlink('../../'.$path);
           db('files')->where('fileid',$value['id'])->delete();
           db('bin')->where(['id'=>$value['id'],array('neq',0)])->delete();
           db('share')->where(['id'=>$value['id'],'type'=>array('neq',0)])->delete();
        }
        $this->success('删除文件成功');
    }
    public function showfile(){
        $arr=[];
        $listFile= db('files')->field('fileid,filename,filesize,filetype,userId,createtime')->order('userId')->select();
        foreach ($listFile as $value){
            $username=db('user')->where('userId',$value['userId'])->field('username')->find();
           $value['username']=$username['username'];
           array_push($arr,$value);
        }
        echo json_encode($arr);
    }
    public function delDir($dir){  //删除文件夹及文件夹下的文件
        if(!is_dir($dir)){
        }
        $handle=dir($dir);
        while(false!== ($entry=$handle->read())){
            if(($entry!=".")&&($entry!="..")){
                if(is_file($dir."/".$entry)){
                    unlink($dir."/".$entry);
                }else{
                    delDir($dir."/".$entry);
                }
            }
        }
        $handle->close();
        rmdir($dir);
    }
    public function deleteShare(){
        $share=$_POST['arr'];
        foreach ($share as $value){
            db('share')->where('sid',$value['id'])->delete();
        }
        $this->success('删除分享成功');

    }
    public function showShare(){
        $arr=[];
        $share=db('share')->order('userId')->select();
        foreach ($share as $value){
            $username=db('user')->where('fileid',$value['userId'])->field('filename')->find();
            $value['username']=$username['username'];
            if($value['type']==0){
                $name=db('folder')->where('folderid',$value['id'])->field('foldername')->find();
                $value['name']=$name['foldername'];
            }else{
                $name=db('files')->where('fileid',$value['id'])->field('filename')->find();
                $value['name']=$name['filename'];
            }
            array_push($arr,$value);
        }
        echo json_encode($arr);
    }
    public function searchUser(){
        $search=$_POST['search'];
        $userlist=db('user')->where(['isadmin'=>0, 'username' => array('like','%'. $search.'%')])->field('userId,username,phone,sex,age,mail')->select();
        echo json_encode($userlist);
    }
    public function searchFileByName(){
        $search=$_POST['search'];
        $arr=[];
        $listFile= db('files')->where(['filename'=>array('like','%'.$search.'%')])-> field('fileid,filename,filesize,filetype,userId')->order('userId')->select();
        foreach ($listFile as $value){
            $username=db('userId')->where('userId',$value['userId'])->field('username')->find();
            $value['username']=$username['username'];
            array_push($arr,$value);
        }
        echo json_encode($arr);
    }
    public function searchFileByUser(){
        $search=$_POST['search'];
        $arr=array();
        $user=db('user')->where(['username'=>array('like','%'.$search.'%')])->field('userId,username')-> select();
        foreach($user as $tmp){
            $listFile= db('files')->where('fileId',$tmp['userId'])-> field('fileid,filename,filesize,filetype,')->order('userId')->select();
            foreach ($listFile as $value){
                $value['username']=$tmp['username'];
                array_push($arr,$value);
            }
        }
        echo json_encode($arr);
    }

    public function searchShareByFile(){
        $search=$_POST['search'];
        $arr=array();
        $share=db('share')->order('userId')->select();
        foreach ($share as $value){
            $username=db('user')->where('fileid',$value['userId'])->field('filename')->find();
            $value['username']=$username['username'];
            if($value['type']==0){
                $name=db('folder')->where(['folderid'=>$value['id'],'foldername'=>array('like','%'.$search.'%')])->field('foldername')->find();
                if($name){
                    $value['name']=$name['foldername'];
                    array_push($arr,$value);
                }

            }else{
                $name=db('files')->where(['fileid'=>$value['id'],'filename'=>array('like','%'.$search.'%')])->field('filename')->find();
                $value['name']=$name['filename'];
                array_push($arr,$value);
            }

        }
        echo json_encode($arr);
    }
    public function searchShareByUser(){
        $search=$_POST['search'];
        $arr=array();
        $user=db('user')->where (['username'=>array('like','%'.$search.'%')])->field('userId,username')->select();
        foreach ($user as $tmp){
            $share=db('share')->where('userId',$tmp['userId'])->select();
            foreach ($share as $value){
                $value['username']=$tmp['username'];
                if($value['type']==0){
                    $name=db('folder')->where('folderid',$value['id'])->field('foldername')->find();
                    $value['name']=$name['foldername'];
                }else {
                    $name = db('files')->where('fileid', $value['id'])->field('filename')->find();
                    $value['name'] = $name['filename'];
                }
                array_push($arr,$value);
            }
        }
        echo json_encode($arr);

    }






}