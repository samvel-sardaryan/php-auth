<?php

function validate_registration($name, $email, $password) {
    $errors = [];

    if(trim($name) === '') {
        $errors['name'] = 'Name is required.';
    }

    if(trim($email) === '') {
        $errors['email'] = 'Email is required.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Invalid email';
    }

    if(trim($password) === '') {   
        $errors['password'] = 'Password is required.';
    } elseif(mb_strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters long';
    }

    return $errors;
}

function validate_login($email, $password) {
        $errors = [];

        if(trim($email) === '') {
            $errors['email'] = 'Email is required.';
        }

        if(trim($password) === '') {
            $errors['password'] = 'Password is required.';
        }

        return $errors;
}