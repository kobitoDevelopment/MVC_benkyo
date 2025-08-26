<?php

require_once __DIR__ . '/../core/BaseController.php';

use App\Core\BaseController;

class HomeController extends BaseController
{
    public function index()
    {
        // 既にログイン済みならマイページへリダイレクト
        if ($this->isLoggedIn()) {
            $this->redirect('/mypage');
        }
        
        $data = [
            'title' => 'ログインシステムのデモ',
            'message' => 'PHPとMySQLで作るMVCフレームワーク'
        ];
        
        $this->render('home/index', $data);
    }
}