<?php

namespace App\Models;

use CodeIgniter\Model;

class UserFcmTokenModel extends Model
{
    protected $table      = 'user_fcm_tokens';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'username',
        'fcm_token_android',
        'fcm_token_ios',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;
}
