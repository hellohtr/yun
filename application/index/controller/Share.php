<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/4/28
 * Time: 21:07
 */
namespace app\index\controller;
use think\Controller;
class Share extends Controller{
    public function share(){
        return $this->fetch();
    }
    public function Show(){
        $path=$_POST['path'];
        $type=$_POST['type'];
        $arr=Array();
        if($type==0){
           $folder= db('folder')->where(['parentid'=>$path,'is_recycle'=>0,'is_share'=>1])->select();
            foreach ($folder as $value){
                $tmp['id']=$value['folderid'];
                $tmp['createtime']=$value['createtime'];
                $tmp['type']=0;
                $tmp['name']=$value['foldername'];
                array_push($arr,$tmp);
            }
            $file=db('files')->where(['folderid'=>$path,'is_recycle'=>0,'is_share'=>1])->select();
            foreach ($file as $value){
                $tmp['name']=$value['filename'];
                $tmp['id']=$value['fileid'];
                $tmp['type']=$value['filetype'];
                $tmp['size']=$value['filesize'];
                $tmp['createtime']=$value['createtime'];
                array_push($arr,$tmp);
            }
        }
        else{
                $list=db('files')->where('fileid',$path)->find();
                $tmp['id']=$list['fileid'];
                $tmp['name']=$list['filename'];
                $tmp['type']=$list['filetype'];
                $tmp['size']=$list['filesize'];
                $tmp['createtime']=$list['createtime'];
                array_push($arr,$tmp);
            }
         echo json_encode($arr);
    }
    public function download()
    { //下载
        $fileid = $_GET['id'];
        $list = db('files')->where('fileid', $fileid)->find();
        $filename = $list['filepath'];
        $basename = $list['filename'];

//        header("Content-Type:image/png");
//        header("Content-Disposition:attachment;filename=" . $basename);
//        header("Content-Length:" . $list['filesize']);
//        echo file_get_contents($filename);
        echo json_encode(['url'=>$filename,'name'=>$basename]);
    }

}