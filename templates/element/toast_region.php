<?php
/*
 * 【このファイルは？】
 * 画面の共通通知領域。成功・警告・エラーの通知（templates/element/toast.php）を入れて、読み上げソフトにも伝える。
 * templates/layout/default.php から読み込む。設計書 COM-002 の NOTICE-001（成功通知）・NOTICE-002（エラー・警告通知）にあたる。
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
 */
?>
<div class="toast-region" data-toast-region>
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
</div>
