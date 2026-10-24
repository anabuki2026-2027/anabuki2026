<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * すべての Controller の「親」。ここに書いたことは、全部の Controller で使える。
 * 今は「保存しました」のような、1 回だけ出るメッセージ（Flash）の機能を入れている。
 *
 * 【担当】D チーム（全体に影響するので、変えるときは A に相談）
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
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\Controller;

use Cake\Controller\Controller;

class AppController extends Controller
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
    }
}
