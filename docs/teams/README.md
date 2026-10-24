# チーム別ガイド

自分のチームのページを開いてください。
そのチームが「何をするのか」「どのファイルをさわるのか」「どんな順番で作業するのか」が書いてあります。

| チーム | 担当 | ページ |
| --- | --- | --- |
| **A** | 全体の統括・管理 | [team-a.md](team-a.md) |
| **B** | Model | [team-b.md](team-b.md) |
| **C** | View | [team-c.md](team-c.md) |
| **D** | Controller | [team-d.md](team-d.md) |
| **E** | デザイン | [team-e.md](team-e.md) |
| **F** | DB（データベース） | [team-f.md](team-f.md) |
| **G** | テスト | [team-g.md](team-g.md) |

> Git の使い方や毎日の作業の流れは [README](../../README.md) を、
> ファイル 1 つ 1 つの説明は [ファイル解説](../FILES.md) を見てください。

---

## 全チーム共通：1 つの機能ができるまで

たとえば「会員登録」の機能は、次の順番でチームからチームへバトンをわたして作ります。

```text
A（統括）      … 「会員登録を作る」と決めて、表の名前や URL をみんなに伝える
   ↓
F（DB）        … データベースに users（会員）の表を作る
   ↓
B（Model）     … users の表を読み書きするプログラムを作る
   ↓
D（Controller）… 「/users/add が開かれたら会員を登録する」処理を作る
   ↓
C（View）      … 登録フォームの画面を作る
   ↓
E（デザイン）   … 画面の見た目（色・配置・画像）を整える
   ↓
G（テスト）     … ちゃんと動くかをチェックする
```

自分の前のチームが終わるまで待つ必要はありません。
**名前（表の名前・URL・変数の名前）さえ先に決めておけば**、同時に作業を進められます。

---

## 全チーム共通：名前の決まり

CakePHP は「名前の付け方」でファイル同士をつなげています。
**名前が 1 文字でもちがうと動きません。** 下の決まりを全チームで守ってください。

「会員（user）」の場合：

| もの | 名前 | 書き方のルール | 作るチーム |
| --- | --- | --- | --- |
| データベースの表 | `users` | 英語・**複数形**・小文字 | F |
| Table ファイル | `UsersTable.php` | 複数形・先頭が大文字 + `Table` | B |
| Entity ファイル | `User.php` | **単数形**・先頭が大文字 | B |
| Controller ファイル | `UsersController.php` | 複数形・先頭が大文字 + `Controller` | D |
| 画面のフォルダ | `templates/Users/` | Controller と同じ名前 | C |
| URL | `/users/add` | 小文字 | D |

2 つの単語の場合（例：ブログ記事 = blog post）：

| もの | 名前 |
| --- | --- |
| データベースの表 | `blog_posts`（単語の間は `_`） |
| Table ファイル | `BlogPostsTable.php`（単語の先頭を大文字） |
| Entity ファイル | `BlogPost.php` |
| Controller ファイル | `BlogPostsController.php` |
| 画面のフォルダ | `templates/BlogPosts/` |
| URL | `/blog-posts/add`（単語の間は `-`） |

> どの機能にどんな名前を付けるかは、**A チームが一覧にしてみんなに共有**します。

---

## 全チーム共通：ブランチのしくみ

ブランチは 3 段に分かれています。

![ブランチ構成](../images/branch-structure.png)

矢印は「ブランチを作る向き」です。作業をまとめるとき（マージ）は、反対に**下から上へ**プルリクエストを出します。
図の「teamA」は `teamA/main`、「teamB main」は `teamB/main`、「member-1」はメンバーの作業ブランチ（例：`teamB/tanaka-users-model`）のことです。

| ブランチ | だれがマージする？ | どこからプルリクエストが来る？ |
| --- | --- | --- |
| `main` | A チーム | `teamA/main` |
| `teamA/main` | A チーム | 各チームの main（リーダー・副リーダーが出す）と、A チームのメンバーの作業ブランチ |
| `teamB/main` 〜 `teamG/main` | そのチームのリーダー・副リーダー | そのチームのメンバーの作業ブランチ |

### ブランチ名の決まり

`teamX/名前-作業内容` で付けます。X はチームの**大文字** 1 文字、名前と作業内容は英語の小文字とハイフンです。

| チーム | チームの main | メンバーの作業ブランチの例 |
| --- | --- | --- |
| A | `teamA/main` | `teamA/tanaka-update-readme` |
| B | `teamB/main` | `teamB/suzuki-users-model` |
| C | `teamC/main` | `teamC/sato-users-add-page` |
| D | `teamD/main` | `teamD/takahashi-users-controller` |
| E | `teamE/main` | `teamE/ito-top-page-design` |
| F | `teamF/main` | `teamF/watanabe-create-users-table` |
| G | `teamG/main` | `teamG/yamamoto-users-test` |

> ⚠️ `teamB` という名前だけのブランチは作らないでください。
> Git のしくみで、`teamB` があると `teamB/main` や `teamB/○○` が作れなくなります。
>
> ⚠️ チームの文字は**大文字**です（`teamb` ではなく `teamB`）。
> Windows はファイルの名前の大文字・小文字を区別しないので、`teamb/○○` と `teamB/○○` がまざると、Git がおかしな動きをします。

### 他のチームの作業はいつ届く？

他のチームの作業は、**main に入ってから**届きます。

```text
F チームの作業 → teamF/main → teamA/main → main → teamB/main → B チームのメンバー
```

たとえば B チームは、F チームの表が main に入って、B のリーダーが teamB/main に取り込んだあとで使えるようになります。
**急いでほかのチームの作業がほしいときは、A チームに相談**してください。

---

## リーダー・副リーダーの仕事

各チームのリーダー・副リーダーは、メンバーの作業の仕事に加えて、次の 3 つを行います。

### 1. メンバーのプルリクエストをチェックしてマージする

メンバーから、チームの main（例：`teamB/main`）あてにプルリクエストが届きます。
GitHub でプルリクエストを開いて、次を確認します。

- [ ] `base` が**チームの main**（例：`teamB/main`）になっている（`main` になっていたら、右上の「Edit」で直す）
- [ ] 画面下の自動チェック（CI）が **緑のチェック** になっている
- [ ] 「Files changed」に、**自分のチームの担当ではないファイル**が入っていない
- [ ] `vendor/`、`tmp/`、`logs/`、`config/app_local.php` が入っていない
- [ ] 自分のパソコンでそのブランチを動かして、こわれていない

問題なければ **「Merge pull request」** を押します。
直してほしいところがあれば、そのファイルの行にコメントを書いて返します。

### 2. 1 つの機能ができたら、A チームにプルリクエストを出す

チームの main に 1 つの機能（例：会員登録の Model）がそろったら、A チームに提出します。

1. GitHub のリポジトリを開いて、**「Pull requests」→「New pull request」** を押します。
2. 次のように選びます。

   ```text
   base: teamA/main  ←  compare: teamB/main
   ```

3. タイトルに「何の機能か」、説明に「何ができるようになったか」「ほかのチームに伝えたいこと」を書いて、**「Create pull request」** を押します。
4. A チームがチェックして、OK なら teamA/main にマージされます。

> ⚠️ チームの main そのもの（`teamB/main`）は、マージしたあとも**消さないで**ください。
> GitHub に「Delete branch」ボタンが出ても押さないでください（メンバーの作業ブランチは消してOK）。

### 3. main の最新版をチームの main に取り込む

A チームから「main を更新したよ」と連絡が来たら、ほかのチームの作業をチームの main に取り込みます。

1. GitHub で **「Pull requests」→「New pull request」** を押します。
2. 次のように選びます（いつもと**逆向き**です）。

   ```text
   base: teamB/main  ←  compare: main
   ```

3. **「Create pull request」** → **「Merge pull request」** を押します。
4. チームのメンバーに「チームの main を更新したので `git pull` してね」と連絡します。
   `composer.lock` や `config/Migrations/` が変わっていたら、`composer install` や `bin/cake migrations migrate` もお願いします。

> `This branch has conflicts` と出てマージできないときは、自分で直そうとせずに A チームに相談してください。
