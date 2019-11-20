<?php
/**
 * Created by PhpStorm.
 * User: xcf
 * Date: 2019/11/20
 */

namespace app\api\model;

use think\Db;
use think\Model;
use app\common\validate\api\Business as V;

class ApiBusOrder extends Model
{
    protected  $table="base_business_order";


    /**
     * 预约信息提交
     * $data 2019/11/20
     */
    public function add()
    {
        //获取提交数据
        $data = input();

        $validate = new V;

        // 验证
        if (!$validate->check($data)) {
            return json(['code' => 0, 'msg'=>$validate->getError()], 256);
        }

        // 验证用户是否已冻结
        if (Db::name('user')->where(['id'=>$data['user_id'], 'status'=>1])->find()) {
            # code...
            return ['code' => 0, 'msg'=>'账号已被冻结'];
        }

        // 添加数据
        $data['create_time'] = time();
        $data['appointment_time'] = strtotime($data['appointment_time'].' 00:00:00');

        if ($this->allowField(true)->save($data)) {
            
            // 获取预约信息
            $result = $this->alias('a')
                         ->join('business b', 'a.bus_id = b.id')
                         ->find($this->id);
            $result['people'] = $this->where(['bus_id'=>$data['bus_id']])->group('user_id')->count();
            $result['appointment_time'] = date("Y/m/d", $result['appointment_time']);

            return ['code' => 1, 'msg'=>'提交成功', 'data'=>$result];
        } else {
            return ['code' => 0, 'msg'=>'提交失败'];
        }
    }

    /**
     * 获取用户预约列表
     * $data 2019/11/20
     */
    public function getUList()
    {
        //获取提交数据
        $data = input();

        if (isset($data['user_id']) && Db::name('user')->find($data['user_id'])) {
            
            # 获取180天前的时间戳
            $time = strtotime('-180 day');

            $result = $this ->alias('a')
                            ->where('a.appointment_time > '.$time)
                            ->join('business b', 'a.bus_id = b.id')
                            ->order('a.create_time desc')
                            ->paginate(10);

            return ['code'=>1, 'data'=>$result];
        } else {

            return ['code' => 0, 'msg'=>'用户不存在'];
        }
    }
}