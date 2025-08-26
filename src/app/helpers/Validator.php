<?php

namespace App\Helpers;

/**
 * 入力値検証を行うバリデータークラス
 * フォームから送信されたデータの妥当性をチェック
 */
class Validator
{
    /**
     * バリデーションエラーを格納する配列
     * @var array
     */
    private array $errors = [];

    /**
     * 検証対象のデータ
     * @var array
     */
    private array $data = [];

    /**
     * コンストラクタ
     * 
     * @param array $data 検証対象のデータ
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * 必須項目のチェック（空白でないことを確認）
     * 
     * @param string $field フィールド名
     * @param string $message エラーメッセージ（省略時はデフォルトメッセージ）
     * @return self メソッドチェーン用
     */
    public function required(string $field, string $message = ''): self
    {
        // デフォルトメッセージの設定
        if (empty($message)) {
            $message = "{$field}は必須項目です。";
        }

        // フィールドが存在しないか、空文字、またはnullの場合はエラー
        if (!isset($this->data[$field]) || trim($this->data[$field]) === '') {
            $this->addError($field, $message);
        }

        return $this;
    }

    /**
     * 最小文字数のチェック
     * 
     * @param string $field フィールド名
     * @param int $minLength 最小文字数
     * @param string $message エラーメッセージ（省略時はデフォルトメッセージ）
     * @return self メソッドチェーン用
     */
    public function minLength(string $field, int $minLength, string $message = ''): self
    {
        if (empty($message)) {
            $message = "{$field}は{$minLength}文字以上で入力してください。";
        }

        if (isset($this->data[$field]) && mb_strlen($this->data[$field]) < $minLength) {
            $this->addError($field, $message);
        }

        return $this;
    }

    /**
     * 最大文字数のチェック
     * 
     * @param string $field フィールド名
     * @param int $maxLength 最大文字数
     * @param string $message エラーメッセージ（省略時はデフォルトメッセージ）
     * @return self メソッドチェーン用
     */
    public function maxLength(string $field, int $maxLength, string $message = ''): self
    {
        if (empty($message)) {
            $message = "{$field}は{$maxLength}文字以内で入力してください。";
        }

        if (isset($this->data[$field]) && mb_strlen($this->data[$field]) > $maxLength) {
            $this->addError($field, $message);
        }

        return $this;
    }

    /**
     * メールアドレス形式のチェック
     * 
     * @param string $field フィールド名
     * @param string $message エラーメッセージ（省略時はデフォルトメッセージ）
     * @return self メソッドチェーン用
     */
    public function email(string $field, string $message = ''): self
    {
        if (empty($message)) {
            $message = "{$field}の形式が正しくありません。";
        }

        if (isset($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, $message);
        }

        return $this;
    }

    /**
     * エラーを追加
     * 
     * @param string $field フィールド名
     * @param string $message エラーメッセージ
     */
    private function addError(string $field, string $message): void
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    /**
     * バリデーション結果の取得
     * 
     * @return bool true: エラーなし, false: エラーあり
     */
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    /**
     * エラーメッセージの取得
     * 
     * @return array エラーメッセージの配列
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * 特定フィールドのエラーメッセージを取得
     * 
     * @param string $field フィールド名
     * @return array 該当フィールドのエラーメッセージ配列
     */
    public function getFieldErrors(string $field): array
    {
        return $this->errors[$field] ?? [];
    }

    /**
     * 最初のエラーメッセージを取得（表示用）
     * 
     * @return string 最初のエラーメッセージ
     */
    public function getFirstError(): string
    {
        foreach ($this->errors as $fieldErrors) {
            if (!empty($fieldErrors)) {
                return $fieldErrors[0];
            }
        }
        return '';
    }

    /**
     * エラーメッセージをクリア
     */
    public function clearErrors(): void
    {
        $this->errors = [];
    }
}