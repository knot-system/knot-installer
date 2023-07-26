# Homestead Installer

This script installs and configures a full Homestead system, consisting of the modules [Eigenheim](https://github.com/maxhaesslein/eigenheim), [Sekretär](https://github.com/maxhaesslein/sekretaer), [Postamt](https://github.com/maxhaesslein/postamt), [Einwohnermeldeamt](https://github.com/maxhaesslein/einwohnermeldeamt) and [Homestead Control](https://github.com/maxhaesslein/homestead-control)


## Initial Setup

Your server needs to run at least PHP 8.0 or later.

Download the latest `homestead-install.php` and upload it to a folder on your webserver. Point your webbrowser to the `homestead-install.php` file (for example, `https://www.example.com/homestead-install.php`) - make sure, that the URL you open is the URL you want to use as your home page; if you want to use `https://`, open the `homestead-install.php` with `https://`, and if you want to use a `www`-subdomain, make sure to include it. The `homestead-install.php` will then guide you through the setup process, which will install and configure all modules in their own subfolders. After the installation, the `homestead-install.php` gets deleted automatically.

You then can open the website at `https://www.example.com`.

The Sekretär backend can be reached inside the `sekretaer/` subfolder (for example, `https://www.example.com/sekretaer/`); there you can log in with your root URL (`https://www.example.com`).

For configuring and updating the Homestead install, log into the Homestead Control backend at the `homestead-control/` subfolder (for example, `https://www.example.com/homestead-control/`); there you can log in with your root URL (`https://www.example.com`).


## Backup

You can export the sites you follow in Sekretär, when managing feeds.

To backup your Eigenheim posts, copy the folder `eigenheim/content/` to a safe location.

To backup the configuration, copy the `config.php` files inside the `eigenheim/`, `sekretaer/`, `postamt/`, `einwohnermeldeamt/` and `homestead-control` subfolders.

For detailed instructions, read the `README.md` inside the `eigenheim/`, `sekretaer/`, `postamt/`, `einwohnermeldeamt/` and `homestead-control/` subfolders.


## Changing the domain name

When you want to change the domain name (or move to a new domain), you need to update the domain at these places:

- in the `eigenheim/config.php` update the URL at the `baseurl_overwrite` option. Also update the `mircosub` option (but keep the `/postamt/` at the end)
- in `postamt/config.php` update the URL in the `allowed_urls` option

if you change the (or move to or from a) subfolder, you also need to update the subfolder in these places:

- in the `eigenheim/config.php` update the `basefolder_overwrite` option
- update the `RewriteBase` option in the `.htaccess`, `eigenheim/.htaccess`, `sekretaer/.htaccess`, `postamt/.htaccess`, `einwohnermeldeamt/.htaccess` and `homestead-control/.htaccess` files
