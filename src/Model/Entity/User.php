<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * @property int $id
 * @property string $public_user_id
 * @property string $email
 * @property string $password
 * @property string $role
 * @property string $status
 * @property \Cake\I18n\DateTime $terms_agreed_at
 * @property string $terms_version
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 * @property \App\Model\Entity\EmailVerificationToken[] $email_verification_tokens
 * @property \App\Model\Entity\PasswordResetToken[] $password_reset_tokens
 * @property \App\Model\Entity\LoginAuditLog[] $login_audit_logs
 */
class User extends Entity
{
    protected array $_accessible = [
        'public_user_id' => true,
        'email' => true,
        'password' => true,
        'terms_agreed_at' => true,
        'terms_version' => true,
        // role/status は一般ユーザーからの mass assignment を禁止
        'role' => false,
        'status' => false,
        'created' => false,
        'modified' => false,
        'email_verification_tokens' => false,
        'password_reset_tokens' => false,
        'login_audit_logs' => false,
        'id' => false,
    ];

    protected array $_hidden = [
        'password',
    ];

    /**
     * 平文パスワード設定時に bcrypt ハッシュ化。
     * CakePHP Authentication プラグインのデフォルトハッシュ方式と互換。
     * （事前にハッシュ済みの値は渡さないこと）
     */
    protected function _setPassword(string $password): string
    {
        return $password === '' ? '' : password_hash($password, PASSWORD_BCRYPT);
    }
}
