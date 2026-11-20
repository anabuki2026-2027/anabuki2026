<?php
/*
 * 【このファイルは？】
 * 共通モーダルの外わく（小見出し、見出し、説明、中身、操作ボタンの場所）。
 * お知らせ詳細・口コミ通報・管理画面の確認や入力など、すべてのモーダルをこの部品で作る。
 * 設計書 COM-002 の MODAL-001 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string $id モーダルの id。ページの中で重ならない名前にする（英字で始め、英数字・-・_ だけ）
 * @var string $title 見出し（必須）。h() で囲んで表示する
 * @var string|null $eyebrow 見出しの上の小さな文字（例：NOTICE・10/20〜10/27）。h() で囲んで表示する
 * @var string|null $description 見出しの下の説明。h() で囲んで表示する
 * @var string|null $body 中身の HTML。そのまま表示するので、値は呼び出す側で h() で囲んでおく
 * @var string|null $actions 操作ボタンの HTML。そのまま表示するので、値は呼び出す側で h() で囲んでおく
 * @var string|null $class 外わくに足す class 名（例：幅の広いモーダル用の目印）。E チームと決めた名前だけを使う
 */

/*
 * 使い方（各画面のファイル）：
 * 1. 中身と操作ボタンを、ビューブロック（名前を付けて HTML をためておく場所）に書く。
 * 2. この部品を modals ブロックに足す。modals ブロックは templates/layout/default.php が
 *    </body> の直前にまとめて出すので、モーダルは <main> の外に置かれる。
 *
 *    <?php $this->start('noticeDetailBody'); ?>
 *        <p><?= h($notice->body) ?></p>
 *    <?php $this->end(); ?>
 *    <?php $this->start('noticeDetailActions'); ?>
 *        <form method="dialog"><button type="submit">閉じる</button></form>
 *    <?php $this->end(); ?>
 *    <?php $this->append('modals', $this->element('modal', [
 *        'id' => 'notice-detail',
 *        'eyebrow' => 'NOTICE',
 *        'title' => $notice->title,
 *        'body' => $this->fetch('noticeDetailBody'),
 *        'actions' => $this->fetch('noticeDetailActions'),
 *    ])); ?>
 *
 * 開くボタンは、各画面に次の形で置く（data-modal-open の値は、開くモーダルの id）。
 *
 *    <button type="button" aria-haspopup="dialog" aria-controls="notice-detail" data-modal-open="notice-detail">
 *        詳しく
 *    </button>
 *
 * 開く処理は E チームの JavaScript が行う（data-modal-open を押したら、同じ id の <dialog> を showModal() で開く）。
 * JavaScript が動かないときはモーダルが開かないので、内容を見るための別の行き方（リンクなど）が要る画面では、
 * 各画面でそれを用意する。
 */

$id = isset($id) && is_string($id) ? $id : '';
$title = isset($title) && is_string($title) ? $title : '';
if (!preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/', $id)) {
    throw new InvalidArgumentException('モーダルの id は、英字で始まり、英数字・-・_ だけの名前にしてください。');
}
if ($title === '') {
    throw new InvalidArgumentException('モーダルの見出し（title）を指定してください。');
}

$eyebrow = isset($eyebrow) && is_string($eyebrow) ? $eyebrow : '';
$description = isset($description) && is_string($description) ? $description : '';
$body = isset($body) && is_string($body) ? $body : '';
$actions = isset($actions) && is_string($actions) ? $actions : '';
$extraClass = isset($class) && is_string($class) ? trim($class) : '';

// 見出しと説明の id。<dialog> の aria-labelledby・aria-describedby で結び付け、
// 読み上げソフトが「何のダイアログか」を伝えられるようにする（MODAL-007）。
$titleId = $id . '-title';
$descriptionId = $id . '-description';
?>
<!--
    共通モーダル（MODAL-001）。
    <dialog> を showModal() で開くと、ブラウザーが次のことを行う（C チーム決定事項 4）。
      ・ほかのモーダルやページより前面（top layer）に出す
      ・背面のページを操作できなくする（読み上げソフトからも見えなくする）
      ・Escape キーで閉じる
    E チームにお願いすること：
      ・開く処理（data-modal-open）と、開いている間の背面のスクロールを止める処理
      ・すでに開いているモーダルがあるときは、別のモーダルを重ねて開かない処理（MODAL-001「重複表示」）
      ・背景（::backdrop）、幅・高さ・余白、画面幅ごとの表示
      ・画面に収まらないときに modal__body の中だけをスクロールさせる CSS
-->
<dialog
    class="modal<?= $extraClass !== '' ? ' ' . h($extraClass) : '' ?>"
    id="<?= h($id) ?>"
    aria-labelledby="<?= h($titleId) ?>"
    <?= $description !== '' ? 'aria-describedby="' . h($descriptionId) . '"' : '' ?>
    data-modal
>
    <div class="modal__inner">
        <div class="modal__header">
            <?php if ($eyebrow !== '') : ?>
                <p class="modal__eyebrow"><?= h($eyebrow) ?></p>
            <?php endif; ?>
            <h2 class="modal__title" id="<?= h($titleId) ?>"><?= h($title) ?></h2>
            <?php if ($description !== '') : ?>
                <p class="modal__description" id="<?= h($descriptionId) ?>"><?= h($description) ?></p>
            <?php endif; ?>
        </div>

        <?php if ($body !== '') : ?>
            <div class="modal__body">
                <?= $body ?>
            </div>
        <?php endif; ?>

        <?php if ($actions !== '') : ?>
            <!-- 操作ボタンの並び・種類（主・副・危険）・処理中の表示は MODAL-002 で決める -->
            <div class="modal__actions">
                <?= $actions ?>
            </div>
        <?php endif; ?>
    </div>
</dialog>
