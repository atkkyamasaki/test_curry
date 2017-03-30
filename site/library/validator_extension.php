<?php

/**
 * ValidatorExtension
 */
class ValidatorExtension extends Validator
{

    /**
     * コンストラクタ
     */
    public function __construct()
    {
        self::$_messages = [
            'required' => '入力必須項目',
            'length' => '長さが不正',
            'length_range' => '長さが不正',
            'numeric' => '数値型の数字',
            'number_string' => '文字列型の数字',
            'alpha' => 'アルファベット',
            'alphanum' => 'アルファベットか文字列型',
            'singlebyte' => '半角文字',
            'regex' => '入力値が不正',
            'date' => '日付が不正',
        ];
    }

    /**
     * 日付
     */
    public function date($value)
    {
        if (!preg_match('/(2[0-9][0-9][0-9])-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[01]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])/', $value)) {
            return false;
        }
        return true;
    }

}