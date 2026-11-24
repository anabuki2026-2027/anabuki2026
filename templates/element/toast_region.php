<?php
/*
 * 【このファイルは？】
 * 画面の共通通知領域。「保存しました」などの通知（templates/element/toast.php）を入れて、読み上げソフトにも伝える。
 * templates/layout/default.php から読み込む。設計書 COM-002 の NOTICE-001（成功通知）にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 */

/*
 * 中に入るもの：
 * ・toastStatus ブロック：画面が移動したあとの成功の通知。D チームが Controller で
 *   $this->Flash->success('…') と書くと、templates/element/flash/success.php がこのブロックに足す。
 *   JavaScript が動かなくても、ここにそのまま表示される。
 * ・<template data-toast-template="success">：画面内通信の成功の通知のひな形。E チームの JavaScript が複製して使う。
 *   <template> の中身は、複製されるまで画面に出ず、読み上げもされない。
 *
 * 【読み上げ（NOTICE-001「読み上げ」）】
 * data-toast-list に role="status" を付けて、通知が足されたら、操作を中断せずに読み上げさせる（C チーム決定事項 5）。
 * aria-atomic="false" は、新しく足された通知だけを読ませるため（role="status" は、省略すると全体を読み直す）。
 * 警告・エラー（role="alert"）の場所は #466 で足す。
 *
 * E チームにお願いすること（動き。C チーム決定事項 5、NOTICE-001「連続通知」）：
 *   ・画面内通信で成功したら、<template data-toast-template="success"> を複製し、data-toast-message に
 *     文言を textContent で入れて（innerHTML は使わない。利用者が入力した文字が HTML として動かないように）、
 *     data-toast-list="status" の最後に足す
 *   ・ページを開いたときから data-toast-list の中にある通知（Flash の通知）は、読み上げソフトに伝わらない
 *     （読み上げは「あとから足された」ときにだけ起きる）。ページを開いたら、いったん取り出して、
 *     下の決まりのとおりに 1 件ずつ足し直す
 *   ・同時に表示するのは 3 件まで。4 件目からは待たせて、前の通知が閉じたら、古い順に表示する
 *   ・表示中か待っている通知と、種類も文言も同じ通知は、足さずに 1 件にまとめる（連打で並ぶのを防ぐ）
 *   ・モーダル（<dialog>）を開いている間は、通知が背景の後ろに隠れて読み上げもされないので、
 *     モーダルを閉じてから通知を足す（MODAL-004「成功時」：モーダルを閉じて受付完了を通知する）
 *   ・自動で閉じる時間、閉じるボタンなど、通知 1 件の動きは templates/element/toast.php を見る
 */
?>
<div class="toast-region" data-toast-region>
    <div class="toast-region__list" role="status" aria-atomic="false" data-toast-list="status">
        <?= $this->fetch('toastStatus') ?>
    </div>
    <template data-toast-template="success">
        <?= $this->element('toast', ['type' => 'success', 'template' => true]) ?>
    </template>
</div>
