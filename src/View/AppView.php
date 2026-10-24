<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * すべての画面の「親」。
 * 全部の画面で使いたい便利な道具（Helper）があれば、ここで読み込む。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     3.0.0
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

use Cake\View\View;

class AppView extends View
{
    /**
     * @return void
     */
    public function initialize(): void
    {
    }
}
