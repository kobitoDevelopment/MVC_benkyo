<div class="mypage-container">
    <h1>こんにちは<?php echo htmlspecialchars($username); ?>さん</h1>
    
    <div class="logout-section">
        <form action="/login/logout" method="POST">
            <button type="submit" class="btn btn-danger">ログアウト</button>
        </form>
    </div>
</div>

<style>
.mypage-container {
    max-width: 600px;
    margin: 50px auto;
    padding: 30px;
    background-color: #f9f9f9;
    border-radius: 5px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
}

.mypage-container h1 {
    color: #333;
    margin-bottom: 30px;
}

.logout-section {
    margin-top: 30px;
}

.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 16px;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-danger:hover {
    background-color: #c82333;
}
</style>