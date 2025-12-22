# LASK - Backlog連携タスク管理システム

Backlogとの連携とAI計画生成機能を備えた学習・タスク管理アプリケーション

## 🚀 技術スタック

- **Backend**: Laravel 12 / PHP 8.2+
- **Frontend**: Blade / Alpine.js / Tailwind CSS v3
- **Database**: SQLite (開発) / MySQL (本番)
- **Auth**: Laravel Breeze
- **DevOps**: Docker / Laravel Sail

## 📦 セットアップ

```bash
# 環境設定
cp .env.example .env

# Dockerコンテナ起動
./vendor/bin/sail up -d

# 依存パッケージのインストール（初回のみ）
./vendor/bin/sail composer install
./vendor/bin/sail npm install

# アプリケーションキー生成
./vendor/bin/sail artisan key:generate

# データベースマイグレーション
./vendor/bin/sail artisan migrate

# 開発サーバー起動（フロントエンド）
./vendor/bin/sail npm run dev
```

## 🔧 開発コマンド

```bash
# Dockerコンテナ起動
./vendor/bin/sail up -d

# フロントエンドビルド（開発モード）
./vendor/bin/sail npm run dev

# テスト実行
./vendor/bin/sail artisan test

# フォーマット
./vendor/bin/sail bin/pint

# コンテナ停止
./vendor/bin/sail down
```

## 📁 プロジェクト構造

```
app/
├── Http/Controllers/    # コントローラー
├── Models/              # Eloquentモデル
├── Services/            # ビジネスロジック
│   └── BacklogMockService.php  # Backlog API モック（本番実装に置換）
└── View/                # Viewコンポーザ

resources/
├── views/               # Bladeビュー (31コンポーネント)
└── css/app.css          # デザインシステム (5テーマ対応)
```

## 🎨 テーマ

5つのカラーテーマをサポート:
- Lavender (紫)
- Mint (緑)
- Peach (オレンジ)
- Sky (青) - デフォルト
- Rose (ピンク)

## 📝 今後の実装予定

- [ ] Backlog API 実連携
- [ ] AI計画生成（LLM連携）
- [ ] リアルタイム通知
- [ ] ポモドーロタイマー

## 📄 ライセンス

MIT License
