<?php

// Validate username: 3-20 chars, letters/numbers/underscore/period only
function validate_username($username)
{
    return preg_match('/^[a-zA-Z0-9_.]{3,20}$/', $username);
}

// Validate password: at least 8 chars, 1 upper, 1 lower, 1 number, 1 special char
function validate_password($password)
{
    return preg_match(
        '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/',
        $password,
    );
}

?>
