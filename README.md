# Homestead

This script installs and configures a full Homestead system, consisting of [Eigenheim](https://github.com/maxhaesslein/eigenheim), [Sekretär](https://github.com/maxhaesslein/sekretaer) and [Postamt](https://github.com/maxhaesslein/postamt).


## Initial Setup

Your server needs to run at least PHP 8.0 or later.

Download the latest release from the [releases page](https://github.com/maxhaesslein/homestead/releases), and extract it to a folder on your webserver. Point your webbrowser to the `install.php` file (for example, `https://www.example.com/install.php`); this will guide you through the setup process, which will install and configure all modules in their own subfolders. After the installation, the `install.php` gets deleted automatically.

You then can open the website at `https://www.example.com`. The Sekretär backend can be reached inside the `sekretaer/` subfolder (for example, `https://www.example.com/sekretaer/`); there you can log in with your root URL (`https://www.example.com`). This will change in the future, when we add the authorization module.


## Updating

An update script will follow in the future. For now, you can update each module individually, see the `README.md` inside the `eigenheim/`, `sekretaer/` and `postamt/` folders.


## Backup

You can export the sites you follow in Sekretär, when managing feeds.

To backup your Eigenheim posts, copy the folder `eigenheim/content/` to a safe location.

For detailed instructions, read the `README.md` inside the `eigenheim/`, `sekretaer/` and `postamt/` folders.
