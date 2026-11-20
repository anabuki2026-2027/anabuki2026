<?php
/*
 * 【このファイルは？】
 * 管理画面共通モーダル。対象データの一覧、説明、入力欄、操作ボタンを決まった形で並べ、フォームとして送る。
 * 資格審査・通報対応・利用停止などの管理操作で使う。設計書 COM-002 の MODAL-005 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var string $id モーダルの id。ページの中で重ならない名前にする（例：credential-12）
 * @var string $title 見出し（必須）。例：さくら さんの資格確認
 * @var string|null $eyebrow 見出しの上の小さな文字（例：CREDENTIAL CHECK）
 * @var string|null $description 見出しの下の説明
 * @var array|string $url フォームを送る先（D チームと決める）。CakePHP の URL の配列か文字列
 * @var array<string, mixed>|null $details 対象データ（項目名 => 値）。値が空のときは「—」を表示する
 * @var string|null $note 対象データの下に表示する注意書き
 * @var array<string, mixed>|null $hidden 画面に出さずに送る値（項目名 => 値）。例：['application_id' => 12]
 * @var array<int, array<string, mixed>>|null $fields 入力欄。1 つずつ次の値を指定する
 *   - name（必須）：項目名（英字で始まり、英数字と _ だけ）
 *   - label（必須）：項目の名前
 *   - type：text（1 行）、textarea（複数行）、radio（1 つ選ぶ）、select（一覧から選ぶ）。省略すると text
 *   - options：radio・select の選択肢（送る値 => 表示する文字）
 *   - required：必須なら true（項目名に「（必須）」を付け、ブラウザーでも確認する）
 *   - maxlength：最大の文字数（text・textarea）
 *   - hint：入力欄の上に出す説明
 *   - placeholder：入力欄の中に出す例（text・textarea）
 *   - empty：select の最初の「選んでください」の文字
 * @var array<string, mixed>|null $values 入力エラーで戻ったときの、前の入力（項目名 => 値）
 * @var array<string, mixed>|null $errors 項目ごとのエラー（項目名 => 文字、または文字の配列）。Entity の getErrors() もそのまま渡せる
 * @var string|null $message フォーム全体へのメッセージ（例：ほかの管理者が先に更新しました）
 * @var string|null $cancel 閉じる・キャンセルのボタンの文字。省略すると「閉じる」
 * @var array<int, array<string, mixed>>|null $buttons 操作ボタン。書き方は templates/element/modal_actions.php と同じ。
 *   type が submit のボタンは、form を書かなくても、このモーダルのフォームを送る
 */

/*
 * 使い方（管理画面のファイル。例：会員管理の資格確認。モック 35-credential-popup.png）：
 * 対象 1 件ごとに、この部品を modals ブロックに足す。項目名・URL・送る値は D・B チームと決める。
 *
 *    <?php $this->append('modals', $this->element('admin_modal', [
 *        'id' => 'credential-' . $application->id,
 *        'eyebrow' => 'CREDENTIAL CHECK',
 *        'title' => $application->user->nickname . ' さんの資格確認',
 *        'url' => ['controller' => 'Credentials', 'action' => 'review', $application->id],
 *        'details' => [
 *            '資格' => $application->qualification_name,
 *            '認定番号' => $application->certificate_number,
 *            '申請日' => $application->created?->format('Y.m.d'),
 *        ],
 *        'note' => '承認すると、この人の評価に級バッジが表示されます。',
 *        'fields' => [
 *            ['name' => 'grade', 'label' => '付与する級', 'type' => 'radio', 'options' => $gradeOptions],
 *            ['name' => 'reason', 'label' => '差し戻す場合の理由', 'maxlength' => 200,
 *                'placeholder' => '例：認定番号を確認できませんでした'],
 *        ],
 *        'buttons' => [
 *            ['label' => '差し戻す', 'variant' => 'danger', 'name' => 'decision', 'value' => 'reject'],
 *            ['label' => '承認して級バッジを付与', 'variant' => 'primary', 'name' => 'decision', 'value' => 'approve'],
 *        ],
 *    ])); ?>
 *
 * 開くボタンは、一覧の各行に次の形で置く（値はモーダルの id）。
 *
 *    <button type="button" aria-haspopup="dialog" aria-controls="credential-<?= h($application->id) ?>"
 *        data-modal-open="credential-<?= h($application->id) ?>">
 *        確認する
 *    </button>
 *
 * 【決まり】
 * ・権限のある管理者だけが開ける（MODAL-005「表示条件」）。権限は D チームが確認し、
 *   権限のない人の画面には、この部品も開くボタンも出さない。送られた先でも D チームが必ず確認する。
 * ・送り方は普通のフォーム（チーム横断 提案事項「管理画面の操作」、合意前）。
 *   成功：D チームが元の画面へ戻し、フラッシュメッセージで結果を出す。一覧も新しい内容で表示される（MODAL-005「成功時」）。
 *   失敗：D チームが同じ画面を表示し、values・errors・message を渡す。このモーダルが開いた状態になり、
 *         入力とエラーが表示される（MODAL-005「エラー時」）。
 * ・取り消しにくい操作（差し戻し、非表示、利用停止など）のボタンは variant を danger にする（MODAL-002）。
 * ・入力後に閉じるときの破棄の確認（MODAL-005「未保存変更」）は、入力用のモーダル全部に共通なので、#463 で作る。
 */

$modalId = isset($id) && is_string($id) ? $id : '';
if (!preg_match('/\A[A-Za-z][A-Za-z0-9_-]*\z/', $modalId)) {
    throw new InvalidArgumentException('管理画面モーダルの id は、英字で始まり、英数字・-・_ だけの名前にしてください。');
}
if (!isset($url) || (!is_string($url) && !is_array($url)) || $url === '' || $url === []) {
    throw new InvalidArgumentException('管理画面モーダルの url（フォームを送る先）を指定してください。');
}

$detailList = isset($details) && is_array($details) ? $details : [];
$noteText = isset($note) && is_string($note) ? $note : '';
$hiddenValues = isset($hidden) && is_array($hidden) ? $hidden : [];
$fieldList = isset($fields) && is_array($fields) ? array_values($fields) : [];
$inputValues = isset($values) && is_array($values) ? $values : [];
$errorList = isset($errors) && is_array($errors) ? $errors : [];
$formMessage = isset($message) && is_string($message) ? $message : '';
$buttonList = isset($buttons) && is_array($buttons) ? $buttons : [];

$formId = $modalId . '-form';
$messageId = $modalId . '-message';

// 入力欄の指定を確かめて、表示に使う形にそろえる。
$fieldTypes = ['text', 'textarea', 'radio', 'select'];
$normalizedFields = [];
foreach ($fieldList as $index => $field) {
    $number = $index + 1;
    if (!is_array($field)) {
        throw new InvalidArgumentException("管理画面モーダルの入力欄 {$number} 番目は、配列で指定してください。");
    }
    $name = isset($field['name']) && is_string($field['name']) ? $field['name'] : '';
    if (!preg_match('/\A[A-Za-z][A-Za-z0-9_]*\z/', $name)) {
        throw new InvalidArgumentException(
            "管理画面モーダルの入力欄 {$number} 番目の name は、英字で始まり、英数字と _ だけの名前にしてください。",
        );
    }
    $label = isset($field['label']) && is_string($field['label']) ? trim($field['label']) : '';
    if ($label === '') {
        throw new InvalidArgumentException("管理画面モーダルの入力欄「{$name}」の label（項目の名前）を指定してください。");
    }
    $type = $field['type'] ?? 'text';
    if (!in_array($type, $fieldTypes, true)) {
        throw new InvalidArgumentException(
            "管理画面モーダルの入力欄「{$name}」の type は、text・textarea・radio・select のどれかにしてください。",
        );
    }
    $options = isset($field['options']) && is_array($field['options']) ? $field['options'] : [];
    if (($type === 'radio' || $type === 'select') && $options === []) {
        throw new InvalidArgumentException("管理画面モーダルの入力欄「{$name}」の options（選択肢）を指定してください。");
    }

    // エラーを文字の配列にそろえる（'...'、['...']、Entity の ['ルール名' => '...'] のどれでも受け取る）。
    $rawErrors = $errorList[$name] ?? [];
    $messages = is_array($rawErrors) ? array_values($rawErrors) : [$rawErrors];
    $messages = array_values(array_filter($messages, fn($item): bool => is_string($item) && $item !== ''));

    $value = $inputValues[$name] ?? null;
    $maxlength = isset($field['maxlength']) && is_int($field['maxlength']) && $field['maxlength'] > 0
        ? $field['maxlength'] : null;

    $normalizedFields[] = [
        'name' => $name,
        'label' => $label,
        'type' => $type,
        'options' => $options,
        'required' => ($field['required'] ?? false) === true,
        'maxlength' => $maxlength,
        'hint' => isset($field['hint']) && is_string($field['hint']) ? $field['hint'] : '',
        'placeholder' => isset($field['placeholder']) && is_string($field['placeholder'])
            ? $field['placeholder'] : null,
        'empty' => isset($field['empty']) && is_string($field['empty']) ? $field['empty'] : '選んでください',
        'value' => is_scalar($value) ? (string)$value : null,
        'errors' => $messages,
        // id。ページに管理画面モーダルがいくつ並んでも重ならないように、モーダルの id で始める。
        'id' => $modalId . '-' . str_replace('_', '-', $name),
    ];
}

// 送信ボタンは、form を書かなくても、このモーダルのフォームを送るようにする。
$actionButtons = [];
foreach ($buttonList as $button) {
    if (is_array($button) && ($button['type'] ?? 'submit') === 'submit' && !isset($button['form'])) {
        $button['form'] = $formId;
    }
    $actionButtons[] = $button;
}

// 入力エラーやメッセージがあるときは、ページを開いたときにこのモーダルを開いた状態にする。
$hasError = $formMessage !== '';
foreach ($normalizedFields as $field) {
    $hasError = $hasError || $field['errors'] !== [];
}

// エラーの文字を、エラー表示の場所の中身にする（2 つ以上あるときは改行で区切る）。
$errorText = fn(array $messages): string => implode('<br>', array_map('h', $messages));
?>
<?php $this->start('adminModalBody'); ?>
    <?php if ($detailList !== []) : ?>
        <!-- 対象データ（MODAL-005「対象データ」）。操作の前に、対象をもう一度確かめられるようにする -->
        <dl class="admin-modal__details">
            <?php foreach ($detailList as $detailLabel => $detailValue) : ?>
                <?php $detailText = is_scalar($detailValue) ? (string)$detailValue : ''; ?>
                <div class="admin-modal__detail">
                    <dt><?= h((string)$detailLabel) ?></dt>
                    <dd><?= $detailText !== '' ? h($detailText) : '—' ?></dd>
                </div>
            <?php endforeach; ?>
        </dl>
    <?php endif; ?>
    <?php if ($noteText !== '') : ?>
        <p class="admin-modal__note"><?= h($noteText) ?></p>
    <?php endif; ?>

    <?php
    // idPrefix：FormHelper が作る id（ラジオボタンなど）の先頭にモーダルの id を付けて、ほかのモーダルと重ならないようにする。
    // valueSources：送られてきた値（リクエスト）を自動では使わない。同じ名前の入力欄がほかの行のモーダルにもあり、
    // 自動で使うと、エラーになった行以外のモーダルにも前の入力が入ってしまうため。前の入力は values で渡す。
    ?>
    <?= $this->Form->create(null, [
        'url' => $url,
        'id' => $formId,
        'idPrefix' => $modalId,
        'valueSources' => ['context'],
        'class' => 'admin-modal__form',
    ]) ?>
        <?php foreach ($hiddenValues as $hiddenName => $hiddenValue) : ?>
            <?= $this->Form->hidden((string)$hiddenName, [
                'value' => is_scalar($hiddenValue) ? (string)$hiddenValue : '',
            ]) ?>
        <?php endforeach; ?>

        <!-- フォーム全体へのメッセージ（例：ほかの管理者が先に更新しました） -->
        <p class="admin-modal__message" id="<?= h($messageId) ?>" role="alert"><?= h($formMessage) ?></p>

        <?php foreach ($normalizedFields as $field) : ?>
            <?php
            $labelText = $field['label'] . ($field['required'] ? '（必須）' : '');
            $hintId = $field['id'] . '-hint';
            $errorId = $field['id'] . '-error';
            // エラー表示の場所は、空のときも出して aria-describedby で結び付けておく（読み上げソフトが項目と一緒に読む）。
            $describedBy = trim(($field['hint'] !== '' ? $hintId . ' ' : '') . $errorId);
            ?>
            <?php if ($field['type'] === 'radio') : ?>
                <fieldset class="admin-modal__field" aria-describedby="<?= h($describedBy) ?>">
                    <legend class="admin-modal__label"><?= h($labelText) ?></legend>
                    <?php if ($field['hint'] !== '') : ?>
                        <p class="admin-modal__hint" id="<?= h($hintId) ?>"><?= h($field['hint']) ?></p>
                    <?php endif; ?>
                    <?= $this->Form->radio($field['name'], $field['options'], [
                        'value' => $field['value'],
                        'required' => $field['required'],
                    ]) ?>
                    <p class="admin-modal__error" id="<?= h($errorId) ?>"><?= $errorText($field['errors']) ?></p>
                </fieldset>
            <?php else : ?>
                <div class="admin-modal__field">
                    <label class="admin-modal__label" for="<?= h($field['id']) ?>"><?= h($labelText) ?></label>
                    <?php if ($field['hint'] !== '') : ?>
                        <p class="admin-modal__hint" id="<?= h($hintId) ?>"><?= h($field['hint']) ?></p>
                    <?php endif; ?>
                    <?php
                    $attributes = [
                        'id' => $field['id'],
                        'value' => $field['value'],
                        'required' => $field['required'],
                        'aria-describedby' => $describedBy,
                        'aria-invalid' => $field['errors'] !== [] ? 'true' : null,
                    ];
                    if ($field['type'] === 'select') {
                        echo $this->Form->select($field['name'], $field['options'], $attributes + [
                            'empty' => $field['empty'],
                        ]);
                    } else {
                        $attributes += ['maxlength' => $field['maxlength'], 'placeholder' => $field['placeholder']];
                        echo $field['type'] === 'textarea'
                            ? $this->Form->textarea($field['name'], $attributes + ['rows' => 3])
                            : $this->Form->text($field['name'], $attributes);
                    }
                    ?>
                    <p class="admin-modal__error" id="<?= h($errorId) ?>"><?= $errorText($field['errors']) ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?= $this->Form->end() ?>
<?php $this->end(); ?>
<?php
// ビューブロックは同じ名前で何度も使うので、取り出したら空にしておく（モーダルが何件あっても混ざらないように）。
$adminBody = $this->fetch('adminModalBody');
$this->reset('adminModalBody');

/*
 * 管理画面共通モーダル（MODAL-005）。閉じ方は C チーム決定事項 4 のとおり（確認用・入力用・処理中で分ける）。
 * 対象の件数だけ出力されるので、説明はページに出ない PHP のコメントに書いている。
 *
 * E チームにお願いすること（MODAL-005 の E の担当「管理操作の内容と危険度が判別できる表示」）：
 *   ・admin-modal__details（対象データの表。モック 35 のように、項目名と値を横に並べる）、admin-modal__note、
 *     admin-modal__field・__label・__hint・__error・__message の見た目。エラーの場所は空のときは見えないようにする（:empty）
 *   ・危険操作のボタン（modal__button--danger）を、ふつうの操作とはっきり区別する
 */
?>
<?= $this->element('modal', [
    'id' => $modalId,
    'eyebrow' => isset($eyebrow) && is_string($eyebrow) ? $eyebrow : null,
    'title' => isset($title) && is_string($title) ? $title : '',
    'description' => isset($description) && is_string($description) ? $description : null,
    'body' => $adminBody,
    'actions' => $this->element('modal_actions', [
        'cancel' => isset($cancel) && is_string($cancel) ? $cancel : null,
        'buttons' => $actionButtons,
    ]),
    'class' => 'admin-modal',
    'autoOpen' => $hasError,
]) ?>
