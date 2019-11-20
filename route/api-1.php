<?php
/**
 * Created by PhpStorm.
 * User: fk
 * Date: 2019/11/19
 * Time: 16:19
 */
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
Route::rule('enrollSubmit','api/bus/submit', 'POST');

//咨询
Route::rule('seekList','api/seek/getlist','GET');
Route::rule('seekDetail','api/seek/detail','GET');

//个人中心 
Route::rule('getUser','api/user/user','GET');
Route::rule('setUser','api/user/uedit','PUT');
Route::rule('mEnroll','api/user/uenroll','GET');
Route::rule('mVideo','api/user/uvideo','GET');
Route::rule('mSeek','api/user/useek','GET');
