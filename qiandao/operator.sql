/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : xiangcun2.0

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-03-29 17:34:04
*/

SET FOREIGN_KEY_CHECKS=0;

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
