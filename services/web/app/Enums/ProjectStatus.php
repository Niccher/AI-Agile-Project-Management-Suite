<?php

namespace App\Enums;

enum ProjectStatus: string
{
    case Planning   = 'planning';
    case InProgress = 'in_progress';
    case Testing    = 'testing';
    case Completed  = 'completed';
    case OnHold     = 'on_hold';
    case Abandoned  = 'abandoned';
}
