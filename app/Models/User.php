<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users'; 
    protected $primaryKey = 'id'; 

    protected $fillable = [
        'name',
        'email',
        'nik',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password; 
    }

    public function getAuthIdentifierName()
    {
        return 'nik'; 
    }
}
