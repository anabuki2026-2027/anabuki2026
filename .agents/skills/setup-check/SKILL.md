---
name: setup-check
description: 授業の「準備チェックリスト」（PHP のバージョン・Git 導入・Composer 導入・GitHub Organization の参加）を確認する。「準備できてる？」「セットアップを確認して」「チェックリストを確認して」と言われたときに使う。
---

# 準備チェック

授業で確認する 4 つの項目を、使っている人のパソコンで確かめる。
README の「✅ ここまでできたかチェック」と同じ内容。

## 1. コマンドで確かめる

次の 2 つは、AI が実行する。

```bash
php -v
composer --version
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
| PHP のバージョン | `PHP 8.2.35` 以上の `8.2.xx` | `8.2.12` なら README「4-2」の 3（PHP の更新）。`8.2` 以外なら、別の PHP が入っている可能性があるので管理チーム（A チーム）に相談 |
| Git 導入 | `git version 2.` で始まる | README「4-1」 |
| Composer 導入 | `Composer version 2.` で始まり、使っている PHP が `C:\xampp\php\php.exe` の 8.2.35 以上 | README「4-4」 |

`user.name` と `user.email` が空なら、README「4-1」の「ユーザー名・メールアドレスの設定」を案内する。

## 2. Organization の参加を確かめる

Organization の参加は、コマンドでは確かめにくい。使っている人に次をお願いする。

1. GitHub にログインした状態で <https://github.com/settings/organizations> を開く。
2. 一覧に `anabuki2026-2027` があれば OK。
3. なければ <https://github.com/orgs/anabuki2026-2027/invitation> を開いて、参加ボタンを押す。
   それでも参加できなければ、自分の GitHub のユーザー名を管理チームに伝える。

> このリポジトリはクローンだけなら Organization に入っていなくてもできる。
> 「クローンできたから大丈夫」ではなく、プッシュのためには参加が必要だと伝える。

## 3. 結果を伝える

最後に、次の形でまとめる。ダメだった項目には、直し方を 1 つだけ短く書く。

```text
| チェック | 結果 |
| --- | --- |
| PHP のバージョン | ✅ 8.2.35 |
| Git 導入 | ✅ 2.xx.x |
| Composer 導入 | ❌ 入っていません → README「4-4」をやってください |
| Organization の参加 | （ブラウザで確認してください） |
```

インストールや PHP の更新は、AI が勝手に行わない。手順を案内するだけにする。
