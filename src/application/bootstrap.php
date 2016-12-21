<?php

/**
 * @author     Nikhil N R, <nikhil@salesx.io>
 * @date       April 24, 2016
 * @brief      All bootstraping activities
 * @details    
 */
session_cache_limiter(false);
session_start();
date_default_timezone_set('Asia/Kolkata');
define('INC_ROOT', dirname(__DIR__));

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \library\IPL\Hash\HashManager as HashManager;
use \library\IPL\Common\Logger as Logger;
use \library\IPL\Token\TokenManager as TokenManager;
use \library\IPL\User\UserManager as UserManager;

require INC_ROOT . '/vendor/autoload.php';


$app = new Slim\App([
    'mode' => file_get_contents(INC_ROOT . '/mode.ini')
        ]);

$dotenv = new Dotenv\Dotenv(INC_ROOT . '/application/config', $app->getContainer()->mode . '.env');
$dotenv->load();

require INC_ROOT . '/application/config/database.php';
require INC_ROOT . '/application/controllers/ApiController.php';



$routeSource = INC_ROOT . '/application/config/routes.xml';
$routeXmlStr = file_get_contents($routeSource);
$routeXml = simplexml_load_string($routeXmlStr);
$routeJson = json_encode($routeXml);
$routeArray = json_decode($routeJson, TRUE);
$GLOBALS["routes"] = $routeArray;
$GLOBALS["v1.0"] = new \ApiController();

$app->post('/{version}/{apiname}', function (Request $request, Response $response) {
    $_SESSION['api_version'] = $request->getAttribute('version');
    $serevrParams = $request->getServerParams();
    $_SESSION['ip_address'] = $serevrParams['REMOTE_ADDR'];
    Logger::setAccessLogString($request);
    try {
        if (!TokenManager::validateTokens($request)) {
            Logger::access_logger($_SESSION['access_log_string']);
            return $GLOBALS[$request->getAttribute('version')]->error(401);
        }
        Logger::access_logger($_SESSION['access_log_string']);
        $apiname = $request->getAttribute('apiname');

        if (array_key_exists($request->getAttribute('apiname'), $GLOBALS["routes"]["post"])) {
            if (!UserManager::validateRoles($GLOBALS["routes"]["post"][$apiname])) {
                return $GLOBALS[$request->getAttribute('version')]->error(401);
            }
            return $GLOBALS[$request->getAttribute('version')]->$apiname($request);
        } else {
            return $GLOBALS[$request->getAttribute('version')]->error(404);
        }
    } catch (Exception $e) {
        return $GLOBALS[$request->getAttribute('version')]->error($e->getCode(), $e);
    }
});

$app->get('/{version}/{apiname}', function (Request $request, Response $response) {
    $_SESSION['api_version'] = $request->getAttribute('version');
    Logger::setAccessLogString($request);
    try {
        if (!TokenManager::validateTokens($request)) {
            Logger::access_logger($_SESSION['access_log_string']);
            return $GLOBALS[$request->getAttribute('version')]->error(401);
        }
        Logger::access_logger($_SESSION['access_log_string']);
        $apiname = $request->getAttribute('apiname');
        if (array_key_exists($request->getAttribute('apiname'), $GLOBALS["routes"]["get"])) {
            return $GLOBALS[$request->getAttribute('version')]->$apiname($request);
        } else {
            return $GLOBALS[$request->getAttribute('version')]->error(404);
        }
    } catch (Exception $e) {
        return $GLOBALS[$request->getAttribute('version')]->error($e->getCode(), $e);
    }
});
