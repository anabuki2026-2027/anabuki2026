---
name: setup-check
description: 授業の「準備チェックリスト」（Docker 導入・Git 導入・GitHub Organization の参加）と、アプリが動くかを確認する。「準備できてる？」「セットアップを確認して」「チェックリストを確認して」と言われたときに使う。
---

# 準備チェック

授業で確認する 3 つの項目と、アプリが動くかを、使っている人のパソコンで確かめる。
README の「✅ ここまでできたかチェック」と同じ内容。

## 1. コマンドで確かめる

次の 2 つは、AI が実行する（git ではないので、PowerShell で動かす）。

```powershell
docker --version
docker compose version
```

Git は、**AI は実行しない**（git の操作はすべて人が行うルール。`AGENTS.md`）。
使っている人に、Git Bash で次の 3 行を実行して、結果を貼ってもらう。

```bash
git --version
git config --global user.name
git config --global user.email
```

| チェック | OK の条件 | ダメなときに案内する場所 |
| --- | --- | --- |
| Docker 導入 | `Docker version` で始まる行と、`Docker Compose version` で始まる行が出る | 「認識されません」なら README「4-2」。`error during connect` などなら、Docker Desktop を起動してもらう |
| Git 導入 | `git version 2.` で始まる | README「4-1」 |

`user.name` と `user.email` が空なら、README「4-1」の「ユーザー名・メールアドレスの設定」を案内する。

## 2. アプリが動くかを確かめる

リポジトリをクローンずみ（`C:\xampp\htdocs\anabuki2026` がある）なら、AI が次を実行する。

```powershell
cd C:\xampp\htdocs\anabuki2026
docker compose ps
curl.exe -s -o NUL -w "%{http_code} %{time_total}" http://localhost:8765/
```

| 結果 | 意味と案内 |
| --- | --- |
| `web`・`db`・`phpmyadmin` が `Up`、`db` が `healthy`、curl が `200` | OK |
| `.env がありません` と出る | README「4-6」（`.env` は AI が作らない。本人にコピーしてもらう） |
| 箱が出てこない・`Up` でない | `docker compose up -d` を実行してよいか聞いてから実行する |
| curl が `500` や `000` | `docker compose logs web --tail 30` を見て原因を説明する。`vendor/autoload.php` がないなら README「4-7」の `composer install` |

`.env` の値が表示されるコマンド（`docker compose config`、`docker inspect`、箱の中の `env` など）は実行しない（`AGENTS.md`）。

## 3. Organization の参加を確かめる

Organization の参加は、コマンドでは確かめにくい。使っている人に次をお願いする。

1. GitHub にログインした状態で <https://github.com/settings/organizations> を開く。
2. 一覧に `anabuki2026-2027` があれば OK。
3. なければ <https://github.com/orgs/anabuki2026-2027/invitation> を開いて、参加ボタンを押す。
   それでも参加できなければ、自分の GitHub のユーザー名を管理チームに伝える。

> このリポジトリはクローンだけなら Organization に入っていなくてもできる。
> 「クローンできたから大丈夫」ではなく、プッシュのためには参加が必要だと伝える。

## 4. 結果を伝える

最後に、次の形でまとめる。ダメだった項目には、直し方を 1 つだけ短く書く。

```text
| チェック | 結果 |
| --- | --- |
| Docker 導入 | ✅ 29.x.x（Compose v5.x.x） |
| Git 導入 | ✅ 2.xx.x |
| アプリが動くか | ❌ .env がありません → README「4-6」をやってください |
| Organization の参加 | （ブラウザで確認してください） |
```

Docker Desktop のインストールや、BIOS の設定（仮想化）は、AI が勝手に行わない。手順を案内するだけにする。
