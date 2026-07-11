<?php

/**
 * Universal Joomla User Bridge Variable Provisioner
 * Compatible with Joomla 3.x, 4.x, and 5.x (With Redirect Loop Mitigation)
 */

$dev = $dev ?? false;

if ($dev) {
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

    // ========================================================================
    // MITIGATION: Force an AJAX header context.
    // This tells the Joomla 5 Language Filter and Admin Tools plugins to 
    // bypass UI/Canonical URL routing checks, eliminating the redirect loop.
    // ========================================================================
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

    define('JPATH_BASE', $_SERVER["DOCUMENT_ROOT"]);

    require_once JPATH_BASE . '/includes/defines.php';
    require_once JPATH_BASE . '/includes/framework.php';

    if (file_exists(JPATH_BASE . '/includes/app.php')) {
        // --- Joomla 4 / 5 Runtime Context ---
        require_once JPATH_BASE . '/includes/app.php';
        $app  = \Joomla\CMS\Factory::getApplication('site');
        $user = \Joomla\CMS\Factory::getUser();
    } else {
        // --- Legacy Joomla 3 Runtime Context ---
        $app = JFactory::getApplication('site');
        $app->initialise();
        $user = JFactory::getUser();
    }

    // Populate variables for downstream application dependencies
    $jid     = $user->id;
    $jname   = $user->name;
    $jguest  = $user->guest;

    $myobj         = new \stdClass();
    $myobj->id     = $user->get('id');
    $myobj->groups = $user->get('groups');

    $jgroups = array();
    if (is_array($myobj->groups)) {
        foreach ($myobj->groups as $group) {
            $jgroups[] = (string)$group;
        }
    }
    $myobj->groups = $jgroups;
}
