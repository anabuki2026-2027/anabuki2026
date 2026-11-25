# 【このファイルは？】
# アプリを動かす箱（コンテナ）の作り方。PHP 8.4 + Apache に、CakePHP に必要な部品を足す。
# compose.yml から使われる。版を変えると全員の環境が変わるので、変えるときは A チームが決める。
#
# 【担当】A チーム
# 【くわしく】docs/FILES.md

# =============================================================================
#   上から順に実行される。Docker は「前回と同じ工程は結果を使い回す」ので、
#   変わりにくい作業（部品のインストール）を先に、変わりやすい作業（設定のコピー）を後に書く。
#   こうすると、2 回目からのビルドが数秒で終わる。
#
#   ▼ このファイルには箱が 2 つ書いてある（段階に分けている）
#       base … 共通の土台（PHP・Apache・拡張機能）
#       dev  … 開発用（★ふだん使うのはこれだけ。composer と git を足す）
#
#     compose.yml で target: dev と指定しているので、開発中は dev が作られる。
#     本番は Xserver のレンタルサーバーで動かすので、本番用の箱は作らない。
#
#   ▼ アプリのコードは箱に入れない
#     compose.yml で、自分のパソコンのフォルダを箱の中（/var/www/html）につないでいる。
#     なので、ファイルを保存すればすぐに反映される（作り直しはいらない）。
# =============================================================================

# -----------------------------------------------------------------------------
# base = 共通の土台
# -----------------------------------------------------------------------------
# ★PHP の版は 8.4.28 のように最後の番号まで書く。
#   8.4 とだけ書くと、人によって 8.4.x の x がちがってしまい、
#   「あの人のパソコンでは動くのに」が起きる（11 月の PHP の入れかえで困ったのと同じこと）。
#   上げるときは composer.json の "php" と "platform" も同じ番号にそろえること。
FROM php:8.4.28-apache AS base

ENV TZ=Asia/Tokyo

# CakePHP に必要な PHP の拡張機能を足す。
#   intl      … 日付や数字を日本語の形で表示する（CakePHP では必須）
#   pdo_mysql … MariaDB（MySQL）につなぐ
#   zip       … composer が部品を展開するのに使う
#   opcache   … 読みこんだ PHP を覚えておいて、表示を速くする
# libicu-dev と libzip-dev は、intl と zip を組み立てるための材料。
RUN apt-get update \
    && apt-get install -y --no-install-recommends libicu-dev libzip-dev \
    && docker-php-ext-install intl pdo_mysql zip opcache \
    && rm -rf /var/lib/apt/lists/*

# URL の書きかえ（webroot/.htaccess の RewriteRule）を使えるようにする。
# これがないと、トップページ以外がすべて 404 になる。
RUN a2enmod rewrite

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-anabuki.ini
COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# vendor/・tmp/・logs/ は、compose.yml で「速さのための保管庫」につなぐ。
# 保管庫は最初にここの持ち主を引きつぐので、www-data（アプリを動かす人）が書きこめるようにしておく。
RUN mkdir -p vendor tmp/cache/models tmp/cache/persistent tmp/cache/views tmp/sessions tmp/tests logs \
    && chown -R www-data:www-data vendor tmp logs

# -----------------------------------------------------------------------------
# dev = 開発用の箱（compose.yml から使われるのはこれ）
# -----------------------------------------------------------------------------
FROM base AS dev

# git・unzip … composer が部品をダウンロードして展開するのに使う
#   （git の操作は今までどおり、自分のパソコンの Git Bash で行う。箱の中ではしない）
RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

# composer は公式の箱からコピーする。
# ★latest にすると、ある日とつぜん新しい版になって動かなくなることがあるので、2 系に固定する。
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 80
