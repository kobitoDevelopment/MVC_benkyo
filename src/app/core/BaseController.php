<?php
/**
 * =================================================================
 * BaseController - MVCアーキテクチャの基底コントローラークラス
 * =================================================================
 * 
 * 【役割と設計意図】
 * このクラスは全てのコントローラーが継承する共通基盤を提供します。
 * DRY原則（Don't Repeat Yourself）に従い、重複するコードを排除し、
 * 保守性と一貫性を向上させます。
 * 
 * 【提供する共通機能】
 * 1. ビューレンダリング（テンプレートエンジン的な機能）
 * 2. リダイレクト処理
 * 3. セッション管理
 * 4. 認証・認可機能
 * 5. CSRF対策（Cross-Site Request Forgery）
 * 6. XSS対策（Cross-Site Scripting）
 * 7. バリデーション機能
 * 8. JSON API レスポンス
 * 
 * 【継承する理由】
 * - 全コントローラーで同じセキュリティ対策を適用
 * - 統一されたレスポンス形式を保証
 * - 新機能追加時の変更箇所を最小化
 */

namespace App\Core;

use App\Helpers\CsrfHelper;
use App\Helpers\Validator;

/**
 * ベースコントローラークラス
 * 
 * 【継承関係】
 * BaseController (基底)
 *    ↑
 *    ├── LoginController (認証処理)
 *    ├── MypageController (マイページ)
 *    └── HomeController (トップページ)
 * 
 * 【テンプレートメソッドパターン】
 * 共通処理の骨組みを基底クラスで定義し、
 * 具体的な処理を子クラスで実装する設計パターンを採用
 */
class BaseController
{
    /**
     * ビューをレンダリングする（MVCのV部分）
     * 
     * 【この機能の重要性】
     * 1. データとプレゼンテーション層の分離
     * 2. セキュリティ対策の自動適用
     * 3. 統一されたレイアウトシステム
     * 4. 開発効率の向上
     * 
     * @param string $view ビュー名（例: "login/index", "mypage/index"）
     * @param array $data ビューに渡すデータ（連想配列）
     */
    protected function render($view, $data = [])
    {
        // ============================================================
        // 1. セッション開始（HTTPヘッダー出力前に必須）
        // ============================================================
        // セッションはHTTPヘッダーでSet-Cookieを使用するため、
        // HTMLの出力前に開始する必要があります
        $this->startSession();
        
        // ============================================================
        // 2. XSS攻撃対策：データの自動エスケープ処理
        // ============================================================
        // ビューに渡される全データを自動的にHTMLエスケープします
        // これにより、悪意のあるスクリプトの実行を防ぎます
        // 例: "<script>alert('XSS')</script>" → "&lt;script&gt;alert('XSS')&lt;/script&gt;"
        $data = $this->escapeData($data);
        
        // ============================================================
        // 3. CSRF攻撃対策：トークンの自動追加
        // ============================================================
        // 全てのビューでCSRFトークンを使用可能にします
        // $csrf_token: トークン値（隠しフィールドの値として使用）
        // $csrf_input: 完成したHTMLの隠しフィールド（直接出力可能）
        $data['csrf_token'] = CsrfHelper::getToken();
        $data['csrf_input'] = CsrfHelper::getHiddenInput();
        
        // ============================================================
        // 4. データの展開（PHPテンプレート機能）
        // ============================================================
        // extract()により連想配列のキーが変数名になります
        // 例: ['title' => 'ログイン', 'error' => ''] → $title, $error変数が使用可能
        // これにより、ビューファイル内で直接変数を使用できます
        extract($data);
        
        // ============================================================
        // 5. ビューファイルの読み込みとレンダリング
        // ============================================================
        // 指定されたビューファイルのパスを構築
        $view_file = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($view_file)) {
            // レイアウトファイル（main.php）を使用してレンダリング
            // レイアウトファイル内で$view_fileがインクルードされます
            include __DIR__ . '/../views/layouts/main.php';
        } else {
            // デバッグ用：ビューファイルが見つからない場合のエラー表示
            echo "View file not found: " . htmlspecialchars($view);
        }
    }
    
    /**
     * HTTPリダイレクト処理
     * 
     * 【使用場面】
     * - ログイン成功後のマイページへの遷移
     * - 未認証ユーザーのログインページへの誘導
     * - フォーム送信後のPRG（Post-Redirect-Get）パターンの実装
     * 
     * @param string $url リダイレクト先URL（例: "/login", "/mypage"）
     */
    protected function redirect($url)
    {
        // HTTPレスポンスヘッダーでリダイレクト指示を送信
        header('Location: ' . $url);
        
        // スクリプトの実行を停止（重要：これがないと後続処理が実行される）
        // exit()により、リダイレクト後の不要な処理実行を防ぎます
        exit();
    }
    
    /**
     * セッションの安全な開始
     * 
     * 【重要なポイント】
     * セッションの多重開始を防ぐための条件分岐を実装
     * session_start()を複数回呼ぶとWarningが発生するため、
     * session_status()で状態をチェックしてから開始します
     */
    protected function startSession()
    {
        // セッションがまだ開始されていない場合のみ開始
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * ユーザーのログイン状態チェック
     * 
     * 【認証の仕組み】
     * セッション変数 $_SESSION['user_id'] の存在でログイン状態を判断
     * これはログイン成功時にLoginController::authenticate()で設定されます
     * 
     * 【セキュリティ考慮事項】
     * - セッションハイジャック対策: LoginControllerでsession_regenerate_id()を実行
     * - セッション固定化攻撃対策: セッションIDの定期的な再生成
     * 
     * @return bool ログイン済みの場合true、未ログインの場合false
     */
    protected function isLoggedIn()
    {
        $this->startSession();
        return isset($_SESSION['user_id']);
    }
    
    /**
     * 認証が必要なページでのアクセス制御（認可機能）
     * 
     * 【ガードクローズパターン】
     * 条件を満たさない場合に早期リターンする設計パターンを採用
     * これにより、後続の処理が確実に認証済みユーザーに対してのみ実行されます
     * 
     * 【使用例】
     * MypageController::index()の冒頭で$this->requireAuth()を呼び出し、
     * 未認証ユーザーのアクセスを自動的にブロックします
     */
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            // 未認証の場合、ログインページにリダイレクト
            $this->redirect('/login');
        }
        // ログイン済みの場合はそのまま処理続行
    }
    
    /**
     * CSRF攻撃対策：トークンの検証
     * 
     * 【CSRF（Cross-Site Request Forgery）攻撃とは】
     * ユーザーが意図しない不正なリクエストを送信させる攻撃手法
     * 例：悪意のあるサイトから銀行サイトへの送金リクエストを偽装
     * 
     * 【対策の仕組み】
     * 1. フォーム送信時に予測困難なトークンを埋め込む
     * 2. リクエスト受信時にトークンの正当性を検証
     * 3. 不正なトークンの場合はリクエストを拒否
     * 
     * @param string|null $token 検証するトークン（nullの場合はPOSTから取得）
     * @return bool 検証結果（true: 正当, false: 不正）
     */
    protected function verifyCsrfToken($token = null): bool
    {
        // トークンが指定されていない場合は、POSTデータから取得
        if ($token === null) {
            $token = $_POST['csrf_token'] ?? '';
        }
        
        // CsrfHelperクラスで実際の検証を実行
        // タイミング攻撃対策としてhash_equals()を使用
        return CsrfHelper::verifyToken($token);
    }
    
    /**
     * バリデーターインスタンスの生成（ファクトリーメソッドパターン）
     * 
     * 【バリデーションの重要性】
     * 1. データ品質の保証
     * 2. セキュリティリスクの軽減
     * 3. ユーザビリティの向上（適切なエラーメッセージ表示）
     * 
     * 【メソッドチェーンによる流麗なAPI】
     * $validator->required('name')->minLength('name', 3)->email('email')
     * のような直感的な記述が可能
     * 
     * @param array|null $data 検証対象のデータ（省略時は$_POSTを使用）
     * @return Validator バリデーターインスタンス
     */
    protected function validator($data = null): Validator
    {
        // データが指定されていない場合はPOSTデータを使用
        if ($data === null) {
            $data = $_POST;
        }
        
        return new Validator($data);
    }
    
    /**
     * データの再帰的HTMLエスケープ処理（XSS攻撃対策）
     * 
     * 【XSS（Cross-Site Scripting）攻撃とは】
     * 悪意のあるJavaScriptコードをWebページに埋め込み、
     * 他のユーザーのブラウザで実行させる攻撃手法
     * 
     * 【対策の仕組み】
     * HTMLの特殊文字（<, >, &, "など）をHTMLエンティティに変換
     * 例: "<script>" → "&lt;script&gt;"
     * 
     * 【再帰処理の必要性】
     * 配列の中に配列が含まれている場合も対応するため
     * 
     * @param mixed $data エスケープ対象のデータ
     * @return mixed エスケープ後のデータ
     */
    private function escapeData($data)
    {
        // 配列の場合：各要素に対して再帰的にエスケープ処理
        if (is_array($data)) {
            return array_map([$this, 'escapeData'], $data);
        }
        
        // 文字列の場合：HTMLエスケープを適用
        if (is_string($data)) {
            return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        }
        
        // その他の型（数値、真偽値など）はそのまま返す
        return $data;
    }
    
    /**
     * JSON API レスポンスの生成
     * 
     * 【RESTful API対応】
     * SPAの開発やマイクロサービス連携に対応する
     * 統一されたJSONレスポンス形式を提供
     * 
     * 【適切なHTTPステータスコードの設定】
     * - 200: 成功
     * - 400: クライアントエラー（バリデーションエラーなど）
     * - 401: 認証エラー
     * - 403: 認可エラー
     * - 500: サーバーエラー
     * 
     * @param array $data レスポンスデータ
     * @param int $statusCode HTTPステータスコード
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        // HTTPステータスコードを設定
        http_response_code($statusCode);
        
        // JSON形式のContent-Typeヘッダーを送信
        // charset=utf-8により日本語の文字化けを防ぐ
        header('Content-Type: application/json; charset=utf-8');
        
        // JSON_UNESCAPED_UNICODEで日本語を正しく出力
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        
        // レスポンス送信後にスクリプト終了
        exit();
    }
}

/**
 * =================================================================
 * 【学習ポイント】BaseControllerの設計パターンとベストプラクティス
 * =================================================================
 * 
 * 1. 【継承による共通化】
 *    - DRY原則の実践（Don't Repeat Yourself）
 *    - 保守性の向上（修正は1箇所だけ）
 *    - 一貫性の保証（全コントローラーで同じ動作）
 * 
 * 2. 【セキュリティ・バイ・デザイン】
 *    - XSS対策の自動適用
 *    - CSRF対策の自動適用
 *    - 認証・認可機能の統一
 * 
 * 3. 【protectedメソッドの使用】
 *    - 継承クラスからのみアクセス可能
 *    - 外部からの不正な呼び出しを防止
 *    - カプセル化の実現
 * 
 * 4. 【エラーハンドリング】
 *    - 適切なHTTPステータスコードの設定
 *    - ユーザーフレンドリーなエラーメッセージ
 *    - セキュリティを考慮した情報の制限
 * 
 * 5. 【拡張性の確保】
 *    - 新機能の追加が容易
 *    - 既存コードへの影響を最小化
 *    - フレームワークのような柔軟性
 */