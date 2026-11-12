# Default administrator login

The initial administrator account is fixed:

- Username: `admin`
- Password: `Tomato@123`

Import `database.sql` through phpMyAdmin for both a fresh database and an existing database. On import, its admin seed creates the account if missing or resets the admin password to `Tomato@123`.

The database stores a PHP password hash. Normal page loads and login requests do not create or replace the account password. Only importing `database.sql` sets the admin password to the fixed default. `setup_admin.php` only creates the account when `admin` is missing. Remove that setup file after installation.

To choose a different fixed password, generate a new hash with PHP's `password_hash()` and update the hash in `database.sql` before importing it.
