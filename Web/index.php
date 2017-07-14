<?php
/**
 * Created by PhpStorm.
 * User: dewid
 * Date: 19.06.17
 * Time: 14:29
 */
//defined('SESSION_PATH') OR define('SESSION_PATH', '/srv/www/htdocs/php-session');
ini_set('assert.exception', 1);
error_reporting(1);
ini_set('display_errors', 1);
ini_set('date.timezone', 'Europe/Moscow');
//ini_set('session.save_path', SESSION_PATH);
define('INDEX', (dirname(__FILE__)));
define('DS', DIRECTORY_SEPARATOR);

require_once(dirname(__FILE__) . DS . '../App/app.inc.php');
require_once(dirname(__FILE__) . DS . '../App/route.inc.php');

OnPhp\Session::start();
$httpRequest = \OnPhp\HttpRequest::createFromGlobals();

if (!empty($_SESSION))
    $httpRequest->setSession(\OnPhp\Session::getAll());

OnPhp\RouterRewrite::me()
    ->route($httpRequest);

(new OnPhp\WebApplication())
    ->dropVar(OnPhp\WebApplication::OBJ_REQUEST)
    ->setRequest($httpRequest)
    ->setPathController(APP_ROOT_PATH . 'Controllers' . DS)
    ->setPathWeb('http://localhost/')
    ->setPathTemplate(APP_ROOT_PATH . 'Templates' . DS)
    ->setPathTemplateDefault(APP_ROOT_PATH . 'Templates' . DS)
    ->setServiceLocator(new OnPhp\ServiceLocator())
    ->add(new OnPhp\WebAppBufferHandler())
    ->add((new OnPhp\WebAppSessionHandler())->setCookieDomain('localhost')->setSessionName('localhost'))
    ->add(new OnPhp\WebAppControllerResolverHandler())
    ->add(new OnPhp\WebAppControllerHandler())
    ->add(new OnPhp\WebAppViewHandler())
    ->run();