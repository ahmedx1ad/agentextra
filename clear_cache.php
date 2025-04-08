<?php
// Clear PHP OpCache if enabled
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OpCache cleared successfully.\n";
} else {
    echo "OpCache not enabled.\n";
}

// Clear APC cache if enabled
if (function_exists('apc_clear_cache')) {
    apc_clear_cache();
    apc_clear_cache('user');
    apc_clear_cache('opcode');
    echo "APC cache cleared successfully.\n";
} else {
    echo "APC not enabled.\n";
}

echo "Cache clearing completed. Please try accessing the page again.\n";
?> 