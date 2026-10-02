# laratter

Laravel で作った Twitter 風の投稿アプリです。Tweet の投稿・いいね・コメント・フォローに加えて、選んだフォロワーだけに Tweet を公開できる「サークル機能」を実装しています。

## 機能

- ユーザー登録・ログイン・ログアウト
- Tweet の投稿・一覧・詳細・編集・削除
- Tweet のキーワード検索
- いいね（like / dislike）
- コメントの投稿・編集・削除
- フォロー・フォロー解除、プロフィールページ
- サークル機能（独自に追加した機能）

### サークル機能

自分のフォロワーの中から好きな人を選んで「サークル」に入れ、サークルのメンバーだけが閲覧できる Tweet を投稿できます。

- サイドバーの「サークル」から、サークルに入れるフォロワーを選択します（人数の上限なし）。
- Tweet の作成・編集時に、公開範囲を「全体に公開」か「サークルのみ」から選べます。
- 「サークルのみ」の Tweet は、投稿者本人とサークルのメンバーだけに表示されます。一覧・検索・プロフィール・詳細・コメント・いいねのすべてで同じ判定をしています。
- サークル外のユーザーが詳細ページの URL を直接開いても 404 になります。
- フォローを解除したユーザーは、相手のサークルから自動で外れます。

## 使用技術

- PHP 8.2 以上 / Laravel 12
- Livewire（Volt・Flux）
- Tailwind CSS / Vite
- MySQL 8.4
- Laravel Sail（Docker）
- PHPUnit

## セットアップ

Docker が動く環境（Windows の場合は WSL2）が必要です。

```bash
git clone <このリポジトリのURL>
cd laratter
cp .env.example .env
```

`.env` のデータベース設定を Sail 用に書き換えます。

```
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=sail
DB_PASSWORD=password
```

依存パッケージを入れて起動します。

```bash
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html \
    laravelsail/php84-composer:latest composer install --ignore-platform-reqs

./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

- アプリ: http://localhost
- phpMyAdmin: http://localhost:8080

## テスト

```bash
./vendor/bin/sail artisan test
```

## 主なテーブル

| テーブル | 内容 |
|---|---|
| `users` | ユーザー |
| `tweets` | Tweet（`circle_only` が true のものはサークルのみに公開） |
| `tweet_user` | いいね |
| `comments` | コメント |
| `follows` | フォロー関係 |
| `circle_members` | サークルの持ち主（`user_id`）と、サークルに入れたフォロワー（`member_id`） |
