<?php
namespace Dropbox;

// The Dropbox SDK autoloader.  You probably shouldn't be using this.  Instead,
// use a global autoloader, like the Composer autoloader.
//
// But if you really don't want to use a global autoloader, do this:
//
//     require_once "<path-to-here>/Dropbox/autoload.php"

/**
 * @internal
 */
function autoload($name)
{
    // If the name doesn't start with "Dropbox\", then its not once of our classes.
    if (\substr_compare($name, "Dropbox\\", 0, 8) !== 0) return;

    // Take the "Dropbox\" prefix off.
    $stem = \substr($name, 8);

    // Convert "\" and "_" to path separators.
    $pathified_stem = \str_replace(array("\\", "_"), '/', $stem);

    $path = __DIR__ . "/" . $pathified_stem . ".php";
    if (\is_file($path)) {
        require_once $path;
    }
}

\spl_autoload_register('Dropbox\autoload');
