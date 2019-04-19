/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : qiandao

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-04-19 18:32:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` varchar(32) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `type` enum('student','teacher') DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL COMMENT '密码',
  `createDate` int(10) DEFAULT NULL,
  `updateDate` int(10) DEFAULT NULL,
  `lastLogin` int(10) DEFAULT NULL COMMENT '最后登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
