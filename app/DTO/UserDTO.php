<?php

namespace App\DTO;

use App\Models\User;

class UserDTO
{
    public $id;
    public $name;
    public $email;
    public $roles;
    
    public static function fromUser(User $user, array $fields = [])
    {
        $dto = new self();
        
        if (empty($fields) || in_array('id', $fields)) {
            $dto->id = $user->id;
        }
        
        if (empty($fields) || in_array('name', $fields)) {
            $dto->name = $user->name;
        }
        
        if (empty($fields) || in_array('email', $fields)) {
            $dto->email = $user->email;
        }
        
        if ((empty($fields) || in_array('roles', $fields)) && $user->relationLoaded('roles')) {
            $dto->roles = $user->roles->map(function($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name
                ];
            })->toArray();
        }
        
        return $dto;
    }
    
    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];
        
        if (isset($this->roles)) {
            $array['roles'] = $this->roles;
        }
        
        return $array;
    }
}
// {
//     public function __construct(
//         public array $user
//     ) {}

//     public static function fromUser($user, $fields = ['id', 'name', 'email'])
//     {
//         $userData = [];
//         foreach ($fields as $field) {
//             if ($field === 'roles') {
//                 $user->load('roles');
//                 $userData['roles'] = $user->roles->pluck('name')->toArray();
//             } elseif (isset($user->$field)) {
//                 $userData[$field] = $user->$field;
//             }
//         }

//         return new self(
//             user: $userData
//         );
//     }

//     public function toArray(): array
//     {
//         return $this->user; 
//     }
// }