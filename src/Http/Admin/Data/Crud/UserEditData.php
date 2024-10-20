<?php

namespace App\Http\Admin\Data\Crud;

use App\Http\Admin\Data\AutomaticCrudData;

class UserEditData extends AutomaticCrudData
{
    public array $roles = [];

    public function hydrate() : void
    {
        parent::hydrate();
        $this->entity->setUpdatedAt( new \DateTimeImmutable() );
        $this->entity->setRoles( $this->roles );
    }
}