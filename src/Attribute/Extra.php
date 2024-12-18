<?php

namespace TreptowKolleg\ORM\Attribute;

enum Extra: string
{
    case ON_UPDATE_CURRENT_TIMESTAMP = "ON UPDATE CURRENT_TIMESTAMP";
    case ON_CREATE_CURRENT_TIMESTAMP = "DEFAULT CURRENT_TIMESTAMP";

}
