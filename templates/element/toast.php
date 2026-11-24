<?php
/*
 * 【このファイルは？】
 * 画面のはしに出る通知（トースト）1 件。「保存しました」のような、処理が終わったことを知らせる。
 * 設計書 COM-002 の NOTICE-001（成功通知）にあたる。共通通知領域（templates/element/toast_region.php）の中に置く。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string $type 通知の種類。今は success（成功）だけ。警告・エラーは #466 で足す
 * @var string|null $message 表示する文言。完了した操作が分かる、短い文にする（例：「お気に入りに追加しました。」）
 * @var bool|null $escape false のときだけ、message を h() で囲まずにそのまま出す。省略すると true（囲む）。
 *   CakePHP の Flash の escape と同じ。利用者が入力した文字が入る文言では false にしない
 * @var bool|null $template true のとき、文言が空の「ひな形」を作る。templates/element/toast_region.php だけが使う。
 *   E チームの JavaScript がこのひな形を複製し、data-toast-message に文言を入れて表示する
 */

/*
 * 使い方：
 * 画面が移動するとき（保存して一覧へ戻るなど）は、この部品を直接使わない。
 * D チームが Controller で $this->Flash->success('保存しました。') と書くと、
 * templates/element/flash/success.php がこの部品を使って、移動先の画面の共通通知領域に出す（NOTICE-004）。
 *
 * 画面を移動しない処理（画面内通信）の成功は、E チームの JavaScript が、
 * toast_region.php の <template data-toast-template="success"> を複製して出す。
 *
 * 【種類ごとの決まり（C チーム決定事項 5）】
 * | 種類    | 自動で閉じる | 手動で閉じる | 読み上げ                          |
 * | success | 6 秒後       | できる       | role="status"（操作を中断しない） |
 * 読み上げの role は、通知 1 件ではなく、通知を入れる場所（toast_region.php）に付けている。
 * 通知が足されたときだけ読み上げられるように、ページを開いたときからある場所に付ける必要があるため。
 */

// 種類ごとの設定。label は、色だけに頼らずに種類が分かるように、文言の前に出す文字（NOTICE-002「識別」）。
// timeout は自動で閉じるまでの時間（ミリ秒）。E チームの JavaScript が data-toast-timeout から読む。
$toastTypes = [
    'success' => ['label' => '完了', 'timeout' => 6000],
];

$type = $type ?? '';
if (!is_string($type) || !isset($toastTypes[$type])) {
    throw new InvalidArgumentException('通知の種類（type）は、success にしてください。');
}
$isTemplate = isset($template) && $template === true;
$message = isset($message) && is_string($message) ? $message : '';
if (!$isTemplate && trim($message) === '') {
    throw new InvalidArgumentException('通知の文言（message）を指定してください。');
}
if (!isset($escape) || $escape !== false) {
    $message = h($message);
}
$setting = $toastTypes[$type];

/*
 * E チームにお願いすること（見た目）：
 *   ・色・位置・動き・画面幅ごとの表示（NOTICE-001 の E の担当）。主要な操作（送信ボタン、スマートフォンの下部ナビなど）と
 *     重ならない位置にする（NOTICE-001「配置場所」「完成条件」）。動きは prefers-reduced-motion のときに止める
 *   ・toast__type（「完了」）は、アイコンを添えてもよいが、文字は画面に残すか visually-hidden にして消さない
 *     （色だけで種類を見分けさせないため）
 *
 * E チームにお願いすること（動き。C チーム決定事項 5）：
 *   ・data-toast-timeout のミリ秒がたったら、自動で閉じる（消す）
 *   ・マウスを乗せている間と、通知の中（閉じるボタン）にフォーカスがある間は、時間を止める
 *   ・閉じるボタン（data-toast-close）は、JavaScript が動かないと押しても何も起きないので hidden にしてある。
 *     JavaScript で hidden を外し、押したら閉じる。
 *     閉じるボタンにフォーカスがあったときは、次の通知の閉じるボタン（なければ #main-content）へフォーカスを移す
 *     （フォーカスが行き先を失わないようにするため）
 */
?>
<div
    class="toast toast--<?= h($type) ?>"
    data-toast
    data-toast-type="<?= h($type) ?>"
    data-toast-timeout="<?= h((string)$setting['timeout']) ?>"
>
    <p class="toast__body">
        <span class="toast__type"><?= h($setting['label']) ?></span>
        <span class="toast__message" data-toast-message><?= $message ?></span>
    </p>
    <button type="button" class="toast__close" aria-label="この通知を閉じる" data-toast-close hidden>
        <span aria-hidden="true">×</span>
    </button>
</div>
