# B チーム（Model）

[← チーム別ガイドにもどる](README.md)

## ひとことで

**データベースとアプリの「橋わたし」をするチームです。**
F チームが作ったデータベースの表を、アプリから読んだり書いたりできるようにします。
「入力のチェック（空欄はダメ、など）」も B チームの仕事です。

> **F チームとのちがい**
> F は「名簿の用紙（表）」を作ります。
> B は「名簿に書きこんだり、名簿から探したりするプログラム」を作ります。

---

## さわるファイル・フォルダ

| フォルダ | 何を入れる？ | 例 |
| --- | --- | --- |
| `src/Model/Table/` | **表全体**をあつかうファイル。データを探す・保存する・入力をチェックする。 | `UsersTable.php` |
| `src/Model/Entity/` | **データ 1 件分**をあつかうファイル。 | `User.php`（会員 1 人分） |
| `src/Model/Behavior/` | いろいろな Table で使い回したい機能。 | |

> たとえると、`Table` は「名簿」全体、`Entity` は名簿の「1 行」です。

---

## 作業の流れ

### 1. F チームが表を作るのを待つ

F チームが作った表は、**main に入って、B のリーダーが `teamB/main` に取り込んだあと**に届きます。
リーダーから「取り込んだよ」と連絡が来たら、最新版をもらって、自分のパソコンにも表を作ります。

```bash
git fetch
git switch teamB/main
git pull
bin/cake migrations migrate
```

> 表の名前と項目は先に決まっているので、表が届くまでの間に、入力チェックの内容などを考えておくとスムーズです。

### 2. Bake でファイルを自動で作る

```bash
bin/cake bake model Users
```

次のファイルが自動で作られます。

| 作られるファイル | だれのもの？ |
| --- | --- |
| `src/Model/Table/UsersTable.php` | B チーム |
| `src/Model/Entity/User.php` | B チーム |
| `tests/TestCase/Model/Table/UsersTableTest.php` | G チーム |
| `tests/Fixture/UsersFixture.php` | G チーム |

> `tests/` の中のファイルも一緒にコミットして大丈夫です。
> プルリクエストを出したら、**G チームに「テストのひな形ができたよ」と伝えて**ください。

### 3. 入力のチェック（バリデーション）を書く

`UsersTable.php` の `validationDefault` の中に、「どんな入力ならOKか」を書きます。
Bake がだいたい作ってくれるので、エラーメッセージを日本語にしたり、ルールを足したりします。

```php
public function validationDefault(Validator $validator): Validator
{
    $validator
        ->scalar('name')
        ->maxLength('name', 50, '名前は 50 文字以内で入力してください')
        ->notEmptyString('name', '名前を入力してください');

    $validator
        ->email('email', false, 'メールアドレスの形が正しくありません')
        ->notEmptyString('email', 'メールアドレスを入力してください');

    return $validator;
}
```

よく使うチェック：

| 書き方 | 意味 |
| --- | --- |
| `notEmptyString('name', 'メッセージ')` | 空欄はダメ |
| `maxLength('name', 50, 'メッセージ')` | 50 文字まで |
| `email('email', false, 'メッセージ')` | メールアドレスの形になっているか |
| `integer('age', 'メッセージ')` | 数字かどうか |

### 4. 表どうしのつながりを書く

「会員は、記事をたくさん書ける」のように、表どうしがつながっているときは `initialize` の中に書きます。

```php
public function initialize(array $config): void
{
    parent::initialize($config);

    $this->hasMany('Posts');
}
```

| 書き方 | 意味 | 例 |
| --- | --- | --- |
| `hasMany('Posts')` | 1 つが、たくさん持っている | 会員は記事をたくさん持つ（`UsersTable` に書く） |
| `belongsTo('Users')` | だれか 1 人のもの | 記事は 1 人の会員のもの（`PostsTable` に書く） |

> `belongsTo` を使うには、表に `user_id` のような項目が必要です。F チームと相談してください。

### 5. Entity で「見せない項目」を決める

パスワードのように、画面やデータに出してはいけない項目は `User.php` の `$_hidden` に書きます。

```php
protected array $_hidden = [
    'password',
];
```

---

## よく使うコマンド

| コマンド | 何が起きる？ |
| --- | --- |
| `bin/cake bake model Users` | `UsersTable.php` と `User.php` を自動で作る |
| `bin/cake migrations migrate` | 自分のパソコンのデータベースを最新にする |
| `composer test` | 自動チェックを実行して、こわれていないか確かめる |

---

## ほかのチームとのつながり

| チーム | やりとり |
| --- | --- |
| F（DB） | **F から受け取る**：表の名前と項目。表に足りない項目があったら F に相談する。 |
| D（Controller） | **D にわたす**：どんな入力チェックがあるか、どんな探し方ができるか。 |
| G（テスト） | **G にわたす**：Bake で作られたテストのひな形。 |

---

## 気をつけること

- 表の名前とファイルの名前がずれていると動きません。[名前の決まり](README.md#全チーム共通名前の決まり)を確認してください。
- このアプリは、**Table ファイルがない表は使えない**設定になっています。D チームが「`UsersTable` がない」というエラーで止まっていたら、B チームの出番です。
- 表の形（項目）を変えたいときは、自分で変えずに F チームにお願いしてください。
