<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * アプリが動き出すときに、一番最初に読まれる設定ファイル。
 * app.php や app_local.php を読み込んで、準備をする。
 *
 * 【担当】A チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         0.10.8
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

require __DIR__ . DIRECTORY_SEPARATOR . 'paths.php';

require CORE_PATH . 'config' . DS . 'bootstrap.php';

use Cake\Cache\Cache;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;
use Cake\Error\ErrorTrap;
use Cake\Error\ExceptionTrap;
use Cake\Http\ServerRequest;
use Cake\Log\Log;
use Cake\Mailer\Mailer;
use Cake\Mailer\TransportFactory;
use Cake\Routing\Router;
use Cake\Utility\Security;
use Detection\MobileDetect;
use function Cake\Core\env;

require CAKE . 'functions.php';

try {
    Configure::config('default', new PhpConfig());
    Configure::load('app', 'default', false);
} catch (Exception $e) {
    exit($e->getMessage() . "\n");
}

if (file_exists(CONFIG . 'app_local.php')) {
    Configure::load('app_local', 'default');
}

if (Configure::read('debug')) {
    Configure::write('Cache._cake_model_.duration', '+1 minute');
    Configure::write('Cache._cake_translations_.duration', '+1 minute');
}

date_default_timezone_set(Configure::read('App.defaultTimezone'));

mb_internal_encoding(Configure::read('App.encoding'));

ini_set('intl.default_locale', Configure::read('App.defaultLocale'));

(new ErrorTrap(Configure::read('Error')))->register();
(new ExceptionTrap(Configure::read('Error')))->register();

if (PHP_SAPI === 'cli') {
    if (Configure::check('Log.debug')) {
        Configure::write('Log.debug.file', 'cli-debug');
    }
    if (Configure::check('Log.error')) {
        Configure::write('Log.error.file', 'cli-error');
    }
}

$fullBaseUrl = Configure::read('App.fullBaseUrl');
if (!$fullBaseUrl) {
    $httpHost = env('HTTP_HOST');

    if ($httpHost) {
        $s = null;
        if (env('HTTPS') || env('HTTP_X_FORWARDED_PROTO') === 'https') {
            $s = 's';
        }
        $fullBaseUrl = 'http' . $s . '://' . $httpHost;
    }
    unset($httpHost, $s);
}
if ($fullBaseUrl) {
    Router::fullBaseUrl($fullBaseUrl);
}
unset($fullBaseUrl);

Cache::setConfig(Configure::consume('Cache'));
ConnectionManager::setConfig(Configure::consume('Datasources'));
TransportFactory::setConfig(Configure::consume('EmailTransport'));
Mailer::setConfig(Configure::consume('Email'));
Log::setConfig(Configure::consume('Log'));
Security::setSalt(Configure::consume('Security.salt'));

ServerRequest::addDetector('mobile', function ($request) {
    $detector = new MobileDetect();

    return $detector->isMobile();
});
ServerRequest::addDetector('tablet', function ($request) {
    $detector = new MobileDetect();

    return $detector->isTablet();
});
