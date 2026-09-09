# PHP Auth

Registration, login, email verification, password reset, roles and permissions.
Plain PHP, no framework.

## Requirements

- PHP 8.1+ with `pdo_mysql`, `curl`, `mbstring`, `openssl`
- MySQL or MariaDB
- Composer

## Setup

1. Install dependencies:

       composer install

2. Create the database, tables and seed data:

       mysql -u root -h 127.0.0.1 < schema.sql

3. Copy the config and fill it in:

       cp src/config.example.php src/config.php

   Set `DB_PASS` to match the password in `schema.sql`.
   Set `MAIL_USER` and `MAIL_PASS` to your Mailtrap SMTP credentials.

4. Make one account an admin so you can reach the admin pages:

       UPDATE users SET role_id = (SELECT id FROM roles WHERE name='admin')
       WHERE email = 'you@example.com';

5. Start the server from the project root:

       php -S localhost:8000 -t public public/index.php

Open http://localhost:8000

## Routes

| Method | Path | What it does | Requires |
|---|---|---|---|
| GET | `/` | Redirects to `/dashboard` or `/login` | — |
| GET | `/register` | Registration form | — |
| POST | `/register` | Create account, send verification email | — |
| GET | `/login` | Login form | — |
| POST | `/login` | Log in | — |
| POST | `/logout` | Log out | login |
| GET | `/verify-email?token=` | Verify email address | — |
| GET | `/verify-notice` | Shown to unverified users | login |
| POST | `/resend-verification` | Send a new verification email | login |
| GET | `/forgot-password` | Request a reset link | — |
| POST | `/forgot-password` | Send the reset email | — |
| GET | `/reset-password?token=` | New password form | — |
| POST | `/reset-password` | Save the new password | — |
| GET | `/dashboard` | Main page | `view_dashboard` |
| GET | `/profile` | Name, email and password settings | login |
| POST | `/profile` | Save name and email | login |
| POST | `/profile/password` | Change password | login |
| GET | `/moderator` | Moderator page | `access_moderator_page` |
| GET | `/admin` | Admin page | `access_admin_page` |
| GET | `/admin/users` | User list | `view_users` |
| POST | `/admin/users/role` | Change a user's role | `manage_users` |

Not logged in redirects to `/login`. Logged in but unverified redirects to
`/verify-notice`. Logged in without the permission returns **403**.

## Roles and permissions

| Role | Permissions |
|---|---|
| user | `view_dashboard` |
| moderator | `view_dashboard`, `view_users`, `access_moderator_page` |
| admin | all five |

New registrations get the `user` role. Permissions live in the database, so adding
a role is `INSERT`s into `roles` and `role_permissions` — no code change.

## Files

    public/index.php   routes and handlers
    src/config.php     settings (not in git)
    src/db.php         database connection
    src/auth.php       users, sessions, tokens
    src/authz.php      permission checks: can(), require_permission()
    src/roles.php      role and user queries
    src/validation.php form validation
    src/mail.php       sending email
    src/helpers.php    escaping, redirects, flash messages, CSRF
    views/             page templates

## Notes

- `src/config.php` is gitignored. It holds passwords.
- Verification tokens last 24 hours, reset tokens 30 minutes. Both set in `config.php`.
- Tokens are stored as a SHA-256 hash, so a database leak yields no working links.
- Tokens work once. Using one clears it from the database.
- Every POST needs a CSRF token. Forms include it with `csrf_field()`; the check
  runs centrally in `public/index.php` before any route is dispatched.
- Changing your email clears verification and sends a new link. `/profile` only
  requires login, not verification, so a mistyped address can still be corrected.
- Emails go to Mailtrap, not to real addresses.
