<?php
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function isValidPhone($phone) {
    return preg_match('/^0(7[0-9]|6[0-9])\d{7}$/', $phone);
}


?>
