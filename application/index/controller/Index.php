<?php
namespace app\index\controller;
use think\Controller;
use think\Session;

class Index extends Controller
{
    public function index()
    {
        if(Session::get('uinfo')){

          return   $this->fetch();
        }
        else return  $this->redirect('login/login');


    }
   public function  share(){
       if(Session::get('uinfo')){
           return   $this->fetch();
       }
       else return  $this->redirect('login/login');
   }
   public function createFolder(){

       $add_data ['foldername']=$_POST['foldername'];

       $add_data['parentid']=$_POST['parentid'];
       if($add_data==null){
           $this->error('文件名为空','index/index');
       }
       else{
           $add_data['userId']=Session::get('uinfo')['userId'];

           $add_data['createtime'] = date(date('Y-m-d H:i:s'));
           $list=db('folder')->where(['userId'=>$add_data['userId'],'parentid'=>$add_data['parentid'],'foldername'=>$add_data['foldername']])->find();
           if($list==null){
               db('folder')->insert($add_data);
               $this->success('新建文件夹成功');
           }
           else{
                $this->error('文件名已存在');

           }


       }

   }

    public function upload(){
        if ((@$_FILES["file"]["type"] == "image/gif") || (@$_FILES["file"]["type"] == "image/jpeg")
            || (@$_FILES["file"]["type"] == "imagepeg") || (@$_FILES["file"]["type"] == "image/png")
            &&(@$_FILES["file"]["size"] <500 * 1024 * 1024)){
            function createFolder($path){
                if (!file_exists($path)) {
                    createFolder(dirname($path));
                    mkdir($path, 0777);
                }
            }
            createFolder("upload/");
            move_uploaded_file($_FILES["file"]["tmp_name"],"upload/i.jpg");
        }


    }
    public function download(){

    }
    public function showfile(){
        $path=$_GET['path'];
        $where['userId']=Session::get('uinfo')['userId'];
        $where['parentid']=$path;
        $list=db('folder')->where($where)->select();
        echo json_encode($list);
    }
    public function Photos(){
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=1;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Documnets(){
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=2;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Music(){
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=3;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Video(){
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=4;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Others(){
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=5;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
}



