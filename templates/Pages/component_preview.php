<?php
/*
 * 【このファイルは？】
 * 共通の部品（モーダル、通知、外部の機能の部品など）の表示を、一時的に確認するページ。
 * 確認したい共通の部品が増えたら、このページに足す。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 *
 * 【モーダルの確認】
 * 1. http://localhost:8765/pages/component_preview を開く。
 * 2. F12で開発者ツールのConsoleを開き「document.getElementById('modal-preview').showModal()」を実行（モーダル表示）。
 *
 * 【通知の確認（NOTICE-001〜005）】
 * ・ページを開くたびに、成功・警告・エラー・案内の Flash を出す（Controller の $this->Flash->success() などと同じ経路）。
 *   成功・警告・エラーはヘッダーの下の共通通知領域（templates/element/toast_region.php）に、案内は <main> の中に出る。
 * ・E チームの CSS と JavaScript ができるまでは、色や位置は付かず、閉じるボタンも隠れたまま（hidden）。
 *   自動で閉じる、3 件まで、といった動きも、E チームの JavaScript ができてから確認する。
 * ・エラーの通知の「該当する項目へ移動」を押すと、下の「メールアドレス」の入力欄へ移動する。
 * ・画面内通信の通知（ひな形の複製）は、E チームの JavaScript がする処理を、Console で次のように試せる。
 *
 *     const toast = document.querySelector('[data-toast-template="success"]').content.firstElementChild.cloneNode(true);
 *     toast.querySelector('[data-toast-message]').textContent = '画面内通信の成功の例です。';
 *     document.querySelector('[data-toast-list="status"]').append(toast);
 *
 *   警告・エラーは、"success" を "warning"・"error" に、"status" を "alert" にかえる。
 */

use App\View\NoticeMessages;

// 確認用の Flash。ふつうは Controller（D チーム）が書くが、ここでは確認のために画面のファイルから入れている。
// レイアウト（templates/layout/default.php）は、このファイルのあとに作られるので、ここで入れた Flash がそのまま出る。
$this->getRequest()->getSession()->write('Flash.flash', [
    [
        'message' => '保存しました。（成功の例）',
        'key' => 'flash',
        'element' => 'flash/success',
        'params' => [],
    ],
    [
        'message' => NoticeMessages::INVALID,
        'key' => 'flash',
        'element' => 'flash/error',
        'params' => ['target' => 'preview-email'],
    ],
    [
        'message' => NoticeMessages::SESSION_EXPIRED,
        'key' => 'flash',
        'element' => 'flash/error',
        'params' => ['action' => ['label' => 'ログインする', 'url' => '/login']],
    ],
    [
        'message' => '<b>注意</b>の例です。HTML は動かず、そのまま文字で出ます。',
        'key' => 'flash',
        'element' => 'flash/warning',
        'params' => [],
    ],
    [
        'message' => '案内の例です。（案内は、まだ共通通知領域に移していません）',
        'key' => 'flash',
        'element' => 'flash/info',
        'params' => [],
    ],
]);

$this->append('modals', $this->element('modal', [
    'id' => 'modal-preview',
    'title' => 'モーダルの確認',
    'description' => '共通モーダルの表示確認用です。',
    'body' => '<p>ここにモーダルの本文が表示されます。</p>',
    'actions' => $this->element('modal_actions'),
    'kind' => 'view',
]));
?>
<h1>共通部品の確認ページ</h1>

<h2>モーダル</h2>
<p>ブラウザーの開発者ツールからモーダルを開きます。</p>

<h2>入力エラーの移動先</h2>
<p>エラーの通知の「該当する項目へ移動」を押すと、この入力欄へ移動します。</p>
<label for="preview-email">メールアドレス</label>
<input type="email" id="preview-email" aria-invalid="true" aria-describedby="preview-email-error">
<p id="preview-email-error">メールアドレスの形で入力してください。（項目の近くのエラーの例）</p>

<h2>外部の機能の部品</h2>
<p>JavaScript がまだないので、代わりの表示だけが出ます（「現在地を使う」ボタンは隠れたまま）。</p>
<?= $this->element('external_feature', [
    'id' => 'preview-location',
    'feature' => 'location',
    'label' => '現在地から探す（確認用）',
    'content' => '<button type="button" data-external-start>現在地を使う</button>',
    'fallback' => '<p>地域を選んで探すこともできます。（代わりの表示の例）</p>',
    'keepFallback' => true,
    'loadingMessage' => '現在地を確認しています…',
    'messages' => [
        'success' => '現在地の周辺を表示しています。',
        'denied' => '位置情報の利用が許可されていません。ブラウザーの設定で許可するか、地域を選んで探してください。',
        'failed' => '現在地を確認できませんでした。地域を選んで探してください。',
    ],
]) ?>
