<?php
include 'sidebars.php';

function renderSidebar($target, $role) {
    global $sidebars;

    foreach ($sidebars as $sidebarConfig) {
        if ($sidebarConfig['target'] === $target && $sidebarConfig['role'] === $role) {
            echo $sidebarConfig['sidebar'];
            return;
        }
    }

    // Render fallback sidebar if no match is found
    foreach ($sidebars as $sidebarConfig) {
        if ($sidebarConfig['target'] === "fallback" && $sidebarConfig['role'] === "default") {
            echo $sidebarConfig['sidebar'];
            return;
        }
    }
}
?>
