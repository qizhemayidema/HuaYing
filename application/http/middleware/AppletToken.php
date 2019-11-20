<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/20
 * Time: 15:23
 */

namespace app\http\middleware;

use think\Db;
use think\Cache;

class AppletToken
{

	public function handle($request, \Closure $next)
    { 
        $token = $request->param('token');

        $cache = new Cache(['type' => config('cache.type')]);
        
        if ($cache->has($token)) {   //设置了还未过期
        	
        	$request->user_id = Db::name('user')->where(['token'=>$token])->value('id');
        	return $next($request);

        }else{  //过期了
        	return json(['code'=>3, 'msg'=>'token失效']);
        }
    }
}