<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/core/Database.php';

use App\Core\Database;

// データベース接続を取得
$db = Database::getInstance()->getConnection();

try {
    // テストユーザーの情報
    $username = 'testuser';
    $email = 'test@example.com';
    $password = password_hash('password123', PASSWORD_DEFAULT);
    
    // 既存のテストユーザーをチェック
    $stmt = $db->prepare("SELECT id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    
    if ($stmt->fetch()) {
        echo "Test user already exists.\n";
    } else {
        // テストユーザーを作成
        $stmt = $db->prepare("
            INSERT INTO users (username, email, password) 
            VALUES (:username, :email, :password)
        ");
        
        $stmt->execute([
            'username' => $username,
            'email' => $email,
            'password' => $password
        ]);
        
        echo "Test user created successfully!\n";
        echo "Username: $username\n";
        echo "Password: password123\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}