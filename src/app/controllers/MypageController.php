<?php

require_once __DIR__ . '/../core/BaseController.php';
require_once __DIR__ . '/../models/User.php';

use App\Core\BaseController;
use App\Models\User;

class MypageController extends BaseController
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new User();
    }
    
    public function index()
    {
        // 認証が必要
        $this->requireAuth();
        
        $this->startSession();
        $user = $this->userModel->findById($_SESSION['user_id']);
        
        $data = [
            'title' => 'マイページ',
            'username' => $user['username']
        ];
        
        $this->render('mypage/index', $data);
    }
}