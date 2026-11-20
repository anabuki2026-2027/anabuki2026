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
 * @var string|null $actions 操作ボタンの HTML。templates/element/modal_actions.php で作ったものを渡す（MODAL-002）
 * @var string|null $class 外わくに足す class 名（例：幅の広いモーダル用の目印）。E チームと決めた名前だけを使う
 * @var bool|null $autoOpen ページを開いたときに、このモーダルを開いた状態にするなら true。
 *   フォームを送って入力エラーで同じ画面に戻ったとき、入力とエラーを見せるために使う（MODAL-005「エラー時」）
 * @var string|null $kind モーダルの種類。閉じ方が変わる（MODAL-006、C チーム決定事項 4）。省略すると confirm
 *   - view（閲覧用。例：お知らせ詳細）：背景を押す・Escape キー・閉じるで閉じる
 *   - confirm（確認用。例：削除の確認）：背景を押しても閉じない。Escape キー・キャンセルで閉じる
 *   - input（入力用。例：通報、資格審査）：未入力なら view と同じ。入力があれば、背景を押しても閉じず、
 *     Escape キー・キャンセルでは「入力内容を破棄しますか」の確認を出す
 */

/*
 * 使い方（各画面のファイル）：
 * 1. 中身を、ビューブロック（名前を付けて HTML をためておく場所）に書く。
 * 2. 操作ボタンは templates/element/modal_actions.php で作る（並び順・種類・処理中の目印をそろえるため）。
 * 3. この部品を modals ブロックに足す。modals ブロックは templates/layout/default.php が
 *    </body> の直前にまとめて出すので、モーダルは <main> の外に置かれる。
 *
 *    <?php $this->start('noticeDetailBody'); ?>
 *        <p><?= h($notice->body) ?></p>
 *    <?php $this->end(); ?>
 *    <?php $this->append('modals', $this->element('modal', [
 *        'id' => 'notice-detail',
 *        'eyebrow' => 'NOTICE',
 *        'title' => $notice->title,
 *        'body' => $this->fetch('noticeDetailBody'),
 *        'actions' => $this->element('modal_actions'),
 *        'kind' => 'view',
 *    ])); ?>
 *
 * フォームを送るモーダル（通報・確認など）の書き方は、templates/element/modal_actions.php の使い方を見る。
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
$isAutoOpen = isset($autoOpen) && $autoOpen === true;
$kind = $kind ?? 'confirm';
if (!in_array($kind, ['view', 'confirm', 'input'], true)) {
    throw new InvalidArgumentException('モーダルの種類（kind）は、view・confirm・input のどれかにしてください。');
}

// 見出しと説明の id。<dialog> の aria-labelledby・aria-describedby で結び付け、
// 読み上げソフトが「何のダイアログか」を伝えられるようにする（MODAL-007）。
$titleId = $id . '-title';
$descriptionId = $id . '-description';
$discardTitleId = $id . '-discard-title';

/*
 * 共通モーダル（MODAL-001）。モーダルの数だけ出力されるので、説明はページに出ない PHP のコメントに書いている。
 * <dialog> を showModal() で開くと、ブラウザーが次のことを行う（C チーム決定事項 4）。
 *   ・ほかのモーダルやページより前面（top layer）に出す
 *   ・背面のページを操作できなくする（読み上げソフトからも見えなくする）
 *   ・Escape キーで閉じる（cancel イベントが起きる）
 *   ・閉じたときに、開く前にフォーカスがあった場所（開くボタン）へフォーカスを戻す（MODAL-006「終了後」）
 *
 * E チームにお願いすること（表示）：
 *   ・開く処理（data-modal-open）と、開いている間の背面のスクロールを止める処理
 *   ・すでに開いているモーダルがあるときは、別のモーダルを重ねて開かない処理（MODAL-001「重複表示」）
 *   ・背景（::backdrop）、幅・高さ・余白、画面幅ごとの表示
 *   ・画面に収まらないときに modal__body の中だけをスクロールさせる CSS
 *   ・data-modal-autoopen が付いたモーダルを、ページを開いたときに showModal() で開く処理（1 ページに 1 つまで）。
 *     このときは開くボタンを押していないので、閉じたら同じ id の data-modal-open のボタンへフォーカスを移す
 *
 * E チームにお願いすること（閉じ方。MODAL-006、C チーム決定事項 4）：
 *   data-modal-kind の値で、閉じ方を変える。
 *   | 種類              | 背景を押す | Escape キー・キャンセル（data-modal-cancel） |
 *   | view              | 閉じる     | 閉じる                                       |
 *   | confirm           | 閉じない   | 閉じる                                       |
 *   | input（入力なし） | 閉じる     | 閉じる                                       |
 *   | input（入力あり） | 閉じない   | 破棄の確認（data-modal-discard）を出す       |
 *   | 送信中（全部）    | 閉じない   | 閉じない                                     |
 *   ・背景を押したかどうかは、押した場所が <dialog> の四角の外かで判定する。<dialog> の中（文字や入力欄）を
 *     押しても閉じない（MODAL-006「内部操作」）。<dialog> に余白（padding）を付けず、modal__inner に付けると判定しやすい。
 *     view には closedby="any" を付けているので、対応しているブラウザーでは JavaScript がなくても背景で閉じる。
 *   ・「入力あり」は、開いたときの値から変わっているかで判定する（決定事項 4）。対象は <dialog> の中の
 *     method="dialog" ではないフォーム。開いたときに値を覚えておき、閉じる操作のときに比べる。
 *   ・入力ありで Escape キーを押したら cancel イベントを止め（preventDefault）、キャンセルを押したら
 *     送信を止めて、modal__inner に hidden を付け、data-modal-discard の hidden を外し、
 *     「入力に戻る」（data-modal-discard-back）へフォーカスを移す。
 *       「入力に戻る」：元にもどし、確認を出す前にフォーカスがあった場所へフォーカスを戻す
 *       「破棄して閉じる」（data-modal-discard-confirm）：フォームを reset() して、元の表示にもどしてから閉じる
 *   ・送信が成功したことを確かめてから閉じる。失敗したときは閉じずに、入力を残す（MODAL-006「成功時」「失敗時」）。
 *     普通のフォームで送る画面では、失敗すると同じ画面が autoOpen で開くので、閉じる処理は要らない。
 */
?>
<dialog
    class="modal<?= $extraClass !== '' ? ' ' . h($extraClass) : '' ?>"
    id="<?= h($id) ?>"
    aria-labelledby="<?= h($titleId) ?>"
    <?= $description !== '' ? 'aria-describedby="' . h($descriptionId) . '"' : '' ?>
    data-modal
    data-modal-kind="<?= h($kind) ?>"
    <?= $kind === 'view' ? 'closedby="any"' : '' ?>
    <?= $isAutoOpen ? 'data-modal-autoopen' : '' ?>
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
            <?php // 操作ボタンの並び・種類（主・副・危険）・処理中の目印は templates/element/modal_actions.php（MODAL-002） ?>
            <div class="modal__actions">
                <?= $actions ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($kind === 'input') : ?>
        <?php
        // 破棄の確認（MODAL-006「入力中の破棄防止」）。モーダルを重ねないように（MODAL-001）、
        // 同じモーダルの中身を、この表示に切り替える（C チーム決定事項 4）。文言は決定事項 20 のとおり。
        // ボタンは JavaScript で動く（確認が出るのは JavaScript が動いているときだけなので、それで足りる）。
        // 並び順は modal_actions.php と同じ（副操作が左、危険操作が右）。
        ?>
        <div class="modal__discard" role="group" aria-labelledby="<?= h($discardTitleId) ?>" data-modal-discard hidden>
            <p class="modal__title" id="<?= h($discardTitleId) ?>">入力内容を破棄しますか</p>
            <p class="modal__description">入力した内容は保存されません。</p>
            <div class="modal__actions">
                <button type="button" class="modal__button modal__button--secondary" data-modal-discard-back>
                    入力に戻る
                </button>
                <button type="button" class="modal__button modal__button--danger" data-modal-discard-confirm>
                    破棄して閉じる
                </button>
            </div>
        </div>
    <?php endif; ?>
</dialog>
