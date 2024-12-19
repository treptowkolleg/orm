<?php

namespace TreptowKolleg\ORM\Util;

use RuntimeException;

class PasswordUtil
{

    /**
     * Hash ein Passworts mit dem PASSWORD_DEFAULT-Algorithmus.
     *
     * @param string $password Das zu hashende Passwort.
     * @return string Der generierte Passwort-Hash.
     */
    public static function hashPassword(string $password): string
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash) {
            return $hash;
        }
        throw new RuntimeException('Hash password failed');
    }

    /**
     * @param string $password das zu überprüfende Passwort.
     * @param string $hash der zugrundeliegende Hash.
     * @return bool Wenn Überprüfung erfolgreich, wird `true` zurückgegeben, ansonsten `false`.
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

}