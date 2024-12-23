<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

trait UserRoleField
{

    #[DB\Column(type: DB\Type::Json, nullable: true)]
    private ?string $roles = null;

    public function getRoles(): array
    {
        $roleArray = json_decode($this->roles, true);
        if ($roleArray === null) {
            $roleArray[] = 'ROLE_USER';
        } elseif (!in_array('ROLE_USER', $roleArray)) {
            $roleArray[] = 'ROLE_USER';
        }
        return $roleArray;
    }

    public function hasRole(string $role): bool
    {
        // Sicherstellen, dass die Rollen als Array vorliegen
        $roles = json_decode($this->roles, true);
        if ($roles === null) {
            $roles = ['ROLE_USER'];
        }
        return in_array($role, $roles, true);
    }

    public function addRole(string $role): static
    {
        // Sicherstellen, dass die Rollen als Array vorliegen
        $roles = json_decode($this->roles, true);
        if ($roles === null) {
            $roles = [];
        }
        // Rolle hinzufügen, wenn sie noch nicht vorhanden ist
        if (!in_array($role, $roles, true)) {
            $roles[] = $role;
            // Speichere die geänderten Rollen wieder als JSON-String
            $this->roles = json_encode($roles);
        }

        return $this;
    }

    public function removeRole(string $role): void
    {
        // Sicherstellen, dass die Rollen als Array vorliegen
        $roles = json_decode($this->roles, true);
        if ($roles === null) {
            $roles = [];
        }
        // Rolle entfernen, falls sie vorhanden ist
        if (($key = array_search($role, $roles, true)) !== false) {
            unset($roles[$key]);
            // Speichere die geänderten Rollen wieder als JSON-String
            $this->roles = json_encode(array_values($roles));
        }
    }

}