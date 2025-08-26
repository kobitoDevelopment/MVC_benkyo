<div class="home-container">
    <h1><?php echo htmlspecialchars($title); ?></h1>
    <p><?php echo htmlspecialchars($message); ?></p>
    
    <div class="login-prompt">
        <p>このアプリケーションは、PHPのMVCパターンを理解するためのシンプルなログインシステムです。</p>
        <a href="/login" class="btn btn-primary">ログインページへ</a>
    </div>
    
    <div class="info">
        <h2>MVC構造について</h2>
        <ul>
            <li><strong>Model</strong>: データとビジネスロジックを管理（User.php）</li>
            <li><strong>View</strong>: ユーザーインターフェースを表示（このファイル）</li>
            <li><strong>Controller</strong>: リクエストを処理し、ModelとViewを連携（HomeController.php）</li>
        </ul>
    </div>
    
    <div class="info">
        <h2>実装されている機能</h2>
        <ul>
            <li>ユーザー認証（ログイン/ログアウト）</li>
            <li>セッション管理</li>
            <li>パスワードのハッシュ化</li>
            <li>ログイン状態の維持</li>
        </ul>
    </div>
</div>

<style>
.home-container {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.login-prompt {
    background-color: #f0f0f0;
    padding: 20px;
    border-radius: 5px;
    text-align: center;
    margin: 30px 0;
}

.info {
    background-color: #e7f3ff;
    padding: 15px;
    border-radius: 5px;
    margin-top: 20px;
}

.btn {
    display: inline-block;
    padding: 10px 30px;
    text-decoration: none;
    border-radius: 3px;
    font-size: 16px;
    margin-top: 10px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}
</style>