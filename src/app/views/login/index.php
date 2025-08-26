<div class="login-container">
    <h1>ログイン</h1>
    
    <?php if (!empty($error)): ?>
        <div class="error-message">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    
    <form action="/login/authenticate" method="POST" class="login-form">
        <!-- CSRF対策: セキュリティトークンを送信 -->
        <?php echo $csrf_input; ?>
        
        <div class="form-group">
            <label for="username">ユーザー名:</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo htmlspecialchars($old_input['username'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" 
                   required autofocus>
        </div>
        
        <div class="form-group">
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" required>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">ログイン</button>
        </div>
    </form>
    
    <div class="test-info">
        <p>テストユーザー情報:</p>
        <ul>
            <li>ユーザー名: testuser</li>
            <li>パスワード: password123</li>
        </ul>
    </div>
</div>

<style>
.login-container {
    max-width: 400px;
    margin: 50px auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.login-form {
    margin-top: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 3px;
    font-size: 16px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 16px;
}

.btn-primary {
    background-color: #007bff;
    color: white;
}

.btn-primary:hover {
    background-color: #0056b3;
}

.error-message {
    background-color: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 3px;
    margin-bottom: 15px;
}

.test-info {
    margin-top: 20px;
    padding: 15px;
    background-color: #d4edda;
    border-radius: 3px;
    font-size: 14px;
}

.test-info ul {
    margin: 5px 0;
    padding-left: 20px;
}
</style>