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
}