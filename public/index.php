<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// [ 应用入口文件 ]

// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

// 定义配置目录
define('CONF_PATH', __DIR__ . '/../config/');

//定义样式、图片、js等的路径
define('COMMON_STATIC', '/static/common/');
define('INDEX_STATIC', '/static/index/');
define('ADMIN_STATIC', '/static/admin/');
define('PLUGINS_STATIC', '/static/plugins/');
define('MOBILE_STATIC', '/static/mobile/');
// 加载框架引导文件
require __DIR__ . '/../thinkphp/start.php';
