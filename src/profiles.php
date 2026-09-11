<?php

require_once __DIR__ . '/db.php';

function get_profile($userId) {
    $blank = [
        'user_id' => null,
        'first_name' => null,
        'last_name' => null,
        'phone' => null,
        'location' => null,
        'date_of_birth' => null,
        'bio' => null,
        'avatar' => null,
    ];

    $db = db();
    $stmt = $db->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $stmt->execute([$userId]);
    $profile = $stmt->fetch();

    return $profile === false ? $blank : $profile;
}

function save_profile($userId, array $d) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO profiles (user_id, first_name, last_name, location, phone, date_of_birth, bio) 
    VALUES (?, ?, ?, ?, ?, ?, ?) 
    ON DUPLICATE KEY UPDATE first_name = VALUES(first_name), last_name = VALUES(last_name), location = VALUES(location), phone = VALUES(phone), date_of_birth = VALUES(date_of_birth), bio = VALUES(bio)");
    return $stmt->execute([$userId, $d['first_name'], $d['last_name'], $d['location'], $d['phone'], $d['date_of_birth'], $d['bio']]);
}

function set_avatar($userId, $filename) {
    $db = db();
    $stmt = $db->prepare("INSERT INTO profiles (user_id, avatar) 
    VALUES (?, ?) 
    ON DUPLICATE KEY UPDATE avatar = VALUES(avatar)");
    return $stmt->execute([$userId, $filename]);
}
