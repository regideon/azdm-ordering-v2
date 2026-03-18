<?php

namespace App\Support;

enum InventoryActionType: int
{
    case CreatedItem = 1;
    case Invoice = 2;
    case Adjustment = 3;
    case Received = 4;
}
