<?php
/*
 * 【このファイルは？】
 * 口コミ通報モーダル。通報する評価の確認、通報理由（1 つ選ぶ）、補足、キャンセルと「通報する」を表示する。
 * 設計書 COM-002 の MODAL-004（SCR-004 の DTL-019、SCR-014 の RLS-014・RLS-015）にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var int|string $reviewId 通報する口コミ・専門家評価の id（1 以上の整数）。モーダルの id は report-（この値）になる
 * @var array|string $url 通報を送る先（D チームと決める）。CakePHP の URL の配列か文字列
 * @var string|null $author 投稿した人のニックネーム（通報対象の確認に使う）
 * @var string|null $excerpt 口コミの本文（通報対象の確認に使う）。先頭の 80 文字だけを表示する
 * @var array<string, string>|null $reasons 通報理由の選択肢（送る値 => 表示する文字）。省略すると下の 4 種類
 * @var array<string, mixed>|null $values 入力エラーで戻ったときの、前の入力（reason、comment）
 * @var array<string, mixed>|null $errors 項目ごとのエラー（reason、comment）。
 *   ['reason' => ['通報理由を選んでください。']] の形。Entity の getErrors() の形もそのまま渡せる
 * @var string|null $message フォーム全体へのメッセージ（例：送信できなかった理由）
 */

/*
 * 使い方（各画面のファイル。例：施設詳細の口コミ一覧）：
 * 1. 通報できる口コミ 1 件ごとに、この部品を modals ブロックに足す。
 *
 *    <?php $this->append('modals', $this->element('review_report_modal', [
 *        'reviewId' => $review->id,
 *        'url' => ['controller' => 'Reports', 'action' => 'add'],
 *        'author' => $review->user->nickname,
 *        'excerpt' => $review->body,
 *    ])); ?>
 *
 * 2. 口コミカードの「通報する」ボタンを、次の形で置く（値は report-（口コミの id））。
 *
 *    <button type="button" aria-haspopup="dialog" aria-controls="report-<?= h($review->id) ?>"
 *        data-modal-open="report-<?= h($review->id) ?>">
 *        通報する
 *    </button>
 *
 * 【「通報する」ボタンを置かない口コミ】（カードの側で判断する。案。B・D・A と合意前）
 * ・自分の口コミ（チーム横断 提案事項 8「自分の投稿は不可」）
 * ・未ログインのとき。ボタンの代わりに、ログイン画面へのリンクを置く（提案事項 8「未ログインならログイン画面へ案内」）
 *
 * 【送ったあと】
 * ・送り方は、画面内通信（JavaScript で送り、JSON で結果を受け取る）の案（チーム横断 X-15、合意前）。
 *   E チームの JavaScript が、このフォームの内容を url へ送る。JavaScript がなくても普通のフォームとして送れる。
 * ・成功：モーダルを閉じて、受付完了を通知する（MODAL-004「成功時」）。
 * ・失敗：モーダルを閉じずに、入力を残したまま、項目の近くにエラーを出す（MODAL-004「エラー時」）。
 *   JSON の errors（例：{"reason": ["通報理由を選んでください。"]}）は、
 *   id が「report-（口コミの id）-（項目名）-error」の場所に、message は「report-（口コミの id）-message」に入れる。
 * ・普通のフォームで同じ画面に戻すときは、values・errors・message をこの部品に渡すと、入力とエラーを表示する。
 */

$reviewIdText = isset($reviewId) && (is_int($reviewId) || is_string($reviewId)) ? (string)$reviewId : '';
if (!preg_match('/\A[1-9][0-9]*\z/', $reviewIdText)) {
    throw new InvalidArgumentException('口コミ通報モーダルの reviewId には、口コミの id（1 以上の整数）を指定してください。');
}
if (!isset($url) || (!is_string($url) && !is_array($url)) || $url === '' || $url === []) {
    throw new InvalidArgumentException('口コミ通報モーダルの url（通報を送る先）を指定してください。');
}

// 補足の最大の文字数（案。チーム横断 提案事項 8「補足は 200 文字まで」。B チームの入力チェックと同じ値にする）
$commentMaxLength = 200;

// 通報理由（案。設計書 RLS-014 の基本案と、モック 13-report-popup.png の文字）。
// 送る値（左側）は B・F チームと決める。決まったら、ここか、呼び出す側の reasons で合わせる。
$defaultReasons = [
    'inappropriate' => '不適切な表現が含まれている',
    'incorrect' => '事実と異なる内容',
    'personal_info' => '個人情報が含まれている',
    'other' => 'その他',
];
$reasonOptions = isset($reasons) && is_array($reasons) && $reasons !== [] ? $reasons : $defaultReasons;

$author = isset($author) && is_string($author) ? trim($author) : '';
$excerpt = isset($excerpt) && is_string($excerpt) ? trim($excerpt) : '';
$inputValues = isset($values) && is_array($values) ? $values : [];
$reasonValue = isset($inputValues['reason']) && is_scalar($inputValues['reason']) ? (string)$inputValues['reason'] : '';
$commentValue = isset($inputValues['comment']) && is_string($inputValues['comment']) ? $inputValues['comment'] : '';
$formMessage = isset($message) && is_string($message) ? $message : '';

// 項目ごとのエラーを、文字の配列にそろえる。
// ['reason' => '...']、['reason' => ['...']]、Entity の ['reason' => ['ルール名' => '...']] のどれでも受け取る。
$fieldErrors = [];
foreach (['reason', 'comment'] as $field) {
    $raw = isset($errors) && is_array($errors) ? ($errors[$field] ?? []) : [];
    $list = is_array($raw) ? array_values($raw) : [$raw];
    $fieldErrors[$field] = array_values(array_filter($list, fn($item): bool => is_string($item) && $item !== ''));
}

// 最初にエラーがある項目。開いたときに、ここへフォーカスを移す（MODAL-007「初期フォーカス」）。
$firstErrorField = null;
foreach ($fieldErrors as $field => $messages) {
    if ($messages !== []) {
        $firstErrorField = $field;
        break;
    }
}

// id。ページに通報モーダルがいくつ並んでも重ならないように、すべて report-（口コミの id）で始める。
$modalId = 'report-' . $reviewIdText;
$formId = $modalId . '-form';
$messageId = $modalId . '-message';
$reasonErrorId = $modalId . '-reason-error';
$commentId = $modalId . '-comment';
$commentHintId = $modalId . '-comment-hint';
$commentErrorId = $modalId . '-comment-error';

// エラーの文字を、エラー表示の場所の中身にする（2 つ以上あるときは改行で区切る）。
$errorText = fn(array $messages): string => implode('<br>', array_map('h', $messages));
?>
<?php $this->start('reviewReportModalBody'); ?>
    <?php if ($author !== '' || $excerpt !== '') : ?>
        <!-- 通報する評価の確認（MODAL-004「通報対象の確認」） -->
        <div class="report-form__target">
            <p class="report-form__target-label">
                通報する評価<?= $author !== '' ? '：' . h($author) . ' さん' : '' ?>
            </p>
            <?php if ($excerpt !== '') : ?>
                <blockquote class="report-form__target-text">
                    <?= h($this->Text->truncate($excerpt, 80, ['ellipsis' => '…', 'exact' => true])) ?>
                </blockquote>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php
    // idPrefix：FormHelper が作る id の先頭に report-（口コミの id）を付けて、ほかの通報モーダルと重ならないようにする。
    // valueSources：送られてきた値（リクエスト）を自動では使わない。同じ名前の入力欄がほかの通報モーダルにもあり、
    // 自動で使うと、エラーになった口コミ以外のフォームにも前の入力が入ってしまうため。前の入力は values で渡す。
    ?>
    <?= $this->Form->create(null, [
        'url' => $url,
        'id' => $formId,
        'idPrefix' => $modalId,
        'valueSources' => ['context'],
        'class' => 'report-form',
        'data-report-form' => true,
    ]) ?>
        <?= $this->Form->hidden('review_id', ['value' => $reviewIdText]) ?>

        <!-- フォーム全体へのメッセージ。E チームの JavaScript が、送信できなかった理由をここに入れる -->
        <p class="report-form__message" id="<?= h($messageId) ?>" role="alert"><?= h($formMessage) ?></p>

        <?php
        // エラー表示の場所は、空のときも出して aria-describedby で結び付けておく。
        // E チームの JavaScript があとからエラーを入れても、読み上げソフトが項目と一緒に読めるようにするため。
        ?>
        <fieldset class="report-form__field" aria-describedby="<?= h($reasonErrorId) ?>">
            <legend class="report-form__label">通報理由（必須）</legend>
            <?= $this->Form->radio('reason', $reasonOptions, [
                'value' => $reasonValue !== '' ? $reasonValue : null,
                'required' => true,
                'aria-invalid' => $fieldErrors['reason'] !== [] ? 'true' : null,
                // すべての選択肢に付くが、ブラウザーは最初の 1 つにフォーカスを移す
                'autofocus' => $firstErrorField === 'reason' ? true : null,
            ]) ?>
            <p class="report-form__error" id="<?= h($reasonErrorId) ?>"><?= $errorText($fieldErrors['reason']) ?></p>
        </fieldset>

        <div class="report-form__field">
            <label class="report-form__label" for="<?= h($commentId) ?>">補足（任意）</label>
            <p class="report-form__hint" id="<?= h($commentHintId) ?>">
                <?= h($commentMaxLength) ?> 文字まで。「その他」を選んだときは、理由を書いてください。
            </p>
            <?= $this->Form->textarea('comment', [
                'id' => $commentId,
                'value' => $commentValue,
                'rows' => 3,
                'maxlength' => $commentMaxLength,
                'aria-describedby' => $commentHintId . ' ' . $commentErrorId,
                'aria-invalid' => $fieldErrors['comment'] !== [] ? 'true' : null,
                'autofocus' => $firstErrorField === 'comment' ? true : null,
                // 「その他」のときは補足が必須（案）。E チームの JavaScript が、この値の理由を選んだら required を付ける
                'data-required-reason' => 'other',
            ]) ?>
            <p class="report-form__error" id="<?= h($commentErrorId) ?>"><?= $errorText($fieldErrors['comment']) ?></p>
        </div>
    <?= $this->Form->end() ?>
<?php $this->end(); ?>
<?php
// ビューブロックは同じ名前で何度も使うので、取り出したら空にしておく（口コミが何件あっても混ざらないように）。
$reportBody = $this->fetch('reviewReportModalBody');
$this->reset('reviewReportModalBody');

/*
 * 口コミ通報モーダル（MODAL-004）。入力用なので、閉じ方は C チーム決定事項 4 のとおり。
 *   未入力：背景を押す・Escape キー・キャンセルで閉じる（RLS-014）
 *   入力あり：背景を押しても閉じない。Escape キー・キャンセルでは破棄の確認を出す（modal.php の kind input）
 *   送信中：閉じない（modal_actions.php の data-modal-submit）
 * 口コミの件数だけ出力されるので、説明はページに出ない PHP のコメントに書いている。
 *
 * E チームにお願いすること：
 *   ・report-form__target（通報する評価の確認）、report-form__field・__label・__hint・__error・__message の見た目。
 *     エラーの場所は空のときも出しているので、空のときは見えないようにする（:empty）
 *   ・理由の選択肢を押しやすい大きさにする（RLS-014 の E の担当「理由選択」）
 *   ・data-report-form のフォームを画面内通信で送る処理と、結果の表示（上の「送ったあと」）
 *   ・data-required-reason の値の理由を選んだときに、補足を必須にする処理
 */
?>
<?= $this->element('modal', [
    'id' => $modalId,
    'title' => 'この評価を通報する',
    'description' => '運営が内容を確認し、必要に応じて非表示にします。',
    'body' => $reportBody,
    'actions' => $this->element('modal_actions', [
        'cancel' => 'キャンセル',
        'buttons' => [
            ['label' => '通報する', 'variant' => 'primary', 'form' => $formId, 'processingLabel' => '送信中…'],
        ],
    ]),
    'kind' => 'input',
    // 普通のフォームで送り、エラーで同じ画面に戻ったときは、開いた状態にして入力とエラーを見せる（MODAL-006「失敗時」）
    'autoOpen' => $formMessage !== '' || $firstErrorField !== null,
    // 項目のエラーがあれば、その項目（autofocus）へ。なければ見出しへ（MODAL-007「初期フォーカス」）
    'initialFocus' => $firstErrorField !== null ? 'first' : 'title',
]) ?>
