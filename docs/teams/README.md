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

## 全チーム共通：ブランチ名の例

README のルール（`feature/名前-作業内容`）どおりに付けます。

| チーム | 例 |
| --- | --- |
| A | `feature/tanaka-update-readme` |
| B | `feature/suzuki-users-model` |
| C | `feature/sato-users-add-page` |
| D | `feature/takahashi-users-controller` |
| E | `feature/ito-top-page-design` |
| F | `feature/watanabe-create-users-table` |
| G | `feature/yamamoto-users-test` |
