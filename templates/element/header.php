<?php
/*
 * 【このファイルは？】
 * 利用者画面の共通ヘッダー（ロゴ、メインナビ、文字サイズ、ログイン・アカウント表示、スマホ用メニュー）。
 * templates/layout/default.php から読み込む。設計書 COM-001、SCR-001 の TOP-001〜008 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 */

// 現在地として選択状態にするナビの項目。各画面のファイルで次のように指定する（C チーム決定事項 1）。
// 例：$this->assign('navCurrent', 'search');
// 使える値：top、search、map、ai、quiz、mypage（何も指定しないときは、どれも選択しない）
$navCurrent = $this->fetch('navCurrent');

// ナビの項目（名前と順番は設計書 COM-004 のとおり）。
// URL は仮。D チーム（config/routes.php は A チーム）と決まったら、ここだけを書きかえる。
$navItems = [
    'search' => ['label' => 'お店を探す', 'url' => '/search'],
    'map' => ['label' => '地図で探す', 'url' => '/map'],
    'ai' => ['label' => 'AIと探す', 'url' => '/chat'],
    'quiz' => ['label' => '知識チェック', 'url' => '/quiz'],
    'mypage' => ['label' => 'マイページ', 'url' => '/mypage'],
];
$loginUrl = '/login';

// 文字サイズの選択肢（C チーム決定事項 2）。
// 保存と切り替えの JavaScript は E チームが作る。ボタンの目印は data-font-size-option。
$fontSizes = [
    'small' => '小',
    'medium' => '中',
    'large' => '大',
];

// ログイン状態は、サーバーで確認したもの（ログインの仕組みが入れる identity）だけを使う（COM-005）。
// ニックネームの項目名（nickname）は仮。B・D チームと確認する。
$identity = $this->getRequest()->getAttribute('identity');
$isLoggedIn = $identity !== null;
$nickname = '';
if ($isLoggedIn && isset($identity['nickname'])) {
    $nickname = (string)$identity['nickname'];
}
?>
<a class="skip-link" href="#main-content">本文へ移動</a>
<header class="site-header">
    <div class="site-header__brand">
        <a class="site-header__logo" href="<?= $this->Url->build('/') ?>" aria-label="イヌノコト トップページへ">
            <!-- 正式なロゴが決まるまでは、文字のロゴを仮に使う（COM-001 No.3） -->
            イヌノコト
        </a>
        <p class="site-header__tagline">人にも、犬にも、やさしい場所。</p>
    </div>

    <nav class="site-header__nav" aria-label="メインメニュー">
        <ul class="site-header__nav-list">
            <?php foreach ($navItems as $key => $item) : ?>
                <li class="site-header__nav-item">
                    <a
                        class="site-header__nav-link"
                        href="<?= $this->Url->build($item['url']) ?>"
                        <?= $navCurrent === $key ? 'aria-current="page"' : '' ?>
                    ><?= h($item['label']) ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <div class="site-header__tools">
        <div class="font-size-switch" role="group" aria-label="文字サイズ">
            <span class="font-size-switch__label" aria-hidden="true">文字</span>
            <?php foreach ($fontSizes as $value => $label) : ?>
                <button
                    type="button"
                    class="font-size-switch__button"
                    data-font-size-option="<?= h($value) ?>"
                    aria-pressed="<?= $value === 'medium' ? 'true' : 'false' ?>"
                ><?= h($label) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="site-header__account">
            <?php if ($isLoggedIn) : ?>
                <a class="site-header__account-link" href="<?= $this->Url->build($navItems['mypage']['url']) ?>">
                    <?php if ($nickname !== '') : ?>
                        <span class="site-header__account-name"><?= h($nickname) ?></span> さんのマイページ
                    <?php else : ?>
                        マイページ
                    <?php endif; ?>
                </a>
            <?php else : ?>
                <a class="site-header__login-link" href="<?= $this->Url->build($loginUrl) ?>">ログイン</a>
            <?php endif; ?>
        </div>
    </div>

    <!--
        スマートフォン用メニュー（COM-008）。開く・閉じるの JavaScript は E チームが作る。
        目印：開くボタンは data-menu-open、閉じるボタンは data-menu-close、メニュー本体は id="site-menu"
    -->
    <button
        type="button"
        class="site-header__menu-button"
        aria-haspopup="dialog"
        aria-controls="site-menu"
        aria-expanded="false"
        data-menu-open
    >メニュー</button>

    <dialog class="site-menu" id="site-menu" aria-label="メニュー">
        <div class="site-menu__inner">
            <button type="button" class="site-menu__close" data-menu-close>閉じる</button>

            <nav class="site-menu__nav" aria-label="メニューの中のメインメニュー">
                <ul class="site-menu__nav-list">
                    <?php foreach ($navItems as $key => $item) : ?>
                        <li class="site-menu__nav-item">
                            <a
                                class="site-menu__nav-link"
                                href="<?= $this->Url->build($item['url']) ?>"
                                <?= $navCurrent === $key ? 'aria-current="page"' : '' ?>
                            ><?= h($item['label']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>

            <div class="font-size-switch" role="group" aria-label="文字サイズ">
                <span class="font-size-switch__label" aria-hidden="true">文字</span>
                <?php foreach ($fontSizes as $value => $label) : ?>
                    <button
                        type="button"
                        class="font-size-switch__button"
                        data-font-size-option="<?= h($value) ?>"
                        aria-pressed="<?= $value === 'medium' ? 'true' : 'false' ?>"
                    ><?= h($label) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="site-menu__account">
                <?php if ($isLoggedIn) : ?>
                    <a class="site-menu__account-link" href="<?= $this->Url->build($navItems['mypage']['url']) ?>">
                        <?php if ($nickname !== '') : ?>
                            <span class="site-menu__account-name"><?= h($nickname) ?></span> さんのマイページ
                        <?php else : ?>
                            マイページ
                        <?php endif; ?>
                    </a>
                <?php else : ?>
                    <a class="site-menu__login-link" href="<?= $this->Url->build($loginUrl) ?>">ログイン</a>
                <?php endif; ?>
            </div>
        </div>
    </dialog>
</header>
