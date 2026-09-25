# PHP Auth

Registration, login, email verification, password reset, roles and permissions, user
profiles with a picture, and a content site on top: posts with categories, tags and
images, threaded comments, likes, a searchable paginated feed, reporting and
moderation, rate limiting and an activity log. Plain PHP, no framework.

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

### Account

| Method | Path | What it does | Requires |
|---|---|---|---|
| GET | `/` | Redirects to `/dashboard` or `/login` | — |
| GET/POST | `/register` | Create an account, send the verification email | guest |
| GET/POST | `/login` | Log in | guest |
| POST | `/logout` | Log out | login |
| GET | `/verify-email?token=` | Verify an email address | — |
| GET | `/verify-notice` | Shown to unverified users | login |
| POST | `/resend-verification` | Send a new verification email | login |
| GET/POST | `/forgot-password` | Request a reset link | guest |
| GET/POST | `/reset-password?token=` | Set a new password | guest |
| GET | `/dashboard` | Main page | `view_dashboard` |

### Profile

| Method | Path | What it does | Requires |
|---|---|---|---|
| GET | `/profile` | Your profile: settings, picture, details, your posts | login |
| POST | `/profile` | Save name and email | login |
| POST | `/profile/password` | Change password | login |
| POST | `/profile/details` | Save details **and** the picture, in one transaction | login |
| POST | `/profile/avatar/delete` | Remove the profile picture | login |
| GET | `/users?id=` | Someone else's profile and their published posts | — |

### Posts

| Method | Path | What it does | Requires |
|---|---|---|---|
| GET | `/posts` | The feed: search, filters, sorting, pagination | — |
| GET | `/posts/show?id=` | One post with its comment thread | — |
| GET/POST | `/posts/create` | Write a post, with tags, a category and images | `create_post`, verified |
| GET/POST | `/posts/edit?id=` | Edit, including adding and removing images | post owner or `manage_posts` |
| POST | `/posts/delete` | Soft-delete a post | post owner or `manage_posts` |
| POST | `/posts/like` | Like or unlike (one route, it toggles) | verified |

Feed parameters, all optional and all validated: `q`, `category`, `author`, `tag`,
`sort` (`newest`, `liked`, `commented`), `page`.

### Comments

| Method | Path | What it does | Requires |
|---|---|---|---|
| POST | `/comments/create` | Comment, or reply with `parent_id` | verified |
| GET/POST | `/comments/edit?id=` | Edit your own comment | comment author |
| POST | `/comments/delete` | Soft-delete | comment author, post author, or `moderate_comments` |

### Reporting and moderation

| Method | Path | What it does | Requires |
|---|---|---|---|
| POST | `/reports/create` | Report a post or a comment | verified |
| GET | `/moderator` | The report queue | `moderate_posts` **or** `moderate_comments` |
| POST | `/moderation/hide-post` | Hide a reported post | `moderate_posts` |
| POST | `/moderation/hide-comment` | Hide a reported comment | `moderate_comments` |

### Admin

| Method | Path | What it does | Requires |
|---|---|---|---|
| GET | `/admin` | Admin index | `access_admin_page` |
| GET | `/admin/users` | User list | `view_users` |
| POST | `/admin/users/role` | Change a user's role | `manage_users` |
| GET | `/admin/categories` | Category list | `access_admin_page` |
| POST | `/admin/categories/create` | Add a category | `access_admin_page` |
| POST | `/admin/categories/rename` | Rename one | `access_admin_page` |
| POST | `/admin/categories/delete` | Delete one; its posts move to the default | `access_admin_page` |
| GET | `/admin/deleted-posts` | Everything soft-deleted | `view_deleted_posts` |
| POST | `/admin/deleted-posts/restore` | Put a post back | `view_deleted_posts` |
| GET | `/admin/activity` | The activity log, filterable by user | `access_admin_page` |

Not logged in redirects to `/login`. Logged in but unverified redirects to
`/verify-notice`. Logged in without the permission returns **403**. Already logged in
and asking for `/login`, `/register`, `/forgot-password` or `/reset-password` redirects
to `/dashboard` — `require_guest()` in `src/auth.php`, the mirror of `require_login()`.

**Reading is public.** The feed, a single post with its comments, and user profiles
need no account. Everything that writes is gated.

## Roles and permissions

| Role | Permissions |
|---|---|
| user | `view_dashboard`, `create_post` |
| moderator | the above, plus `view_users`, `access_moderator_page`, `moderate_posts`, `moderate_comments` |
| admin | all ten, adding `access_admin_page`, `manage_users`, `manage_posts`, `view_deleted_posts` |

New registrations get the `user` role. Permissions live in the database, so adding a
role is `INSERT`s into `roles` and `role_permissions` — no code change. A role with
only `moderate_comments`, for example, sees the moderation queue and can hide a
comment but gets 403 on a post, because every check is a `can()` and never a role name.

`manage_posts` (admin only) lets a user edit or delete **any** post. Everyone can edit
and delete their own, which is ownership rather than a permission — see the notes.

## Files

    public/index.php   bootstrap, the route table, the CSRF gate, dispatch
    public/style.css   the whole stylesheet

    handlers/          request handlers, one file per area
      auth.php         register, login, logout, verification, password reset
      pages.php        pages that only check a permission and render a view
      admin.php        users, role changes, deleted posts, the activity log
      profile.php      your own profile, and viewing someone else's
      posts.php        the feed and post create/edit/delete
      comments.php     comments and replies
      categories.php   admin category management
      moderation.php   reporting, the queue, hiding

    src/config.php     settings (not in git)
      db.php           database connection
      auth.php         users, sessions, tokens
      authz.php        permission and ownership checks: can(), require_permission(),
                       owns_post(), owns_comment(), require_comment_deleter()
      roles.php        role queries
      users.php        user queries
      profiles.php     profile read/write, avatar column
      posts.php        post queries, feed filtering and sorting
      comments.php     the two-query comment thread
      categories.php   category queries
      tags.php         tag normalising and the post_tag links
      likes.php        toggle_like(), liked_by()
      reports.php      reports and the polymorphic target lookup
      activity.php     log_activity(), list_activity()
      ratelimit.php    the three limits
      upload.php       image validation and storage, shared by avatars and posts
      validation.php   form validation
      mail.php         sending email
      helpers.php      escaping, redirects, flash messages, CSRF, local_path()

    views/             page templates
      _header.php      doctype, head, the nav
      _footer.php      closes the layout
      _post.php        renders one post; the only place a post is rendered
      _comment.php     renders one comment

    public/uploads/avatars/   profile pictures (gitignored except .gitkeep)
    public/uploads/posts/     post images (same)

Three layers, three directories: `handlers/` handles a request, `src/` talks to the
database, `views/` renders HTML. `handlers/` sits beside `public/` rather than inside
it, so nothing there is reachable over HTTP.

Every page view sets `$title`, requires `_header.php` as its second line and
`_footer.php` as its last. The layout cannot live in `public/index.php`: handlers call
`redirect()`, and `header()` fails once any output has been sent.

## Notes

### Account and security

- `src/config.php` is gitignored. It holds passwords.
- Flash messages are consumed on read, so a page that redirects carrying one must land
  on a page that reads it. An unread flash waits and surfaces later on an unrelated page.
- Verification tokens last 24 hours, reset tokens 30 minutes, both from `config.php`.
  Tokens are stored as a SHA-256 hash and work once.
- Every POST needs a CSRF token. Forms include it with `csrf_field()`; the check runs
  centrally in `public/index.php` before any route is dispatched.
- Any "come back here" field goes through `local_path()`, which accepts only a path on
  this site. `//evil.com` starts with a slash but is another host, so it is rejected.
- **Rate limits**: 10 posts a day, 5 comments a minute, 5 failed logins per 15 minutes.
  The first two need no table — the rows being limited are the record. Failed logins
  leave nothing behind, so they get `login_attempts`, keyed on email **and** IP. A
  session counter would be worthless: the attacker controls the cookie.

### Content

- Posts are **soft-deleted**: `deleted_at` is set and every read filters
  `deleted_at IS NULL`. A deleted post is a 404 even for its author and for an admin;
  `find_deleted_post()` is the one query that inverts the filter, for the restore page.
- Images are kept on disk when a post is soft-deleted, so restoring brings them back.
- Creating or editing a post is a **transaction**: the post, its tags and its image rows
  commit together. The filesystem is not transactional, so uploads are moved **before**
  the transaction opens, deleted in the `catch` if it fails, and old files are deleted
  only **after** commit.
- Uploads are validated by decoding the bytes with `getimagesize()`, never by the
  filename or the client's MIME type, and stored under a random name. They land in a
  directory the web server will execute.
- Editing or deleting a post needs ownership, a different question from a permission:
  `can('manage_posts')` gives the same answer for every post, while `owns_post($post)`
  depends on the row, so it can only be checked after the lookup.
- Comments are capped at two levels **on write**: a reply to a reply becomes a sibling,
  so rendering never recurses. A deleted comment that still has live replies is kept in
  the query and rendered as `[deleted]`, so the thread does not collapse.
- Likes insert first and let the `UNIQUE (user_id, post_id)` key decide. Checking first
  is two statements, and two fast clicks both pass the check.

### The feed

- `sort` is a whitelist key, never a fragment of SQL: `ORDER BY` cannot take a
  placeholder, so the user's value *selects* a fragment you wrote.
  `?page=-5&sort=; DROP TABLE users; --` renders page 1, newest first.
- `page` is clamped at both ends, and the offset is computed **after** the clamp.
- A page of ten posts costs **five queries**: the count, the page, tags, images and
  likes. Tags, images and likes are batched by id, so the number does not grow with the
  page size.
- `LIKE` wildcards are added in PHP (`'%' . $q . '%'`), not in the SQL string, because a
  placeholder stands for a whole value.

### Moderation and the log

- `reports.target_type` / `target_id` is polymorphic and deliberately has **no foreign
  key**: the target is a post or a comment. That is why the queue fetches each kind with
  its own `IN (…)` query rather than joining, and why a report can outlive its target.
- Hiding is the same soft delete an author performs — one mechanism, two callers.
- `activity_log.user_id` is nullable with `ON DELETE SET NULL`. Every other foreign key
  in the schema cascades; this one must not, or the audit trail would be deletable by
  the person it incriminates. The listing uses a `LEFT JOIN` for the same reason.
- Logging happens in handlers, never in `src/`, so a fixture calling `create_post()`
  writes no audit rows.

## Tests

Nineteen suites, **587 checks**, each targeting its own port so they never share a
session. They cover authentication, RBAC, CSRF, profiles, pictures, posts, comments,
likes, the feed, reports, moderation, deleted posts, rate limits, the activity log, the
layout, and the eight acceptance scenarios from the spec.
