<?php
//环境变量
define('ENV'                , getenv('ENV') ? getenv('ENV') : (isset($_SERVER['ENV']) ? $_SERVER['ENV'] : 'product'));          //环境变量，dev(开发), test(测试), product(正式)
define('DEBUG'              , ENV == 'dev' );    //调试
define('IS_LOCAL'           , ENV != 'product'  ); //本地环境+测试环境
define('IS_TEST'            , ENV != 'product'  && 'test' == ENV); //测试环境
define('IS_PRODUCT'         , ENV == 'product'); //线上环境
define('IS_WIN'             , '\\' == DIRECTORY_SEPARATOR);
define('IS_PHP_V5_3'        , version_compare(PHP_VERSION, '5.3', '>='));//是否为5.3以上版本
define('NEED_PHP_V5_3'      , false);//true需要5.3以上版本
define('__GET'              , isset($_GET['__get']) && DEBUG);  //调试模式下，通过$_GET获取_POST数据

define('EOL'                        , "\n");//换行符

define('DEFAULT_PAGE_SIZE', 20);	//默认页面尺寸
define('API_KEY', 'hjiyGHDnmkLITbd874dnn#ppoubnlmdf');	//默认apiKEY值

?>