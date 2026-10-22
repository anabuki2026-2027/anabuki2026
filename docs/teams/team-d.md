# D チーム（Controller）

[← チーム別ガイドにもどる](README.md)

## ひとことで

**アプリの「まとめ役」です。**
「この URL が開かれたら、B チームの Model からデータを持ってきて、C チームの画面にわたす」という流れを作ります。
フォームから送られたデータを受け取って、保存するのも D チームの仕事です。

> レストランにたとえると、D チームは**注文を受ける店員**です。
> 注文（URL）を聞いて、厨房（Model）に材料を頼み、盛り付け係（View）に料理をわたします。

---

## さわるファイル・フォルダ

| ファイル・フォルダ | 何をするもの？ |
| --- | --- |
| `src/Controller/○○Controller.php` | 各機能の Controller。例：`UsersController.php` |
| `src/Controller/AppController.php` | すべての Controller の「親」。全体に影響するので、**変えるときは A チームに相談**。 |
| `src/Controller/PagesController.php` | トップページなど、データを使わないページを表示する。 |
| `src/Controller/Component/` | いろいろな Controller で使い回したい機能。 |
| `config/routes.php` | URL の案内表。**A チームと相談して**変える。 |

---

## 作業の流れ

### 1. URL と処理の名前を決める

A チームと相談して、どんな URL を作るか決めます。
CakePHP では、URL の形がそのまま Controller と処理の名前になります。

| URL | Controller | 処理（アクション） | 何をする？ |
| --- | --- | --- | --- |
| `/users` | `UsersController` | `index` | 一覧を出す |
| `/users/view/3` | `UsersController` | `view` | 3 番の会員をくわしく出す |
| `/users/add` | `UsersController` | `add` | 新しく登録する |
| `/users/edit/3` | `UsersController` | `edit` | 3 番の会員を編集する |
| `/users/delete/3` | `UsersController` | `delete` | 3 番の会員を消す |

この形なら `config/routes.php` を変えなくても動きます。
`/login` のような特別な URL を作りたいときだけ、A チームに `routes.php` の変更をお願いします。

### 2. Bake でひな形を作る

B チームの Table ができていれば、上の 5 つの処理を自動で作れます。

```bash
bin/cake bake controller Users
```

### 3. 処理を書く

**一覧の例**

```php
public function index()
{
    $users = $this->paginate($this->Users);
    $this->set(compact('users'));
}
```

- `$this->Users` で、B チームが作った `UsersTable` を使えます。
- `$this->set(compact('users'))` で、`$users` という名前で C チームの画面にデータをわたします。

**登録の例**

```php
public function add()
{
    $user = $this->Users->newEmptyEntity();
    if ($this->request->is('post')) {
        $user = $this->Users->patchEntity($user, $this->request->getData());
        if ($this->Users->save($user)) {
            $this->Flash->success('登録しました');

            return $this->redirect(['action' => 'index']);
        }
        $this->Flash->error('登録できませんでした');
    }
    $this->set(compact('user'));
}
```

| 書き方 | 意味 |
| --- | --- |
| `newEmptyEntity()` | 空っぽのデータ（1 件分）を用意する |
| `$this->request->is('post')` | フォームが送信されたかどうか |
| `$this->request->getData()` | フォームに入力された内容 |
| `patchEntity(...)` | 入力された内容をデータに入れる（このとき B チームの入力チェックが動く） |
| `save(...)` | データベースに保存する |
| `$this->Flash->success('...')` | 次の画面に、緑色のメッセージを 1 回だけ出す |
| `$this->redirect([...])` | 別のページに移動する |

### 4. 表示を確認する

```bash
bin/cake server
```

ブラウザで <http://localhost:8765/users> などを開いて確認します。
C チームの画面がまだなくても、「`Users/index.php` がありません」というエラーが出れば、Controller までは動いています。

---

## よく使うコマンド

| コマンド | 何が起きる？ |
| --- | --- |
| `bin/cake bake controller Users` | `UsersController.php` を自動で作る |
| `bin/cake routes` | 今使える URL の一覧を見る |
| `composer test` | 自動チェックを実行して、こわれていないか確かめる |

---

## ほかのチームとのつながり

| チーム | やりとり |
| --- | --- |
| A（統括） | **A と決める**：URL。`routes.php` を変えたいときも A に相談。 |
| B（Model） | **B から受け取る**：Table ファイル。どんな探し方・入力チェックがあるか。 |
| C（View） | **C にわたす**：ファイルの場所（Controller と処理の名前）と、変数の名前（`$users`、`$user` など）。 |
| G（テスト） | **G にわたす**：どの URL で、何が起きるはずか。 |

---

## 気をつけること

- このアプリは、**Table ファイルがない表は使えない**設定になっています。「`UsersTable` が見つからない」というエラーが出たら、B チームの作業を待ってください。
- 画面の HTML は Controller に書かずに、C チームにまかせます。
- 変数の名前を変えたら、必ず C チームに伝えてください。画面にデータが出なくなります。
- 消す処理（`delete`）は、まちがってリンクを開いただけで消えないよう、Bake が作った形（POST のときだけ動く）のまま使ってください。
