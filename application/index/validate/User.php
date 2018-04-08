<?php
namespace app\index\validate;

use think\Validate;

class User extends Validate
{
    protected $rule = [
        'username'  =>  'require|min:3|unique:user',
        'password' =>'require|min:6|max:18'
    ];


    protected $message = [
    	'username.min' =>'用户名长度必须大于3位',
    	'password.min' =>'密码必须大于6位',
    	'username.unique' =>'用户名已经存在！',
    	'password.max'=>'密码必须小于18位',
    ];

}
?>