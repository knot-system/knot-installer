# Homestead

This script installs and configures a full Homestead system, consisting of [Eigenheim](https://github.com/maxhaesslein/eigenheim), [Sekretär](https://github.com/maxhaesslein/sekretaer) and [Postamt](https://github.com/maxhaesslein/postamt).


## Initial Setup

Your server needs to run at least PHP 8.0 or later.

Download the latest release from the releases page, and extract it to a folder on your webserver. Open the `install.php`. This will guide you through the setup process, which will install and configure all modules in their own subfolders.

You then can open the website at `https://www.example.com`. The Sekretär backend lives at `https://www.example.com/sekretär/`, and you need to log in with your root URL `https://www.example.com` (this will change in the future, when we add the authorization module).
