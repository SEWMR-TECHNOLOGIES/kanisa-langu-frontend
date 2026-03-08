<?php
/**
 * Generate a secure reset code.
 *
 * @return string Returns the generated reset code.
 */
function generateResetCode() {
    // Generate a secure random code
    // The length of the code can be adjusted as needed
    $resetCode = bin2hex(random_bytes(16)); // 32 characters long
    return $resetCode;
}

?>
