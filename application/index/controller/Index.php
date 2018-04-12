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

    public function upload(){           //上传文件

        if ((@$_FILES["file"]["type"] == "image/gif") || (@$_FILES["file"]["type"] == "image/jpeg")
            || (@$_FILES["file"]["type"] == "imagepeg") || (@$_FILES["file"]["type"] == "image/png")
            &&(@$_FILES["file"]["size"] <500 * 1024 * 1024)){
            $files['filetype']=1;
            $files['filename']=@$_FILES["file"]["name"];
            $files['filesize']=@$_FILES["file"]["size"];
            $files['useId']=Session::get('uinfo')['userId'];
            $files['folderid']=$_POST('parentid');
            $files['createtime']=date(date('Y-m-d H:i:s'));
            
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
    public function search(){       //查找功能
        $data=$_GET('search');
        $list1=db('folder')->where(['userId'=>Session::get('uinfo')['userId'],'filename'=>array('like',$data),'isrecovery'=>0])->select();
        $list2=db('files')->where(['userId'=>Session::get('uinfo')['userId'],'filename'=>array('like',$data),'isrecovery'=>0])->select();
        $list3=array_merge($list1,$list2);
        echo json_encode($list3);
    }

    public function rename(){
        $foldername=$_POST['foldername'];
        $folderid=$_POST['folderid'];
        $parenid=$_POST['parentid'];
        $userId=Session::get('uinfo')['userId'];
        $list=db('folder')->where(['userId'=>$userId,'parentid'=>$parenid,'foldername'=>$foldername])->select();
        if($list==null){
            db('folder')->where(['userId'=>$userId,'folderid'=>$folderid])->data(['foldername'=>$foldername])->update();
             $this->success('重命名成功','index/index.html?path='.$parenid);
        }
        else $this->error('文件名已存在','index/index.html?path='.$parenid);
    }

    public function remove(){           //移动
        $parentid=$_POST['parentid'];
    }
    public function delete(){           //删除

        $id=$_POST['foldername'];

    }
    public function download(){         //下载


    }
    public function showfile(){         //展示文件
        $path=$_GET['path'];
        $where['userId']=Session::get('uinfo')['userId'];
        $where['parentid']=$path;
        $where['isrecovery']=0;
        $list=db('folder')->where($where)->select();
        echo json_encode($list);
    }
    public function Photos(){   // 查找图片类型的文件
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=1;
        $list=db('files')->where($where)->select();
        $where['isrecovery']=0;
        echo json_encode($list);
    }
    public function Documnets(){   //查找文档类型的文件
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=2;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Music(){     //查找音乐类型的文件
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=3;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Video(){     //查找视频类型的文件
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=4;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function Others(){       //查找其他类型的文件
        $where['userId']=Session::get('uinfo')['userId'];
        $where['filetype']=5;
        $list=db('files')->where($where)->select();
        echo json_encode($list);
    }
    public function recycleBin(){     //回收站
        $list1=db('folder')->where(['userId'=>Session::get('uinfo')['userId'],'isrecovery'=>1])->select();


    }
}



