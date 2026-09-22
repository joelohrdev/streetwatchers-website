<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Collectives
    |--------------------------------------------------------------------------
    |
    | Collectives aren't part of the launch. While this is off, their public pages return 404 and the
    | links to them are hidden. Site admins can still manage collectives from the admin panel.
    |
    */

    'collectives' => (bool) env('FEATURE_COLLECTIVES', false),

];
