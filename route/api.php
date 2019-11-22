<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 16:19
 */


use think\facade\Route;

Route::group('api',function(){
        // 律师
    Route::rule('lawList','api/law/getlist','GET');
    Route::rule('lawDetail','api/law/detail','GET');

    //分校
    Route::rule('brochureList','api/brochure/getlist','GET');
    Route::rule('brochureDetail','api/brochure/detail','GET');

    //业务
    Route::rule('busList','api/bus/getlist','GET');
    Route::rule('busDetail','api/bus/detail','GET');
    Route::rule('busEnroll','api/bus/enroll','GET');
    Route::rule('enrollSubmit','api/bus/submit', 'POST')->middleware(app\http\middleware\AppletToken::class);

    //咨询
    Route::rule('seekList','api/seek/getlist','GET');
    Route::rule('seekDetail','api/seek/detail','GET');

    //个人中心
    Route::rule('getUser','api/user/user','GET')->middleware(app\http\middleware\AppletToken::class);
    Route::rule('setUser','api/user/uedit','PUT')->middleware(app\http\middleware\AppletToken::class);
    Route::rule('mEnroll','api/user/uenroll','GET')->middleware(app\http\middleware\AppletToken::class);
    Route::rule('mVideo','api/user/uvideo','GET')->middleware(app\http\middleware\AppletToken::class);
    Route::rule('mSeek','api/user/useek','GET')->middleware(app\http\middleware\AppletToken::class);


    //首页
    Route::rule('homeIndex','api/AppletHome/homeIndex','GET');

    //在线课程
    Route::rule('appletCourse','api/AppletCourse/courseList','GET');
    Route::rule('appletCourseDetail','api/AppletCourse/courseDetail','GET');

    //支付
    Route::rule('appletWePay','api/AppletPay/appletWeiPay', 'POST')->middleware(app\http\middleware\AppletToken::class);
    Route::rule('appletWeCheck','api/AppletPay/appletWeiCheck')->name('pay.notify');

    //订单
    Route::rule('appletOrderPayStatus','api/AppletPay/getOrderPayStatus','GET')->middleware(app\http\middleware\AppletToken::class);

    //登陆
    Route::rule('appletLogin','api/AppletUser/Login', 'POST');

    //师资力量详情
    Route::rule('appletTeacherDetail','api/AppletTeacher/getTeacherDetail','GET');

    //发表评论
    Route::rule('appletPublishComment','api/AppletComment/publishComment', 'POST')->middleware(app\http\middleware\AppletToken::class);


});