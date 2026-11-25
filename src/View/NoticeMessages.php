<?php
declare(strict_types=1);

/*
 * 【このファイルは？】
 * 通知に出す共通の文言（通信の失敗、ログイン切れなど 8 種類）を 1 か所にまとめたもの。
 * 設計書 COM-002 の NOTICE-003（処理状態と通知の共通連携）、C チーム決定事項 6 にあたる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */

namespace App\View;

/**
 * 通知の共通の文言。
 *
 * 使う場所：
 * - 画面内通信（JavaScript）：templates/element/toast_region.php が、この文言を <template data-notice-messages> に出す。
 *   E チームの JavaScript は、そこから文言を読む（JavaScript の中に同じ文を書かない）。
 * - Controller（D チーム）：Flash で同じ場面の文言を出すときは、この定数を使う。
 *   例：$this->Flash->error(NoticeMessages::INVALID);
 * - テスト（G チーム）：期待する文言として、この定数を使う。
 *
 * 業務の結果（「お気に入りに追加しました。」など）は、ここに入れず、各画面で書く（決定事項 6）。
 *
 * 【処理の結果と、出す文言（NOTICE-003）】
 * 画面内通信の応答が、D チームと決める形（JSON。success・message・errors・data。チーム横断 X-15、合意前）なら、
 * message をそのまま出す。その形でない応答（CakePHP の標準のエラー画面など）は、例外の文などの内部情報が入るので、
 * 中身を出さずに、下の表の文言を出す（NOTICE-002「内部情報」）。
 *
 * | 結果                                         | 送る（POST など） | 読む（GET）   |
 * | 成功（2xx で success が true）               | 応答の message    | 応答の message |
 * | 入力に問題がある（400・422 で errors がある）| INVALID           | INVALID       |
 * | ログインが切れた（401）                      | SESSION_EXPIRED   | SESSION_EXPIRED |
 * | 権限がない（403）                            | FORBIDDEN         | FORBIDDEN     |
 * | 画面が古い（CSRF エラー）                    | CSRF              | ―             |
 * | サーバーのエラー（5xx）                      | UNEXPECTED        | LOAD_FAILED   |
 * | 応答はあるが、形が読めない                   | UNKNOWN_RESULT    | LOAD_FAILED   |
 * | 応答がない（通信の失敗・時間切れ）           | UNKNOWN_RESULT    | NETWORK       |
 *
 * 送る処理で応答がないときに NETWORK ではなく UNKNOWN_RESULT にするのは、サーバーには届いて保存まで
 * 終わっているかもしれないため。自動で送り直さず、再読み込みして確かめるよう案内する（NOTICE-003「通信失敗時」）。
 * CSRF エラーは、CakePHP では 403 になり、権限がないときと見分けられない。D チームに、決めた形の JSON で
 * message に CSRF の文言を入れて返してもらう（D チームと相談中）。
 */
final class NoticeMessages
{
    /**
     * 通信に失敗した（読む処理で応答がない）。
     */
    public const NETWORK = '通信できませんでした。接続を確認して、もう一度お試しください。';

    /**
     * 結果が分からない（送る処理で応答がない、または応答が読めない）。
     */
    public const UNKNOWN_RESULT = '結果を確認できませんでした。もう一度送信する前に、画面を再読み込みして確認してください。';

    /**
     * ログインが切れた（401）。
     */
    public const SESSION_EXPIRED = 'ログインの有効期限が切れました。もう一度ログインしてください。';

    /**
     * 権限がない（403）。
     */
    public const FORBIDDEN = 'この操作を行う権限がありません。';

    /**
     * 入力に問題がある。項目ごとのエラーは、入力欄の近くに出す。
     */
    public const INVALID = '入力内容を確認してください。';

    /**
     * 画面が古い（CSRF エラー。悪いサイトからの送信を防ぐ仕組みが働いたとき）。
     */
    public const CSRF = '画面を開いてから時間がたったため、送信できませんでした。再読み込みしてからお試しください。';

    /**
     * 読み込みに失敗した（読む処理でサーバーのエラー、または応答が読めない）。
     */
    public const LOAD_FAILED = '読み込めませんでした。もう一度お試しください。';

    /**
     * 想定外のエラー（送る処理でサーバーのエラー）。
     */
    public const UNEXPECTED = '問題が発生しました。時間をおいて、もう一度お試しください。';

    /**
     * すべての文言を、JavaScript が使う名前つきで返す。
     *
     * 名前（左側）は、HTML の data-notice-message の値になる。E チームの JavaScript はこの名前で文言を探す。
     *
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'network' => self::NETWORK,
            'unknown-result' => self::UNKNOWN_RESULT,
            'session-expired' => self::SESSION_EXPIRED,
            'forbidden' => self::FORBIDDEN,
            'invalid' => self::INVALID,
            'csrf' => self::CSRF,
            'load-failed' => self::LOAD_FAILED,
            'unexpected' => self::UNEXPECTED,
        ];
    }
}
