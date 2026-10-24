<?php
/*
 * 【このファイルは？】
 * 注意メッセージの見た目。
 *
 * 【担当】C・E チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var array $params
 * @var string $message
 */
if (!isset($params['escape']) || $params['escape'] !== false) {
    $message = h($message);
}
?>
<div class="message warning" onclick="this.classList.add('hidden');"><?= $message ?></div>
