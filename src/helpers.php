<?php

function e(?string $value){
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect($path): never
{
    header('Location: ' . $path);
    exit;
}