<?php
/*
 * 【このファイルは？】
 * 画面の共通通知領域。成功・警告・エラーの通知（templates/element/toast.php）を入れて、読み上げソフトにも伝える。
 * 処理中の状態と通知の共通の決まりも、ここに書いている。templates/layout/default.php から読み込む。
 * 設計書 COM-002 の NOTICE-001（成功通知）・NOTICE-002（エラー・警告通知）・NOTICE-003（処理状態と通知の共通連携）にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 */

/*
 * 中に入るもの：
 * ・toastAlert ブロック：警告・エラーの通知。D チームが Controller で $this->Flash->warning('…')・
 *   $this->Flash->error('…') と書くと、templates/element/flash/warning.php・error.php がこのブロックに足す。
 * ・toastStatus ブロック：成功の通知。$this->Flash->success('…') で、templates/element/flash/success.php が足す。
 *   どちらも、JavaScript が動かなくても、ここにそのまま表示される。
 * ・<template data-toast-template="…">：画面内通信の通知のひな形（success・warning・error）。
 *   E チームの JavaScript が複製して使う。<template> の中身は、複製されるまで画面に出ず、読み上げもされない。
 *
 * 【読み上げ（NOTICE-001・002「読み上げ」、C チーム決定事項 5）】
 * ・警告・エラーは data-toast-list="alert"（role="alert"）に入れて、すぐに読み上げさせる（重要度が高いため）。
 * ・成功は data-toast-list="status"（role="status"）に入れて、操作を中断せずに読み上げさせる。
 * ・aria-atomic="false" は、新しく足された通知だけを読ませるため（role="alert"・role="status" は、省略すると全体を読み直す）。
 * ・警告・エラーを先に置いているのは、Tab キーで移動したときに、重要な通知の閉じるボタンやリンクに先に届くようにするため。
 *
 * E チームにお願いすること（動き。C チーム決定事項 5、NOTICE-001「連続通知」）：
 *   ・画面内通信の結果が出たら、種類に合う <template data-toast-template="…"> を複製し、data-toast-message に
 *     文言を textContent で入れて（innerHTML は使わない。利用者が入力した文字が HTML として動かないように）、
 *     success は data-toast-list="status"、warning・error は data-toast-list="alert" の最後に足す
 *   ・ページを開いたときから data-toast-list の中にある通知（Flash の通知）は、読み上げソフトに伝わらないことがある
 *     （読み上げは「あとから足された」ときにだけ起きる）。ページを開いたら、いったん取り出して、
 *     下の決まりのとおりに 1 件ずつ足し直す
 *   ・同時に表示するのは、2 つの場所を合わせて 3 件まで。4 件目からは待たせて、前の通知が閉じたら、古い順に表示する
 *   ・表示中か待っている通知と、種類も文言も同じ通知は、足さずに 1 件にまとめる（連打で並ぶのを防ぐ）
 *   ・モーダル（<dialog>）を開いている間は、通知が背景の後ろに隠れて読み上げもされないので、
 *     モーダルを閉じてから通知を足す（MODAL-004「成功時」：モーダルを閉じて受付完了を通知する）。
 *     モーダルの中で起きたエラーは、通知ではなく、モーダルの中（data-modal-status と項目ごとのエラー）に出す
 *     （MODAL-006「失敗時」：閉じずに入力を残す。templates/element/modal.php を見る）
 *   ・自動で閉じる時間、閉じるボタン、移動のリンクなど、通知 1 件の動きは templates/element/toast.php を見る
 *
 * 【処理状態と通知の共通の決まり（NOTICE-003）】
 * 共通の文言は src/View/NoticeMessages.php で管理し、下の <template data-notice-messages> に出している。
 * どの結果でどの文言を出すかの表も、NoticeMessages.php にある。
 * 地図・現在地・QR コード・画像の読み込みなど、外部の機能の結果の通知は templates/element/external_feature.php を見る
 * （NOTICE-005。文言は各画面で書く）。
 *
 * C が HTML に付ける目印：
 * | 目印                              | 付ける場所       | 意味                                                         |
 * | data-processing-label="保存中…"   | 送信ボタン       | 処理中に、ボタンの文字をこれに変える。ないときは「処理中…」  |
 * | data-async-form                   | <form>           | 画面内通信で送るフォーム。JavaScript がなくても普通に送れる  |
 * | data-error-prefix="report-12"     | data-async-form  | 項目ごとのエラーの場所の id の先頭（FormHelper の idPrefix） |
 * | data-form-message                 | フォームの中     | モーダルの中のフォームで、全体へのメッセージを入れる場所     |
 * 項目ごとのエラーの場所の id は「（data-error-prefix）-（項目名）-error」。data-error-prefix がないときは
 * 「（項目名）-error」。項目名の「.」と「_」は「-」にする（FormHelper の id と同じ。例：user_name → user-name-error）。
 *
 * E チームにお願いすること（すべてのフォーム。method="dialog" を除く）：
 *   ・処理開始：送信されたら <form> に aria-busy="true" を付け、押したボタン（submit イベントの submitter）の文字を
 *     data-processing-label の文字に変え、同じフォームの送信ボタンを aria-disabled="true" にする。
 *     aria-busy="true" の間にもう一度送られたら、preventDefault() で止める（重複送信の防止）。
 *     送る前に disabled を付けると、押したボタンの name・value が送られなくなるので使わない（modal_actions.php と同じ）
 *   ・処理終了：ブラウザーの「戻る」でページが復元されたとき（pageshow イベントの persisted が true）は、
 *     上の状態を元にもどす（成否にかかわらず、再操作できる状態にもどす。NOTICE-003「処理終了」）
 *   ・モーダルの中のフォームは、これに加えて templates/element/modal_actions.php の「処理中」の決まりにも従う
 *
 * E チームにお願いすること（data-async-form のフォーム）：
 *   ・submit を止め（preventDefault）、フォームの内容を fetch で action の URL へ送る。ヘッダーに
 *     Accept: application/json と、X-CSRF-Token（フォームの中の _csrfToken の値）を付ける。
 *     30 秒たっても応答がないときは、時間切れ（応答がない）として扱う
 *   ・応答を受け取ったら（成否にかかわらず）、処理中の状態を元にもどす（NOTICE-003「処理終了」）。
 *     ただし、送る処理で結果が分からないとき（UNKNOWN_RESULT）も、自動で送り直さない（NOTICE-003「通信失敗時」）
 *   ・成功：前のエラーの表示を消し、data を使って各画面の表示を更新してから（更新の内容は各画面の部品に書く）、
 *     成功の通知を出す。モーダルの中のフォームなら、先にモーダルを閉じる（MODAL-006「成功時」）
 *   ・入力に問題がある：入力はそのまま残す。前のエラーを消してから、errors の各項目の文を、上の id の場所に
 *     textContent で入れ（2 つ以上なら改行で区切る）、その入力欄に aria-invalid="true" を付け、
 *     最初のエラーの入力欄へフォーカスを移す（NOTICE-003「入力不正時」）
 *   ・通知を出す場所：モーダルの中のフォームなら data-form-message（role="alert"）に文言を入れる
 *     （モーダルの前に通知は見えないため）。それ以外は、エラーの通知を出す。入力に問題があるときは、
 *     通知の data-toast-target を使って、最初のエラーの入力欄へのリンクを付ける
 *   ・ログインが切れた（401）：エラーの通知に data-toast-action で「ログインする」のリンクを付ける。
 *     リンク先は data-login-url の値（NOTICE-003「認証切れ」）。モーダルの中なら、data-form-message にも文言を入れる
 */
?>
<div
    class="toast-region"
    data-toast-region
    <?php // ログイン画面の URL。templates/element/header.php の $loginUrl と同じ。D チームと決まったら両方を書きかえる ?>
    data-login-url="<?= h($this->Url->build('/login')) ?>"
>
    <div class="toast-region__list" role="alert" aria-atomic="false" data-toast-list="alert">
        <?= $this->fetch('toastAlert') ?>
    </div>
    <div class="toast-region__list" role="status" aria-atomic="false" data-toast-list="status">
        <?= $this->fetch('toastStatus') ?>
    </div>
    <?php foreach (['success', 'warning', 'error'] as $toastType) : ?>
        <template data-toast-template="<?= h($toastType) ?>">
            <?= $this->element('toast', ['type' => $toastType, 'template' => true]) ?>
        </template>
    <?php endforeach; ?>
    <?php // 共通の文言（src/View/NoticeMessages.php）。E チームの JavaScript が data-notice-message の名前で探して使う ?>
    <template data-notice-messages>
        <?php foreach (\App\View\NoticeMessages::all() as $noticeKey => $noticeMessage) : ?>
            <p data-notice-message="<?= h($noticeKey) ?>"><?= h($noticeMessage) ?></p>
        <?php endforeach; ?>
    </template>
</div>
