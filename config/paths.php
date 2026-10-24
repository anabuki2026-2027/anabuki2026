<?php
/*
 * 【このファイルは？】
 * 「webroot フォルダはここ」「logs フォルダはここ」のように、フォルダの場所を決めている。
 *
 * 【担当】なし（編集しない。自動で作られたり、上書きされたりするため）
 * 【くわしく】docs/FILES.md
 */

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       MIT License (https://opensource.org/licenses/mit-license.php)
 */

if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

define('ROOT', dirname(__DIR__));

define('APP_DIR', 'src');

define('APP', ROOT . DS . APP_DIR . DS);

define('CONFIG', ROOT . DS . 'config' . DS);

define('WWW_ROOT', ROOT . DS . 'webroot' . DS);

define('TESTS', ROOT . DS . 'tests' . DS);

define('TMP', ROOT . DS . 'tmp' . DS);

define('LOGS', ROOT . DS . 'logs' . DS);

define('CACHE', TMP . 'cache' . DS);

define('RESOURCES', ROOT . DS . 'resources' . DS);

define('CAKE_CORE_INCLUDE_PATH', ROOT . DS . 'vendor' . DS . 'cakephp' . DS . 'cakephp');

define('CORE_PATH', CAKE_CORE_INCLUDE_PATH . DS);
define('CAKE', CORE_PATH . 'src' . DS);
