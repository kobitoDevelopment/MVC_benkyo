<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/Database.php';

use App\Core\Database;

// マイグレーションディレクトリ
$migrations_dir = __DIR__ . '/migrations/';

// データベース接続を取得
$db = Database::getInstance()->getConnection();

// migrationsテーブルが存在しない場合は作成
$db->exec("
    CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL,
        executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )
");

// 実行済みのマイグレーションを取得
$executed = $db->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);

// マイグレーションファイルを取得
$migration_files = glob($migrations_dir . '*.php');
sort($migration_files);

foreach ($migration_files as $file) {
    $migration_name = basename($file);
    
    // 既に実行済みの場合はスキップ
    if (in_array($migration_name, $executed)) {
        continue;
    }
    
    echo "Running migration: $migration_name\n";
    
    // マイグレーションファイルを読み込んで実行
    require_once $file;
    
    // 実行済みとして記録
    $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
    $stmt->execute(['migration' => $migration_name]);
    
    echo "Completed: $migration_name\n";
}

echo "All migrations completed.\n";