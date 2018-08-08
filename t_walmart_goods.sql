/*
 Navicat Premium Data Transfer

 Source Server         : vmware
 Source Server Type    : MySQL
 Source Server Version : 50718
 Source Host           : 192.168.220.128:3306
 Source Schema         : cdiscount

 Target Server Type    : MySQL
 Target Server Version : 50718
 File Encoding         : 65001

 Date: 28/06/2018 11:11:13
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for t_walmart_goods
-- ----------------------------
DROP TABLE IF EXISTS `t_walmart_goods`;
CREATE TABLE `t_walmart_goods`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `walmart_id` int(11) NULL DEFAULT NULL COMMENT '亚马逊采集链接关联ID',
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '标题',
  `href` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `goods_code` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产品唯一标识',
  `brand_name` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '品牌名称',
  `type` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '类别0中文1英文',
  `img` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '图集',
  `price` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '售价',
  `shipping_fee` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮费',
  `evaluation_num` int(11) NULL DEFAULT 0 COMMENT '评价数量',
  `evaluation_score` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '评价得分',
  `evaluation_item` int(11) NULL DEFAULT NULL COMMENT '2018年有多少条评论',
  `seller_num` int(11) NULL DEFAULT NULL COMMENT '卖家数量',
  `seller_min_priece` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '卖家最低价格',
  `attribute_description` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '属性描述',
  `sale_description` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '销售标识',
  `is_owner` smallint(2) NULL DEFAULT NULL COMMENT '自营1是0否',
  `last_time` int(11) NULL DEFAULT NULL COMMENT '最后更新时间',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '创建时间',
  `updated_at` timestamp(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5853 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '亚马逊采集数据产品表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_walmart_goods_developer
-- ----------------------------
DROP TABLE IF EXISTS `t_walmart_goods_developer`;
CREATE TABLE `t_walmart_goods_developer`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `goods_id` int(2) NULL DEFAULT NULL COMMENT '主表ID',
  `status` smallint(2) NULL DEFAULT 0 COMMENT '状态待开发0未完成1已完成2',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `sku` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'SKU',
  `date` date NULL DEFAULT NULL COMMENT '开发时间',
  `author_id` int(11) NULL DEFAULT NULL COMMENT '添加人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `updated_at` timestamp(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '亚马逊数据采集开发人员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_walmart_goods_sale
-- ----------------------------
DROP TABLE IF EXISTS `t_walmart_goods_sale`;
CREATE TABLE `t_walmart_goods_sale`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `goods_id` int(2) NULL DEFAULT NULL COMMENT '主表ID',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `status` smallint(2) NULL DEFAULT 0 COMMENT '是否开发0否1是',
  `author_id` int(11) NULL DEFAULT NULL COMMENT '添加人',
  `date` datetime(0) NULL DEFAULT NULL COMMENT '记录时间',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `updated_at` timestamp(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '亚马逊数据采集销售人员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_walmart_log
-- ----------------------------
DROP TABLE IF EXISTS `t_walmart_log`;
CREATE TABLE `t_walmart_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` smallint(2) NULL DEFAULT NULL COMMENT '类别0销售人员日志1开发人员日志',
  `goods_id` int(11) NULL DEFAULT NULL COMMENT '商品ID',
  `status` smallint(2) NULL DEFAULT NULL COMMENT '状态',
  `remark` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '备注',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '内容',
  `author_id` int(11) NULL DEFAULT NULL COMMENT '添加人',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `updated_at` timestamp(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '亚马逊数据采集开日志表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for t_walmart_set
-- ----------------------------
DROP TABLE IF EXISTS `t_walmart_set`;
CREATE TABLE `t_walmart_set`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` smallint(2) NULL DEFAULT NULL COMMENT '类别0中文1英文',
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '采集地址',
  `sum` int(11) NULL DEFAULT NULL COMMENT '产品总数',
  `author_id` int(11) NULL DEFAULT NULL COMMENT '添加人（销售人员）',
  `status` smallint(2) NULL DEFAULT 1 COMMENT '状态0关闭1开启',
  `last_time` int(11) NULL DEFAULT NULL COMMENT '最后采集时间戳',
  `created_at` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `updated_at` timestamp(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0) COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 112 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '亚马逊采集链接设置' ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;


