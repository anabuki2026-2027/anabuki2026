# G チーム（テスト）

[← チーム別ガイドにもどる](README.md)

## ひとことで

**アプリが「ちゃんと動くか」をチェックするチームです。**
チェックのしかたは 2 つあります。

| チェックのしかた | どうやる？ |
| --- | --- |
| **自動チェック（テスト）** | 「このページを開いたら、この文字が出るはず」というプログラムを書いて、コマンド 1 つでまとめてチェックする |
| **手でチェック** | 実際にブラウザでアプリをさわって、おかしいところがないか確かめる |

自動チェックを書いておくと、だれかが別の場所を変えたときに「前は動いていたのに、こわれた！」にすぐ気づけます。

---

## さわるファイル・フォルダ

| ファイル・フォルダ | 何をするもの？ |
| --- | --- |
| `tests/TestCase/` | 自動チェックのプログラムを入れる。`src/` と同じ形にそろえる。 |
| `tests/Fixture/` | チェックで使う「ためしのデータ」を入れる。 |
| `tests/bootstrap.php` | チェックを始める前の準備。 |
| `phpunit.xml.dist` | 自動チェックの道具（PHPUnit）の設定。 |

ファイルの場所は、チェックしたいファイルと同じ形にします。

| チェックしたいファイル | チェックのファイル |
| --- | --- |
| `src/Controller/UsersController.php` | `tests/TestCase/Controller/UsersControllerTest.php` |
| `src/Model/Table/UsersTable.php` | `tests/TestCase/Model/Table/UsersTableTest.php` |

---

## 自動チェックを動かしてみる

まずは、もとから入っているチェックを動かしてみましょう。

```bash
composer test
```

最後に **`OK`** と緑色で出れば、全部のチェックに合格です。
**`FAILURES!`** や **`ERRORS!`** と赤色で出たら、どこかがこわれています。

> チェック用のデータベースは自動で用意される（`tmp/tests.sqlite`）ので、準備はいりません。
> ふだん使っているデータベースのデータが消えることもありません。

1 つのファイルだけチェックしたいときは、こう書きます。

```bash
vendor/bin/phpunit tests/TestCase/Controller/UsersControllerTest.php
```

---

## 作業の流れ

### 1. ひな形を用意する

B チームが `bin/cake bake model` を使うと、テストのひな形も一緒に作られます。
ないときは、自分で作ります。

```bash
bin/cake bake test Controller Users
bin/cake bake test Table Users
bin/cake bake fixture Users
```

### 2. ためしのデータを書く

`tests/Fixture/UsersFixture.php` の中に、チェックで使う会員のデータを書きます。

```php
public function init(): void
{
    $this->records = [
        [
            'id' => 1,
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password',
            'created' => '2026-10-01 10:00:00',
            'modified' => '2026-10-01 10:00:00',
        ],
    ];
    parent::init();
}
```

### 3. チェックを書く

**ページのチェックの例**（`tests/TestCase/Controller/UsersControllerTest.php`）

```php
protected array $fixtures = [
    'app.Users',
];

public function testIndex(): void
{
    $this->get('/users');

    $this->assertResponseOk();
    $this->assertResponseContains('テスト太郎');
}

public function testAddWithEmptyName(): void
{
    $this->enableCsrfToken();
    $this->post('/users/add', ['name' => '', 'email' => 'hanako@example.com']);

    $this->assertResponseContains('名前を入力してください');
}
```

| 書き方 | 意味 |
| --- | --- |
| `$fixtures = ['app.Users']` | `UsersFixture` のためしのデータを使う |
| `$this->get('/users')` | `/users` のページを開く |
| `$this->post('/users/add', [...])` | フォームに入力して送信する |
| `$this->enableCsrfToken()` | フォームを送信するときに必要な「合言葉」を付ける |
| `assertResponseOk()` | ページがちゃんと開けたか |
| `assertResponseContains('文字')` | ページにその文字が出ているか |
| `assertRedirect(['action' => 'index'])` | 別のページに移動したか |

> チェックの名前（`testIndex` など）は、**必ず `test` から始めて**ください。`test` で始まらないとチェックされません。

### 4. 合格するまで確かめる

```bash
composer test
```

---

## 手でチェックするとき

ブラウザで実際にさわって、次のようなことを確かめます。

- [ ] 全部のリンクを押して、エラーにならないか
- [ ] フォームを空のまま送ったら、エラーメッセージが出るか
- [ ] とても長い文字や、記号（`<script>` など）を入れても画面がくずれないか
- [ ] 登録・編集・削除をしたら、一覧にちゃんと反映されるか
- [ ] スマホの大きさの画面でも見やすいか（ブラウザで `F12` →スマホのマークで確認できる）

---

## 不具合を見つけたら

GitHub の **Issue** に書いて、担当のチームに知らせます。

書くこと：

1. **どこで**：開いた URL（例：`/users/add`）
2. **何をしたら**：やったこと（例：名前を空のまま「登録する」を押した）
3. **どうなった**：実際に起きたこと（例：真っ白な画面になった）
4. **どうなるはず**：本当はどうなってほしいか（例：「名前を入力してください」と出る）
5. **スクリーンショット**

どのチームの担当かわからないときは、A チームに聞いてください。

---

## ほかのチームとのつながり

| チーム | やりとり |
| --- | --- |
| B・D | **B・D から受け取る**：作ったプログラムと、「どう動くはずか」。 |
| F（DB） | **F と決める**：ためしのデータの形（表の項目）。 |
| C・E | 画面にどんな文字が出るか、見た目がくずれていないか。 |
| A（統括） | 見つけた不具合を報告して、担当チームに割りふってもらう。 |

---

## 気をつけること

- プルリクエストを出すと、GitHub が自動で同じチェックを動かしてくれます（CI）。プルリクエストの画面で赤い ✕ が出ていたら、どこかのチェックが不合格です。
- チェックが不合格になったとき、**チェックのほうを書きかえて合格させるのはやめてください。** まずはプログラムのほうがまちがっていないか、担当のチームと確かめます。
