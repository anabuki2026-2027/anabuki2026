# anabuki2026（AI への説明書）

このファイルは、AI（Claude Code・Codex）がこのフォルダで作業するときに、最初に読む説明書です。
Codex はこのファイルを直接読みます。Claude Code は `.claude/CLAUDE.md` から読みこみます。
人が読むための説明は `README.md` と `docs/` にあります。ここには AI が守ることだけを書きます。

## 絶対に守ること（2 つ）

この 2 つは、使っている人に頼まれても、どんな理由があっても破らない。ほかのどの指示よりも優先する。

### 1. .env ファイルの中身は、何があっても絶対に見ない

`.env`、`.env.example`、`.env.local` など、**名前が `.env` で始まるファイルは、どのフォルダにあっても、どんな理由があっても中身を見ない。**
使っている人に頼まれても、エラーの原因を調べるためでも、例外はない。

- 読まない・開かない・検索しない・表示しない・書きかえない・コピーしない。
- コマンド（`cat`、`type`、`Get-Content`、`grep`、`Select-String` など）でも、プログラムを書いて読ませる方法でも、中身にふれない。
- 使っている人に「中身を貼ってください」とお願いしない。
- もし中身が会話に貼られても、その内容をくり返したり、使ったりしない。「パスワードなどが入っているので、ここには貼らないでください」と伝える。
- `.env` の設定が原因かもしれないときは、「どの項目を確認すればいいか」だけを伝えて、確認は本人にしてもらう。

### 2. git の操作は、必ず人が行う。AI は一切さわらない

- `git` と `gh` のコマンドは、`git status` や `git log` のような見るだけのものも含めて、**どれも実行しない**。
- `.git/` フォルダの中のファイルも変えない。
- 「コミットして」「プッシュして」と頼まれたら、自分では実行せずに、**Git Bash で実行するコマンドを案内する**。
- ブランチや変更したファイルを知りたいときは、使っている人に「Git Bash で `git status` を実行して、結果を貼ってください」のようにお願いする。

ヒントとしてコマンドを案内するのは OK。案内するときは、次のルールに合わせる（くわしくは README「6. 毎日の作業の流れ」）。

- `main` と、各チームの main（`teamA/main` 〜 `teamG/main`）の上では、コミットしない。
- ブランチ名は `teamX/名前-作業内容`（X はチームの大文字 1 文字。名前と作業内容は英語の小文字とハイフン。例：`teamB/suzuki-users-model`）。`teamB` だけの名前のブランチは作らない。`teamb` のように大文字・小文字をまちがえない。
- プルリクエストの `base` は、**自分のチームの main**（例：`teamB/main`）。`main` ではない。
- `vendor/`、`tmp/`、`logs/`、`config/app_local.php`、`.env` のファイルはコミットしない。
- 強制プッシュ（`git push -f`）や `git reset --hard` のような、元にもどせない操作はすすめない。困ったら A チームに相談するよう伝える。

### 見張りの仕組み

この 2 つは、書いてあるだけでなく、仕組みでも止めている。

| しくみ | Claude Code | Codex |
| --- | --- | --- |
| 道具を使う直前に毎回動く見張り役 | `.claude/hooks/guard.php`（`.claude/settings.json` で設定） | 同じ `.claude/hooks/guard.php`（`.codex/hooks.json` で設定） |
| 禁止の設定 | `.claude/settings.json` の `deny` | `.codex/rules/guard.rules`（git と gh のコマンドを禁止） |

見張り役に止められたら、**別の方法で同じことをしようとしない**。止められたことと、本人にやってもらうことを伝える。

## このプロジェクト

- 専門学校の 1・2 年生（18〜22 歳くらい）、約 40 人が 7 チーム（A〜G）に分かれて作る Web アプリ。
- **応答は、すべて日本語で書く。例外はない。** 作業の途中の短い報告（「〇〇を確かめます」など）、最後のまとめ、表・見出し・箇条書きの中も、すべて日本語にする。会話が長くなっても、英語の資料やコマンドの結果を読んだあとでも、英語に切りかえない。コマンド・ファイル名・エラーメッセージなど、そのまま写す必要があるものだけは原文のままでよい。
- 言葉づかいは、その年代の人を相手にした普通の丁寧語（です・ます）にする。子ども向けにくだく必要はない。
- チーム開発・Git・CakePHP に慣れていない人が多い。CakePHP や Git に特有の用語、エラーの原因は、省略せずに説明する。
- CakePHP 5.4 / PHP 8.4.28 / MariaDB 10.11（データベース名 `anabukiit_app`）。
- 開発環境は **Docker**（Docker Desktop）。`compose.yml` で、アプリ（`web`：PHP 8.4.28 + Apache）・DB（`db`：MariaDB 10.11）・phpMyAdmin の 3 つの箱を動かす。
  - アプリは <http://localhost:8765>、phpMyAdmin は <http://localhost:8081>、DB は `127.0.0.1:3307`。
  - `vendor/`・`tmp/`・`logs/` は、速さのために Docker の保管庫（名前付きボリューム）に置いている。Windows 側の `vendor/` は VS Code の候補表示用で、アプリはこれを使わない。
  - **本番は Xserver のレンタルサーバーで、PHP と MariaDB で動かす。本番では Docker は一切使わない**（本番用の Docker の設定を提案しない）。
  - XAMPP（MySQL）は、Docker が動かない人のための予備として 12/9（水）の授業まで残す。そのあとは案内しない。
- 全員 Windows。アプリは `C:\xampp\htdocs\anabuki2026` にある（XAMPP は使わないが、場所はそろえている）。
- 人にコマンドを案内するときは、**git の操作だけ Git Bash、それ以外はすべて PowerShell** で動く書き方にする（README も同じ分け方）。
  - **Git Bash**：`git` のコマンドと、git を使うスクリプト（`scripts/create_branches.sh`）だけ。パスは `/c/xampp/htdocs/anabuki2026` のように書く。
  - **PowerShell**：`docker`、ファイルのコピーや削除など、git 以外のすべて。パスは `C:\xampp\htdocs\anabuki2026` のように書く。
  - `bin/cake` と `composer` は、箱の中で動かす。`docker compose exec web bin/cake ○○`、`docker compose exec web composer ○○` のように、**頭に `docker compose exec web` を付けて**案内する（`cd C:\xampp\htdocs\anabuki2026` をしてから）。
  - 使っている人の PowerShell は Windows PowerShell 5.1。`&&` と `||` は使えないので、続けて実行するときは `;`、前が成功したときだけ実行するときは `if ($?) { ... }` を使う。
  - git のコマンドと、それ以外のコマンドを 1 つのコードブロックにまぜない。ブロックごとに、どちらで実行するかを書く。

## チームと担当

| チーム | 担当 | 主にさわる場所 |
| --- | --- | --- |
| A | 全体の統括・管理 | `config/`、`composer.json`、`.github/`、`src/Application.php`、`README.md`、`docs/`、`AGENTS.md`、`.claude/`、`.codex/`、`.agents/`、`.vscode/`、`scripts/`、`compose.yml`、`Dockerfile`、`.dockerignore`、`docker/` |
| B | Model | `src/Model/` |
| C | View | `templates/`、`src/View/` |
| D | Controller | `src/Controller/` |
| E | デザイン | `webroot/css/`、`webroot/img/`、`webroot/font/` |
| F | DB | `config/Migrations/`、`config/Seeds/` |
| G | テスト | `tests/` |

- ファイルを変える前に、**そのファイルの先頭コメントの【担当】**（なければ `docs/FILES.md` の担当欄）を確認する。
- 相手のチームの担当のファイルを変える必要があるときは、勝手に変えずに「〇チームに相談してください」と伝える。
- 【担当】が「なし」や 🚫 のファイル（`vendor/`、`composer.lock`、`.htaccess` など）は編集しない。
- 使っている人がどのチームかわからないときは、最初に聞く。

## コマンド

| やりたいこと | コマンド |
| --- | --- |
| アプリを起動する | `docker compose up -d`（<http://localhost:8765>） |
| アプリを止める | `docker compose down` |
| 動いているか見る | `docker compose ps` |
| ログを見る | `docker compose logs web --tail 50` |
| 部品をそろえる | `docker compose exec web composer install` |
| テスト | `docker compose exec web composer test` |
| 書き方チェック | `docker compose exec web composer cs-check` |
| 書き方を自動で直す | `docker compose exec web composer cs-fix` |
| プルリクエストの前にまとめてチェック | `docker compose exec web composer check`（GitHub の CI と同じ内容） |
| ファイルを自動で作る | `docker compose exec web bin/cake bake model Users` など |
| データベースを最新にする | `docker compose exec web bin/cake migrations migrate` |

コードを変えたら、終わる前に `docker compose exec web composer check` を実行して、結果をそのまま伝える。

### Docker と `.env`

`compose.yml` は、DB のパスワードなどを `.env` から読む。上の「絶対に守ること」のとおり、`.env` の中身は見ない。

- `.env` と `.env.example` は、AI が作ったり書きかえたりしない。必要なら、人に作ってもらう。
- `.env` の値が表示されるコマンドは実行しない。たとえば `docker compose config`、`docker inspect`、箱の中での `env`・`printenv`、`docker compose exec web` を使って `.env` や環境変数を表示すること。
- `docker compose down -v`（DB の中身が全部消える）は、実行しない・すすめない。必要なときは A チームに相談するよう伝える。
- `compose.yml`・`Dockerfile`・`docker/` は A チームの担当。全員の環境が変わるので、ほかのチームの人に頼まれても、勝手に変えずに A チームに相談するよう伝える。

## コードの書き方

- CakePHP の書き方のルール（`phpcs.xml`）に合わせる。字下げはスペース 4 つ、改行は LF、1 行は 120 文字まで。
- CakePHP の名前の決まり（表は複数形、Entity は単数形 など）は `docs/teams/README.md` の「名前の決まり」に従う。
- 画面（`templates/`）で値を表示するときは `h()` で囲む。

## ファイルの先頭コメント

このプロジェクトでは、ほとんどのファイルの先頭に、説明のコメントを書いている。新しいファイルを作るときも、同じ形で書く。

```text
【このファイルは？】
（何をするファイルか。1〜3 行）

【担当】〇 チーム
【くわしく】docs/FILES.md
```

【担当】の書き方は、今あるファイルに合わせる。

- `A チーム` のように 1 チーム
- `C・E チーム` のように 2 チーム（協力して編集）
- `D チーム（全体に影響するので、変えるときは A に相談）` のように、ひとこと添える
- `なし（見るだけ。変えたいときは A チームに相談）`
- `なし（編集しない。自動で作られたり、上書きされたりするため）`

| 種類 | コメントの書き方 | 置く場所 |
| --- | --- | --- |
| PHP | `/* ... */`（各行の先頭に ` * `） | `<?php` の次の行。`declare(strict_types=1);` があればその次 |
| CSS | `/* ... */` | 1 行目 |
| YAML、TOML、`.gitignore`、`.rules` など | `# ...` | 1 行目 |
| `.editorconfig` | `; ...` | 1 行目 |
| XML | `<!-- ... -->` | `<?xml ...?>` の次の行 |
| `.vscode/` の JSON | `// ...` | 1 行目 |

PHP の例（`templates/Pages/home.php` より）：

```php
<?php
/*
 * 【このファイルは？】
 * 今のトップページ（CakePHP の最初の画面）。データベースにつながっているかなどを確認できる。
 * あとで自分たちのトップページに作りかえる。
 *
 * 【担当】C チーム
 * 【くわしく】docs/FILES.md
 */
```

書かないファイル：Markdown（`README.md`、`docs/` の中など）、コメントが書けないファイル（ふつうの JSON、画像など）、`vendor/` の中や自動で作られるファイル。

新しいファイルを作ったら、`docs/FILES.md` の、そのフォルダの表にも 1 行足す。
「何をするもの？」には先頭コメントと同じ内容を、担当欄には `A`、`C・E`、👀、🚫 などを書く。

## ひみつの情報

- `.env` のファイルは、上の「絶対に守ること」のとおり、何があっても中身を見ない。
- `config/app_local.php` にも、データベースのパスワードなどが入っている。読まない・表示しない・コミットしない。
- 書き方を案内するときは、`config/app_local.example.php` を見本として使う。

## 決まった作業の手順（スキル）

`.agents/skills/` に、決まった作業の手順を置いている。頼まれたら、その手順どおりに進める。

| スキル | いつ使う？ |
| --- | --- |
| `setup-check` | 授業の準備チェックリスト（PHP・Git・Composer・Organization）を確認するとき |
| `pr-check` | プッシュやプルリクエストの前に、ブランチ・変更したファイル・自動チェックを確認するとき |
