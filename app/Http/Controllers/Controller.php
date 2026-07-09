<?php

namespace App\Http\Controllers;

use App\Traits\HttpResponses;
use App\Traits\ResolvesRouteIds;

abstract class Controller
{
    use HttpResponses;
    use ResolvesRouteIds;
}
