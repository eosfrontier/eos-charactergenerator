<?php

/**
 * Universal Joomla User Bridge Variable Provisioner
 * Compatible with Joomla 3.x, 4.x, and 5.x
 * Exposes: $jid, $jname, $jguest, $myobj, $jgroups
 */

// Inherit or default development flag
$dev = $dev ?? false;

if ($dev) {
    // Exact structural matching for local development / testing environments
    $jid     = 747;
    $jname   = "Development User";
    $jguest  = 0;

    $myobj         = new \stdClass();
    $myobj->id     = $jid;
    $myobj->groups = ["32", "66", "64"];

    $jgroups = ["32", "66", "64"];
} else {
    if (!defined('_JEXEC')) {
        define('_JEXEC', 1);
    }

    // Core structural root pathway assignment
    define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);

    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';

    // Abstract the Framework Bootstrap Engine
    if (file_exists(JPATH_BASE . '/includes/app.php')) {
        // --- Joomla 4 / 5 Runtime Context ---
        require_once JPATH_BASE . '/includes/app.php';
        $app  = \Joomla\CMS\Factory::getApplication('site');
        $user = \Joomla\CMS\Factory::getUser();
    } else {
        // --- Legacy Joomla 3 Runtime Context ---
        $app = JFactory::getApplication('site');
        $app->initialise(); // Instantiates critical session/cookie mapping for J3
        $user = JFactory::getUser();
    }

    // 1. Populate standard core global primitive variables
    $jid     = $user->id;
    $jname   = $user->name;
    $jguest  = $user->guest;

    // 2. Map standard abstract layout object
    $myobj         = new \stdClass();
    $myobj->id     = $user->get('id');
    $myobj->groups = $user->get('groups'); // Fetches active Access Control (ACL) arrays

    // 3. Normalize groups array structure into string primitives for safety
    $jgroups = array();
    if (is_array($myobj->groups)) {
        foreach ($myobj->groups as $group) {
            $jgroups[] = (string)$group;
        }
    }

    // Explicitly guarantee myobj internal array matches string conversion structure
    $myobj->groups = $jgroups;
}

// Downstream dependent applications can now safely evaluate from this point forward.