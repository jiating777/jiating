/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : qiandao

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-04-19 18:32:11
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for userdetail
-- ----------------------------
DROP TABLE IF EXISTS `userdetail`;
CREATE TABLE `userdetail` (
  `id` varchar(32) NOT NULL,
  `userId` varchar(32) NOT NULL COMMENT '外键，连接user表',
  `userNum` varchar(30) DEFAULT NULL COMMENT '学号/教师工号',
  `educational` int(2) DEFAULT NULL COMMENT '学历',
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of userdetail
-- ----------------------------
