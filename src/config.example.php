<?php

const DB_HOST    = '127.0.0.1';
const DB_NAME    = 'php_auth';
const DB_USER    = 'php_auth_user';
const DB_PASS    = 'CHANGE_ME';
const DB_CHARSET = 'utf8mb4';

const MIN_PASS_LENGTH  = 8;
const MAX_PASS_LENGTH  = 72;
const MAX_NAME_LENGTH  = 100;
const MAX_EMAIL_LENGTH = 255;

const MAIL_HOST      = 'sandbox.smtp.mailtrap.io';
const MAIL_PORT      = 2525;
const MAIL_USER      = 'CHANGE_ME';
const MAIL_PASS      = 'CHANGE_ME';
const MAIL_FROM      = 'noreply@php-auth.local';
const MAIL_FROM_NAME = 'PHP Auth';

const APP_URL          = 'http://localhost:8000';
const VERIFY_TOKEN_TTL = 1440;
const RESET_TOKEN_TTL  = 30;
