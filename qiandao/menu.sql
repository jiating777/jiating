/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 50547
Source Host           : localhost:3306
Source Database       : xiangcun2.0

Target Server Type    : MYSQL
Target Server Version : 50547
File Encoding         : 65001

Date: 2019-03-29 17:24:35
*/

SET FOREIGN_KEY_CHECKS=0;

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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES ('1', '首页', '/index', '0', 'icon-home', '0', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('2', '角色管理', '/role', '0', 'fa fa-sun-o', '0', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('3', '用户管理', '/user', '0', 'fa fa-users', '0', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('4', '作息时间', '/schedule', '0', 'icon-wrench', '0', '2', '1', '_self', '4');
INSERT INTO `menu` VALUES ('5', '留言', '/message', '0', 'fa fa-twitch', '0', '2', '1', '_self', '5');
INSERT INTO `menu` VALUES ('6', 'XXXX', '/xxx', '0', 'fa fa-hashtag', '0', '2', '1', '_self', '6');
INSERT INTO `menu` VALUES ('7', '综合管理', '/setting', '0', 'icon-settings', '0', '2', '1', '_self', '7');
INSERT INTO `menu` VALUES ('8', '用户管理', 'admin/villages/index', '222', 'public/static/pages/image/village.jpg', '1', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('9', '乡村居民', 'admin/member/index', '222', 'public/static/pages/image/member.jpg', '1', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('10', '村里事', 'admin/articlevillage/index', '222', 'public/static/pages/image/articlevillage.jpg', '1', '2', '1', '_self', '4');
INSERT INTO `menu` VALUES ('11', '便民服务', 'admin/articlework/index', '222', 'public/static/pages/image/articlework.jpg', '1', '2', '1', '_self', '5');
INSERT INTO `menu` VALUES ('12', '动态', 'admin/Communitydynamics/index', '222', 'public/static/pages/image/communitydynamics.jpg', '1', '2', '1', '_self', '6');
INSERT INTO `menu` VALUES ('13', '留言箱', 'admin/messages/index', '222', 'public/static/pages/image/messages.jpg', '1', '2', '1', '_self', '7');
INSERT INTO `menu` VALUES ('14', '帮扶人', 'admin/povertyparty/index', '333', '', '2', '2', '2', '_self', '1');
INSERT INTO `menu` VALUES ('15', '扶贫项目', 'admin/povertyproject/index', '333', 'public/static/pages/image/povertyproject.jpg', '1', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('16', '扶贫政策', 'admin/articlepolicy/index', '333', 'public/static/pages/image/articlepolicy.jpg', '1', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('17', '扶贫工作', 'admin/povertywork/index', '333', 'public/static/pages/image/povertywork.jpg', '1', '2', '1', '_self', '4');
INSERT INTO `menu` VALUES ('18', '作息时间', null, '333', '', '2', '2', '2', '_self', '5');
INSERT INTO `menu` VALUES ('19', '党建要闻', 'admin/articleparty/index', '444', 'public/static/pages/image/articleparty.jpg', '1', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('20', '通知公告', 'admin/articlenotice/index', '444', 'public/static/pages/image/articlenotice.jpg', '1', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('21', '党组织', 'admin/organization/index', '444', 'public/static/pages/image/organization.jpg', '1', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('22', '党员档案', 'admin/partymember/index', '444', '', '2', '2', '2', '_self', '4');
INSERT INTO `menu` VALUES ('23', '微课堂', 'admin/microclassroom/index', '444', 'public/static/pages/image/microclassroom.jpg', '1', '2', '1', '_self', '6');
INSERT INTO `menu` VALUES ('24', '在线考试', 'admin/onlineexam/index', '444', 'public/static/pages/image/onlineexam.jpg', '1', '2', '1', '_self', '7');
INSERT INTO `menu` VALUES ('25', '投票调研', 'admin/research/index', '444', 'public/static/pages/image/research.jpg', '1', '2', '1', '_self', '8');
INSERT INTO `menu` VALUES ('26', '活动报名', 'admin/activity/index', '444', '', '1', '2', '2', '_self', '9');
INSERT INTO `menu` VALUES ('27', '会议', 'admin/meeting/index', '444', 'public/static/pages/image/meeting.jpg', '1', '2', '1', '_self', '5');
INSERT INTO `menu` VALUES ('29', '农产品', 'admin/product/index', '444', 'public/static/pages/image/product.jpg', '1', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('30', '销售订单', 'admin/order/index', '444', 'public/static/pages/image/order.jpg', '1', '2', '2', '_self', '5');
INSERT INTO `menu` VALUES ('46', '轮播图', 'admin/adbanner/index', '777', 'public/static/pages/image/adbanner.jpg', '1', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('47', '头条', 'admin/articletoutiao/index', '777', 'public/static/pages/image/articletoutiao.jpg', '1', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('48', '管理员', 'admin/operator/index', '777', 'public/static/pages/image/operator.jpg', '1', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('50', '修改密码', 'admin/admin/profile', '777', 'public/static/pages/image/profile.jpg', '1', '2', '1', '_self', '5');
INSERT INTO `menu` VALUES ('52', '清除缓存', 'admin/common/delcache', '0', 'fa fa-refresh', '1', '2', '2', '_self', '100');
INSERT INTO `menu` VALUES ('53', '品种管理', 'admin/producttype/index', '444', 'public/static/pages/image/producttype.jpg', '1', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('54', '申领样品', 'admin/receiveorder/index', '444', 'public/static/pages/image/receiveorder.jpg', '1', '2', '1', '_self', '4');
INSERT INTO `menu` VALUES ('55', '预售', 'admin/presale/index', '444', 'public/static/pages/image/presale.jpg', '1', '2', '1', '_self', '6');
INSERT INTO `menu` VALUES ('56', '预售订单', 'admin/presaleorder/index', '444', 'public/static/pages/image/presaleorder.jpg', '1', '2', '2', '_self', '7');
INSERT INTO `menu` VALUES ('57', '分类管理', 'admin/producttypetop/index', '444', 'public/static/pages/image/producttypetop.jpg', '1', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('58', '上线配置', 'admin/MiniProgram/index', '777', 'public/static/pages/image/miniprogram.jpg', '6', '2', '1', '_self', '6');
INSERT INTO `menu` VALUES ('59', '乡镇管理', 'admin/Townprogram/index', '777', 'public/static/pages/image/townprogram.jpg', '0', '2', '1', '_self', '7');
INSERT INTO `menu` VALUES ('60', '农事专题', 'admin/knowledgetype/index', '666', 'public/static/pages/image/knowledgetype.jpg', '1', '2', '1', '_self', '1');
INSERT INTO `menu` VALUES ('61', '农事知识', 'admin/knowledge/index', '666', 'public/static/pages/image/knowledge.jpg', '1', '2', '1', '_self', '2');
INSERT INTO `menu` VALUES ('62', '村民审核', 'admin/member/shenhe', '222', 'public/static/pages/image/shenhe.jpg', '1', '2', '1', '_self', '3');
INSERT INTO `menu` VALUES ('63', '设置', 'admin/townconfig/config', '777', 'public/static/pages/image/townconfig.jpg', '6', '2', '1', '_self', '8');
