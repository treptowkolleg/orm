<?php

namespace TreptowKolleg\ORM\Attribute;

enum Type
{
    case Integer;
    case String;
    case Text;
    case MediumText;
    case LongText;
    case Boolean;
    case DateTime;
    case Date;
    case Json;
    case ManyToOne;
    case OneToMany;
    case ManyToMany;
}
