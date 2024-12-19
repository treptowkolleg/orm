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
            $roleArray = [];
        }
        // Füge immer die Rolle 'ROLE_USER' hinzu, falls sie nicht vorhanden ist.
        return array_merge($roleArray, ['ROLE_USER']);
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

    public function addRole(string $role): void
    {
        // Sicherstellen, dass die Rollen als Array vorliegen
        $roles = json_decode($this->roles, true);
        if ($roles === null) {
            $roles = ['ROLE_USER'];
        }
        // Rolle hinzufügen, wenn sie noch nicht vorhanden ist
        if (!in_array($role, $roles, true)) {
            $roles[] = $role;
            // Speichere die geänderten Rollen wieder als JSON-String
            $this->roles = json_encode($roles);
        }
    }

    public function removeRole(string $role): void
    {
        // Sicherstellen, dass die Rollen als Array vorliegen
        $roles = json_decode($this->roles, true);
        if ($roles === null) {
            $roles = ['ROLE_USER'];
        }
        // Rolle entfernen, falls sie vorhanden ist
        if (($key = array_search($role, $roles, true)) !== false) {
            unset($roles[$key]);
            // Speichere die geänderten Rollen wieder als JSON-String
            $this->roles = json_encode(array_values($roles));
        }
    }

}