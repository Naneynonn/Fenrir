<?php

namespace Exan\Dhp\Enums\Parts;

enum MessageActivityTypes: int 
{
    case JOIN  = 1;
    case SPECTATE  = 2;
    case LISTEN  = 3;
    case JOIN_REQUEST  = 5;
}