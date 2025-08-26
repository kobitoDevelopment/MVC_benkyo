<?php

namespace App\Helpers;

/**
 * CSRF対策を提供するヘルパークラス
 * Cross-Site Request Forgery攻撃からアプリケーションを保護
 */
class CsrfHelper
{
    /**
     * セッションを安全に開始
     */
    private static function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * CSRFトークンを生成してセッションに保存
     * 
     * @return string 生成されたCSRFトークン
     */
    public static function generateToken(): string
    {
        self::ensureSession();

        // セキュアなランダムトークンを生成
        $token = bin2hex(random_bytes(32));
        
        // セッションにトークンを保存
        $_SESSION['csrf_token'] = $token;
        
        return $token;
    }

    /**
     * CSRFトークンを取得（存在しない場合は新規生成）
     * 
     * @return string CSRFトークン
     */
    public static function getToken(): string
    {
        self::ensureSession();

        // 既存のトークンがあればそれを返す
        if (isset($_SESSION['csrf_token'])) {
            return $_SESSION['csrf_token'];
        }

        // なければ新規生成
        return self::generateToken();
    }

    /**
     * CSRFトークンの検証
     * 
     * @param string $token 検証するトークン
     * @return bool 検証結果（true: 有効, false: 無効）
     */
    public static function verifyToken(string $token): bool
    {
        self::ensureSession();

        // セッションにトークンが存在しない場合は無効
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }

        // タイミング攻撃を防ぐためhash_equalsを使用
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * CSRFトークンをセッションから削除（ワンタイムトークン実現）
     */
    public static function removeToken(): void
    {
        self::ensureSession();
        unset($_SESSION['csrf_token']);
    }

    /**
     * HTMLフォーム用のhidden inputタグを生成
     * 
     * @return string CSRFトークンを含むhidden inputタグ
     */
    public static function getHiddenInput(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }
}