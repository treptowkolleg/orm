<?php

namespace TreptowKolleg\ORM\Field;

use TreptowKolleg\ORM\Attribute as DB;

trait CreatedAndUpdatedField
{

    use CreatedField;
    use UpdatedField;

}