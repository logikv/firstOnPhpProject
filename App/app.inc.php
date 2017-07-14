<?php
/**
 * Created by PhpStorm.
 * User: dewid
 * Date: 19.06.17
 * Time: 14:21
 */


defined('INDEX') or define('INDEX', dirname(__FILE__));
defined('DS') or define('DS', DIRECTORY_SEPARATOR);
define('DEFAULT_ENCODING', 'UTF-8');
define('APP_ROOT_PATH', dirname(__FILE__) . DS);
define('PATH_CLASSES', APP_ROOT_PATH);
define('APP_CONFIG', APP_ROOT_PATH . 'Configs' . DS);
define('DATA_PATH', APP_ROOT_PATH . '..'.DS.'Data' . DS);
define('ONPHP_TEMP_PATH', '/var/log/onphp/');
define('__LOCAL_DEBUG__', true);
define('ENV', 'dev');
define('SITE_NAME', 'newsinfo.ru');
define('__INDEX__', INDEX);
//php /home/srv/www/htdocs/ked.su/onphp-framework/meta/bin/build.php /home/srv/www/htdocs/ked.su/newKed/App/app.inc.php /home/srv/www/htdocs/ked.su/newKed/App/Meta/include.xml
require_once(APP_ROOT_PATH . '../onphp-framework/global.inc.php');
(new OnPhp\AutoloaderClassPathCache())
    ->setNamespaceResolver(new OnPhp\NamespaceResolverPSR0())
    ->addPaths(
        [
            APP_ROOT_PATH,
            APP_ROOT_PATH . 'Auto',
            APP_ROOT_PATH . 'Auto/Business',
            APP_ROOT_PATH . 'Auto/DAOs',
            APP_ROOT_PATH . 'Auto/Proto',
            APP_ROOT_PATH . 'Base',
            APP_ROOT_PATH . 'Business',
            APP_ROOT_PATH . 'DAOs',
            APP_ROOT_PATH . 'Exceptions',
            APP_ROOT_PATH . 'Proto',
            APP_ROOT_PATH . 'Controllers',
        ]
    )
    ->register();

OnPhp\Cache::setDefaultWorker('\\OnPhp\\CacheDaoWorker');
OnPhp\Cache::setPeer(new \OnPhp\SocketMemcached());

$db = \Base\Config::me()->getItem('database');
\OnPhp\DBPool::me()
    ->setDefault(
        \OnPhp\DB::spawn($db['connector'], $db['user'], $db['password'], $db['host'], $db['base'])
            ->setEncoding(DEFAULT_ENCODING)
    );