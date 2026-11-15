<?php
/*
 * 【このファイルは？】
 * 利用者画面のスマートフォン用下部ナビ（トップ、探す、AI、知識、マイページ）。
 * templates/layout/default.php から読み込む。設計書 COM-001 の COM-009 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 */

// 現在地として選択状態にする項目。各画面のファイルで指定する値（navCurrent）を、ヘッダーと共通で使う。
// 使える値：top、search、map、ai、quiz、mypage（何も指定しないときは、どれも選択しない）
// 下部ナビには「地図で探す」がないので、map のときは「探す」を選択する（C チーム決定事項 1）。
$navCurrent = $this->fetch('navCurrent');
if ($navCurrent === 'map') {
    $navCurrent = 'search';
}

// 下部ナビの項目（名前と順番は設計書 COM-009 と C チーム決定事項 1 のとおり）。
// URL は仮。templates/element/header.php の $navItems と同じ URL にそろえる。
// icon はアイコンの目印。アイコンの画像と表示は E チームが bottom-nav__icon--（icon の値）に CSS で付ける。
$bottomNavItems = [
    'top' => ['label' => 'トップ', 'url' => '/', 'icon' => 'home'],
    'search' => ['label' => '探す', 'url' => '/search', 'icon' => 'search'],
    'ai' => ['label' => 'AI', 'url' => '/chat', 'icon' => 'ai'],
    'quiz' => ['label' => '知識', 'url' => '/quiz', 'icon' => 'quiz'],
    'mypage' => ['label' => 'マイページ', 'url' => '/mypage', 'icon' => 'mypage'],
];
?>
<!--
    画面の下に固定する表示、本文や通知と重ならない余白、端末の安全領域（iPhone の下の部分）は E チームが CSS で作る。
    PC の幅（1200px 以上）では表示しない（C チーム決定事項 3）。
-->
<nav class="bottom-nav" aria-label="画面下のメニュー">
    <ul class="bottom-nav__list">
        <?php foreach ($bottomNavItems as $key => $item) : ?>
            <li class="bottom-nav__item">
                <a
                    class="bottom-nav__link"
                    href="<?= $this->Url->build($item['url']) ?>"
                    <?= $navCurrent === $key ? 'aria-current="page"' : '' ?>
                >
                    <span class="bottom-nav__icon bottom-nav__icon--<?= h($item['icon']) ?>" aria-hidden="true"></span>
                    <span class="bottom-nav__label"><?= h($item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
