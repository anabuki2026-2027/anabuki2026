#!/usr/bin/php -q
<?php
/*
 * 【このファイルは？】
 * bin/cake と bin/cake.bat が中で使っているファイル。
 * 打ったコマンドを CakePHP に伝える。
 *
 * 【担当】なし（編集しない。自動で作られたり、上書きされたりするため）
 * 【くわしく】docs/FILES.md
 */

require dirname(__DIR__) . '/vendor/autoload.php';

use App\Application;
use Cake\Console\CommandRunner;

$runner = new CommandRunner(new Application(dirname(__DIR__) . '/config'), 'cake');
exit($runner->run($argv));
