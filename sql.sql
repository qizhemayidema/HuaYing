DROP TABLE IF EXISTS `base_video`;
CREATE TABLE IF NOT EXISTS `base_video` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`cate_id` int(11) unsigned NOT NULL COMMENT '分类id',
`auther_id` int(11) unsigned NOT NULL COMMENT '作者id',
`title` varchar(60) NOT NULL COMMENT '标题',
`pic` varchar(128) NOT NULL COMMENT '课程封面图',
`roll_pic` varchar(1270) not null default '' comment '轮播图',
`source_url` varchar(256) NOT NULL COMMENT '资源路径',
`keywords` varchar(255) not null default '' comment '关键字',
`see_sum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '观看次数',
`collect_sum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '收藏数量',
`comment_sum` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '评论数量',
`like_sum` int(11) unsigned not null default 0 comment '点赞数量',
`share_sum` int(11) unsigned not null default 0 comment '分享数量',
`section_sum` int(11) unsigned not null default 0 comment '视频数量',
`price` decimal(10,2) not null default 0 comment '售价',
`is_feel` tinyint(1) not null default 0 comment '是否免费 0否 1是',
`status` tinyint(1) not null comment '审核状态 0 禁止 1 未审核 2 审核通过',
`create_time` int(11) NOT NULL COMMENT '上传时间',
`delete_time` int(1) NOT NULL DEFAULT 0 COMMENT '0  没被删除 时间戳已经删除',
PRIMARY KEY (`id`),
INDEX cate_id(`cate_id`),
INDEX create_time(`create_time`),
INDEX delete_time(`delete_time`),
INDEX auther_id(`auther_id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

create table `base_video_section`(
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`title` varchar(60) NOT NULL COMMENT '标题',
`source_url` varchar(256) NOT NULL COMMENT '资源路径',
PRIMARY KEY (`id`)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `base_category`;
CREATE TABLE IF NOT EXISTS `base_category` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`p_id` int(11) NOT null default 0 comment '父级id',
`type` tinyint(1) not null comment '类型 1 业务分类 2 律所分类 3 课程分类 4招生简章分类',
`name` varchar(64) NOT NULL COMMENT '分类名称',
`data_sum` int(11) NOT NULL DEFAULT 0 COMMENT '分类下数据的数量',
`order_num` int(11) not null default 0 comment '排序',
PRIMARY KEY (`id`),
INDEX p_id(`p_id`),
INDEX `type`(`type`),
INDEX `order_num`(`order_num`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `base_comment`;
CREATE TABLE IF NOT EXISTS `base_comment` (
`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
`type` tinyint(1) unsigned NOT NULL COMMENT '评论对象类型 1 律所 2 老师评价 ',
`public_id` int(11) NOT NULL COMMENT '所评论的id 例如 视频的评论,这里存的是视频id',
`user_id` int(11) NOT NULL COMMENT '用户id',
`nickname` varchar(60) NOT NULL COMMENT '评论用户昵称',
`avatar_url` varchar(256) not null comment '头像',
`comment` text NOT NULL COMMENT '评论内容',
`top_id` int(11) unsigned not null default 0 comment '顶级comment的id',
`reply_nickname` varchar(60) NOT NULL default '' COMMENT '回复对象的昵称',
`reply_user_id` int(11) unsigned not null default 0 comment '回复对象的id',
`is_show` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示 0 否 1 是',
`like_sum` int(11) NOT NULL default 0 COMMENT '点赞数量',
`comment_sum` int(11) not null default 0 comment '顶级节点被回复的数量',
`score` char(10) not null default '5' comment '评价分数',
`create_time` int(11) NOT NULL COMMENT '创建时间',
PRIMARY KEY (`id`),
index `type`(`type`),
index `public_id`(`public_id`),
index `top_id`(`top_id`),
index `user_id`(`user_id`),
index `is_show`(`is_show`),
index `create_time`(`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `base_manager`;
CREATE TABLE IF NOT EXISTS `base_manager` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`user_name` char(32) NOT NULL COMMENT '用户名',
`password` char(32) NOT NULL COMMENT '密码',
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



DROP TABLE IF EXISTS `base_user`;
CREATE TABLE IF NOT EXISTS `base_user` (
`id` int(11) NOT NULL AUTO_INCREMENT,
`phone` char(11) NOT NULL default '' COMMENT '手机号',
`wechat` varchar(128) not null default '' comment '微信号',
`username` varchar(32) not null default '' comment '用户名',
`password` char(32) NOT NULL default '' COMMENT '密码',
`nickname` varchar(120) NOT NULL default '未命名' COMMENT '用户昵称',
`avatar_url` varchar(128) NOT NULL default '/static/index/default/20150828225753jJ4Fc.jpeg' COMMENT '头像',
`openid` varchar(50) not null default '' comment 'openid',
`unionid` varchar(128) not null default '' comment 'unionid',
`sex` tinyint(1) not null default 1 comment '性别 0 保密 1 男 2 女',
`self_desc` varchar(127) not null default '' comment '自我介绍',
`country` varchar(120) NOT NULL COMMENT '国家',
`province` varchar(120) NOT NULL COMMENT '省市',
`city` varchar(120) NOT NULL default '' COMMENT '城市',
`focus_sum` int(11) unsigned not null default 0 comment '关注数量 我 -> 别人',
`fans_sum` int(11) unsigned not null default 0 comment '粉丝数',
`get_like_sum` int(11) unsigned not null default 0 comment '获赞数 别人 -> 我',
`works_sum` int(11) unsigned not null default 0 comment '作品数',
`collect_sum` int(11) unsigned not null default 0 comment '我的收藏数量',
`create_time` int(11) NOT NULL COMMENT '创建时间',
`status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0 正常  1 已冻结',
`score` decimal(10,2) not null default 0 comment '学分|积分',
`auth_type` tinyint(1) not null default 0 comment '认证类型 1 个人 2 企业 0 无',
`auth_id` int(11) not null default 0 comment '认证id',
`token` varchar(32) not null comment 'token值 身份令牌',
`version` int(11)  NOT NULL DEFAULT 1 comment '防止并发产生问题使用的版本号 初始为1',
 PRIMARY KEY (`id`),
 index `status`(`status`),
 index `create_time`(`create_time`),
 index `version`(`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


create table `base_teacher`(
`id` int auto_increment,
`name` varchar(30) not null comment '称呼',
`pic` varchar(128) not null comment '照片',
`desc` varchar(128) not null comment '简介',
`content` text not null comment '介绍',
primary key(`id`)
)engine=innodb charset=utf8;


--业务表
create table `base_business`(
`id` int auto_increment,
`cate_id` int(11) not null comment '分类id',
`title` varchar(64) not null comment '标题',
`content` text not null comment '内容介绍',
`create_time` int(11) not null comment '创建时间',
`delete_time` int(11) not null default 0 comment '删除时间',
primary key(`id`),
index `cate_id`(`cate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--业务预约表
create table `base_business_order`(
`id` int auto_increment,
`bus_id` int(11) not null comment '业务id',
`user_id` int(11) not null comment '用户id',
`name` varchar(30) not null comment '姓名',
`phone` varchar(18) not null comment '电话',
`create_time` int(11) not null comment '创建时间',
`desc` varchar(63) not null comment '描述',
primary key(`id`)
)engine=innodb charset=utf8;

--咨询表
create table `base_seek`(
`id` int auto_increment,
`title` varchar(64) not null comment '标题',
`pic` varchar(128) not null comment '封面',
`desc` varchar(256) not null comment '简介',
`content` text not null comment '内容',
`price` decimal(10,2) not null comment '价格',
`create_time` int(11) not null comment '创建时间',
`delete_time` int(11) not null default 0 comment '删除时间',
primary key(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--律所
create table `base_law`(
`id` int auto_increment,
`cate_id` int(11) not null comment '分类id',
`name` varchar(64) not null comment '名称',
`pic` varchar(128) not null comment '封面',
`phone` varchar(20) not null comment '联系电话',
`address` varchar(128) not null comment '地址',
`content` text not null comment '简介',
`create_time` int(11) not null comment '创建时间',
primary key(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--招生简章
create table `base_brochure`(
`id` int auto_increment,
`cate_id` int not null comment '分类id',
`name` varchar(30) not null comment '名称',
`pic` varchar(128) not null comment '封面',
`content` text not null comment '内容',
`create_time` int(11) not null comment '创建时间',
primary key(`id`)
)engine=innodb charset=utf8;

create table `base_history`(
`id` int auto_increment,
`type` tinyint(1) not null comment '1 购买视频 2付费咨询 3预约业务 4评价老师 5评价课程',
`user_id` int(11) not null comment '用户id',
`object_id` int(11) not null comment '对某对象操作id',
`create_time` int(11) not null comment '创建时间',
primary key(`id`),
index `type`(`type`),
index `user_id`(`user_id`),
index `object_id`(`object_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;