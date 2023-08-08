# Knot Installer

This script installs and configures a full Knot System, consisting of the modules [Knot Site](https://github.com/maxhaesslein/knot-site), [Knot Home](https://github.com/maxhaesslein/knot-home), [Knot Daemon](https://github.com/maxhaesslein/knot-daemon), [Knot Auth](https://github.com/maxhaesslein/knot-auth) and [Knot Control](https://github.com/maxhaesslein/knot-control)

**This is an early beta version!** Some things may break, or change in the future!

## Initial Setup

Your server needs to run at least PHP 8.0 or later.

Download the latest `knot-install.php` and upload it to a folder on your webserver. Point your webbrowser to the `knot-install.php` file (for example, `https://www.example.com/knot-install.php`) - make sure, that the URL you open is the URL you want to use as your home page; if you want to use `https://`, open the `knot-install.php` with `https://`, and if you want to use a `www`-subdomain, make sure to include it. The `knot-install.php` will then guide you through the setup process, which will install and configure all modules in their own subfolders. After the installation, the `knot-install.php` gets deleted automatically.

You then can open the website at `https://www.example.com`.

The Knot Home backend can be reached inside the `knot-home/` subfolder (for example, `https://www.example.com/knot-home/`); there you can log in with your root URL (`https://www.example.com`).

For configuring and updating the Knot System install, log into the Knot Control backend at the `knot-control/` subfolder (for example, `https://www.example.com/knot-control/`); there you can log in with your root URL (`https://www.example.com`).


## Backup

You can export the sites you follow in Knot Home, when managing feeds.

To backup your Knot Site posts, copy the folder `knot-site/content/` to a safe location.

To backup the configuration, copy the `config.php` files inside the `knot-site/`, `knot-home/`, `knot-daemon/`, `knot-auth/` and `knot-control` subfolders.

For detailed instructions, read the `README.md` inside the `knot-site/`, `knot-home/`, `knot-daemon/`, `knot-auth/` and `knot-control/` subfolders.


## Changing the domain name

When you want to change the domain name (or move to a new domain), you need to update the domain at these places:

- in the `knot-site/config.php` update the URL at the `baseurl_overwrite` option. Also update the `mircosub` option (but keep the `/knot-daemon/` at the end)
- in `knot-daemon/config.php` update the URL in the `allowed_urls` option

if you change the (or move to or from a) subfolder, you also need to update the subfolder in these places:

- in the `knot-site/config.php` update the `basefolder_overwrite` option
- update the `RewriteBase` option in the `.htaccess`, `knot-site/.htaccess`, `knot-home/.htaccess`, `knot-daemon/.htaccess`, `knot-auth/.htaccess` and `knot-control/.htaccess` files
