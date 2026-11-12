# Default administrator login

For a fresh database imported from `database.sql`, the default account is:

- Username: `admin`
- Password: `admin123`

Change this password after your first login. The SQL seed uses `INSERT IGNORE`, so importing `database.sql` again does not reset the password of an account that already exists.

Alternatively, `setup_admin.php` creates an administrator with a random password when the `users` table is empty, then prints that password once. It refuses to run if any user already exists. Remove `setup_admin.php` from the server after setup.
