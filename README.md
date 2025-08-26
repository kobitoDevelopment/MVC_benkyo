# PHP と MySQL で作る MVC フレームワーク

このプロジェクトは、PHP と MySQL を使用して MVC（Model-View-Controller）パターンを理解するための学習用ログインシステムです。

## MVC パターンとは

MVC（Model-View-Controller）は、アプリケーションを 3 つの主要なコンポーネントに分離するデザインパターンです：

### 1. Model（モデル）

- **役割**: データとビジネスロジックを管理
- **責任**: データベースとのやり取り、データの検証、ビジネスルールの実装
- **例**: `User.php` - ユーザー認証、セッション管理

### 2. View（ビュー）

- **役割**: ユーザーインターフェースを表示
- **責任**: HTML の生成、データの表示形式の定義
- **例**: `login/index.php`, `mypage/index.php`

### 3. Controller（コントローラー）

- **役割**: リクエストを処理し、Model と View を連携
- **責任**: URL ルーティング、ユーザー入力の処理、適切な View の選択
- **例**: `LoginController.php`, `MypageController.php`

## アプリケーションの処理フロー

1. **リクエスト受信**: ユーザーが URL にアクセス（例: `/login`）
2. **ルーティング**: `index.php`が URL を解析し、適切な Controller を特定
3. **Controller 処理**: Controller がリクエストを処理
4. **Model 操作**: 必要に応じて Model を使用してデータを取得/更新
5. **View 選択**: Controller が適切な View を選択
6. **レスポンス送信**: View が HTML を生成してユーザーに返す

## ディレクトリ構造

```
src/
├── app/
│   ├── controllers/       # コントローラー
│   │   ├── HomeController.php
│   │   ├── LoginController.php
│   │   └── MypageController.php
│   ├── models/           # モデル
│   │   └── User.php
│   ├── views/            # ビュー
│   │   ├── components/   # 再利用可能なコンポーネント
│   │   │   ├── header.php
│   │   │   └── footer.php
│   │   ├── layouts/      # レイアウトテンプレート
│   │   │   └── main.php
│   │   ├── home/
│   │   │   └── index.php
│   │   ├── login/
│   │   │   └── index.php
│   │   └── mypage/
│   │       └── index.php
│   ├── core/            # フレームワークのコア機能
│   │   ├── BaseController.php
│   │   └── Database.php
│   ├── helpers/         # 汎用ヘルパークラス
│   │   ├── CsrfHelper.php      # CSRF対策
│   │   └── Validator.php       # バリデーション機能
│   └── config/          # 設定ファイル
│       └── database.php
├── public/              # 公開ディレクトリ
│   ├── index.php       # エントリーポイント
│   └── .htaccess       # URL書き換え設定
├── database/           # データベース関連
│   ├── migrations/     # マイグレーションファイル
│   └── migrate.php     # マイグレーション実行スクリプト
└── autoload.php        # 手作りオートローダー

```

## 環境構築手順

### 1. Docker イメージのビルドとコンテナの起動

```bash
docker-compose build
docker-compose up -d
```

### 2. データベースマイグレーションの実行

```bash
docker-compose exec php php /var/www/html/database/migrate.php
```

これにより以下のテーブルが作成されます：

- `users`: ユーザー情報（id, username, email, password）
- `sessions`: セッション情報（id, user_id, ip_address, user_agent, last_activity）
- `migrations`: 実行済みマイグレーションの管理

### 3. テストユーザーの作成（オプション）

```bash
docker-compose exec php php /var/www/html/database/seed_test_user.php
```

このコマンドで以下のテストユーザーが作成されます：

- ユーザー名: `testuser`
- パスワード: `password123`

## 使用方法

1. **アプリケーションにアクセス**: http://localhost:8080
2. **ログインページ**: http://localhost:8080/login
3. **テストユーザーでログイン**:
   - ユーザー名: `testuser`
   - パスワード: `password123`
4. **マイページ**: ログイン後に自動的にリダイレクト
5. **ログアウト**: マイページのログアウトボタンをクリック

## URL ルーティング

| URL                   | Controller       | Action       | 説明           |
| --------------------- | ---------------- | ------------ | -------------- |
| `/`                   | HomeController   | index        | トップページ   |
| `/login`              | LoginController  | index        | ログイン画面   |
| `/login/authenticate` | LoginController  | authenticate | ログイン処理   |
| `/login/logout`       | LoginController  | logout       | ログアウト処理 |
| `/mypage`             | MypageController | index        | マイページ     |

## 主要なコンポーネントの説明

### BaseController（基底コントローラー）

- すべてのコントローラーが継承する基本クラス
- 共通機能を提供：
  - `render()`: ビューの描画（XSS 対策・CSRF トークン自動追加）
  - `redirect()`: リダイレクト処理
  - `isLoggedIn()`: ログイン状態の確認
  - `requireAuth()`: 認証が必要なページの保護
  - `verifyCsrfToken()`: CSRF トークンの検証
  - `validator()`: バリデーターインスタンスの作成
  - `jsonResponse()`: JSON 形式のレスポンス出力

### Database（データベース接続）

- シングルトンパターンでデータベース接続を管理
- PDO を使用した安全なデータベース操作

### User Model

- ユーザー認証とセッション管理
- パスワードのハッシュ化（`password_hash()`使用）
- セッション情報のデータベース保存

## セキュリティ対策

1. **パスワードハッシュ化**: BCrypt アルゴリズムを使用
2. **SQL インジェクション対策**: PDO のプリペアドステートメント使用
3. **XSS 対策**: BaseController で全データを自動エスケープ
4. **CSRF 対策**: CSRF トークンによるリクエスト検証
5. **セッション管理**: データベースでセッション情報を管理、セッションハイジャック対策
6. **入力値検証**: Validator クラスによる包括的なバリデーション

## 開発ツール

- **phpMyAdmin**: http://localhost:8081
  - ユーザー名: `user`
  - パスワード: `password`
