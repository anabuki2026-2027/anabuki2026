<?php
/*
 * 【このファイルは？】
 * 共通モーダルの操作ボタン（閉じる・キャンセル、副操作、危険操作、主操作）を、決まった順番と目印で並べる部品。
 * templates/element/modal.php の actions に渡して使う。設計書 COM-002 の MODAL-002 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string|null $cancel 閉じる・キャンセルのボタンの文字。省略すると「閉じる」
 * @var array<int, array<string, mixed>>|null $buttons 閉じる以外のボタン。1 つずつ次の値を指定する
 *   - label（必須）：ボタンの文字。h() で囲んで表示する。「通報する」「削除する」のように、押すと何が起きるかが分かる文字にする
 *   - variant：primary（主操作）、secondary（副操作）、danger（危険操作）のどれか。省略すると secondary
 *   - type：submit（フォームを送る）か button（JavaScript で動かす）。省略すると submit
 *   - form：送るフォームの id（type が submit のときは必須）。フォームはモーダルの中身（body）に置く
 *   - name・value：どのボタンで送ったかをサーバーに伝える値（例：name decision、value reject）
 *   - formaction：このボタンだけ送り先を変えるときの URL（文字列か、CakePHP の URL の配列）
 *   - processingLabel：処理中に表示する文字。省略すると「処理中…」
 */

/*
 * 使い方（各画面のファイル）：
 * 1. 入力欄を持つ <form> を、モーダルの中身（body）に書き、id を付ける。
 * 2. この部品で作ったボタンを、modal.php の actions に渡す。ボタンは form 属性で 1 のフォームとつながるので、
 *    <form> の外（modal__actions の中）に置かれても送信できる。
 *
 *    <?php $this->start('reportBody'); ?>
 *        <?= $this->Form->create(null, ['id' => 'report-form', 'url' => ['action' => 'report']]) ?>
 *            （通報理由のラジオボタンなど）
 *        <?= $this->Form->end() ?>
 *    <?php $this->end(); ?>
 *    <?php $this->append('modals', $this->element('modal', [
 *        'id' => 'report',
 *        'title' => 'この評価を通報する',
 *        'body' => $this->fetch('reportBody'),
 *        'actions' => $this->element('modal_actions', [
 *            'cancel' => 'キャンセル',
 *            'buttons' => [
 *                ['label' => '通報する', 'variant' => 'primary', 'form' => 'report-form'],
 *            ],
 *        ]),
 *    ])); ?>
 *
 * 見るだけのモーダル（お知らせ詳細など）は、buttons を省略すると「閉じる」だけになる。
 *    'actions' => $this->element('modal_actions'),
 *
 * 【決まり（MODAL-002）】
 * ・並び順はこの部品が決める。左から「閉じる・キャンセル → 副操作 → 危険操作 → 主操作」（モック 13・35 のとおり）。
 *   同じ種類の中では、buttons に書いた順に並ぶ。どの画面でも、主操作は右はし、キャンセルは左はしになる。
 * ・主操作（primary）は 1 つのモーダルに 1 つまで。削除の確認のように危険操作が一番の操作のときは、
 *   primary を置かずに danger だけを置く。
 * ・危険操作（danger）は、削除、却下、差し戻し、取消など、取り消しにくい操作に使う。
 * ・閉じる・キャンセルのボタンは必ず付く（MODAL-006「閉じる、キャンセルを共通で利用できる」）。
 */

$cancelLabel = isset($cancel) && is_string($cancel) && trim($cancel) !== '' ? $cancel : '閉じる';
$buttonList = isset($buttons) && is_array($buttons) ? $buttons : [];

// 種類ごとの並び順（小さいほど左）。閉じる・キャンセルは、この外で一番左に出す。
$variantOrder = ['secondary' => 1, 'danger' => 2, 'primary' => 3];

$normalized = [];
$primaryCount = 0;
foreach (array_values($buttonList) as $index => $button) {
    $number = $index + 1;
    if (!is_array($button)) {
        throw new InvalidArgumentException("モーダルの操作ボタン {$number} 番目は、配列で指定してください。");
    }

    $label = isset($button['label']) && is_string($button['label']) ? trim($button['label']) : '';
    if ($label === '') {
        throw new InvalidArgumentException("モーダルの操作ボタン {$number} 番目の文字（label）を指定してください。");
    }

    $variant = $button['variant'] ?? 'secondary';
    if (!is_string($variant) || !isset($variantOrder[$variant])) {
        throw new InvalidArgumentException(
            "モーダルの操作ボタン「{$label}」の variant は、primary・secondary・danger のどれかにしてください。",
        );
    }
    if ($variant === 'primary') {
        $primaryCount++;
    }

    $type = $button['type'] ?? 'submit';
    if ($type !== 'submit' && $type !== 'button') {
        throw new InvalidArgumentException(
            "モーダルの操作ボタン「{$label}」の type は、submit か button にしてください。",
        );
    }

    $form = isset($button['form']) && is_string($button['form']) ? $button['form'] : '';
    if ($type === 'submit' && !preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/', $form)) {
        throw new InvalidArgumentException(
            "モーダルの操作ボタン「{$label}」には、送るフォームの id（form）を指定してください。"
            . 'id は英字で始まり、英数字・-・_ だけの名前にします。',
        );
    }

    $formaction = $button['formaction'] ?? null;
    $processingLabel = isset($button['processingLabel']) && is_string($button['processingLabel'])
        && trim($button['processingLabel']) !== '' ? $button['processingLabel'] : '処理中…';

    $normalized[] = [
        'order' => $variantOrder[$variant],
        'index' => $index,
        'label' => $label,
        'variant' => $variant,
        'type' => $type,
        'form' => $form,
        'name' => isset($button['name']) && is_string($button['name']) ? $button['name'] : '',
        'value' => isset($button['value']) && is_scalar($button['value']) ? (string)$button['value'] : '',
        'formaction' => is_string($formaction) || is_array($formaction) ? $this->Url->build($formaction) : '',
        'processingLabel' => $processingLabel,
    ];
}

if ($primaryCount > 1) {
    throw new InvalidArgumentException('モーダルの主操作（variant が primary のボタン）は 1 つまでにしてください。');
}

// 種類の順に並べる。同じ種類の中では、書いた順のまま。
usort($normalized, fn(array $a, array $b): int => [$a['order'], $a['index']] <=> [$b['order'], $b['index']]);
?>
<!--
    モーダルの操作ボタン（MODAL-002）。
    閉じる・キャンセルは <form method="dialog"> の送信なので、JavaScript がなくても <dialog> が閉じる。
    このとき <dialog> の returnValue は "cancel" になる。

    E チームにお願いすること：
      ・見た目：modal__button--primary（主）・--secondary（副）・--danger（危険）の優先順位が分かる色と形、
        押しやすい大きさ（タップ領域）。danger は赤字など、ふつうの操作とはっきり区別する
      ・並び：広い画面では右寄せの横並び（HTML の順のまま、主操作が右はし）。
        狭い画面では、指で押しやすいように縦並びにし、主操作を一番上にするなど並べ替える（MODAL-002「配置方針」）
      ・処理中（二重送信の防止）：data-modal-submit のボタンでフォームが送られたら、
          1. 同じモーダルの data-modal-submit・data-modal-cancel のボタンをすべて押せなくする
          2. 押したボタンの文字を data-processing-label の文字に変え、aria-busy="true" を <dialog> に付ける
          3. 処理中は Escape キーや背景を押しても閉じないようにする（C チーム決定事項 4「処理中」）
        注意：送る前にボタンへ disabled を付けると、そのボタンの name・value が送られなくなる。
        送信が始まったあと（submit イベントの中で、少し後に）付けるか、aria-disabled で押せなくする。
        入力エラーで同じ画面に戻ったときは、ページが新しく表示されるので、元の状態にもどる。
-->
<form method="dialog" class="modal__cancel-form">
    <button type="submit" class="modal__button modal__button--secondary" value="cancel" data-modal-cancel>
        <?= h($cancelLabel) ?>
    </button>
</form>
<?php foreach ($normalized as $button) : ?>
    <button
        type="<?= h($button['type']) ?>"
        class="modal__button modal__button--<?= h($button['variant']) ?>"
        <?php if ($button['form'] !== '') : ?>
            form="<?= h($button['form']) ?>"
        <?php endif; ?>
        <?php if ($button['name'] !== '') : ?>
            name="<?= h($button['name']) ?>"
            value="<?= h($button['value']) ?>"
        <?php endif; ?>
        <?php if ($button['formaction'] !== '') : ?>
            formaction="<?= h($button['formaction']) ?>"
        <?php endif; ?>
        <?php if ($button['type'] === 'submit') : ?>
            data-modal-submit
            data-processing-label="<?= h($button['processingLabel']) ?>"
        <?php endif; ?>
    >
        <?= h($button['label']) ?>
    </button>
<?php endforeach; ?>
