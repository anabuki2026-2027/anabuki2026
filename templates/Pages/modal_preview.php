<?php
/*
 * 【このファイルは？】
 * モーダルの表示を一時的に確認するページ。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 *
 * 【使い方】
 * 1. http://localhost:8765/pages/modal_preview を開く。
 * 2. F12で開発者ツールのConsoleを開き「document.getElementById('modal-preview').showModal()」を実行（モーダル表示）。
 *
 */

$this->append('modals', $this->element('modal', [
    'id' => 'modal-preview',
    'title' => 'モーダルの確認',
    'description' => '共通モーダルの表示確認用です。',
    'body' => '<p>ここにモーダルの本文が表示されます。</p>',
    'actions' => $this->element('modal_actions'),
    'kind' => 'view',
]));
?>
<h1>モーダル確認ページ</h1>
<p>ブラウザーの開発者ツールからモーダルを開きます。</p>
