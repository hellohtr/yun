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
    public function data(){

    }
}