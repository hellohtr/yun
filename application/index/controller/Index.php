<?php

namespace app\index\controller;

use think\Controller;
use think\Env;
use think\Session;

class Index extends Controller
{
    private $salt='er45wi6HRI21U42Eolkj';
    public function index()
    {
        if (Session::get('uinfo')) {

            return $this->fetch();
        } else return $this->redirect('login/login');
    }

    public function showShare()
    {        //分享功能
        $arr = array();
        $userId = Session::get('uinfo')['userId'];
        $listShare = db('share')->where('userId', $userId)->select();
        foreach ($listShare as $value) {
            if ($value['type'] == 0) {
                $tmp = db('folder')->where(['folderid'=>$value['id'],'is_recycle'=>0])->find();
                if($tmp) {
                    $value['name'] = $tmp['foldername'];
                    $value['type'] = 0;
                    array_push($arr, $value);
                }
            }
            else {
                $tmp = db('files')->where(['fileid'=>$value['id'],'is_recycle'=>0])->find();
                if($tmp) {
                    $value['name'] = $tmp['filename'];
                    $value['type'] = $tmp['filetype'];
                    $value['filesize'] = $tmp['filesize'];
                    array_push($arr, $value);
                }

            }

        }
        echo json_encode($arr);
    }

    public function createFolder()
    {          //新建文件夹

        $add_data ['foldername'] = $_POST['foldername'];

        $add_data['parentid'] = $_POST['parentid'];
        if ($add_data == null) {
            $this->error('文件名为空', 'index/index');
        } else {
            $add_data['userId'] = Session::get('uinfo')['userId'];

            $add_data['createtime'] = date(date('Y-m-d H:i:s'));
            $list = db('folder')->where(['userId' => $add_data['userId'], 'parentid' => $add_data['parentid'], 'is_recycle' => 0, 'foldername' => $add_data['foldername']])->find();
            if ($list == null) {
                db('folder')->insert($add_data);
                $this->success('新建文件夹成功');
            } else {
                $this->error('文件名已存在');
            }
        }
    }

    public function upload()
    {               //上传功能
        $files['filename'] = @$_FILES["file"]["name"];
        $files['filesize'] = @$_FILES["file"]["size"];
        $files['userId'] = Session::get('uinfo')['userId'];
        $files['folderid'] = $_POST['parentid'];
        $files['createtime'] = date(date('Y-m-d H:i:s'));
        $datetime = strtotime($files['createtime']);
        $temp = explode('.', $files['filename']);
        $type = strtolower($temp[sizeof($temp) - 1]);
        function createFolder($path)
        {
            if (!file_exists($path)) {
                createFolder(dirname($path));
                mkdir($path, 0777);
            }
        }

        echo @$_FILES["file"]["type"];
        if (@$_FILES["file"]["size"] > 500 * 1024 * 1024) {
            $this->error('上传文件超过500M');
        } else {
            if (preg_match('/(png|jpg|gif|bmp|jpeg)/', $type)) {
                $files['filetype'] = 1;
                createFolder("../upload/" . $files['userId'] . "/image/");
                move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/{$files['userId']}/image/" . $datetime . '.' . $type);
                $files['filepath'] = "upload/{$files['userId']}/image/" . $datetime . '.' . $type;
                db('files')->insert($files);
            } elseif (preg_match('/(avi|mp4|mov|mpeg|mpg|ram|qt)/', $type)) {
                $files['filetype'] = 4;
                createFolder("../upload/" . $files['userId'] . "/video/");
                move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/{$files['userId']}/video/" . $datetime . '.' . $type);
                $files['filepath'] = "upload/{$files['userId']}/video/" . $datetime . '.' . $type;
                db('files')->insert($files);
            } elseif (preg_match('/(doc|docx|txt|xls|pdf|ppt)/', $type)) {
                $files['filetype'] = 2;
                $datetime = strtotime($files['createtime']);
                createFolder("../upload/" . $files['userId'] . "/document/");
                move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/{$files['userId']}/document/" . $datetime . '.' . $type);
                $files['filepath'] = "upload/{$files['userId']}/document/" . $datetime . '.' . $type;
                db('files')->insert($files);
            } elseif (preg_match('/(mp3|asf|asp|au|mid|wav|asx)/', $type)) {
                $files['filetype'] = 3;
                $datetime = strtotime($files['createtime']);
                createFolder("../upload/" . $files['userId'] . "/music/");
                move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/{$files['userId']}/music/" . $datetime . '.' . $type);
                $files['filepath'] = "upload/{$files['userId']}/music/" . $datetime . '.' . $type;
                db('files')->insert($files);
            } else {
                $files['filetype'] = 5;
                $datetime = strtotime($files['createtime']);
                createFolder("../upload/" . $files['userId'] . "/other/");
                move_uploaded_file($_FILES["file"]["tmp_name"], "../upload/{$files['userId']}/other/" . $datetime . '.' . $type);
                $files['filepath'] = "upload/{$files['userId']}/other/" . $datetime . '.' . $type;
                db('files')->insert($files);
            }
        }
    }

    public function search()
    {//查找功能
        $data = $_GET['search'];
        $list2 = db('files')->where(['userId' => Session::get('uinfo')['userId'], 'filename' => array('like','%'. $data.'%'), 'is_recycle' => 0])->select();
        echo json_encode($list2);
    }

    public function rename()
    {       //重命名
        $arr=$_POST['arr'];
        $type = $arr[0]['type'];
        $name = $arr[0]['name'];
        $id = $arr[0]['id'];
        $userId = Session::get('uinfo')['userId'];
        if ($type == 0) {
            $tmp=db('folder')->where('folderid',$id)->find();

            $list = db('folder')->where(['userId' => $userId, 'parentid' => $tmp['parentid'], 'foldername' => $name, 'is_recycle' => 0])->find();
            if ($list == null ) {
                db('folder')->where(['userId' => $userId, 'folderid' => $id])->data(['foldername' => $name])->update();
                $this->success('重命名成功');
            } elseif ($list['folderid']==$id){
                db('folder')->where(['userId' => $userId, 'folderid' => $id])->data(['foldername' => $name])->update();
                $this->success('重命名成功');
            }else $this->error('文件夹名已存在');
        } else {
            $tmp=db('files')->where('fileid',$id)->find();
            $list=db('files')->where(['folderid'=>$tmp['folderid'],'filename'=>$name])->find();
            if($list==null or $list['fileid']==$id){
                db('files')->where(['userId' => $userId, 'fileid' => $id])->data(['filename' => $name])->update();
                $this->success('重命名成功');
            }elseif ($list['fileid']==$id){
                db('files')->where(['userId' => $userId, 'fileid' => $id])->data(['filename' => $name])->update();
                $this->success('重命名成功');
            }
            else {
                    $this->error('文件名已存在');
                }
        }
    }

    public function remove()
    {           //移动
        $type = $_POST['type'];
        $id = $_POST['id'];
        $parentid = $_POST['parentid'];
        $userId = Session::get('uinfo')['userId'];
        if ($type == 0) {
            db('folder')->where(['userId' => $userId, 'folderid' => $id])->data(['parentid' => $parentid])->update();
            $this->success('移动成功', 'index/index.html?path=' . $parentid);
        } else {
            db('files')->where(['userId' => $userId, 'fileid' => $id])->data(['parentid' => $parentid])->update();
            $this->success('移动成功', 'index/index.html?path=' . $parentid);
        }
    }

    public function Delete()
    {       //删除
        $arr = $_POST['arr'];
        $add_data['userId'] = Session::get('uinfo')['userId'];
        $add_data['createtime'] = date(date('Y-m-d H:i:s'));
        function Recursion($id)
        {
            $list = db('folder')->where(['parentid' => $id, 'is_recycle' => 0])->select();

            foreach ($list as $item) {
                Recursion($item['folderid']);
            }
            db('folder')->where('folderid', $id)->update(['is_recycle' => 1]);
            db('files')->where('folderid', $id)->update(['is_recycle' => 1]);

        }

        foreach ($arr as $key => $value) {
            if ($value['type'] == 0) {
                $add_data['id'] = $value['id'];
                $add_data['type'] = $value['type'];
                Recursion($add_data['id']);
                db('bin')->insert($add_data);
            } else {
                $add_data['id'] = $value['id'];
                $add_data['type'] = $value['type'];
                db('files')->where('fileid', $add_data['id'])->update(['is_recycle' => 1]);
                db('bin')->insert($add_data);
            }
        }
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

    public function showfolder()
    {         //展示文件夹
        $path = $_GET['path'];
        $where['userId'] = Session::get('uinfo')['userId'];
        $where['parentid'] = $path;
        $where['is_recycle'] = 0;
        $listfloder = db('folder')->where($where)->select();
        echo json_encode($listfloder);

    }
    public function deleteShare(){     //删除分享
         $data=$_POST['arr'];
        function Recursion($id)
        {
            $list = db('folder')->where(['parentid' => $id, 'is_share' => 1])->select();
            foreach ($list as $item) {
                Recursion($item['folderid']);
            }
            db('folder')->where('folderid', $id)->update(['is_share' => 0]);
            db('files')->where('folderid', $id)->update(['is_share' => 0]);
        }
         foreach ($data as $tmp){
             $list=db('share')->where('sid',$tmp['id'])->find();
             if($tmp['type']==0){
                 Recursion($list['id']);
                 db('share')->where('sid',$tmp['id'])->delete();
             }else {
                 db('files')->where('fileid',$list['id'])->update(['is_share' => 0]);
                 db('share')->where('sid',$tmp['id'])->delete();
             }
         }

    }

    public function addShare(){      //新增分享文件
        $add_data['userId']=Session::get('uinfo')['userId'];
        $add_data['createtime']=date(date("Y-m-d H:i:s"));
        function Recursion($id)
        {
            $list = db('folder')->where(['parentid' => $id, 'is_share' => 0])->select();
            foreach ($list as $item) {
                Recursion($item['folderid']);
            }
            db('folder')->where('folderid', $id)->update(['is_share' => 1]);
            db('files')->where('folderid', $id)->update(['is_share' => 1]);
        }
        $arr=$_POST['arr'];
        foreach($arr as $tmp){
            if($tmp['type']==0){
                $add_data['type']=$tmp['type'];
                $add_data['id']=$tmp['id'];
                Recursion($tmp['id']);
                db('share')->insert($add_data);
            }
            else{
                $add_data['type']=$tmp['type'];
                $add_data['id']=$tmp['id'];
                db('share')->insert($add_data);
                db('files')->where('folderid',$tmp['id'])->update(['is_share' => 1]);
            }
        }
        echo 1;
    }

    public function showfile()
    {     //展示文件
        $path = $_GET['path'];
        $where['userId'] = Session::get('uinfo')['userId'];
        $where['folderid'] = $path;
        $where['is_recycle'] = 0;
        $listfile = db('files')->where($where)->select();
        echo json_encode($listfile);
    }

    public function showtype()
    {     //展示分类文件
        $where['filetype'] = $_GET['type'];
        $where['userId'] = Session::get('uinfo')['userId'];
        $where['is_recycle'] = 0;
        $show = db('files')->where($where)->select();
        echo json_encode($show);
    }

    public function recycleBin()
    {     //回收站
        $userId = Session::get('uinfo')['userId'];
        $listbin = db('bin')->where('userId', $userId)->select();
        echo json_encode($listbin);
    }

    public function restore()
    {   //从回收站还原信息
        $arr = $_POST['arr'];
        function Recursion($id)
        {
            $list = db('folder')->where(['parentid' => $id,'is_recycle' => 1])->select();
            foreach ($list as $item) {
                Recursion($item['folderid']);
            }
            db('folder')->where('folderid', $id)->update(['is_recycle' => 0]);
            db('files')->where('folderid', $id)->update(['is_recycle' => 0]);
        }
        foreach ($arr as $value){
            $tmp=db('bin')->where('bid',$value['id'])->find();
            if($tmp['type']==0){
                Recursion($tmp['id']);
                db('bin')->where('bid',$value['id'])->delete();
            }
            else{
                db('files')->where('fileid',$tmp['id'])->update(['is_recycle' => 0]);
                db('bin')->where('bid',$value['id'])->delete();
            }
        }

    }

    public function deleteAll()
    {   //清空回收站
        $userId = Session::get('uinfo')['userId'];

        $file=db('files')->where(['userId' => $userId, 'is_recycle' => 1])->select();
        foreach ($file as $value){
            $url='../'.$value['filepath'];
            unlink($url);
        }

        db('files')->where(['userId'=>$userId,'is_recycle'=>1])->delete();
        db('folder')->where(['userId' => $userId, 'is_recycle' => 1])->delete();
        db('bin')->where('userId', $userId)->delete();

    }

    public function Copy()
    {
        $userId = Session::get('uinfo')['userId'];
        $id = $_POST['id'];
        $parentid = $_POST['parentid'];
        $type = $_POST['type'];
        if ($type == 0) {

        } else {
            $add_data = db('files')->where('fileid', $id)->select();
            unset($add_data['fileid']);
            unset($add_data['folderid']);
            unset($add_data['createtime']);
            $add_data['folderid'] = $parentid;
            $add_data['createtime'] = date(date('Y-m-d H:i:s'));
            db('files')->insert($add_data);
        }
    }

    public function showBin()
    {  //展示回收站信息
        $arr = array();
        $userId = Session::get('uinfo')['userId'];
        $listFolder = db('bin')->where(['userId' => $userId])->select();
        foreach ($listFolder as $value) {
            if ($value['type'] == 0) {
                $tmp = db('folder')->where('folderid', $value['id'])->find();
                $value['name']=$tmp['foldername'];
                unset($value['id']);
                array_push($arr, $value);
            } else {
                $tmp = db('files')->where('fileid', $value['id'])->find();
                $value['name']=$tmp['filename'];
                $value['filesize']=$tmp['filesize'];
                $value['type']=$tmp['filetype'];
                unset($value['id']);
               array_push($arr, $value);
            }
        }
        echo json_encode($arr);
    }

    public function modifypassword(){
        $old=$_POST['oldpassword'];
        $new=$_POST['newpassword'];
        $re=$_POST['repassword'];
        $userId=Session::get('uinfo')['userId'];

        $user=db('user')->where('userId',$userId)->field('password')->find();
        $password  = md5($old.$this->salt);
        if($password==$user['password']){
            if(strlen($new)>=6  && strlen($re)>=6){
                if($new==$re){
                    $newpassword=md5($new.$this->salt);
                    db('user')->where('userId',$userId)->update(['password'=>$newpassword]);
                    echo 3;
                }else echo 2;
            }else{
                echo 1;
            }
        }

        else  echo 0;
    }

    public function getUserInformation(){
        $userId=Session::get('uinfo')['userId'];
        $user=db('user')->where('userId',$userId)->field('username,sex,age,phone,mail,createtime')->find();
        echo json_encode($user);
    }

    public function updateUser(){
        $userId=Session::get('uinfo')['userId'];
        $data['age']=$_POST['age'];
        $data['sex']=$_POST['sex'];
        $data['phone']=$_POST['phone'];
        $data['mail']=$_POST['mail'];
        db('user')->where('userId',$userId)->update($data);
        echo 1;
    }
    public function getFileUrl(){
        $id=$_POST['id'];
        $file=db('files')->where('fileId',$id)->field('filepath')->find();
        echo $file['filepath'];
    }






}



