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
    private $salt='er45wi6HRI21U42Eolkj';
    public function index(){
        if(Session::get('uinfo')){
            if(Session::get('uinfo')['isadmin']==1){
                return $this->fetch();
            }
            else return redirect('../../index/index/index');

        }else return redirect('../../index/login/login');
    }
    public function showUser(){
        $userlist=db('user')->field('userId,username,phone,sex,age,mail')->select();
        echo json_encode($userlist);
    }
    public function deleteUser(){
        $user=$_POST['id'];
        function deldir($dir) {
 //先删除目录下的文件：
            if(is_dir($dir)){
                $dh=opendir($dir);
                while ($file=readdir($dh)) {
                    if($file!="." && $file!="..") {
                        $fullpath=$dir."/".$file;
                        if(!is_dir($fullpath)) {
                            unlink($fullpath);
                        } else {
                            deldir($fullpath);
                        }
                    }
                }
                closedir($dh);
//删除当前文件夹：
                rmdir($dir);

            }

        }
        $path="../upload/".$user;
        $tmp=deldir($path);
        db('bin')->where('userId',$user)->delete();
        db('share')->where('userId',$user)->delete();
        db('files')->where('userId',$user)->delete();
        db('folder')->where('userId',$user)->delete();
        db('user')->where('userId',$user)->delete();
        echo 1;

    }
    public function addUser(){
        $data['username']=$_POST['username'];
        $password=$_POST['password'];
        $data['age']=$_POST['age'];
        $data['sex']=$_POST['sex'];
        $data['phone']=$_POST['phone'];
        $data['mail']=$_POST['mail'];
        $data['isadmin']=$_POST['isadmin'];

        $data['createtime']=date(date("Y-m-d H:i:s"));
        $user=db('user')->where('username',$data['username'])->find();
        if(strlen($password)<6){
            echo 0;
        }elseif ($user){
            echo 1;
        }else{
            $data['password']=md5($password.$this->salt);
            db('user')->insert($data);
            echo 2;
        }
    }
    public function alterUser(){
        $user=$_POST['user'];

            db('user')->where('userId',$user['userId'])->update($user);
            $this->success('修改用户成功');

    }
    public function deleteFile(){
        $file = $_POST['fileid'];

            $path=db('files')->where('fileid',$file)->field('filepath')->find();
            unlink('../'.$path['filepath']);
           db('files')->where('fileid',$file)->delete();
           db('bin')->where(['id'=>$file,'type'=>array('neq',0)])->delete();
           db('share')->where(['id'=>$file,'type'=>array('neq',0)])->delete();

        echo 1;
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

    public function deleteShare(){
        $sid=$_POST['shareid'];
        $list=db('share')->where('sid',$sid)->find();
        function Recursion($id)
        {
            $list = db('folder')->where(['parentid' => $id, 'is_share' => 1])->select();
            foreach ($list as $item) {
                Recursion($item['folderid']);
            }
            db('folder')->where('folderid', $id)->update(['is_share' => 0]);
            db('files')->where('folderid', $id)->update(['is_share' => 0]);
        }
        if($list['type']==0){
            Recursion($list['id']);
            db('share')->where('sid',$sid)->delete();
        }
        else{
            db('files')->where('fileid',$list['id'])->update(['is_share' => 0]);
            db('share')->where('sid',$sid)->delete();
        }
        echo 1;


    }
    public function showShare(){
        $arr=[];
        $share=db('share')->order('userId')->select();
        foreach ($share as $value){
            $username=db('user')->where('userId',$value['userId'])->field('username')->find();
            $value['username']=$username['username'];
            if($value['type']==0){
                $name=db('folder')->where('folderid',$value['id'])->field('foldername')->find();
                $value['name']=$name['foldername'];

            }else{
                $name=db('files')->where('fileid',$value['id'])->field('filename,filetype,filesize')->find();
                $value['name']=$name['filename'];
                $value['type']=$name['filetype'];
                $value['filesize']=$name['filesize'];
            }
            array_push($arr,$value);
        }
        echo json_encode($arr);
    }
    public function searchUser(){
        $search=$_GET['search'];
        $userlist=db('user')->where(['isadmin'=>0, 'username' => array('like','%'. $search.'%')])->field('userId,username,phone,sex,age,mail')->select();
        echo json_encode($userlist);
    }
    public function searchFileByName(){
        $search=$_GET['search'];
        $arr=[];
        $listFile= db('files')->where(['filename'=>array('like','%'.$search.'%')])-> field('fileid,filename,filesize,filetype,userId,createtime')->order('userId')->select();
        foreach ($listFile as $value){
            $username=db('user')->where('userId',$value['userId'])->field('username')->find();
            $value['username']=$username['username'];
            array_push($arr,$value);
        }
        echo json_encode($arr);
    }
    public function searchFileByUser(){
        $search=$_GET['search'];
        $arr=array();
        $user=db('user')->where(['username'=>array('like','%'.$search.'%')])->field('userId,username')-> select();
        foreach($user as $tmp){
            $listFile= db('files')->where('userId',$tmp['userId'])-> field('fileid,filename,filesize,filetype,createtime')->order('userId')->select();
            foreach ($listFile as $value){
                $value['username']=$tmp['username'];
                array_push($arr,$value);
            }
        }
        echo json_encode($arr);
    }

    public function searchShareByFile(){
        $search=$_GET['search'];
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
                $name=db('files')->where(['fileid'=>$value['id'],'filename'=>array('like','%'.$search.'%')])->field('filename,filetype,filesize')->find();
                $value['name']=$name['filename'];
                $value['type']=$name['type'];
                $value['filesize']=$name['filesize'];
                array_push($arr,$value);
            }

        }
        echo json_encode($arr);
    }
    public function searchShareByUser(){
        $search=$_GET['search'];
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
                    $name = db('files')->where('fileid', $value['id'])->field('filename,filetype')->find();
                    $value['name'] = $name['filename'];
                    $value['type']=$name['filetype'];
                }
                array_push($arr,$value);
            }
        }
        echo json_encode($arr);

    }
    public function modifyUser(){
        $userId=$_POST['userId'];
        $data['sex']=$_POST['sex'];
        $data['phone']=$_POST['phone'];
        $data['age']=$_POST['age'];
        $data['mail']=$_POST['mail'];
        db('user')->where('userId',$userId)->update($data);
        echo 1;
    }
    public function modifyFile(){
        $fileid=$_POST['fileid'];
        $filename=$_POST['filename'];
        db('files')->where('fileid',$fileid)->update(['filename'=>$filename]);
        $this->success('修改文件成功');
    }









}