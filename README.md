# activeCollab Gitolite

activeCollab Gitolite Module which helps to create git-repositories directly from activeCollab

This module adds many new new features to activeCollab including:

* Create/manage git-repositories directly from activeCollab.
* Manage commit-access levels per user for every repository.
* Clone directly remote-repos from Github, bitbucket and/or private-repos from any remote git-server.
* Team-members can manage multiple public keys from their profile.
* Support for Github style webhooks.

activeCollab has limited built-in support for git repos. By default, you cannot create new git repos from activeCollab. You have to create them elsewhere, then clone them in activeCollab’s “work/git” folder on server and then you have to add them into activeCollab (again manually) :(

## This activeCollab-Gitolite module solves above problem and add many new features including:

* Create new git repositories and manage access-permission level for them within activeCollab.
* Interface to manage public SSH keys. Support for multiple public keys is already present.
* Option to clone git repos from remote server. Support for all public-server (e.g. Github) and private server which uses SSH-key-based authentication.
* Webhook support to trigger automatic code-update script on remote server, run continuous integration (CI) scripts.
* Works with new gitolite server as well as existing gitolite server. Also supports local as well remote gitolite server.

## Behind the scene…

This module uses [gitolite](https://github.com/sitaramc/gitolite) to setup a central git-server and manage git-hosting. You can install a new gitolite-setup on same machine on which activeCollab is currently running or use an existing gitolite (version 3x). [Gitolite is used by kernel.org to control git-access to linux’s source code](http://www.kernel.org/faq/#whygitolite). So you can definitely rely on it! ;-)

Note: For Ubuntu server, this module comes with a shell-script gitolite.sh to install a new gitolite instance automatically.
