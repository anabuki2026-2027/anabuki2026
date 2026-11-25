# 使っている外部ツール・プラグインの一覧

このアプリで使っている「他の人が作ったもの」（ライブラリ・プラグイン・ソフト・サービスなど）をまとめたページです。
Composer で入れる部品だけでなく、CSS・フォント・パソコンに入れるソフト・GitHub の仕組み・AI の道具も載せています。

> バージョンは **2026-11-20 時点の `composer.lock` と各ファイル** から調べたものです。
> 部品を足したり、`composer update` で更新したりしたときは、A チームがこのページも直してください。

---

## もくじ

1. [Composer で入れている部品](#1-composer-で入れている部品)
2. [Composer を使わずに置いている部品（CSS・フォント）](#2-composer-を使わずに置いている部品cssフォント)
3. [パソコンに入れるソフト](#3-パソコンに入れるソフト)
4. [PHP の拡張機能](#4-php-の拡張機能)
5. [VS Code の拡張機能](#5-vs-code-の拡張機能)
6. [GitHub の仕組み](#6-github-の仕組み)
7. [AI の道具](#7-ai-の道具)
8. [設定ファイルだけあるもの・おすすめだけされているもの](#8-設定ファイルだけあるものおすすめだけされているもの)

---

## 1. Composer で入れている部品

`composer.json` に書いてあり、`composer install` で `vendor/` に入る部品です。
`vendor/` の中は編集しません（`composer install` のたびに元にもどります）。

### 1-1. アプリを動かすのに使う部品（`require`）

本番のサーバーでも使う部品です。

| 部品 | バージョン | 何をするもの？ | どこで使う？ |
| --- | --- | --- | --- |
| `cakephp/cakephp` | 5.4.3 | このアプリの土台（フレームワーク）。 | アプリ全体 |
| `cakephp/migrations` | 5.2.6 | データベースの表を、プログラムで作ったり変えたりする **CakePHP のプラグイン**。 | `config/Migrations/`、`config/Seeds/`（F チーム）。`bin/cake migrations migrate` |
| `cakephp/plugin-installer` | 2.0.3 | CakePHP のプラグインを、Composer で入れたときに使えるようにする部品。 | `composer install` のとき自動で動く |
| `mobiledetect/mobiledetectlib` | 4.11.0 | 見ている人がスマホかタブレットかを判定する部品。 | `config/bootstrap.php`（`$this->request->is('mobile')`・`is('tablet')` で使える） |

### 1-2. 開発のときだけ使う部品（`require-dev`）

自分のパソコンや GitHub の自動チェックで使う部品です。本番のサーバーには入れません。

| 部品 | バージョン | 何をするもの？ | どこで使う？ |
| --- | --- | --- | --- |
| `cakephp/bake` | 3.8.1 | コマンド 1 行で Controller・Model・画面のファイルを自動で作る **CakePHP のプラグイン**。 | `bin/cake bake model Users` など |
| `cakephp/debug_kit` | 5.2.4 | 開発中、画面の右下に出るアイコン。どんな処理や SQL が動いたかを見られる **CakePHP のプラグイン**。 | ブラウザの画面（`debug` が `true` のときだけ） |
| `cakephp/cakephp-codesniffer` | 5.3.2 | コードの書き方が CakePHP のルールどおりかをチェックする。 | `composer cs-check`、`composer cs-fix`（設定は `phpcs.xml`） |
| `phpunit/phpunit` | 11.5.57 | 自動チェック（テスト）を動かす道具。 | `composer test`（設定は `phpunit.xml.dist`。G チーム） |
| `josegonzalez/dotenv` | 4.0.0 | `.env` ファイルの設定を読みこむ部品。 | 入ってはいるが、今は使っていない（`config/bootstrap.php` に読みこむ処理がない） |

> **CakePHP のプラグイン** として読みこんでいるのは、`config/plugins.php` に書いてある
> `DebugKit`・`Bake`・`Migrations` の 3 つです。

### 1-3. 上の部品といっしょに自動で入る部品

上の部品が動くために必要なので、Composer が自動でいっしょに入れる部品です。
`composer.json` には書いていません。ふだん意識する必要はありません。

| 部品（まとめ） | 主なバージョン | 何をするもの？ | だれが使う？ |
| --- | --- | --- | --- |
| `cakephp/chronos` | 3.5.1 | 日付・時刻をあつかう。 | CakePHP |
| `laminas/laminas-diactoros`、`laminas/laminas-httphandlerrunner` | 3.9.0、2.14.0 | ブラウザとのやりとり（リクエスト・レスポンス）をあつかう。 | CakePHP |
| `league/container` | 5.2.0 | 部品どうしをつなぐ仕組み（DI コンテナ）。 | CakePHP |
| `psr/*`（11 個） | — | PHP の部品どうしが話すための「共通のルール」。 | CakePHP など |
| `composer/ca-bundle` | 1.5.14 | 安全な通信（HTTPS）の証明書を探す。 | CakePHP |
| `cakephp/twig-view`、`twig/twig`、`twig/markdown-extra`、`jasny/twig-extensions` | 2.1.2、3.30.0 など | ファイルのひな形（テンプレート）を作る仕組み。 | Bake |
| `brick/varexporter`、`nikic/php-parser` | 0.7.0、5.9.0 | PHP のコードを読んだり書き出したりする。 | Bake、PHPUnit |
| `doctrine/sql-formatter` | 1.5.4 | SQL を見やすく整える。 | DebugKit |
| `composer/composer` と `composer/*`・`seld/*`・`react/promise`・`justinrainbow/json-schema` など | 2.10.3 など | Composer そのものの部品。 | DebugKit |
| `squizlabs/php_codesniffer`、`slevomat/coding-standard`、`dealerdirect/phpcodesniffer-composer-installer`、`phpstan/phpdoc-parser` | 4.0.4、8.31.1 など | 書き方チェックの本体と、追加のルール。 | CakePHP CodeSniffer |
| `phpunit/*`、`sebastian/*`、`phar-io/*`、`myclabs/deep-copy`、`theseer/tokenizer`、`staabm/side-effects-detector` | — | テストの部品。 | PHPUnit |
| `m1/env` | 2.2.0 | `.env` ファイルを読む部品。 | dotenv |
| `symfony/*`（`console`、`process`、`string`、`polyfill-*` など） | 7.4 系、1.x 系 | コマンド画面・ファイル操作・文字列などの部品。 | Composer、Twig など |

> 全部の部品とバージョンは、PowerShell で `composer show` を実行すると見られます
> （`cd C:\xampp\htdocs\anabuki2026` をしてから実行します）。

### ライセンス（使ってよい条件）

1 の部品は、すべて **MIT** か **BSD-3-Clause** というライセンスです。
どちらも「自由に使ってよい。ただし、作った人の名前とライセンスの文は消さない」という意味です。

---

## 2. Composer を使わずに置いている部品（CSS・フォント）

`webroot/` に、ファイルをそのまま置いている部品です。他の人が作ったものなので、中身は編集しません。
見た目を変えたいときは、E チームが自分たちの CSS ファイルで上書きします。

| 部品 | バージョン | 何をするもの？ | 置き場所 | ライセンス |
| --- | --- | --- | --- | --- |
| Milligram | 1.4.1 | シンプルできれいな見た目にしてくれる CSS。 | `webroot/css/milligram.min.css` | MIT |
| normalize.css | 8.0.1 | ブラウザごとの見た目のちがいをそろえる CSS。 | `webroot/css/normalize.min.css` | MIT |
| Raleway（フォント） | — | 英数字の文字のデザイン。`webroot/css/fonts.css` で読みこんでいる。 | `webroot/font/raleway-*.woff2` | SIL Open Font License（`webroot/font/Raleway-License.txt`） |
| CakeDingbats（アイコンフォント） | — | CakePHP の最初の画面で使うアイコン。 | `webroot/font/cakedingbats-webfont.*` | CakePHP 付属 |

> 今は、インターネット上（CDN）から CSS や JavaScript を読みこんでいる場所はありません。
> JavaScript のライブラリ（jQuery など）も使っていません（`webroot/js/` は空）。

---

## 3. パソコンに入れるソフト

くわしい入れ方は [README の「4. はじめての準備」](../README.md#4-はじめての準備最初の-1-回だけ) を見てください。

| ソフト | バージョン | 何に使う？ |
| --- | --- | --- |
| Git（Git Bash） | 2.x | Git の操作。Git のコマンドを打つ黒い画面もこれ。 |
| PowerShell（Windows PowerShell 5.1） | Windows に最初から入っている | Git 以外のコマンドを打つ黒い画面。 |
| XAMPP | — | PHP と MySQL と Apache がまとめて入っている。`C:\xampp` に入れる。 |
| PHP | **8.4.28 以上の 8.4** | アプリを動かす言語。XAMPP に入っている 8.2.12 は古いので、8.4 に入れかえる。 |
| MySQL | XAMPP に入っているもの | データベース（データベース名 `anabukiit_app`）。 |
| phpMyAdmin | XAMPP に入っているもの | ブラウザでデータベースの中身を見る（<http://localhost/phpmyadmin>）。 |
| Composer | 2.x | 1 の部品をダウンロードする。 |
| VS Code | — | ファイルを編集する。 |
| Figma | — | デザインを作る（デザインだけを担当する人）。 |

---

## 4. PHP の拡張機能

PHP に「追加の機能」を足すものです。XAMPP の `C:\xampp\php\php.ini` で、使う・使わないを切りかえます。

| 拡張機能 | 何に使う？ | メモ |
| --- | --- | --- |
| `zip` | Composer が部品をダウンロードして広げる。 | XAMPP では最初は無効。`;extension=zip` の `;` を消す（README 4-3）。 |
| `intl` | 日付や数字を、国や言語に合わせて表示する。 | CakePHP に必要。 |
| `mbstring` | 日本語などの文字をあつかう。 | CakePHP に必要。 |
| `pdo_mysql` | PHP から MySQL につなぐ。 | 自分のパソコンで使う。 |
| `pdo_sqlite` | PHP から SQLite（ファイル 1 つのデータベース）につなぐ。 | GitHub の自動チェックで、テスト用のデータベースとして使う。 |

---

## 5. VS Code の拡張機能

`.vscode/extensions.json` でおすすめしているものです。フォルダを開くと「インストールしますか？」と出ます。

| 拡張機能 | ID | 何をするもの？ |
| --- | --- | --- |
| Japanese Language Pack | `ms-ceintl.vscode-language-pack-ja` | VS Code を日本語にする。 |
| PHP Intelephense | `bmewburn.vscode-intelephense-client` | PHP の入力の手助け（候補を出す、ミスに波線を引く など）。 |
| EditorConfig for VS Code | `editorconfig.editorconfig` | `.editorconfig` の書き方ルール（字下げなど）を VS Code に伝える。 |
| Git Graph | `mhutchie.git-graph` | ブランチやコミットの流れを図で見る。 |

| 入れなくてよいが、設定だけあるもの | ID | メモ |
| --- | --- | --- |
| Code Spell Checker | `streetsidesoftware.code-spell-checker` | 入れている人向けに、`.vscode/settings.json` でよく出る言葉を登録している。 |

| 入れないでほしいもの | ID | 理由 |
| --- | --- | --- |
| Django | `batisteo.vscode-django` | `templates/` の `.php` を Django のファイルとして扱い、コードに色が付かなくなるため。 |

---

## 6. GitHub の仕組み

| 仕組み | 何をするもの？ | 設定ファイル |
| --- | --- | --- |
| GitHub | チームのファイルを置く場所。Organization は `anabuki2026-2027`。 | — |
| GitHub Actions | プルリクエストを出したときなどに、GitHub が自動でチェックを動かす仕組み。 | `.github/workflows/` |
| Dependabot | 部品（Composer と GitHub Actions）に新しいバージョンが出たら、毎週「更新しませんか？」とプルリクエストを作る。 | `.github/dependabot.yml` |

GitHub Actions の中で使っている、他の人が作った部品（アクション）と道具です。

| アクション・道具 | バージョン | 何をするもの？ | 使っている場所 |
| --- | --- | --- | --- |
| `actions/checkout` | v7 | リポジトリのファイルを取ってくる。 | `ci.yml` |
| `shivammathur/setup-php` | v2 | 決まったバージョンの PHP を用意する（8.4 と 8.5 で試す）。 | `ci.yml` |
| `ramsey/composer-install` | v4 | `composer install` を実行する。 | `ci.yml` |
| `actions/stale` | v10 | 長いあいだ動きのない Issue・プルリクエストに印を付けて、自動で閉じる。 | `stale.yml` |
| PHPStan | 2.1 | プログラムのミスを、動かす前に見つける（レベル 8）。`setup-php` で入れている。 | `ci.yml`（設定は `phpstan.neon`） |
| cs2pr | — | 書き方チェックの結果を、プルリクエストの画面に表示する。`setup-php` で入れている。 | `ci.yml` |

> `main-source-branch-check.yml` は、外部のアクションを使わずに、GitHub に最初からある機能だけで動いています。

---

## 7. AI の道具

くわしくは [AGENTS.md](../AGENTS.md) を見てください。

| 道具 | 何をするもの？ | 設定ファイル |
| --- | --- | --- |
| Claude Code | AI にコードを書いてもらったり、質問したりする道具。 | `.claude/`（`settings.json`、`hooks/guard.php`、`skills/`） |
| Codex（CLI・VS Code の拡張機能） | Claude Code と同じ種類の、AI の道具。 | `.codex/`（`config.toml`、`hooks.json`、`rules/guard.rules`） |

どちらも、`.env` の中身を見ない・git の操作をしない、というルールを守らせる設定をしています。
決まった作業の手順（スキル）は `.agents/skills/` にあり、`setup-check` と `pr-check` の 2 つです。

---

## 8. 設定ファイルだけあるもの・おすすめだけされているもの

### 設定ファイルはあるが、Composer では入れていないもの

| 道具 | 設定ファイル | 今の状態 |
| --- | --- | --- |
| PHPStan | `phpstan.neon` | Composer では入れていない。GitHub の自動チェック（6）でだけ動く。 |
| Psalm | `psalm.xml` | 入れていない。どこでも動いていない（CakePHP のひな形に最初から入っていた設定）。 |

### `composer.json` の `suggest` に書いてあるもの（入れていない）

CakePHP のひな形が「こういう部品もあるよ」とおすすめしているだけで、このアプリには入っていません。
入れたいときは、A チームに相談してください。

| 部品 | 何をするもの？ |
| --- | --- |
| `cakephp/repl` | コマンド画面で、PHP を 1 行ずつためせるようにする。 |
| `dereuromark/cakephp-ide-helper` | Bake で作ったファイルの説明コメントを、コードに合わせて自動で直す。 |
| `markstory/asset_compress` | CSS や JavaScript のファイルをまとめて、小さくする。 |
| `phpstan/phpstan` | 6 の PHPStan を、自分のパソコンでも使えるようにする。 |
