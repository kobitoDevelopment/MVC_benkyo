<?php
/**
 * =================================================================
 * MVCフレームワークのエントリーポイント（フロントコントローラー）
 * =================================================================
 * 
 * このファイルは全てのHTTPリクエストの入口となり、以下の役割を担います：
 * 1. URLを解析してどのControllerのどのActionを実行するかを決定
 * 2. 該当するコントローラーファイルを読み込み
 * 3. コントローラーのインスタンスを生成してアクションを実行
 * 
 * 【フロントコントローラーパターンとは】
 * すべてのリクエストを単一のエントリーポイントで受け付け、
 * 適切な処理クラス（Controller）に振り分ける設計パターンです。
 * これにより、認証・ログ・セキュリティチェックなどの
 * 共通処理を一箇所で管理できます。
 */

// Composerの自動読み込み機能を有効化
// 名前空間を使ったクラスの自動読み込みが可能になります
require_once __DIR__ . '/../vendor/autoload.php';

// ============================================================
// 1. URLルーティング処理の開始
// ============================================================

// HTTPリクエストの基本情報を取得
$request_uri = $_SERVER['REQUEST_URI'];    // 例: "/login/authenticate?param=value"
$request_method = $_SERVER['REQUEST_METHOD']; // 例: "GET", "POST", "PUT", "DELETE"

/**
 * URLパスの解析処理
 * 
 * 例: "/login/authenticate?param=value" → "/login/authenticate"
 * parse_url()関数でクエリパラメータを除いたパス部分だけを抽出
 */
$path = parse_url($request_uri, PHP_URL_PATH);
$path = trim($path, '/'); // 前後のスラッシュを除去: "login/authenticate"
$segments = explode('/', $path); // スラッシュで分割: ["login", "authenticate"]

// ============================================================
// 2. コントローラーとアクションの決定
// ============================================================

/**
 * MVCの命名規則に基づいたクラス名の生成
 * 
 * URL構造: /controller/action/param1/param2/...
 * 
 * 例:
 * "/" → HomeController::index()
 * "/login" → LoginController::index()
 * "/login/authenticate" → LoginController::authenticate()
 * "/mypage" → MypageController::index()
 */
$controller_name = !empty($segments[0]) ? ucfirst($segments[0]) . 'Controller' : 'HomeController';
$action_name = !empty($segments[1]) ? $segments[1] : 'index';

// URL の3番目以降はパラメータとして扱う
// 例: "/user/profile/123/edit" → params = ["123", "edit"]
$params = array_slice($segments, 2);

// ============================================================
// 3. コントローラーファイルの読み込みと実行
// ============================================================

// 対応するコントローラーファイルのパスを構築
$controller_file = __DIR__ . '/../app/controllers/' . $controller_name . '.php';

// ファイル存在確認とクラスの動的読み込み
if (file_exists($controller_file)) {
    // コントローラーファイルを読み込み
    require_once $controller_file;
    
    // クラスが正しく定義されているか確認
    if (class_exists($controller_name)) {
        // コントローラーのインスタンスを生成
        $controller = new $controller_name();
        
        // 指定されたアクション（メソッド）が存在するか確認
        if (method_exists($controller, $action_name)) {
            /**
             * アクションの実行
             * 
             * call_user_func_array()を使用することで、
             * URLパラメータをメソッドの引数として渡すことができます
             * 
             * 例: LoginController::authenticate($param1, $param2)
             */
            call_user_func_array([$controller, $action_name], $params);
        } else {
            // 指定されたアクションが見つからない場合のエラーハンドリング
            header("HTTP/1.0 404 Not Found");
            echo "404 Not Found - Action '{$action_name}' not found in {$controller_name}";
        }
    } else {
        // コントローラークラスが定義されていない場合のエラーハンドリング
        header("HTTP/1.0 404 Not Found");
        echo "404 Not Found - Controller class '{$controller_name}' not found";
    }
} else {
    // コントローラーファイルが存在しない場合のエラーハンドリング
    header("HTTP/1.0 404 Not Found");
    echo "404 Not Found - Controller file '{$controller_file}' not found";
}

/**
 * =================================================================
 * 【学習ポイント】このルーティングシステムの特徴
 * =================================================================
 * 
 * 1. 【シンプルさ】
 *    - 複雑な設定ファイルなしでURL→Controllerのマッピングが可能
 *    - 命名規則に従えば自動的にルーティングされる
 * 
 * 2. 【拡張性】
 *    - 新しいControllerを追加するだけで新しいURLが使える
 *    - パラメータの受け渡しも自動的に処理される
 * 
 * 3. 【制限事項】
 *    - RESTfulなルーティング（GET /users/123 と PUT /users/123 の区別）は未対応
 *    - 複雑なURL パターンマッチングは困難
 * 
 * 4. 【改善案】
 *    - ルーティング設定ファイルの導入
 *    - HTTPメソッドに応じた処理の分岐
 *    - 正規表現を使ったより柔軟なパターンマッチング
 */