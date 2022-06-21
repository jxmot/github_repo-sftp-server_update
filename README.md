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
    * If the 0.0.0 tag is created sometime *after* the repository and other tags have been created and other tags then it *should be* OK. The application will *sort* the tags prior to using them.
  * Try to be consistent in how you advance the version number. 
* Prior to updating files on the server:
  * Have properly at least two tagged releases.
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

```
{
    "user":"github_username",
    "server":"path/to/server-yourserver.json",
    "repo":"path/to/repo-your_repository_name_on_github.json",
    "mode":"stage",
    "verbose": true
}
```

## GitHub User

```
{
    "user": "github_username",
    "tokenfile": "path/to/token-github_username.json"
}
```

## GitHub Token

```
{
    "note":"not used, here for convenience to aid in identifying which token is being used",
    "token":"your personal access token goes here"
}
```

### User Owned Repositories

**This portion is under development and is likely to change.**

```
{
    "name": "your_repository_name_on_github",
    "stage": {
        "_comment": "the enitre repository root folder contents will go here.",
        "dest": "%DOCROOT%/temp/stage/",
        "sourceroot": ""
    },
    "test": {
        "_comment": [
            "%DOCROOT% is 'docroot' in sftp-server.json, and everything ",
            "found in repo/'sourceroot' will be copied to "dest"."
        ],
        "dest": "%DOCROOT%/test/",
        "sourceroot": "public_html/"
    },
    "live": {
        "_comment": "",
        "dest": "%DOCROOT%",
        "sourceroot": "public_html/"
    },
    "_comment00": "path to the location of the repostiory's root folder",
    "sourceroot": "../../",
    "_comment01": [
        "global exclusions, looks at changed files(paths included) and ",
        "if a match is found then that file is not copied to the server."
        "use regex here, add entries to array as needed"
    ]
    "exclude": ["/^folder_in_repo/i","/gitignore/i","/gitkeep/i","/readme/i"],
    "_comment02": [
        "if endtag is present then use that tag instead of the repos last found tag",
        "and the begtag=(last found - 1) will be what is found in the repo.",
        "actually HEAD is the only choice here other than empty"
    ],
    "endtag": "HEAD",
    "_comment03": [
        "can use 0.0.0 here, but",
        "requires that the repo has a 0.0.0 release, this ",
        "can be done at any time... create a tag 0.0.0 then ",
        "attached a release to the existing tag.",
        "NOTE: creating the 0.0.0 results in a PushEvent without",
        "any commit content.",
    ],
    "begtag": "2.1.4",
}
```

## SFTP Server

```
{
    "keyfile": "ssh/private_key_file",
    "phrasefile": "ssh/yourserver-passphrase.json",
    "server": "yourserver.whatever",
    "login": "yourserver_login",
    "home": "/home/yourserver_login/",
    "docroot": "/home/yourserver_login/public_html/"
}
```

```
{
    "phrase": "yourserver-passphrase-goes-here"
}
```

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

