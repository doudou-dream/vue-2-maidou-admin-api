SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for m_admin
-- ----------------------------
DROP TABLE IF EXISTS `m_admin`;
CREATE TABLE `m_admin`  (
  `id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'ID',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '管理账号',
  `password` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '管理密码',
  `password_salt` char(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '加密因子',
  `nickname` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '昵称',
  `avatar` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '头像',
  `introduce` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '简介',
  `is_root` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1-超级管理',
  `last_active` int(10) NOT NULL DEFAULT 0 COMMENT '最后登录时间',
  `last_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建IP',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `name`(`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for m_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `m_auth_group`;
CREATE TABLE `m_auth_group`  (
  `id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组id',
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '描述信息',
  `listorder` int(10) NULL DEFAULT 100 COMMENT '排序ID',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1-系统默认角色',
  `update_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建IP',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '权限组表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for m_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `m_auth_group_access`;
CREATE TABLE `m_auth_group_access`  (
  `id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `admin_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `group_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `admin_id`(`admin_id`, `group_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员与用户组关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for m_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `m_auth_rule`;
CREATE TABLE `m_auth_rule`  (
  `id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '规则id',
  `parent_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0' COMMENT '上级分类ID',
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '名称',
  `url` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '权限链接',
  `method` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '请求类型',
  `slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '地址标识',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '描述',
  `listorder` int(10) NULL DEFAULT 100 COMMENT '排序ID',
  `is_need_auth` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1-验证权限',
  `is_system` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1-系统权限',
  `update_time` int(11) NOT NULL DEFAULT 0 COMMENT '更新时间',
  `update_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '修改IP',
  `create_time` int(11) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `create_ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '创建IP',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '规则表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of m_auth_rule
-- ----------------------------
INSERT INTO `m_auth_rule` VALUES ('13daab049f0cd2138bc8959f4a561642', 'cb6b5885c02afda8f5b2032e74ff7bc0', '删除', '/group/:id', 'DELETE', 'maidou.group.delete', '', 100, 0, 0, 1642147090, '127.0.0.1', 1642147090, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('18bde8944f3fe4ce8bc0d196033b1e92', 'bdadf86dfe7e11863992de6740e5ffb5', '验证码', '/login/captcha', 'GET', 'maidou.login.captcha', '', 100, 0, 0, 1642145892, '127.0.0.1', 1642145892, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('25d596dab66f90877bda40e12fd4cfc7', 'bdadf86dfe7e11863992de6740e5ffb5', '登录', '/login', 'POST', 'maidou.login.login', '', 100, 0, 0, 1642145932, '127.0.0.1', 1642145932, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('2a3e82521ab0200db0b47532f220b174', 'cb6b5885c02afda8f5b2032e74ff7bc0', '列表', '/group', 'GET', 'maidou.group.index', '', 100, 0, 0, 1642146856, '127.0.0.1', 1642146856, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('2d18160892317567a9275168106c3a35', 'f2d3e96e39c2e7aca36316db3e4b43b8', '创建', '/rule', 'POST', 'maidou.rule.create', '', 100, 0, 0, 1642146521, '127.0.0.1', 1642146521, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('347245ca6bffebb49abf9de67caf39b3', 'f2d3e96e39c2e7aca36316db3e4b43b8', '修改', '/rule/:id', 'PUT', 'maidou.rule.update', '', 100, 0, 0, 1642146602, '127.0.0.1', 1642146602, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('356d99ab74bef8537ae268098cffe7c8', 'f2d3e96e39c2e7aca36316db3e4b43b8', '删除', '/rule/:id', 'DELETE', 'maidou.rule.delete', '', 100, 0, 0, 1642146628, '127.0.0.1', 1642146628, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('3d155f15ec05b96778329a42f48d5af8', '0', '账号管理', '#', 'OPTIONS', 'maidou.admin', '', 100, 0, 0, 1642145986, '127.0.0.1', 1642145986, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('457f7cd58a7e823542a59b5dc388fbf8', 'cb6b5885c02afda8f5b2032e74ff7bc0', '授权', '/group/:id', 'PATCH', 'maidou.group.access', '', 100, 0, 0, 1642147133, '127.0.0.1', 1642147133, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('4b3d5d0f89c82fdce538776e93f83ff0', 'f2d3e96e39c2e7aca36316db3e4b43b8', '列表', '/rule', 'GET', 'maidou.rule.index', '', 100, 0, 0, 1642146496, '127.0.0.1', 1642146496, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('751c106d72e0f06c601065a8e0e29b03', '3', '创建', '/admin', 'POST', 'maidou.admin.create', '', 100, 0, 0, 1642146094, '127.0.0.1', 1642146094, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('92955fa09547143ea889caaec149ba7f', '3d155f15ec05b96778329a42f48d5af8', '详情', '/admin/:id', 'GET', 'maidou.admin.detail', '', 100, 0, 0, 1642146213, '127.0.0.1', 1642146213, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('9893a3f73405f76fd18bbf312993c49c', '3d155f15ec05b96778329a42f48d5af8', '角色列表', '/admin/group', 'GET', 'maidou.admin.group', '', 100, 0, 0, 1642146452, '127.0.0.1', 1642146452, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('ac03d73ca9d5d803dee78e4b0e6611ea', '3d155f15ec05b96778329a42f48d5af8', '授权', '/admin/:id', 'PATCH', 'maidou.admin.access', '', 100, 0, 0, 1642146427, '127.0.0.1', 1642146427, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('acf266b0505dd7c38e3fabf20c508e72', 'cb6b5885c02afda8f5b2032e74ff7bc0', '创建', '/group', 'POST', 'maidou.group.create', '', 100, 0, 0, 1642146891, '127.0.0.1', 1642146891, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('ae7898a55fff48e9c0f36b62aa728499', 'cb6b5885c02afda8f5b2032e74ff7bc0', '修改', '/group/:id', 'PUT', 'maidou.group.update', '', 100, 0, 0, 1642147014, '127.0.0.1', 1642147014, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('b3896389a244ef995c606df0e99c4351', '3d155f15ec05b96778329a42f48d5af8', '删除', '/admin/:id', 'DELETE', 'maidou.admin.delete', '', 100, 0, 0, 1642146278, '127.0.0.1', 1642146278, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('b93dfb46db4416ea28d5a18194b03da8', 'cb6b5885c02afda8f5b2032e74ff7bc0', '详情', '/group/:id', 'GET', 'maidou.group.detail', '', 100, 0, 0, 1642146964, '127.0.0.1', 1642146964, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('bbc051444b00f00c47c062a278f645fd', '3d155f15ec05b96778329a42f48d5af8', '修改密码', '/admin/modify-password', 'PATCH', 'maidou.admin.modifyPassword', '', 100, 0, 0, 1642146168, '127.0.0.1', 1642146168, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('bdadf86dfe7e11863992de6740e5ffb5', '0', '登录', '#', 'OPTIONS', 'maidou.login', '', 100, 0, 0, 1642145834, '127.0.0.1', 1642145834, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('cb6b5885c02afda8f5b2032e74ff7bc0', '0', '角色管理', '#', 'OPTIONS', 'maidou.group', '', 100, 0, 0, 1642146005, '127.0.0.1', 1642146005, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('d57c3eef125cb5e3a431ebefae19ef28', 'bdadf86dfe7e11863992de6740e5ffb5', '退出', '/login/log-out/:id', 'DELETE', 'maidou.login.loginOut', '', 100, 0, 0, 1642145964, '127.0.0.1', 1642145964, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('f2d3e96e39c2e7aca36316db3e4b43b8', '0', '权限管理', '#', 'OPTIONS', 'maidou.rule', '', 100, 0, 0, 1642146024, '127.0.0.1', 1642146024, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('f3364b5ddc1b2782631cd395642dac52', '3d155f15ec05b96778329a42f48d5af8', '修改', '/admin/:id', 'PUT', 'maidou.admin.update', '', 100, 0, 0, 1642146242, '127.0.0.1', 1642146242, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('f79525f9243db8307590593ac0077e39', 'f2d3e96e39c2e7aca36316db3e4b43b8', '详情', '/rule/:id', 'GET', 'maidou.rule.detail', '', 100, 0, 0, 1642146549, '127.0.0.1', 1642146549, '127.0.0.1');
INSERT INTO `m_auth_rule` VALUES ('fac0a6f42099784962914b07a3864355', '3d155f15ec05b96778329a42f48d5af8', '列表', '/admin', 'GET', 'maidou.admin.index', '', 100, 0, 0, 1642146064, '127.0.0.1', 1642146064, '127.0.0.1');

-- ----------------------------
-- Table structure for m_auth_rule_access
-- ----------------------------
DROP TABLE IF EXISTS `m_auth_rule_access`;
CREATE TABLE `m_auth_rule_access`  (
  `id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `group_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `rule_id` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `rule_id`(`rule_id`, `group_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '用户组与权限关联表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for m_migrations
-- ----------------------------
DROP TABLE IF EXISTS `m_migrations`;
CREATE TABLE `m_migrations`  (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `end_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of m_migrations
-- ----------------------------
INSERT INTO `m_migrations` VALUES (20181113071924, 'CreateRulesTable', '2022-01-14 16:45:17', '2022-01-14 16:45:17', 0);

-- ----------------------------
-- Table structure for m_rules
-- ----------------------------
DROP TABLE IF EXISTS `m_rules`;
CREATE TABLE `m_rules`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ptype` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `v0` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `v1` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `v2` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `v3` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `v4` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `v5` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 118 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
