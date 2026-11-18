<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * users（会員・運営統合テーブル）
 *
 * @property \App\Model\Table\EmailVerificationTokensTable&\Cake\ORM\Association\HasMany $EmailVerificationTokens
 * @property \App\Model\Table\PasswordResetTokensTable&\Cake\ORM\Association\HasMany $PasswordResetTokens
 * @property \App\Model\Table\LoginAuditLogsTable&\Cake\ORM\Association\HasMany $LoginAuditLogs
 */
class UsersTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('users');
        $this->setDisplayField('email');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp'); // created / modified

        // users.id -> 各テーブルの user_id（1対多）
        $this->hasMany('EmailVerificationTokens', [
            'foreignKey' => 'user_id',
            'dependent' => true, // DB側: ON DELETE CASCADE
        ]);
        $this->hasMany('PasswordResetTokens', [
            'foreignKey' => 'user_id',
            'dependent' => true, // DB側: ON DELETE CASCADE
        ]);
        $this->hasMany('LoginAuditLogs', [
            'foreignKey' => 'user_id',
            'dependent' => false, // DB側: ON DELETE SET NULL（履歴を保持）
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('public_user_id')
            ->maxLength('public_user_id', 36)
            ->uuid('public_user_id')
            ->requirePresence('public_user_id', 'create')
            ->notEmptyString('public_user_id');

        $validator
            ->scalar('email')
            ->maxLength('email', 255)
            ->email('email')
            ->requirePresence('email', 'create')
            ->notEmptyString('email');

        $validator
            ->scalar('password')
            ->maxLength('password', 255)
            ->requirePresence('password', 'create')
            ->notEmptyString('password');

        $validator
            ->scalar('role')
            ->maxLength('role', 20)
            ->inList('role', ['general', 'expert', 'facility_manager', 'admin'])
            ->notEmptyString('role');

        $validator
            ->scalar('status')
            ->maxLength('status', 20)
            ->inList('status', ['pending', 'active', 'suspended', 'withdrawn'])
            ->notEmptyString('status');

        $validator
            ->dateTime('terms_agreed_at')
            ->requirePresence('terms_agreed_at', 'create')
            ->notEmptyDateTime('terms_agreed_at');

        $validator
            ->scalar('terms_version')
            ->maxLength('terms_version', 20)
            ->requirePresence('terms_version', 'create')
            ->notEmptyString('terms_version');

        $validator
            ->dateTime('created')
            ->allowEmptyDateTime('created');

        $validator
            ->dateTime('modified')
            ->allowEmptyDateTime('modified');

        return $validator;
    }

    public function buildRules(RulesChecker $rules): RulesChecker
    {
        $rules->add($rules->isUnique(['public_user_id']), ['errorField' => 'public_user_id']);
        $rules->add($rules->isUnique(['email']), ['errorField' => 'email']);

        return $rules;
    }
}
