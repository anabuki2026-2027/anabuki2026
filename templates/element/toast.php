<?php
/*
 * 【このファイルは？】
 * 画面のはしに出る通知（トースト）1 件。「保存しました」のような成功と、警告・エラーを知らせる。
 * 設計書 COM-002 の NOTICE-001（成功通知）と NOTICE-002（エラー・警告通知）にあたる。
 * 共通通知領域（templates/element/toast_region.php）の中に置く。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string $type 通知の種類。success（成功）、warning（警告）、error（エラー）のどれか
 * @var string|null $message 表示する文言。
 *   - success：完了した操作が分かる、短い文にする（例：「お気に入りに追加しました。」）
 *   - warning・error：利用者が次に取れる対応を含めた、一般の人向けの文にする（例：「入力内容を確認してください。」）。
 *     例外の文（$e->getMessage()）、SQL、ファイルのパスなど、技術者向けの内容は入れない（NOTICE-002「内部情報」）
 * @var bool|null $escape false のときだけ、message を h() で囲まずにそのまま出す。省略すると true（囲む）。
 *   CakePHP の Flash の escape と同じ。利用者が入力した文字が入る文言では false にしない
 * @var string|null $target 入力エラーのとき、移動先にする入力欄の id（例：email）。warning・error だけで使える。
 *   指定すると、通知に「該当する項目へ移動」のリンクが付く（NOTICE-002「完成条件」：入力エラー箇所へ移動できる）
 * @var bool|null $template true のとき、文言が空の「ひな形」を作る。templates/element/toast_region.php だけが使う。
 *   E チームの JavaScript がこのひな形を複製し、data-toast-message に文言を入れて表示する
 */

/*
 * 使い方：
 * 画面が移動するとき（保存して一覧へ戻るなど）や、入力エラーで同じ画面を表示し直すときは、この部品を直接使わない。
 * D チームが Controller で次のように書くと、templates/element/flash/ の部品が、この部品を使って共通通知領域に出す。
 *
 *    $this->Flash->success('保存しました。');
 *    $this->Flash->warning('…');
 *    $this->Flash->error('入力内容を確認してください。', ['params' => ['target' => 'email']]);
 *
 * target は、最初にエラーになった入力欄の id。FormHelper が作る id は、項目名の「.」と「_」を「-」にしたもの
 * （例：email → email、user.email → user-email）。入力欄の近くのエラーの文は、FormHelper が今までどおり表示する
 * （NOTICE-002「項目別エラー」：入力不正はトーストだけにせず、対象項目の近くにも表示する）。
 *
 * 画面を移動しない処理（画面内通信）の通知は、E チームの JavaScript が、
 * toast_region.php の <template data-toast-template="…"> を複製して出す。
 *
 * 【種類ごとの決まり（C チーム決定事項 5）】
 * | 種類    | 自動で閉じる | 手動で閉じる | 読み上げ                          |
 * | success | 6 秒後       | できる       | role="status"（操作を中断しない） |
 * | warning | 閉じない     | できる       | role="alert"（すぐ読み上げる）    |
 * | error   | 閉じない     | できる       | role="alert"                      |
 * 読み上げの role は、通知 1 件ではなく、通知を入れる場所（toast_region.php）に付けている。
 * 通知が足されたときだけ読み上げられるように、ページを開いたときからある場所に付ける必要があるため。
 */

// 種類ごとの設定。
// label は、色だけに頼らずに種類が分かるように、文言の前に出す文字（NOTICE-002 の E の担当「色だけに頼らず識別」）。
// timeout は自動で閉じるまでの時間（ミリ秒）。E チームの JavaScript が data-toast-timeout から読む。
// null は自動で閉じない（内容を確認する時間を確保する。NOTICE-002「終了」）。
$toastTypes = [
    'success' => ['label' => '完了', 'timeout' => 6000],
    'warning' => ['label' => '注意', 'timeout' => null],
    'error' => ['label' => 'エラー', 'timeout' => null],
];

$type = $type ?? '';
if (!is_string($type) || !isset($toastTypes[$type])) {
    throw new InvalidArgumentException('通知の種類（type）は、success・warning・error のどれかにしてください。');
}
$isTemplate = isset($template) && $template === true;
$message = isset($message) && is_string($message) ? $message : '';
if (!$isTemplate && trim($message) === '') {
    throw new InvalidArgumentException('通知の文言（message）を指定してください。');
}
if (!isset($escape) || $escape !== false) {
    $message = h($message);
}
$target = isset($target) && is_string($target) ? $target : '';
if ($target !== '') {
    if ($type === 'success') {
        throw new InvalidArgumentException('移動先の入力欄（target）は、warning・error の通知だけで指定できます。');
    }
    if (!preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/', $target)) {
        throw new InvalidArgumentException('移動先の入力欄（target）は、英字で始まり、英数字・-・_ だけの id にしてください。');
    }
}
$setting = $toastTypes[$type];

/*
 * E チームにお願いすること（見た目）：
 *   ・種類ごとの色・位置・動き・画面幅ごとの表示（NOTICE-001・002 の E の担当）。主要な操作（送信ボタン、
 *     スマートフォンの下部ナビなど）と重ならない位置にする。動きは prefers-reduced-motion のときに止める
 *   ・toast__type（「完了」「注意」「エラー」）は、アイコンを添えてもよいが、文字は画面に残すか visually-hidden にして
 *     消さない（色だけで種類を見分けさせないため。NOTICE-002 の E の担当）
 *
 * E チームにお願いすること（動き。C チーム決定事項 5）：
 *   ・data-toast-timeout があれば、そのミリ秒がたったら自動で閉じる（消す）。data-toast-timeout がない通知
 *     （warning・error）は、自動で閉じない
 *   ・マウスを乗せている間と、通知の中（閉じるボタン、移動のリンク）にフォーカスがある間は、時間を止める
 *   ・閉じるボタン（data-toast-close）は、JavaScript が動かないと押しても何も起きないので hidden にしてある。
 *     JavaScript で hidden を外し、押したら閉じる。
 *     閉じるボタンにフォーカスがあったときは、次の通知の閉じるボタン（なければ #main-content）へフォーカスを移す
 *     （フォーカスが行き先を失わないようにするため）
 *   ・移動のリンク（data-toast-target）は、JavaScript がなくても、ページ内リンクで入力欄の場所へ移動する。
 *     JavaScript では、押したら href の入力欄へ focus() する（ブラウザーによっては、移動だけでフォーカスが移らないため）。
 *     通知は閉じない（エラーの内容を見ながら直せるようにする）
 *   ・画面内通信で入力エラーを受け取ったときは、ひな形の data-toast-target の hidden を外し、href に
 *     「#（最初の aria-invalid="true" の入力欄の id）」を入れる
 */
?>
<div
    class="toast toast--<?= h($type) ?>"
    data-toast
    data-toast-type="<?= h($type) ?>"
    <?= $setting['timeout'] !== null ? 'data-toast-timeout="' . h((string)$setting['timeout']) . '"' : '' ?>
>
    <p class="toast__body">
        <span class="toast__type"><?= h($setting['label']) ?></span>
        <span class="toast__message" data-toast-message><?= $message ?></span>
    </p>
    <?php if ($type !== 'success' && ($target !== '' || $isTemplate)) : ?>
        <?php // ひな形では、入力エラーのときだけ JavaScript が hidden を外して使う ?>
        <a
            class="toast__link"
            href="<?= $target !== '' ? '#' . h($target) : '#' ?>"
            data-toast-target
            <?= $target === '' ? 'hidden' : '' ?>
        >該当する項目へ移動</a>
    <?php endif; ?>
    <button type="button" class="toast__close" aria-label="この通知を閉じる" data-toast-close hidden>
        <span aria-hidden="true">×</span>
    </button>
</div>
