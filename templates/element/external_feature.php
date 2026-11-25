<?php
/*
 * 【このファイルは？】
 * 地図、現在地、QR コード、画像の読み込みなど、ブラウザーや外部の機能を使う場所を囲む部品。
 * 読み込み中の表示、失敗したときの代わりの表示、結果の通知を、どの画面でも同じ形で扱う。
 * 設計書 COM-002 の NOTICE-005（画面内検証・外部処理の通知）にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string $id この場所の id。ページの中で重ならない名前にする（英字で始め、英数字・-・_ だけ）
 * @var string $feature 機能の名前。E チームの JavaScript が、どの処理を動かすかを見分けるのに使う。
 *   今決めているもの：map（地図）、location（現在地）、qr（QR コード）、image（画像の読み込み・プレビュー）。
 *   ほかの機能を足すときは E チームと決める
 * @var string $label この場所の名前（例：「施設の地図」）。読み上げソフトに伝える。h() で囲んで表示する
 * @var string|null $content 機能を使う部分の HTML（地図を入れる箱、「現在地を使う」ボタンなど）。
 *   JavaScript がないと使えないので、最初は隠しておき、E チームの JavaScript が使えると確かめてから見せる。
 *   そのまま表示するので、値は呼び出す側で h() で囲んでおく
 * @var string $fallback 代わりの表示の HTML（例：住所と外部の地図へのリンク、「リストで見る」、「地域から探す」）。
 *   JavaScript が動かないときと、失敗したときに見せる（NOTICE-005「処理継続」）。必須。
 *   そのまま表示するので、値は呼び出す側で h() で囲んでおく
 * @var bool|null $keepFallback true のとき、成功しても代わりの表示を隠さない（例：現在地が使えても「地域から探す」は残す）。
 *   省略すると false（成功したら隠す。例：地図が出たら、代わりの住所の表示は隠す）
 * @var string|null $loadingMessage 処理中に、この場所に出す文字（例：「地図を読み込んでいます…」）
 * @var array<string, string> $messages 結果ごとの通知の文言。キーは下の「結果の名前」。failed は必須。
 *   文言は各画面で書く（C チーム決定事項 6「画像・位置情報・地図・QR の失敗と代わりの操作」は各画面で管理）。
 *   技術的な原因だけでなく、利用者が次に取れる操作を書く（NOTICE-005「文言」）
 * @var string|null $field 画面内検証（送る前の確認）の結果を出す入力欄の id（例：画像のファイルを選ぶ欄）。
 *   指定すると、失敗の文言を、その入力欄のエラーの場所（「（入力欄の id）-error」）にも出す（NOTICE-002「項目別エラー」）
 */

/*
 * 【結果の名前】
 * | 名前        | いつ                                                        | 通知の種類 |
 * | success     | うまくいった（文言がなければ、通知を出さない）              | success    |
 * | denied      | 利用者が許可しなかった（位置情報、カメラなど）              | warning    |
 * | unsupported | ブラウザーや端末が対応していない                            | warning    |
 * | timeout     | 時間内に終わらなかった                                      | warning    |
 * | failed      | そのほかの失敗（読み込めない、通信できない）。必須          | warning    |
 * 機能に特有の結果は、名前を足して使う（例：位置情報の inaccurate〔精度が足りない〕、画像の too-large〔大きすぎる〕）。
 * 文言がない結果になったときは、failed の文言を使う。
 *
 * 使い方（例：地図検索の「現在地を使う」。SCR-003 MAP-008）：
 *
 *    <?php $this->start('locationContent'); ?>
 *        <button type="button" data-external-start>現在地を使う</button>
 *    <?php $this->end(); ?>
 *    <?php $this->start('locationFallback'); ?>
 *        <p>地域を選んで探すこともできます。</p>
 *        （地域の選択）
 *    <?php $this->end(); ?>
 *    <?= $this->element('external_feature', [
 *        'id' => 'current-location',
 *        'feature' => 'location',
 *        'label' => '現在地から探す',
 *        'content' => $this->fetch('locationContent'),
 *        'fallback' => $this->fetch('locationFallback'),
 *        'keepFallback' => true,
 *        'loadingMessage' => '現在地を確認しています…',
 *        'messages' => [
 *            'success' => '現在地の周辺を表示しています。',
 *            'denied' => '位置情報の利用が許可されていません。ブラウザーの設定で許可するか、地域を選んで探してください。',
 *            'unsupported' => 'この端末では現在地を使えません。地域を選んで探してください。',
 *            'timeout' => '現在地を確認できませんでした。もう一度お試しいただくか、地域を選んで探してください。',
 *            'failed' => '現在地を確認できませんでした。地域を選んで探してください。',
 *        ],
 *    ]) ?>
 *
 * 同じ名前のビューブロックを何度も使うときは、取り出したあとに $this->reset('名前') で空にする。
 * 位置情報など、利用者の操作のあとでしか始めない機能は、content の中のボタンに data-external-start を付ける
 * （SCR-003「利用者の明示操作後だけ取得」）。付けないときは、ページを開いたときに始める（地図、QR コードなど）。
 */

$id = isset($id) && is_string($id) ? $id : '';
if (!preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/', $id)) {
    throw new InvalidArgumentException('外部の機能の場所の id は、英字で始まり、英数字・-・_ だけの名前にしてください。');
}
$feature = isset($feature) && is_string($feature) ? $feature : '';
if (!preg_match('/\A[a-z][a-z0-9-]*\z/', $feature)) {
    throw new InvalidArgumentException('機能の名前（feature）は、英小文字で始まり、英小文字・数字・- だけにしてください。');
}
$label = isset($label) && is_string($label) ? trim($label) : '';
if ($label === '') {
    throw new InvalidArgumentException('外部の機能の場所の名前（label）を指定してください。');
}
$fallback = isset($fallback) && is_string($fallback) ? $fallback : '';
if (trim($fallback) === '') {
    throw new InvalidArgumentException(
        '代わりの表示（fallback）を指定してください。失敗したときも、ほかの方法で使えるようにするためです。',
    );
}
$content = isset($content) && is_string($content) ? $content : '';
$keepFallbackValue = isset($keepFallback) && $keepFallback === true;
$loadingMessage = isset($loadingMessage) && is_string($loadingMessage) ? $loadingMessage : '';

$messageList = isset($messages) && is_array($messages) ? $messages : [];
foreach ($messageList as $result => $text) {
    if (!is_string($result) || !preg_match('/\A[a-z][a-z0-9-]*\z/', $result)) {
        throw new InvalidArgumentException('結果の名前（messages のキー）は、英小文字・数字・- だけにしてください。');
    }
    if (!is_string($text) || trim($text) === '') {
        throw new InvalidArgumentException("結果「{$result}」の文言を指定してください。");
    }
}
if (!isset($messageList['failed'])) {
    throw new InvalidArgumentException('失敗したときの文言（messages の failed）を指定してください。');
}

$fieldId = isset($field) && is_string($field) ? $field : '';
if ($fieldId !== '' && !preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/', $fieldId)) {
    throw new InvalidArgumentException('入力欄の id（field）は、英字で始まり、英数字・-・_ だけにしてください。');
}

/*
 * 状態（data-external-state）。サーバーは fallback で出し、E チームの JavaScript が書きかえる。
 * | 状態     | content | fallback                          | 意味                                   |
 * | fallback | 隠す    | 見せる                            | JavaScript がない、またはまだ始めていない |
 * | idle     | 見せる  | 見せる                            | 使える。利用者の操作を待っている       |
 * | loading  | 見せる  | 見せる                            | 処理中                                 |
 * | ready    | 見せる  | keepFallback なら見せる。なければ隠す | 成功                               |
 * | failed   | 見せる（unsupported のときだけ隠す） | 見せる | 失敗。もう一度試せる               |
 *
 * E チームにお願いすること（動き）：
 *   ・ページを開いたら、data-external-feature の機能が使えるかを確かめる（例：navigator.geolocation があるか）。
 *     使えなければ、結果 unsupported として扱う。使えれば content の hidden を外し、
 *     data-external-start があれば状態を idle にして押されるのを待つ。なければ、すぐ始める
 *   ・始めたら、状態を loading にし、この場所に aria-busy="true" を付け、data-external-status に loadingMessage を入れる。
 *     data-external-start のボタンは aria-disabled="true" にして、続けて押されても始めない（二重の実行を防ぐ）
 *   ・終わったら（成否にかかわらず）、aria-busy と aria-disabled を外し、data-external-status を空にする
 *   ・結果に合う文言を <template data-external-messages> の data-external-message から取り（なければ failed）、
 *     success は成功、それ以外は警告の通知を出す（templates/element/toast_region.php のひな形を使う）。
 *     success の文言がないときは、通知を出さない（地図が出たなど、見れば分かる結果）
 *   ・data-external-field があり、結果が success 以外なら、文言を「（data-external-field の値）-error」の場所にも
 *     textContent で入れ、その入力欄に aria-invalid="true" を付け、通知に data-toast-target のリンクを付ける。
 *     success になったら、エラーの場所を空にし、aria-invalid を外す
 *   ・失敗しても、ページのほかの部分はそのまま使えるようにする（エラーで JavaScript 全体を止めない。NOTICE-005「処理継続」）
 *   ・取得した現在地などの値は、URL や保存先に入れない（SCR-003「現在地は URL へ保存しない」）
 *
 * E チームにお願いすること（見た目）：
 *   ・external-feature__status（処理中の文字）、external-feature__fallback（代わりの表示）の見た目
 *   ・読み込み中の表示と、失敗したときに代わりの表示へ切り替わったことが分かる見た目
 */
?>
<section
    class="external-feature external-feature--<?= h($feature) ?>"
    id="<?= h($id) ?>"
    aria-label="<?= h($label) ?>"
    data-external-feature="<?= h($feature) ?>"
    data-external-state="fallback"
    <?= $keepFallbackValue ? 'data-external-keep-fallback' : '' ?>
    <?= $fieldId !== '' ? 'data-external-field="' . h($fieldId) . '"' : '' ?>
>
    <?php if ($content !== '') : ?>
        <div class="external-feature__content" data-external-content hidden>
            <?= $content ?>
        </div>
    <?php endif; ?>

    <?php // 処理中の文字。読み上げソフトにも伝える。結果の文言は通知で出すので、ここには入れない ?>
    <p
        class="external-feature__status"
        role="status"
        data-external-status
        <?= $loadingMessage !== '' ? 'data-loading-message="' . h($loadingMessage) . '"' : '' ?>
    ></p>

    <div class="external-feature__fallback" data-external-fallback>
        <?= $fallback ?>
    </div>

    <?php // 結果ごとの文言。<template> の中身は画面に出ず、E チームの JavaScript が data-external-message で探して使う ?>
    <template data-external-messages>
        <?php foreach ($messageList as $result => $text) : ?>
            <p data-external-message="<?= h($result) ?>"><?= h($text) ?></p>
        <?php endforeach; ?>
    </template>
</section>
