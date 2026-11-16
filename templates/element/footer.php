<?php
/*
 * 【このファイルは？】
 * 利用者画面の共通フッター（ロゴ、運営者情報、各種案内へのリンク、著作権表示）。
 * templates/layout/default.php から読み込む。設計書 COM-001 の COM-011 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 */

// 正式な案内ページと URL は未決定。A チームの文面と D・A チームの URL 決定後に合わせる。
$footerLinks = [
    ['label' => '利用規約', 'url' => '/pages/terms'],
    ['label' => 'プライバシーポリシー', 'url' => '/pages/privacy'],
    ['label' => 'お問い合わせ', 'url' => '/pages/contact'],
    ['label' => '運営者情報', 'url' => '/pages/operator'],
];
?>
<footer class="site-footer">
    <div class="site-footer__inner">
        <div class="site-footer__brand">
            <!-- 正式なロゴ素材と運営者名は A・E チームの決定後に差し替える（COM-001 No.3）。 -->
            <a class="site-footer__logo" href="<?= $this->Url->build('/') ?>" aria-label="イヌノコト トップページへ">
                イヌノコト
            </a>
            <p class="site-footer__operator">運営者情報：準備中</p>
        </div>

        <nav class="site-footer__nav" aria-label="サイト案内">
            <ul class="site-footer__links">
                <?php foreach ($footerLinks as $link) : ?>
                    <li class="site-footer__item">
                        <a class="site-footer__link" href="<?= $this->Url->build($link['url']) ?>">
                            <?= h($link['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <!-- 著作権者の正式表記は A チームの決定後に差し替える。 -->
        <small class="site-footer__copyright">© <?= h((string)date('Y')) ?> イヌノコト</small>
    </div>
</footer>
