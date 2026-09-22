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

function validate_profile_details(array $d) {
    $errors = [];

    if (mb_strlen($d['first_name']) > 100) {
        $errors['first_name'] = 'Maximum 100 characters.';
    }

    if (mb_strlen($d['last_name']) > 100) {
        $errors['last_name'] = 'Maximum 100 characters.';
    }

    if ($d['phone'] !== '') {
        if (mb_strlen($d['phone']) > 30) {
            $errors['phone'] = 'Maximum 30 characters.';
        } elseif (!preg_match('/^\+?[0-9 ()\-]+$/', $d['phone'])) {
            $errors['phone'] = 'Phone may contain only digits, spaces and + ( ) -';
        } else {
            $digits = preg_replace('/\D/', '', $d['phone']);
            if (strlen($digits) < 7 || strlen($digits) > 15) {
                $errors['phone'] = 'Phone must contain between 7 and 15 digits.';
            }
        }
    }

    if (mb_strlen($d['location']) > 100) {
        $errors['location'] = 'Maximum 100 characters.';
    }

    if (mb_strlen($d['bio']) > 1000) {
        $errors['bio'] = 'Maximum 1000 characters.';
    }

    if ($d['date_of_birth'] !== '') {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d['date_of_birth'])) {
            $errors['date_of_birth'] = 'Invalid date format.';
        } else {
            $dob = explode('-', $d['date_of_birth']);
            if (!checkdate($dob[1], $dob[2], $dob[0])) {
                $errors['date_of_birth'] = 'Invalid date.';
            }
        }
    }

    return $errors;
}

function validate_profile_avatar(array $file) {
    $errors = [];
    $max = 3 * 1024 * 1024;

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return $errors;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['avatar' => 'Upload failed'];
    }

    if ($file['size'] > $max) {
        $errors['avatar'] = 'File too large (max 3MB)';
    }

    if (!in_array($file['type'], ['image/jpeg', 'image/png', 'image/webp'])) {
        $errors['avatar'] = 'Only JPG, PNG, and WEBP allowed';
    }

    return $errors;
}

function validate_post($title, $content, $status, $categoryId) {
    $errors = [];

    if (trim($title) === '') {
        $errors['title'] = 'Title is required.';
    } elseif (mb_strlen($title) > 150) {
        $errors['title'] = 'Maximum 150 characters.';
    }

    if (trim($content) === '') {
        $errors['content'] = 'Content is required.';
    } elseif (mb_strlen($content) > 5000) {
        $errors['content'] = 'Maximum 5000 characters.';
    }

    if (!in_array($status, STATUS_TYPES)) {
        $errors['status'] = 'Invalid status.';
    }

    if ((int)$categoryId <= 0 || !category_exists($categoryId)) {
        $errors['category_id'] = 'Invalid category.';
    }

    return $errors;
}

function validate_category($name, $slug) {
    $errors = [];

    if (trim($name) === '') {
        $errors['name'] = 'Name is required.';
    } elseif (mb_strlen($name) > 50) {
        $errors['name'] = 'Maximum 50 characters.';
    }

    if (trim($slug) === '') {
        $errors['slug'] = 'Slug is required.';
    } elseif (mb_strlen($slug) > 50) {
        $errors['slug'] = 'Maximum 50 characters.';
    } elseif (!preg_match('/^[a-z0-9-]+$/', $slug)) {
        $errors['slug'] = 'Slug may contain only lowercase letters, numbers and hyphens.';
    }

    return $errors;
}

function validate_comment($content) {
    $errors = [];

    if (trim($content) === '') {
        $errors['content'] = 'Comment is required.';
    } elseif (mb_strlen($content) > 2000) {
        $errors['content'] = 'Maximum 2000 characters.';
    }

    return $errors;
}
