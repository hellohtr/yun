<?php
namespace app\index\controller;
use think\Controller;
use think\Env;
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
   public function  share(){                //分享功能

   }
   public function createFolder(){          //新建文件夹

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
        $files['filename']=@$_FILES["file"]["name"];
        $files['filesize']=@$_FILES["file"]["size"];
        $files['userId']=Session::get('uinfo')['userId'];
        $files['folderid']=$_POST['parentid'];
        $files['createtime']=date(date('Y-m-d H:i:s'));
        $datetime=strtotime($files['createtime']);
        $temp=explode('.',$files['filename']);
        $type=strtolower($temp[sizeof($temp)-1]);

        function createFolder($path){
            if (!file_exists($path)) {
                createFolder(dirname($path));
                mkdir($path, 0777);
            }
        }
        echo @$_FILES["file"]["type"];
        if (preg_match('/(png|jpg|gif|bmp|jpeg)/',$type)
            &&(@$_FILES["file"]["size"] <500 * 1024 * 1024)){
            $files['filetype']=1;
            createFolder("upload/".$files['userId']."/image/");
            move_uploaded_file($_FILES["file"]["tmp_name"],"upload/{$files['userId']}/image/".$datetime.'.'.$type);
            $files['filepath']="upload/{$files['userId']}/image/".$datetime.'.'.$type;
            db('files')->insert($files);
        }
        elseif(preg_match('/(avi|mp4|mov|mpeg|mpg|ram|qt)/',$type)
            &&(@$_FILES["file"]["size"] <500 * 1024 * 1024)){
            $files['filetype']=4;
            createFolder("upload/".$files['userId']."/video/");
            move_uploaded_file($_FILES["file"]["tmp_name"],"upload/{$files['userId']}/video/".$datetime.'.'.$type);
            $files['filepath']="upload/{$files['userId']}/video/".$datetime.'.'.$type;
            db('files')->insert($files);
        }
        elseif(preg_match('/(doc|docx|txt|xls|pdf|ppt)/',$type)
            &&(@$_FILES["file"]["size"] <500 * 1024 * 1024)){
            $files['filetype']=2;
            $datetime=strtotime($files['createtime']);
            createFolder("upload/".$files['userId']."/document/");
            move_uploaded_file($_FILES["file"]["tmp_name"],"upload/{$files['userId']}/document/".$datetime.'.'.$type);
            $files['filepath']="upload/{$files['userId']}/document/".$datetime.'.'.$type;
            db('files')->insert($files);
        }
        elseif (preg_match('/(mp3|asf|asp|au|mid|wav|asx)/',$type)
            &&(@$_FILES["file"]["size"] <500 * 1024 * 1024)){
            $files['filetype']=3;
            $datetime=strtotime($files['createtime']);
            createFolder("upload/".$files['userId']."/music/");
            move_uploaded_file($_FILES["file"]["tmp_name"],"upload/{$files['userId']}/music/".$datetime.'.'.$type[1]);
            $files['filepath']="upload/{$files['userId']}/music/".$datetime.'.'.$type;
            db('files')->insert($files);
        }
        else{
            $files['filetype']=5;
            $datetime=strtotime($files['createtime']);
            createFolder("upload/".$files['userId']."/other/");
            move_uploaded_file($_FILES["file"]["tmp_name"],"upload/{$files['userId']}/other/".$datetime.'.'.$type);
            $files['filepath']="upload/{$files['userId']}/other/".$datetime.'.'.$type;
            db('files')->insert($files);
        }



    }
    public function search(){       //查找功能
        $data=$_GET('search');
        $list1=db('folder')->where(['userId'=>Session::get('uinfo')['userId'],'filename'=>array('like',$data),'is_recycle'=>0])->select();
        $list2=db('files')->where(['userId'=>Session::get('uinfo')['userId'],'filename'=>array('like',$data),'is_recycle'=>0])->select();
        $list3=array_merge($list1,$list2);
        echo json_encode($list3);
    }

    public function rename(){
        $foldername=$_POST['foldername'];
        $folderid=$_POST['folderid'];
        $parenid=$_POST['parentid'];
        $userId=Session::get('uinfo')['userId'];
        $list=db('folder')->where(['userId'=>$userId,'parentid'=>$parenid,'foldername'=>$foldername,'is_recycle'=>0])->select();
        if($list==null){
            db('folder')->where(['userId'=>$userId,'folderid'=>$folderid])->data(['foldername'=>$foldername])->update();
             $this->success('重命名成功','index/index.html?path='.$parenid);
        }
        else $this->error('文件名已存在','index/index.html?path='.$parenid);
    }

    public function remove(){           //移动
        $parentid=$_POST['parentid'];
        $folderid=$_POST['folderid'];
        $type=$_POST['type'];
        if($type=='file'){
            db('files')->where('fileid',$folderid)->update('folderid',$parentid);
        }
        else{
            db('folder')->where('folderid',$folderid)->update('parentid',$parentid);
        }
    }
    public function delete(){           //删除
        $add_data['createtime']=date(date('Y-m-d H:i:s'));
        $add_data['fid']=$_POST['id'];
        $add_data['type']=$_POST['type'];
        if($_POST['type']=='file'){
            $fileid=$_POST['id'];
            db('files')->where('fileid',$fileid)->update('is_recycle',1);
            db('bin')->insert($add_data);
        }
        else{
            $folderid=$_POST['id'];
            db('folder')->where('folderid',$folderid)->update('is_recycle',1);
            db('bin')->insert($add_data);
        }


    }
    public function download(){ //下载
        $fileid=$_GET('fileid');
        $list=db('files')->where('fileid',$fileid)->select();
//        import('ORG.Net.Http');
//        $http=new \ORG\Net\Http();
        $filename=$list['path'];
        $basename=$list['filename'];
        header("Content-Type:image/png");
        header("Content-Disposition:attachment;filename=".$basename['basename']);
        header("Content-Length:".$list['filesize']);
        readfile($filename);



    }
    public function showfolder(){         //展示文件
        $path=$_GET['path'];
        $where['userId']=Session::get('uinfo')['userId'];
        $where['parentid']=$path;
        $where['is_recycle']=0;
        $listfloder=db('folder')->where($where)->select();

        echo json_encode($listfloder);

    }
    public function showfile(){
        $path=$_GET['path'];
        $where['userId']=Session::get('uinfo')['userId'];
        $where['folderid']=$path;
        $where['is_recycle']=0;
        $listfile=db('files')->where($where)->select();
        echo json_encode($listfile);
    }
    public function showtype(){
        $where['filetype']=$_GET['type'];
        $where['userId']=Session::get('uinfo')['userId'];
        $where['is_recycle']=0;
        $show=db('files')->where($where)->select();
        echo json_encode($show);
    }

    public function recycleBin(){     //回收站
        $userId=Session::get('uinfo')['userId'];
        $listbin=db('bin')->where('userId',$userId)->select();
        echo json_encode($listbin);



    }
}



