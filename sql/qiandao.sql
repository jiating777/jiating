/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : qiandao

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-07-05 18:59:54
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for adminlog
-- ----------------------------
DROP TABLE IF EXISTS `adminlog`;
CREATE TABLE `adminlog` (
  `adminId` varchar(32) NOT NULL COMMENT '操作者ID',
  `logInfo` text COMMENT '内容',
  `logIp` varchar(32) DEFAULT NULL COMMENT 'IP',
  `logUrl` varchar(255) DEFAULT NULL COMMENT '操作URL',
  `logTime` int(10) DEFAULT NULL COMMENT '操作时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='管理员操作日志表';

-- ----------------------------
-- Records of adminlog
-- ----------------------------
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin删除了1条schedule数据。', '127.0.0.1', 'admin/schedule/delete', '1555748372');
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin删除了1条messages数据。', '127.0.0.1', 'admin/message/delete', '1562238699');
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin添加了一条school数据。', '127.0.0.1', 'admin/school/addpost', '1562311883');
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin更新了一条school数据。', '127.0.0.1', 'admin/school/editPost', '1562311979');
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin添加了一条school数据。', '127.0.0.1', 'admin/school/addpost', '1562312125');
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin添加了一条menu数据。', '127.0.0.1', 'admin/menu/addpost', '1562312892');
INSERT INTO `adminlog` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin删除了1条menu数据。', '127.0.0.1', 'admin/menu/delete', '1562312904');

-- ----------------------------
-- Table structure for course
-- ----------------------------
DROP TABLE IF EXISTS `course`;
CREATE TABLE `course` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `classname` varchar(255) DEFAULT NULL,
  `createrId` int(11) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `taketime` varchar(255) DEFAULT NULL COMMENT '上课时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of course
-- ----------------------------
INSERT INTO `course` VALUES ('1', '网络工程', '3', '东3-105', '周一、五六节');
INSERT INTO `course` VALUES ('2', '工程训练', '3', '数计4号楼-309', '周五、七八节');

-- ----------------------------
-- Table structure for department
-- ----------------------------
DROP TABLE IF EXISTS `department`;
CREATE TABLE `department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schoolId` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of department
-- ----------------------------
INSERT INTO `department` VALUES ('1', '2', '人文学院');
INSERT INTO `department` VALUES ('2', '2', '国际学院');
INSERT INTO `department` VALUES ('3', '2', '经济学院');
INSERT INTO `department` VALUES ('4', '2', '管理学院');
INSERT INTO `department` VALUES ('5', '2', '法学院');
INSERT INTO `department` VALUES ('6', '2', '马克思主义学院');
INSERT INTO `department` VALUES ('7', '2', '经济学院');
INSERT INTO `department` VALUES ('8', '2', '社会与人类学院');
INSERT INTO `department` VALUES ('9', '2', '数学科学学院');
INSERT INTO `department` VALUES ('10', '2', '信息科学与技术学院');
INSERT INTO `department` VALUES ('11', '2', '软件学院');
INSERT INTO `department` VALUES ('12', '2', '航空航天学院');
INSERT INTO `department` VALUES ('13', '2', '电子科学与技术学院');
INSERT INTO `department` VALUES ('14', '3', '电气工程与自动化学院');
INSERT INTO `department` VALUES ('15', '3', '外国语学院');
INSERT INTO `department` VALUES ('16', '3', '法学院');
INSERT INTO `department` VALUES ('17', '3', '外国语学院');
INSERT INTO `department` VALUES ('18', '3', '数学与计算机科学学院');
INSERT INTO `department` VALUES ('19', '3', '环境与资源学院学院');
INSERT INTO `department` VALUES ('20', '3', '建筑与城乡规划学院');
INSERT INTO `department` VALUES ('21', '3', '厦门工艺美术学院');
INSERT INTO `department` VALUES ('22', '3', '海洋学院');
INSERT INTO `department` VALUES ('23', '3', '马克思主义学院');
INSERT INTO `department` VALUES ('24', '3', '经济与管理学院');
INSERT INTO `department` VALUES ('25', '3', '外国语学院');
INSERT INTO `department` VALUES ('26', '3', '法学院');
INSERT INTO `department` VALUES ('27', '3', '化学学院');
INSERT INTO `department` VALUES ('28', '3', '土木工程学院');

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL COMMENT '菜单名称',
  `url` varchar(255) DEFAULT NULL COMMENT '操作路径',
  `parentId` int(11) DEFAULT NULL COMMENT '上级ID',
  `imgPath` varchar(255) DEFAULT '' COMMENT '图标地址',
  `isRole` int(1) DEFAULT '1' COMMENT '是否分配权限，1-分配权限，2-特殊权限  6-镇超级管理员权限  0-本系统超级管理员权限',
  `isQuick` int(1) DEFAULT '2' COMMENT '是否为快捷操作,1-是，2-否',
  `status` int(1) DEFAULT '1' COMMENT '是否显示，1-显示，2-不显示',
  `target` varchar(20) DEFAULT '_self',
  `sorting` int(11) DEFAULT NULL COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('1', '首页', '/index', '0', 'icon-home', '0', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('2', '角色管理', '/role', '0', 'fa fa-sun-o', '0', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('3', '用户管理', '/user', '0', 'fa fa-users', '0', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('4', '作息时间', '/schedule', '0', 'icon-wrench', '0', '2', '1', '_self', '4');
INSERT INTO `menu` VALUES ('5', '留言', '/message', '0', 'fa fa-twitch', '0', '2', '1', '_self', '5');
INSERT INTO `menu` VALUES ('6', '学校设置', '/school', '0', 'icon-settings', '0', '2', '1', '_self', '6');
INSERT INTO `menu` VALUES ('7', '修改密码', 'admin/admin/profile', '777', 'public/static/pages/image/profile.jpg', '1', '2', '1', '_self', '5');
INSERT INTO `menu` VALUES ('8', '清除缓存', 'admin/common/delcache', '0', 'fa fa-refresh', '1', '2', '2', '_self', '100');
INSERT INTO `menu` VALUES ('10', '院系设置', '/department', '0', 'fa fa-sun-o', '1', '2', '1', '_self', '8');
INSERT INTO `menu` VALUES ('11', '菜单管理', '/menu', '0', 'fa fa-hashtag', '1', '2', '1', '_self', '9');

-- ----------------------------
-- Table structure for messages
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages` (
  `id` varchar(32) NOT NULL,
  `courseId` int(10) DEFAULT NULL COMMENT '所属镇ID',
  `createDate` int(10) DEFAULT NULL COMMENT '创建时间',
  `updateDate` int(10) DEFAULT NULL COMMENT '更新时间',
  `responderId` varchar(32) DEFAULT '0' COMMENT '回复ID',
  `userId` varchar(32) DEFAULT NULL COMMENT '留言/回复者ID,留言id对应user表ID，回复id对应member表ID',
  `content` text COMMENT '留言内容',
  `isDelete` tinyint(2) DEFAULT '2' COMMENT '是否删除，1-已删除；2-正常',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='村留言表';

-- ----------------------------
-- Records of messages
-- ----------------------------
INSERT INTO `messages` VALUES ('dfsdg', '1', '1561953228', '1561953228', '0', '1', '老师第二次课留得什么作业', '2');
INSERT INTO `messages` VALUES ('fdgfdg', '1', '1562048730', '1562048730', '0', '1', 'test成果', '2');

-- ----------------------------
-- Table structure for operator
-- ----------------------------
DROP TABLE IF EXISTS `operator`;
CREATE TABLE `operator` (
  `id` varchar(32) NOT NULL,
  `loginName` varchar(60) DEFAULT NULL COMMENT '账户名',
  `password` varchar(64) DEFAULT NULL COMMENT '登录密码',
  `createDate` int(10) DEFAULT NULL COMMENT '创建时间',
  `updateDate` int(10) DEFAULT NULL COMMENT '最后跟新时间',
  `createOper` varchar(32) DEFAULT NULL COMMENT '创建人，存储operator表id',
  `updateOper` varchar(32) DEFAULT NULL COMMENT '最后修改人',
  `lastLogin` int(10) DEFAULT NULL COMMENT '最后登录时间',
  `isDelete` tinyint(1) DEFAULT '2' COMMENT '标识是否已删除，1-已删除，2-正常',
  `type` int(2) DEFAULT NULL COMMENT '0-系统超级管理员，1-其他管理员',
  `menuId` text COMMENT '权限ID组，以'',''分隔的字符串',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique` (`loginName`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT COMMENT='村组织成员表，即可登陆后台人员';

-- ----------------------------
-- Records of operator
-- ----------------------------
INSERT INTO `operator` VALUES ('02f09a6dd26b156c8908dbda13e6104f', 'admin', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1531907868', null, null, null, null, '2', '0', null);
INSERT INTO `operator` VALUES ('1ae71a7a8f643965a1f889e4ef2ee807', 'test002', '20dfa24ad7483cb99415299a903b4357a63dfcc5d2c146293a38dc65840e121e', '1533260602', '1533805667', '02f09a6dd26b156c8908dbda13e6104f', '02f09a6dd26b156c8908dbda13e6104f', null, '2', '1', '8,9,10,11,12,13,15,16,17,19,20,21,23,24,25,29,30,53,54,55,56,57,46,47,48,50,60,61,2,3,4,5,7,6,1');
INSERT INTO `operator` VALUES ('e8a35e197922aa04affaab4dd711b7d0', 'test004', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1533721539', null, '02f09a6dd26b156c8908dbda13e6104f', null, null, '1', '1', '8,9,10,13,15,17,21,29,46,60,61,2,3,4,5,7,6,1');

-- ----------------------------
-- Table structure for record
-- ----------------------------
DROP TABLE IF EXISTS `record`;
CREATE TABLE `record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studentId` int(11) DEFAULT NULL,
  `qiandaotime` int(11) DEFAULT NULL COMMENT '签到时间',
  `classId` int(11) DEFAULT NULL COMMENT '班课ID',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of record
-- ----------------------------
INSERT INTO `record` VALUES ('1', '1', '1561945856', '1');
INSERT INTO `record` VALUES ('2', '1', '1561945892', '1');
INSERT INTO `record` VALUES ('3', '1', '1561946028', '2');

-- ----------------------------
-- Table structure for role
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT '角色名称',
  `typeName` varchar(255) DEFAULT NULL COMMENT '英文类型',
  `total` int(11) DEFAULT '0' COMMENT '这一角色下总人数',
  `appPageId` varchar(255) DEFAULT NULL COMMENT 'APP页面权限ID组',
  `createDate` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1f856baefd5e48228f8caea10b2f3154', '用户管理员', 'admin1', '0', '3,5', '1562205557');
INSERT INTO `role` VALUES ('df', '管理员1', 'manage1', '11', '2,7', '1553416721');

-- ----------------------------
-- Table structure for schedule
-- ----------------------------
DROP TABLE IF EXISTS `schedule`;
CREATE TABLE `schedule` (
  `id` varchar(50) NOT NULL,
  `schoolId` int(11) DEFAULT NULL,
  `total` int(2) DEFAULT NULL COMMENT '每天总节数',
  `schedule` varchar(500) DEFAULT NULL COMMENT '序列化的作息时间',
  `createDate` int(10) DEFAULT NULL,
  `updateDate` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of schedule
-- ----------------------------
INSERT INTO `schedule` VALUES ('a8e50ce46fc22aac40cbe9cf7ab9e6ed', '3', '3', 'a:3:{i:0;a:2:{s:5:\"start\";s:5:\"08:20\";s:3:\"end\";s:5:\"09:05\";}i:1;a:2:{s:5:\"start\";s:5:\"09:15\";s:3:\"end\";s:5:\"10:00\";}i:2;a:2:{s:5:\"start\";s:5:\"10:20\";s:3:\"end\";s:5:\"11:05\";}}', '1554884989', '1554959416');
INSERT INTO `schedule` VALUES ('dbc31c016f2fb824ef19143086942a78', '1', '3', 'a:4:{i:0;a:2:{s:5:\"start\";s:5:\"08:20\";s:3:\"end\";s:5:\"09:05\";}i:1;a:2:{s:5:\"start\";s:5:\"09:15\";s:3:\"end\";s:5:\"10:00\";}i:2;a:2:{s:5:\"start\";s:5:\"10:20\";s:3:\"end\";s:5:\"11:05\";}i:3;a:2:{s:5:\"start\";s:5:\"11:22\";s:3:\"end\";s:5:\"13:08\";}}', '1555747078', '1555747473');

-- ----------------------------
-- Table structure for school
-- ----------------------------
DROP TABLE IF EXISTS `school`;
CREATE TABLE `school` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '学校名称',
  `code` varchar(255) DEFAULT NULL COMMENT '学校代码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of school
-- ----------------------------
INSERT INTO `school` VALUES ('1', '北京大学', '1001');
INSERT INTO `school` VALUES ('2', '厦门大学', '10384');
INSERT INTO `school` VALUES ('3', '福州大学', '10386');
INSERT INTO `school` VALUES ('4', '清华大学', '1003');
INSERT INTO `school` VALUES ('5', '华侨大学', '10385');
INSERT INTO `school` VALUES ('6', '福建工程学院', '10388');
INSERT INTO `school` VALUES ('7', '福建农林大学', '10389');
INSERT INTO `school` VALUES ('8', '集美大学', '10390');
INSERT INTO `school` VALUES ('9', '福建医科大学', '10392');
INSERT INTO `school` VALUES ('10', '福建中医药学院', '10393');
INSERT INTO `school` VALUES ('11', '福建师范大学', '10394');
INSERT INTO `school` VALUES ('12', '闽江学院', '10395');
INSERT INTO `school` VALUES ('13', '泉州师范学院', '10399');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `type` enum('student','teacher') DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  `createDate` int(10) DEFAULT NULL,
  `updateDate` int(10) DEFAULT NULL,
  `lastLogin` int(10) DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', '张李栋', '15900000003', 'student', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1561943138', '1561943138', '1561943138');
INSERT INTO `user` VALUES ('2', '欧阳询', '15035298674', 'student', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1561943906', '1561943906', '1561943906');
INSERT INTO `user` VALUES ('3', '赵路', '15900000002', 'teacher', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1561943906', '1561943906', '1561943906');
INSERT INTO `user` VALUES ('4', '曾翼', '13025687459', 'teacher', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1561943138', '1561943138', '1561943138');
INSERT INTO `user` VALUES ('5', '李浅浅', '15207925556', 'student', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1561944315', '1561944315', '1561944315');
INSERT INTO `user` VALUES ('6', '孙果果', '13125698789', 'student', '8fdd3f646a042b9b171d74b2ada91a48af38d240ad4ad4b748f9fa7673160fe4', '1561965915', '1561965915', '1561965915');

-- ----------------------------
-- Table structure for userdetail
-- ----------------------------
DROP TABLE IF EXISTS `userdetail`;
CREATE TABLE `userdetail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL COMMENT '外键，连接user表',
  `userNum` varchar(30) DEFAULT NULL COMMENT '学号/教师工号',
  `educational` varchar(100) DEFAULT NULL COMMENT '学历',
  `schoolId` int(11) DEFAULT NULL,
  `departmentId` int(11) DEFAULT NULL COMMENT '院系ID',
  `createDate` int(10) DEFAULT NULL,
  `updateDate` int(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `用户` (`userId`),
  KEY `schoolId` (`schoolId`),
  KEY `depart` (`departmentId`),
  CONSTRAINT `用户` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON UPDATE NO ACTION,
  CONSTRAINT `depart` FOREIGN KEY (`departmentId`) REFERENCES `department` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `userdetail_ibfk_1` FOREIGN KEY (`schoolId`) REFERENCES `school` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of userdetail
-- ----------------------------
INSERT INTO `userdetail` VALUES ('1', '1', '180327005', '本科', '3', '18', '1561965915', '1561965915');
INSERT INTO `userdetail` VALUES ('2', '2', '180327006', '本科', '3', '18', '1561967456', '1561967456');
INSERT INTO `userdetail` VALUES ('3', '3', 'N7015', '博士', '3', '18', '1561967456', '1561967456');
INSERT INTO `userdetail` VALUES ('4', '4', 'N8569', '博士', '3', '18', '1561946028', '1561946028');
INSERT INTO `userdetail` VALUES ('5', '5', '180327036', '本科', '3', '18', '1561946028', '1561946028');
INSERT INTO `userdetail` VALUES ('6', '6', '180327045', '本科', '3', '18', '1561946028', '1561946028');
