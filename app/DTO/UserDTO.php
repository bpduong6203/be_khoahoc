<?php

namespace App\DTO;

class UserDTO
{
    public function __construct(
        public array $user
    ) {}

    public static function fromUser($user, $fields = ['id', 'name', 'email'])
    {
        $userData = [];
        foreach ($fields as $field) {
            if ($field === 'roles') {
                $user->load('roles');
                $userData['roles'] = $user->roles->pluck('name')->toArray();
            } elseif (isset($user->$field)) {
                $userData[$field] = $user->$field;
            }
        }

        return new self(
            user: $userData
        );
    }

    public function toArray(): array
    {
        return $this->user; 
    }
}