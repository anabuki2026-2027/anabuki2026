<?php
/*
 * 【このファイルは？】
 * 成功したときのメッセージ（例：「保存しました」）の見た目。
 * Controller の $this->Flash->success() で使われ、移動先の画面の共通通知領域に、成功の通知として出す（NOTICE-001）。
 *
 * 【担当】C・E チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */

// ここでは何も出力せず、toastStatus ブロックに足す。
// templates/layout/default.php が、このブロックを共通通知領域（templates/element/toast_region.php）の中に出す。
// 文言は h() で囲んで出す。D チームが Flash に 'escape' => false を付けたときだけ、そのまま出す（今までと同じ）。
$this->append('toastStatus', $this->element('toast', [
    'type' => 'success',
    'message' => $message,
    'escape' => $params['escape'] ?? true,
]));
