<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * 自動チェック（テスト）を始める前の準備。チェック用のデータベースを用意する。
 *
 * 【担当】G チーム
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
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Chronos\Chronos;
use Cake\Core\Configure;
use Cake\TestSuite\ConnectionHelper;
use Migrations\TestSuite\Migrator;

require dirname(__DIR__) . '/vendor/autoload.php';

require dirname(__DIR__) . '/config/bootstrap.php';

if (empty($_SERVER['HTTP_HOST']) && !Configure::read('App.fullBaseUrl')) {
    Configure::write('App.fullBaseUrl', 'http://localhost');
}

Chronos::setTestNow(Chronos::now());

session_id('cli');

ConnectionHelper::addTestAliases();

(new Migrator())->run();
