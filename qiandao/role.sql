/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : xiangcun2.0

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-03-29 17:49:51
*/

SET FOREIGN_KEY_CHECKS=0;

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
INSERT INTO `role` VALUES ('df', '教师', 'teacher', '11', null, '1553416721');
