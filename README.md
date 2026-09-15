# PHP Auth

Registration, login, email verification, password reset, roles and permissions,
user profiles with a picture, and posts. Plain PHP, no framework.

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
| GET | `/profile` | Your own profile: settings, picture, details, your posts | login |
| POST | `/profile` | Save name and email | login |
| POST | `/profile/password` | Change password | login |
| POST | `/profile/details` | Save first/last name, phone, location, date of birth, bio | login |
| POST | `/profile/avatar` | Upload or replace the profile picture | login |
| POST | `/profile/avatar/delete` | Remove the profile picture | login |
| GET | `/users?id=` | Another user's profile and their posts, read-only | verified |
| GET | `/posts` | Feed of every post, newest first | verified |
| GET | `/posts/create` | New post form | verified |
| POST | `/posts/create` | Publish a post | verified |
| GET | `/posts/edit?id=` | Edit form | post owner or `manage_posts` |
| POST | `/posts/edit` | Save changes | post owner or `manage_posts` |
| POST | `/posts/delete` | Soft-delete a post | post owner or `manage_posts` |
| GET | `/moderator` | Moderator page | `access_moderator_page` |
| GET | `/admin` | Admin page | `access_admin_page` |
| GET | `/admin/users` | User list | `view_users` |
| POST | `/admin/users/role` | Change a user's role | `manage_users` |

Not logged in redirects to `/login`. Logged in but unverified redirects to
`/verify-notice`. Logged in without the permission returns **403**. Already logged in
and asking for `/login`, `/register`, `/forgot-password` or `/reset-password` redirects
to `/dashboard` — `require_guest()` in `src/auth.php`, the mirror of `require_login()`.

## Roles and permissions

| Role | Permissions |
|---|---|
| user | `view_dashboard` |
| moderator | `view_dashboard`, `view_users`, `access_moderator_page` |
| admin | all six |

New registrations get the `user` role. Permissions live in the database, so adding
a role is `INSERT`s into `roles` and `role_permissions` — no code change.

`manage_posts` (admin only) lets a user edit or delete **any** post. Everyone can
edit and delete their own, which is ownership rather than a permission — see below.

## Files

    public/index.php   bootstrap, the route table, the CSRF gate, dispatch
    handlers/          request handlers, one file per area
    handlers/auth.php    register, login, logout, verification, password reset
    handlers/pages.php   pages that only check a permission and render a view
    handlers/admin.php   user list and role changes
    handlers/profile.php your own profile, and viewing someone else's
    handlers/posts.php   the feed and post create/edit/delete
    src/config.php     settings (not in git)
    src/db.php         database connection
    src/auth.php       users, sessions, tokens
    src/authz.php      permission and ownership checks: can(), require_permission(),
                       owns_post(), require_post_owner()
    src/roles.php      role queries
    src/users.php      user queries: find_user(), list_users(), user_exists()
    src/profiles.php   profile read/write, avatar column
    src/posts.php      post queries, all filtering deleted_at IS NULL
    src/upload.php     avatar upload validation and storage
    src/validation.php form validation
    src/mail.php       sending email
    src/helpers.php    escaping, redirects, flash messages, CSRF
    views/             page templates
    views/_post.php    renders one post; the only place a post is rendered

    public/uploads/avatars/   uploaded pictures (gitignored except .gitkeep)

Three layers, three directories: `handlers/` handles a request, `src/` talks to the
database, `views/` renders HTML. `handlers/` sits beside `public/` rather than inside
it, so nothing there is reachable over HTTP.

## Notes

- `src/config.php` is gitignored. It holds passwords.
- Flash messages are consumed on read, so a page that redirects somewhere carrying one
  must land on a page that reads it. An unread flash is not discarded — it waits, and
  surfaces later on an unrelated page. That is why `verify_email()` sends a logged-in
  user to `/dashboard` rather than `/login`, and why the dashboard renders flashes.
- Verification tokens last 24 hours, reset tokens 30 minutes. Both set in `config.php`.
- Tokens are stored as a SHA-256 hash, so a database leak yields no working links.
- Tokens work once. Using one clears it from the database.
- Every POST needs a CSRF token. Forms include it with `csrf_field()`; the check
  runs centrally in `public/index.php` before any route is dispatched.
- Changing your email clears verification and sends a new link. `/profile` only
  requires login, not verification, so a mistyped address can still be corrected.
- Emails go to Mailtrap, not to real addresses.
- Every user may have one profile row. `profiles.user_id` is both the primary key
  and the foreign key, so a second profile is impossible. `get_profile()` returns
  an array of nulls when there is no row, so views never have to check.
- Profile pictures are validated by decoding them with `getimagesize()`, never by
  the uploaded filename or the client's MIME type, and are stored under a random
  name. Uploads land in a directory the web server will execute, so a file named
  `shell.php` that was trusted would run.
- Posts are **soft-deleted**: `deleted_at` is set and every read filters
  `deleted_at IS NULL`. A deleted post is a 404 even for its author.
- Editing or deleting a post needs ownership, which is a different question from a
  permission: `can('manage_posts')` is the same answer for every post, while
  `owns_post($post)` depends on the row, so it can only be checked after the
  lookup. The gate passes for either.
- Posts are rendered by one partial, `views/_post.php`, used by the feed and both
  profile pages — so an edit shows up everywhere without any page being updated.
