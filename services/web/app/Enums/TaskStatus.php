<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Todo       = 'todo';
    case InProgress = 'in_progress';
    case Review     = 'review';
    case Approved   = 'approved';
    case Rejected   = 'rejected';
    case Done       = 'done';
}
