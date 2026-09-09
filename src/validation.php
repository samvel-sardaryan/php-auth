<?php

function validate_registration($name, $email, $password) {
    $errors = [];

    if (trim($name) === '') {
        $errors['name'] = 'Name is required.';
    }

    if (trim($email) === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email';
    }

    if (trim($password) === '') {
        $errors['password'] = 'Password is required.';
    } elseif (mb_strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    }

    return $errors;
}

function validate_login($email, $password) {
    $errors = [];

    if (trim($email) === '') {
        $errors['email'] = 'Email is required.';
    }

    if (trim($password) === '') {
        $errors['password'] = 'Password is required.';
    }

    return $errors;
}

function validate_reset($password, $confirm) {
    $errors = [];

    if (trim($password) === '') {
        $errors['password'] = 'Password is required.';
    } elseif (mb_strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long.';
    }

    if ($password !== $confirm) {
        $errors['confirm'] = 'Passwords do not match.';
    }

    return $errors;
}

function validate_profile($name, $email) {
    $errors = [];

    if (trim($name) === '') {
        $errors['name'] = 'Name is required.';
    }

    if (trim($email) === '') {
        $errors['email'] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email';
    }

    return $errors;
}

function validate_password_change($current, $new, $confirm) {
    $errors = [];

    $errors = validate_reset($new, $confirm);

    if (trim($current) === '') {
        $errors['current'] = 'Current password is required.';
    }

    return $errors;
}
