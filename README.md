# NextLog

Backlog連携とAI計画生成機能を持つタスク管理アプリケーション。

## 技術スタック

- Laravel 12 / PHP 8.2+
- Blade / Alpine.js / Tailwind CSS v3
- SQLite (開発) / MySQL (本番)
- Laravel Breeze (認証)
- Docker / Laravel Sail

## セットアップ

```bash
cp .env.example .env

./vendor/bin/sail up -d
./vendor/bin/sail composer install
./vendor/bin/sail npm install
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail npm run dev
```

## 開発コマンド

```bash
# コンテナ起動
./vendor/bin/sail up -d

# フロントエンド開発サーバー
./vendor/bin/sail npm run dev

# テスト
./vendor/bin/sail artisan test

# コードフォーマット
./vendor/bin/sail bin/pint

# コンテナ停止
./vendor/bin/sail down
```

## ディレクトリ構成

```
app/
├── Http/Controllers/    # コントローラー
├── Models/              # Eloquentモデル
├── Services/            # ビジネスロジック
└── View/                # Viewコンポーザ

resources/
├── views/               # Bladeテンプレート
└── css/app.css          # スタイル定義
```

## テーマ

5色のカラーテーマに対応:

- Lavender (紫)
- Mint (緑)
- Peach (オレンジ)
- Sky (青) - デフォルト
- Rose (ピンク)

## ライセンス

MIT
