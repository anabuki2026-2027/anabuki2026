<?php
/*
 * 【このファイルは？】
 * ふつうのページの「外わく」（上のメニューなど、どのページでも同じ部分）。
 * 各ページの中身は、この外わくの中にはめこまれて表示される。CSS の読み込みもここでしている。
 *
 * 【担当】C・E チーム
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
 * @since         0.10.0
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 * @var \App\View\AppView $this
 */

$cakeDescription = 'CakePHP: the rapid development php framework';

// Flash（画面が移動したあとに 1 回だけ出すメッセージ）を、ここで先に作っておく。
// 成功・警告・エラーの通知（flash/success.php・warning.php・error.php）は、作るときに toastStatus・toastAlert
// ブロックへ足されるので、下の共通通知領域（toast_region）より先に作っておかないと、通知領域が空のまま出てしまう。
// それ以外のメッセージ（flash/info.php・default.php）は、今までどおり <main> の中に出す。
$flashMessages = $this->Flash->render();
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?= $cakeDescription ?>:
        <?= $this->fetch('title') ?>
    </title>
    <?= $this->Html->meta('icon') ?>

    <?= $this->Html->css(['normalize.min', 'milligram.min', 'fonts', 'cake']) ?>

    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
    <?= $this->fetch('script') ?>
</head>
<body>
    <?= $this->element('header') ?>
    <?php // 共通通知領域（NOTICE-001・002）。成功・警告・エラーの通知を出す。位置と見た目は E チームが CSS で決める。 ?>
    <?= $this->element('toast_region') ?>
    <main class="main" id="main-content" tabindex="-1">
        <div class="container">
            <?= $flashMessages ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>
    <?= $this->element('footer') ?>
    <?= $this->element('bottom_nav') ?>
    <?php
    // 各画面のモーダル（templates/element/modal.php）を、<main> の外にまとめて出す（MODAL-001）。
    // 各画面のファイルで $this->append('modals', $this->element('modal', [...])) のように足す。
    ?>
    <?= $this->fetch('modals') ?>
</body>
</html>
