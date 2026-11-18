<?php
/*
 * 【このファイルは？】
 * 探し方の表示切り替え（リスト・地図・AI）。今の表示を選択状態にし、リストと地図の間では検索条件を引き継ぐ。
 * 設計書 SCR-002 の SRC-002 にあたる。同じ部品を SCR-003（MAP-001）と SCR-005（AI-002）でも使える。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string|null $current 今の表示。list（リスト）、map（地図）、ai（AI）のどれか
 */

// 使い方：各画面のファイルで、今の表示を current に指定して読み込む。
// 例：リスト（SCR-002）なら $this->element('search_view_switch', ['current' => 'list'])
// 使える値：list、map、ai（何も指定しないとき・ほかの値のときは、どれも選択しない）
$current = isset($current) && is_string($current) ? $current : '';

// 切り替えの項目（名前と順番は設計書 SRC-002 とモック 02-search-list.png のとおり）。
// URL は設計書のとおり（リストは SCR-002 の /search、地図は SCR-003 の /search?view=map、AI は SCR-005 の /chat）。
// ただし最終的なルートは未定なので仮。D チーム（config/routes.php は A チーム）と決まったら書きかえる。
// query：その表示にするために URL に付ける値。
// keepConditions：true なら今の検索条件を引き継ぐ（リストと地図の間だけ。AI へは引き継がない）。
// icon：アイコンの目印。アイコンの画像と表示は E チームが view-switch__icon--（icon の値）に CSS で付ける。
$viewItems = [
    'list' => ['label' => 'リスト', 'url' => '/search', 'query' => [], 'keepConditions' => true, 'icon' => 'list'],
    'map' => [
        'label' => '地図',
        'url' => '/search',
        'query' => ['view' => 'map'],
        'keepConditions' => true,
        'icon' => 'map',
    ],
    'ai' => ['label' => 'AI', 'url' => '/chat', 'query' => [], 'keepConditions' => false, 'icon' => 'ai'],
];

// リストと地図で共通に使う検索条件の名前（設計書 SRC-022、MAP-013）。この名前の条件だけを引き継ぐ。
// 並び順（sort）とページ（page）はリストだけ、検索範囲（range）は地図だけで使うので、引き継がない。
// q・prefecture・categories・sizes・access・openNow は、設計書 SRC-022 の URL の例にある名前。
// conditions（条件・設備）と favorites（お気に入りのみ）は仮の名前。D チームと決まったら書きかえる。
$sharedConditionKeys = ['q', 'prefecture', 'categories', 'sizes', 'conditions', 'access', 'openNow', 'favorites'];

// 今の URL の条件（? のあとの部分）から、引き継ぐ条件だけを取り出す。空の条件は付けない。
$queryParams = $this->getRequest()->getQueryParams();
$sharedConditions = [];
foreach ($sharedConditionKeys as $conditionKey) {
    $conditionValue = $queryParams[$conditionKey] ?? null;
    if ($conditionValue === null || $conditionValue === '' || $conditionValue === []) {
        continue;
    }
    $sharedConditions[$conditionKey] = $conditionValue;
}

// 各項目のリンク先を作る。
// 今の表示の項目は、今の条件のまま（並び順やページも含めて）同じ画面をもう一度表示する（SRC-002「リスト」）。
$viewLinks = [];
foreach ($viewItems as $viewKey => $item) {
    if ($viewKey === $current) {
        $query = $item['query'] + $queryParams;
    } elseif ($item['keepConditions']) {
        $query = $item['query'] + $sharedConditions;
    } else {
        $query = $item['query'];
    }
    $queryString = http_build_query($query);
    $viewLinks[$viewKey] = [
        'label' => $item['label'],
        'url' => $item['url'] . ($queryString !== '' ? '?' . $queryString : ''),
        'icon' => $item['icon'],
    ];
}

// 選択中の項目には aria-current="page" を付ける。読み上げソフトは「現在のページ」と読み上げる。
// 選択中・通常・フォーカスの見た目は、E チームが CSS で付ける（目印は .view-switch__link[aria-current="page"]）。
// templates/element/header.php のメインナビと同じやり方。
?>
<nav class="view-switch" aria-label="表示切り替え">
    <ul class="view-switch__list">
        <?php foreach ($viewLinks as $viewKey => $link) : ?>
            <li class="view-switch__item">
                <a
                    class="view-switch__link"
                    href="<?= $this->Url->build($link['url']) ?>"
                    <?= $viewKey === $current ? 'aria-current="page"' : '' ?>
                >
                    <span
                        class="view-switch__icon view-switch__icon--<?= h($link['icon']) ?>"
                        aria-hidden="true"
                    ></span>
                    <span class="view-switch__label"><?= h($link['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>
