<?php
/**
 * =================================================================
 * 手作りオートローダー
 * =================================================================
 * 
 * PSR-4規格に基づいたクラス自動読み込み機能を提供します。
 * Composerを使わずにクラスファイルの自動読み込みを実現します。
 * 
 * 【PSR-4とは】
 * PHP Standard Recommendation 4の略で、
 * 名前空間とファイルパスのマッピング規約です。
 * 
 * 例: App\Controllers\HomeController
 * → app/Controllers/HomeController.php
 */

/**
 * オートローダー関数の登録
 * 
 * spl_autoload_register()により、クラスが使用される際に
 * 自動的にこの関数が呼び出されます。
 */
spl_autoload_register(function ($class_name) {
    // 名前空間のマッピング設定
    $namespace_mappings = [
        'App\\' => __DIR__ . '/app/',
    ];
    
    // 各名前空間マッピングをチェック
    foreach ($namespace_mappings as $namespace => $base_dir) {
        // クラス名が対象の名前空間で始まるかチェック
        if (strpos($class_name, $namespace) === 0) {
            // 名前空間プレフィックスを除去
            $relative_class = substr($class_name, strlen($namespace));
            
            // バックスラッシュをディレクトリセパレータに変換
            $file_path = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
            
            // ファイルが存在する場合は読み込み
            if (file_exists($file_path)) {
                require_once $file_path;
                return;
            }
        }
    }
});

/**
 * 個別ファイルの自動読み込み
 * 
 * Composerのcomposer.jsonのfilesセクションと同等の機能
 * 特定のファイルを事前に読み込みます。
 */
$files_to_autoload = [
    __DIR__ . '/app/core/Database.php'
];

foreach ($files_to_autoload as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}