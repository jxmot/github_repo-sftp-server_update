# This is a "work in progress"

**2022-06-21 Some code has been written, and a "proof of concept" application was created. As development proceeds this README will be updated. Stay Tuned!**

# github_repo-sftp-server_update

**GitHub Repository Changes, Server Update via SFTP**

This is a PHP application that:

* Uses the GitHub API V3 to:
  * Obtain release tags from a repository.
  * Obtains a list of changed files between two tags.
* Builds a list of (local) path+files using the changed file list.
* Uses SFTP to transfer the (local) files to a server using predefined destinations(*aka "modes"*):
  * **staging**: uploads *select* paths and files to a designated "staging" area on the server.
  * **test**: uploads *select* paths and files to a "testing" area on the server. 
  * **live**: uploads *select* paths and files to the "live" area on the server.
* Uses SFTP to transfer(*back up*) files **from** the server prior to overwriting them. NOTE: The file timestamps are preserved when transferring them to the server.
* Run from the command line or execute via a script.

The primary use of this application is to keep files up to date on a web server. It will rely on the following:

* The GitHub repository exists on local storage. This is likely to be the location where you edit and locally test your files.
* The local repository is up to date with GitHub. Local files can be newer or different from what is kept in GitHub. However if any files are **not** committed and pushed then they will not appear as "changed".
* The repository has tagged releases:
  * Start with "0.0.0" and tag a release before any project files are added to the repository.
  * When preparing to stage, test, or deploy be sure to tag a release.

## Tag Names

[*Semantic versioning*](https://semver.org/) is strongly recommended. Be sure to tag a *release*, that operation will create a zip-file and a gzip file containing the current (*at the time of tagging*) repository contents. *NOTE: The zip/gzip files might be used in a subsequent version of this application.*

## First Time File Update

For the given repository the application can obtain the changed files that occurred between the "0.0.0" tag and "HEAD". 

### Subsequent Updates

For the given repository the application can obtain the changed files that occurred between the *latest* tag and the one *prior* to the latest. Any files that have changed(*committed and pushed*) after the latest tag *may* not be transfered. However it will be possible to choose "HEAD" tag and the one *prior* to the latest tag.

## Best Practices

* When creating a new repository that this application will use when updating a server:
  * Immediately after creation tag the repository with 0.0.0 (*numeric only revision numbers are recommended*)
  * 
* Prior to updating files on the server:
  * Have properly tagged releases.
  * All changed files have been committed and pushed to the repository.
  * TBD

## Server and Client

Although this application could be used to place files anywhere on the server it was designed with *web servers* in mind. 

The **server** environment where this application was originally intended for is:

* Apache 2.X
* cPanel
* Linux
* Document root path: `/home/USER/public_html`

The **client** environment where this application was originally intended for is:

* PHP >5.6
* Website repositories have a `public_html` folder where all of the website files are contained. 
* This application requires *slightly modified* version of [phpseclib](<https://github.com/phpseclib/phpseclib>) for SSH and SFTP. You will need to install it with *composer* and then copy the modified source to `/path/to/this/repository/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP.php`. Yes, there are 3 `phpseclib` in the path.

### Folder Hierarchy Examples

**Server:**

```
/home/USER
        |
        +---- public_html
                     +--- website folders and files
```

**Client:**

```
path_to_repository 
        |
        +---- public_html
        |            +--- website folders and files
        |
        +---- other project folders and files, optional copy to server
```

## Required Preparation

* Obtain a GitHub *personal access token*: read:user, repo
* Obtain a *private key* and a *pass phrase* for your server's SSH/SFTP connection.
* Install [phpseclib **3.0.14**](<https://github.com/phpseclib/phpseclib>) via composer into this repository. Then copy `/path/to/this/repository/phpseclib/SFTP.php` to `/path/to/this/repository/vendor/phpseclib/phpseclib/phpseclib/Net/SFTP.php`. See [phpseclib Notes](#phpseclib_notes) for more information.

# Configuration

All configuration data is saved in JSON formatted files.

## Application Run

## GitHub User

## GitHub Token

### User Owned Repositories

## SFTP Server

# Running the Application

See [Required Preparation](#required_preparation) before proceeding, this application will not run successfully until the preparations are completed.

# phpseclib Notes

The version of [phpseclib](https://github.com/phpseclib/phpseclib) used here is **3.0.14**. For the most part it appears to be bug free *so far*. However one bug has appeared and I have made a "fix" to the phpseclib source in this repository.

**Installation:** `composer require phpseclib/phpseclib:~3.0`

**Bug Description:** When "date preservation" is enabled a run-time exception occurs: `'Error setting file time'` when copying files to/from the server.

**Cause:** After `stat()` returns the code attempts to access the "file time" with `$stat['time']`. The problem is that there is no member in `$stat[]` named `'time'`. The correct name is `'mtime'`.

**Correction:** Change all occurences of `$stat['time']` to `$stat['mtime']`.

**Note:** This appears to be fixed in this [commit](https://github.com/phpseclib/phpseclib/commit/e700ac75612024c0aea72413d1f3731b0fa71910). It was created 10 days after the 3.0.14 release.

# Future

* Obtain files from GitHub via the API instead of locally.
* TBD

