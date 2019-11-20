<?php

namespace app\common\validate\api;

use think\Validate;

class Business extends Validate
{

    protected   $rules = [
        'bus_id'  => 'require',
        'user_id'  => 'require',
        'name'   => 'require',
        'desc'   => 'require',
        'phone'   => 'require',
        'appointment_time'   => 'require'
    ];

    protected   $messages = [
        'bus_id.require'    => '请选择业务',
        'user_id.require'   => '用户未登录',
        'name.require'      => '请输入用户名',
        'phone.require'      => '请输入手机号',
        'desc.require'      => '请输入描述',
        'appointment_time.require'  => '请选择预约时间',
    ];
}
