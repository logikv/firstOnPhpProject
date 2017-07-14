<?php
if (!extension_loaded('onphp')) {
    echo 'Trying to load onPHP extension.. ';

    if (!@dl('onphp.so')) {
        echo "failed.\n";
    } else {
        echo "done.\n";
    }
}

date_default_timezone_set('Europe/Moscow');
define('ONPHP_TEST_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);

require ONPHP_TEST_PATH . '../global.inc.php.tpl';

define('ENCODING', 'UTF-8');

mb_internal_encoding(ENCODING);
mb_regex_encoding(ENCODING);

\OnPhp\AutoloaderPool::get('onPHP')->addPath(ONPHP_TEST_PATH . 'misc');

$testPathes = [
    ONPHP_TEST_PATH . 'core' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Autoloader' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Ip' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Net' . DIRECTORY_SEPARATOR . 'Http' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'Routers' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'main' . DIRECTORY_SEPARATOR . 'Utils' . DIRECTORY_SEPARATOR . 'AMQP' . DIRECTORY_SEPARATOR,
    ONPHP_TEST_PATH . 'db' . DIRECTORY_SEPARATOR,
];

$config = dirname(__FILE__) . '/config.inc.php';

include is_readable($config) ? $config : $config . '.tpl';

final class AllTests
{
    public static $dbs = null;
    public static $paths = null;
    public static $workers = null;

    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new TestSuite('onPHP-' . ONPHP_VERSION);

        // meta, DB and DAOs ordered tests portion
        if (self::$dbs) {
            try {
                /**
                 * @todo fail - constructor with argument, but static method 'me' - without
                 */
                \OnPhp\Singleton::getInstance('DBTestPool', self::$dbs)->connect();
            } catch (Exception $e) {
                \OnPhp\Singleton::dropInstance('DBTestPool');
                \OnPhp\Singleton::getInstance('DBTestPool');
            }

            // build stuff from meta

            $metaDir = ONPHP_TEST_PATH . 'meta' . DIRECTORY_SEPARATOR;
            $path = ONPHP_META_PATH . 'bin' . DIRECTORY_SEPARATOR . 'build.php';

            $_SERVER['argv'] = [];

            $_SERVER['argv'][0] = $path;

            $_SERVER['argv'][1] = $metaDir . 'config.inc.php';

            $_SERVER['argv'][2] = $metaDir . 'config.meta.xml';

            $_SERVER['argv'][] = '--force';
            $_SERVER['argv'][] = '--no-schema-check';
            $_SERVER['argv'][] = '--drop-stale-files';
//            $_SERVER['argv'][] = '--puml';

            include $path;

            \OnPhp\AutoloaderPool::get('onPHP')->addPaths([
                ONPHP_META_AUTO_BUSINESS_DIR,
                ONPHP_META_AUTO_DAO_DIR,
                ONPHP_META_AUTO_PROTO_DIR,

                ONPHP_META_DAO_DIR,
                ONPHP_META_BUSINESS_DIR,
                ONPHP_META_PROTO_DIR
            ]);

            $dBCreator = (new DBTestCreator())->
            setSchemaPath(ONPHP_META_AUTO_DIR . 'schema.php')->
            setTestPool(DBTestPool::me());

            $out = \OnPhp\MetaConfiguration::me()->getOutput();

            foreach (DBTestPool::me()->getPool() as $connector => $db) {
                \OnPhp\DBPool::me()->setDefault($db);

                $out
                    ->info('Using ')
                    ->info(get_class($db), true)
                    ->infoLine(' connector.');

                $dBCreator->dropDB(true);

                $dBCreator->createDB()->fillDB();

                \OnPhp\MetaConfiguration::me()->checkIntegrity();

                $out->newLine();

                $dBCreator->dropDB();
            }

            \OnPhp\DBPool::me()->dropDefault();
        }

        foreach (self::$paths as $testPath) {
            foreach (glob($testPath . '*Test' . EXT_CLASS, GLOB_BRACE) as $file) {
                $suite->addTestFile($file);
            }
        }

        return $suite;
    }
}

AllTests::$dbs = $dbs;
AllTests::$paths = $testPathes;
AllTests::$workers = $daoWorkers;
?>