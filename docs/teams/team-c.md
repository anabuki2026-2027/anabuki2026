# C チーム（View）

[← チーム別ガイドにもどる](README.md)

## ひとことで

**ブラウザに表示される画面（HTML）を作るチームです。**
D チームから受け取ったデータを、フォームや一覧の形にして画面に並べます。
色や飾りつけなどの見た目は、E チームと協力して仕上げます。

> **E チームとのちがい**
> C は「画面に何を、どの順番で並べるか（HTML）」を作ります。
> E は「それをどんな色・大きさ・配置で見せるか（CSS・画像）」を作ります。

---

## さわるファイル・フォルダ

| ファイル・フォルダ | 何をするもの？ |
| --- | --- |
| `templates/○○/` | 各ページの中身。例：`templates/Users/add.php` |
| `templates/layout/default.php` | すべてのページに共通の「外わく」（上のメニューなど）。**E チームと協力**。 |
| `templates/layout/error.php` | エラーページの外わく。**E チームと協力**。 |
| `templates/element/` | いろいろなページで使い回す小さな部品（ヘッダー、メッセージなど）。 |
| `templates/Pages/home.php` | トップページ。 |
| `templates/Error/` | 「ページが見つかりません」などのエラー画面。 |
| `templates/email/` | メールの本文。 |
| `src/View/AppView.php` | すべての画面の「親」。 |
| `src/View/Helper/` | 画面で使い回す便利な道具。 |
| `webroot/js/` | 画面に動きをつけるプログラム（JavaScript）。**E チームと協力**。 |

---

## 作業の流れ

### 1. D チームと「ファイルの場所」と「変数の名前」を決める

画面のファイルの場所は、**Controller の名前と処理の名前**で決まります。

| D チームが作る処理 | C チームが作るファイル |
| --- | --- |
| `UsersController` の `index` | `templates/Users/index.php` |
| `UsersController` の `add` | `templates/Users/add.php` |
| `UsersController` の `view` | `templates/Users/view.php` |

また、D チームが画面にわたすデータの**変数の名前**（例：`$users`、`$user`）も聞いておきます。

### 2. 画面を作る

画面のファイルは「HTML の中に、ところどころ PHP が入ったもの」です。

**一覧ページの例**（`templates/Users/index.php`）

```php
<h1>会員一覧</h1>

<ul>
    <?php foreach ($users as $user): ?>
        <li><?= h($user->name) ?></li>
    <?php endforeach; ?>
</ul>

<?= $this->Html->link('新しく登録する', ['action' => 'add']) ?>
```

**登録フォームの例**（`templates/Users/add.php`）

```php
<h1>会員登録</h1>

<?= $this->Form->create($user) ?>
<?= $this->Form->control('name', ['label' => '名前']) ?>
<?= $this->Form->control('email', ['label' => 'メールアドレス']) ?>
<?= $this->Form->button('登録する') ?>
<?= $this->Form->end() ?>
```

`$this->Form` を使うと、B チームが作った入力チェックのエラーメッセージも、自動で入力欄の下に表示されます。

### 3. 表示を確認する

```bash
bin/cake server
```

ブラウザで <http://localhost:8765/users> などを開いて確認します。

---

## よく使う書き方

| 書き方 | 何が起きる？ |
| --- | --- |
| `<?= h($user->name) ?>` | データを画面に出す。**必ず `h()` で囲む**（下の「気をつけること」を参照）。 |
| `<?= $this->Html->link('文字', ['action' => 'add']) ?>` | リンクを作る |
| `<?= $this->Html->image('logo.png', ['alt' => 'ロゴ']) ?>` | `webroot/img/logo.png` を表示する |
| `<?= $this->Form->create($user) ?>` 〜 `<?= $this->Form->end() ?>` | フォームを作る |
| `<?= $this->Form->control('name') ?>` | 入力欄を作る |
| `<?= $this->element('header') ?>` | `templates/element/header.php` の部品をはめこむ |
| `<?php foreach ($users as $user): ?>` 〜 `<?php endforeach; ?>` | データの数だけくりかえす |
| `<?php if ($user->is_admin): ?>` 〜 `<?php endif; ?>` | 条件に合うときだけ表示する |

### Bake で画面のひな形を作る

D チームの Controller と B チームの Table ができていれば、画面のひな形を自動で作れます。

```bash
bin/cake bake template Users
```

`templates/Users/` に `index.php`、`view.php`、`add.php`、`edit.php` ができるので、そこから作りかえると早いです。

---

## ほかのチームとのつながり

| チーム | やりとり |
| --- | --- |
| D（Controller） | **D から受け取る**：ファイルの場所（Controller と処理の名前）、変数の名前。 |
| E（デザイン） | **E と決める**：HTML に付ける `class` の名前。外わく（layout）は一緒に作る。 |
| B（Model） | 入力欄の名前（`name`、`email` など）は、表の項目の名前と同じにする。 |
| G（テスト） | 画面にどんな文字が出るかを伝えておくと、テストを作りやすくなる。 |

---

## 気をつけること

- **データを画面に出すときは、必ず `h()` で囲んでください。**
  囲まないと、悪い人が名前の欄にプログラムを書きこんだとき、それが画面で動いてしまいます（XSS という攻撃）。
- **画面のファイルの中で、データベースを読んだりしないでください。** データは D チームから受け取ります。
- `class` の名前は、E チームと決めた名前を使ってください。名前がずれると、見た目が反映されません。
