<?php

namespace TreptowKolleg\ORM\ORM;

enum Types
{
    case Integer;
    case String;
    case Boolean;
    case DateTime;
    case Json;
    case ManyToOne;
    case OneToMany;
    case ManyToMany;
}
