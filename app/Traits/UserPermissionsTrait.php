<?php

namespace App\Traits;

use App\Models\Admin;
use App\Models\Deputy;
use App\Models\Enforcer;
use App\Models\Head;
use App\Models\Violator;

trait UserPermissionsTrait
{
    public function getUserType($user)
    {
        return strtolower(class_basename($user));
    }

    public function getViewableUserTypes($authUser)
    {
        $userType = $this->getUserType($authUser);
        
        return match ($userType) {
            'head'   => ['deputy', 'admin', 'enforcer'],
            'deputy' => ['admin', 'enforcer'],
            'admin'  => ['enforcer'],
            default  => [],
        };
    }

    public function getManageableUserTypes($authUser)
    {
        $userType = $this->getUserType($authUser);
        
        return match ($userType) {
            'head'   => ['deputy', 'admin', 'enforcer'],
            'deputy' => ['admin', 'enforcer'],
            'admin'  => ['enforcer'],
            default  => [],
        };
    }

    public static function getModelClass($userType)
    {
        return match ($userType) {
            'admin'    => Admin::class,
            'head'     => Head::class,
            'deputy'   => Deputy::class,
            'enforcer' => Enforcer::class,
            'violator' => Violator::class,
            default    => throw new \InvalidArgumentException("Invalid user type: {$userType}"),
        };
    }

    public function canManageUserType($authUser, $targetUserType)
    {
        return in_array($targetUserType, $this->getManageableUserTypes($authUser));
    }

    public function canViewUserType($authUser, $targetUserType)
    {
        return in_array($targetUserType, $this->getViewableUserTypes($authUser));
    }
}
