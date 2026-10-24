<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * ページ全体を読み直さずに、一部分だけを入れかえるとき（Ajax）に使う画面の土台。
 *
 * 【担当】なし（見るだけ。変えたいときは A チームに相談）
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
 * @since         3.0.4
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */
namespace App\View;

class AjaxView extends AppView
{
    /**
     * @var string
     */
    protected string $layout = 'ajax';

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->response = $this->response->withType('ajax');
    }
}
