<?php
/*
 * 【このファイルは？】
 * お知らせ詳細モーダル。お知らせ 1 件の重要表示、タイトル、本文、掲載期間と「閉じる」を、共通モーダルで表示する。
 * 設計書 COM-002 の MODAL-003 にあたる。トップページ（SCR-001 の TOP-011）などのお知らせ一覧から開く。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

/**
 * @var \App\View\AppView $this
 * @var int|string $id お知らせの id（1 以上の整数）。モーダルの id は notice-（この値）になる
 * @var string $title タイトル（必須）
 * @var string|null $body 本文。ふつうの文字として表示し、改行はそのまま残す（NOT-005）。HTML は動かない
 * @var \DateTimeInterface|null $startsAt 掲載開始日時
 * @var \DateTimeInterface|null $endsAt 掲載終了日時。終了日時がないお知らせは null
 * @var bool|null $important 重要なお知らせなら true（NOT-008）
 */

/*
 * 使い方（各画面のファイル。例：トップページのお知らせ一覧）：
 * 1. お知らせ 1 件ごとに、この部品を modals ブロックに足す。
 *    本文はページを開くときに一緒に受け取り、通信せずにモーダルで表示する（チーム横断 提案事項）。
 *    お知らせの列の名前（title、body など）は F・B チームと決まりしだい合わせる。
 *
 *    <?php foreach ($notices as $notice) : ?>
 *        <?php $this->append('modals', $this->element('notice_modal', [
 *            'id' => $notice->id,
 *            'title' => $notice->title,
 *            'body' => $notice->body,
 *            'startsAt' => $notice->starts_at,
 *            'endsAt' => $notice->ends_at,
 *            'important' => $notice->is_important,
 *        ])); ?>
 *    <?php endforeach; ?>
 *
 * 2. 一覧の「詳しく」ボタンを、次の形で置く。data-modal-open と aria-controls の値は notice-（お知らせの id）。
 *
 *    <button type="button" aria-haspopup="dialog" aria-controls="notice-<?= h($notice->id) ?>"
 *        data-modal-open="notice-<?= h($notice->id) ?>">
 *        詳しく<span class="visually-hidden">：<?= h($notice->title) ?></span>
 *    </button>
 *
 *    「詳しく」が何件も並ぶので、読み上げソフトでどのお知らせかが分かるように、タイトルも添える
 *    （見た目では隠す class 名は E チームと決める）。
 *
 * 【表示しないもの】
 * 公開中で、その画面が掲載先に入っているお知らせだけを渡す（MODAL-003「表示条件」）。
 * どのお知らせを渡すかは B（取得）・D（画面へ渡す）チームが決めるので、この部品では公開状態を判定しない。
 * 取得に失敗したときは、お知らせを渡さない（一覧ごと表示しない。TOP-011「エラー時」）。
 */

// 掲載期間は日本時間で表示する（NOT-007「日本時間として入力・表示し」）。
// アプリの標準のタイムゾーン（config/app.php の App.defaultTimezone）は UTC なので、ここで日本時間に直す。
$displayTimezone = new DateTimeZone('Asia/Tokyo');

$noticeId = isset($id) && (is_int($id) || is_string($id)) ? (string)$id : '';
if (!preg_match('/\A[1-9][0-9]*\z/', $noticeId)) {
    throw new InvalidArgumentException('お知らせ詳細モーダルの id には、お知らせの id（1 以上の整数）を指定してください。');
}
$title = isset($title) && is_string($title) ? $title : '';
$body = isset($body) && is_string($body) ? trim($body) : '';
$isImportant = isset($important) && $important === true;

// 日時を「10/20」の形（日本時間）にする。日時でないときは null。
$formatDate = function (mixed $value) use ($displayTimezone): ?array {
    if (!$value instanceof DateTimeInterface) {
        return null;
    }
    $local = DateTimeImmutable::createFromInterface($value)->setTimezone($displayTimezone);

    return [
        'text' => $local->format('n/j'),
        // <time> の datetime 属性用（例：2026-10-20T09:00:00+09:00）
        'datetime' => $local->format(DATE_ATOM),
    ];
};
$start = $formatDate($startsAt ?? null);
$end = $formatDate($endsAt ?? null);

// 日付を <time> タグにする（読み上げソフトや検索エンジンが日時として分かるように）。
$timeTag = fn(array $date): string => '<time datetime="' . h($date['datetime']) . '">' . h($date['text']) . '</time>';

// 見出しの上の小さな文字。モック 11-notice-popup.png のとおり「NOTICE・10/20〜10/27」にする。
// 終了日時がないお知らせは「NOTICE・10/20〜」（NOT-007「終了日時未設定」は非公開にするまで掲載が続く）。
$eyebrow = 'NOTICE';
if ($start !== null) {
    $eyebrow .= '・' . $start['text'] . '〜' . ($end !== null ? $end['text'] : '');
}
?>
<?php $this->start('noticeModalBody'); ?>
    <?php if ($isImportant) : ?>
        <p class="notice-detail__important">重要</p>
    <?php endif; ?>
    <?php if ($body !== '') : ?>
        <p class="notice-detail__text"><?= nl2br(h($body)) ?></p>
    <?php endif; ?>
    <?php if ($start !== null) : ?>
        <p class="notice-detail__period">掲載期間：<?= $timeTag($start) ?>〜<?= $end !== null ? $timeTag($end) : '' ?></p>
    <?php endif; ?>
<?php $this->end(); ?>
<?php
// ビューブロックは同じ名前で何度も使うので、取り出したら空にしておく（お知らせが何件あっても混ざらないように）。
$noticeBody = $this->fetch('noticeModalBody');
$this->reset('noticeModalBody');

/*
 * お知らせ詳細モーダル（MODAL-003）。閲覧用なので、背景を押す・Escape キー・閉じるで閉じる（C チーム決定事項 4）。
 * 開いたときは、ブラウザーが最初の操作（閉じる）にフォーカスを移す（MODAL-003「初期フォーカス」）。
 * お知らせの件数だけ出力されるので、説明は HTML コメントではなく、ページに出ない PHP のコメントに書いている。
 *
 * E チームにお願いすること：
 *   ・notice-detail__important（重要の印）、notice-detail__text（本文。長文でも読みやすい行の高さ）、
 *     notice-detail__period（掲載期間）の見た目（MODAL-003 の E の担当「タイトル、本文、掲載日時の読みやすさ」）
 */
?>
<?= $this->element('modal', [
    'id' => 'notice-' . $noticeId,
    'eyebrow' => $eyebrow,
    'title' => $title,
    'body' => $noticeBody,
    'actions' => $this->element('modal_actions'),
]) ?>
