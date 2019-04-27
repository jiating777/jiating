/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : xiangcun2.0

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-04-11 16:02:33
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
