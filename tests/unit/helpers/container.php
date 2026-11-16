<?php

declare(strict_types=1);

namespace epiphyt\Impressum;

/**
 * Stand-in for the plugin container.
 *
 * The real function lives in the plugin's main file, which is never loaded in
 * unit tests. It has to be declared exactly once, so tests set the container
 * they want to be handed out instead of declaring their own function.
 */
function get_container()
{
    return $GLOBALS['impressum_test_container'] ?? null;
}
