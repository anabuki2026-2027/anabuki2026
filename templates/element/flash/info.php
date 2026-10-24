<?php
/*
 * 【このファイルは？】
 * お知らせメッセージの見た目。
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
<div class="message" onclick="this.classList.add('hidden');"><?= $message ?></div>
