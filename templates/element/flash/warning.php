<?php
/*
 * 【このファイルは？】
 * 注意メッセージの見た目。
 * Controller の $this->Flash->warning() で使われ、画面の共通通知領域に、警告の通知として出す（NOTICE-002）。
 *
 * 【担当】C・E チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */

// ここでは何も出力せず、toastAlert ブロックに足す。
// templates/layout/default.php が、このブロックを共通通知領域（templates/element/toast_region.php）の中に出す。
// 文言は h() で囲んで出す。D チームが Flash に 'escape' => false を付けたときだけ、そのまま出す（今までと同じ）。
// 移動先の入力欄があるときは、params の target にその id を入れる（使い方は templates/element/flash/error.php と同じ）。
$this->append('toastAlert', $this->element('toast', [
    'type' => 'warning',
    'message' => $message,
    'escape' => $params['escape'] ?? true,
    'target' => $params['target'] ?? null,
]));
