<?php
/*
 * 【このファイルは？】
 * 追加機能（プラグイン）の一覧。次の 3 つが入っている。
 *   DebugKit   … 開発中、画面の右下に出るアイコン。どんな処理が動いたかを見られる
 *   Bake       … コマンド 1 行で Controller や Model のファイルを自動で作る
 *   Migrations … データベースの表を、プログラムで作ったり変えたりする
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
 * @since         5.0.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

return [
    'DebugKit' => ['onlyDebug' => true],
    'Bake' => ['onlyCli' => true, 'optional' => true],
    'Migrations' => ['onlyCli' => true],
];
