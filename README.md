# PHP Auth

Registration, login, email verification, password reset. Plain PHP, no framework.

## Requirements

- PHP 8.1+ with `pdo_mysql`, `curl`, `mbstring`, `openssl`
- MySQL or MariaDB
- Composer

## Setup

1. Install dependencies:

       composer install

2. Create the database and table:

       mysql -u root -h 127.0.0.1 < schema.sql

3. Copy the config and fill it in:

       cp src/config.example.php src/config.php

   Set `DB_PASS` to match the password in `schema.sql`.
   Set `MAIL_USER` and `MAIL_PASS` to your Mailtrap SMTP credentials.

4. Start the server from the project root:

       php -S localhost:8000 -t public public/index.php

Open http://localhost:8000

## Routes

| Method | Path | What it does |
|---|---|---|
| GET | `/register` | Registration form |
| POST | `/register` | Create account, send verification email |
| GET | `/login` | Login form |
| POST | `/login` | Log in |
| GET | `/dashboard` | Protected page, verified users only |
| POST | `/logout` | Log out |
| GET | `/verify-email?token=` | Verify email address |
| GET | `/verify-notice` | Shown to unverified users |
| POST | `/resend-verification` | Send a new verification email |
| GET | `/forgot-password` | Request a reset link |
| POST | `/forgot-password` | Send the reset email |
| GET | `/reset-password?token=` | New password form |
| POST | `/reset-password` | Save the new password |

## Files

    public/index.php   routes and handlers
    src/config.php     settings (not in git)
    src/db.php         database connection
    src/auth.php       users, sessions, tokens
    src/validation.php form validation
    src/mail.php       sending email
    src/helpers.php    escaping, redirects, flash messages
    views/             page templates

## Notes

- `src/config.php` is gitignored. It holds passwords.
- Verification tokens last 24 hours, reset tokens 30 minutes. Both are set in `config.php`.
- Tokens work once. Using one clears it from the database.
- Emails go to Mailtrap, not to real addresses.
