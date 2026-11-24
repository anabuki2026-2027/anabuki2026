<?php
/*
 * 【このファイルは？】
 * 失敗したときのメッセージ（例：「保存できませんでした」）の見た目。
 * Controller の $this->Flash->error() で使われ、画面の共通通知領域に、エラーの通知として出す（NOTICE-002）。
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
// 入力エラーのときは、params の target に最初のエラーの入力欄の id を入れると、その項目へ移動するリンクが付く。
//   例：$this->Flash->error('入力内容を確認してください。', ['params' => ['target' => 'email']]);
$this->append('toastAlert', $this->element('toast', [
    'type' => 'error',
    'message' => $message,
    'escape' => $params['escape'] ?? true,
    'target' => $params['target'] ?? null,
]));
