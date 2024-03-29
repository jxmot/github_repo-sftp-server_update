# This is a "work in progress"

**2022-06-21 Some code has been written, and a "proof of concept" application was created. As development proceeds this README will be updated. Stay Tuned!**

**2022-06-24** tagged release "0.0.1":

Using appropriately edited JSON configuration files:

* successfully copied files to a remote server, "stage" destination

**2022-06-26** tagged release "0.0.2":

Many changes, including:
* Prior to copying a file to the server, if it exists there then back up the file locally. This is optional on a per mode basis.
* README updates, still in draft
* Refactored JSON config files. Mostly in `repos/`.

The code was tested with a private repository and a "live" server. All modes (*as described in the README*) work as expected, including file backups. No bugs were **seen**.

**2022-06-28** tagged release "0.0.3":

* README updates
* Added appEcho() and a "verbose" flag in run.json, console output can be enabled or disabled.
  * Added `"tstamp": true` to run.json, when `true` console output messages are prepended with a timestamp.
* Refactored test(), stage(), and live() into getModeDest()

**2022-07-01** tagged release "0.0.4":

**IMPORTANT!**

At this time further development is **suspended**. This is due to not being able to determine if any file or folder has been removed/deleted or renamed. The apparent cause is that the necessary information is not provided by the GitHub v3 API.

I posted a question on the GitHub Community Forum [here](<https://github.community/t/tag-compare-where-are-the-deleted-removed-files/259349>).

When this issue is resolved development will resume.

Changes:

* Added Metrics.php, used for measuring the duration of file operations.


**2022-08-10** GitHub Community discussion board

**IMPORTANT!** It was turned into something awful and practically unusable. The post I made in July cannot be found, or accessed through the link. So any answers I may have received are lost forever. Gee thanks a lot!!!

So, thanks a lot GitHub for making this so bad (even though it "looks" nice). This is so far from any "normal" discussion board UI that it has to be a joke. After all who would design such a crappy thing? I Know! Micro$oft!!!

---

# github_repo-sftp-server_update

*GitHub Repository Changes to Server via SFTP*

The primary use of this application is to update files on a web server.

## Overview

<div align="center">
    <figure>
<!-- NOTE: When Github renders the images it will REMOVE the "margin", and ADD "max-width:100%" -->
        <img src="./mdimg/overview.png" style="width:75%;border: 2px solid black;margin-right: 1rem;"; alt="Application Overview"/>
        <br>
        <figcaption><strong>Operation Overview</strong></figcaption>
    </figure>
</div>
<br>

**1** - Request the releases for a specific repository, an array of "release" objects is returned that are sorted by the date they were created.<br>
**2** - Request the comparison between two tags, an object is returned containing an array named `"files"`. That array will be used for choosing which files to copy to the server. Each file will be *new*, *modified*, or *deleted*.<br>
**3** - If enabled, each *modified* file is copied from the server to a local backup folder before it is overwritten.<br>
**4** - Files that are *new* or *modified* are copied from local storage to the server. Folder paths on the server are created as needed.<br>

## Some Details

This is a PHP application that:

* Uses the GitHub API V3 to:
  * Obtain a list of tagged releases from a repository.
  * Obtain a list of changed files between two tags.
* Builds a list of (local) path+files using the changed file list.
* Uses SFTP to transfer the (local) files to a server using predefined destinations(*aka "modes"*):
  * **stage**: uploads *select* paths and files to a designated "staging" area on the server.
  * **test**: uploads *select* paths and files to a "testing" area on the server. 
  * **live**: uploads *select* paths and files to the "live" area on the server.
* Uses SFTP to transfer(*back up*) files **from** the server prior to overwriting them. NOTE: The file timestamps are preserved when transferring them to the server.
* If "backups" are enabled then prior to copying a file to the server it will be copied *from* the server and placed in   a timestamped backup folder.
* Run from the command line or execute via a script.

This application will rely on the following:

* The GitHub repository exists on local storage. This is likely to be the location where you edit and locally test your files.
* The local repository is up to date with GitHub. Local files can be newer or different from what is kept in GitHub. However if any files are **not** committed and pushed then they will not appear as "changed" and will not be copied to the server.
* The repository has tagged releases, see [Best Practices](#best_practices) below for details.

## Tagged Release Names

[*Semantic versioning*](https://semver.org/) is strongly recommended. Be sure to tag a *release*, that operation will create a zip-file and a gzip file containing the current (*at the time of tagging*) repository contents. *NOTE: The zip/gzip files might be used in a subsequent version of this application.*

**NOTE:** When a release is created two things happen, first a tag is created. And second the release is created.

## GitHub API - Get Tags vs Get Releases

A measurable amount of effort has gone into comparing the GitHub API responses to *repository releases* and *repository tags* requests. And it has been determined that requesting *repository releases* is the better choice.

| Endpoint | Sorted |    By   | Date & Time |
|:--------:|:------:|:-------:|:-----------:|
|   Tags   |  Yes   |   Tag*  | not present |
| Releases |  Yes   | Created |   present   |

* The result sorting cannot be changed for either endpoint.
* Tag* sorting gets strange if tags are **not** strictly numeric. This is a known problem and has had discussions on the GitHub Community forums.

## GitHub API Issues

The first issue is with the inability to know (*via the API*) which files have been "deleted" or "removed". The "compare" endpoint will return an array of files. But they will **only** be "new" or "modified". 

## First Time File Update

For the given repository the application can obtain the changed files that occurred between the "0.0.0" tag and "HEAD". 

### Subsequent Updates

For the given repository the application can obtain the changed files that occurred between the *latest* tag and the one *prior* to the latest. Any files that have changed(*committed and pushed*) after the latest tag *may* not be transfered. However it will be possible to choose "HEAD" tag and the one *prior* to the latest tag.

## Best Practices

* When creating a new repository that this application will use when updating a server:
  * Immediately after creation tag the repository with 0.0.0 (*numeric only revision numbers are recommended, this is due to how GitHub sorts tags*)
    * If the 0.0.0 tag is created sometime *after* repository creation, and other tags have been created then it *should be* OK. The application will *sort* the tags prior to using them.
  * Try to be consistent with how you advance the version number. 
* Prior to updating files on the server:
  * Have at least two tagged releases. 
  * All locally changed repository files have been committed and pushed to the repository.
  * TBD

## Server and Client

Although this application could be used to place files anywhere on the server it was designed with *web servers* in mind. 

The **server** environment where this application was originally intended for is:

* Apache 2.X
* cPanel
* Linux
* Document root path: `/home/USER/public_html`
* SSH and SFTP

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

In addition to being placed in appropriate locations the configuration files use this naming convention:

* **`owners/owner-*.json`**:
* **`tokens/token-*.json`**:
* **`repos/repo-*.json`**: 
  * **`repos/stage-*.json`**:
  * **`repos/test-*.json`**:
  * **`repos/live-*.json`**:
* **`servers/server-*.json`**: 

## Application Run

The default run-time configuration file name is `run.json`:

```
{
    "owner":"owners/owner-github_username.json",
    "server":"servers/server-yourserver.json",
    "repo":"repos/repo-your_repository_name_on_github.json",
    "mode":"stage",
    "verbose": true,
    "tstamp": true,
    "debug": false,
    "metrics": true
}
```

It is possible to keep more than one "run" JSON file. Just give them different names and pass the name as an argument to the application - 

```
php run.php run
```

If no argument is provided the default run file is `run.json`.

### Settings

* `"owner"` - 
* `"server"` - 
* `"repo"` - 
* `"verbose"` - 
* `"tstamp"` - 
* `"debug"` - 
* `"metrics"` - 

#### Metrics

```
{
    "owner":"github_username",
    "repo":"your_repository_name_on_github",
    "server":"yourserver.tld",
    "mode":"stage",
    "debug":true,
    "tags": {
        "beg":"",
        "end":""
    },
    "files": {
        "new":0,
        "mod":0,
        "del":0
    },
    "mstart": ["20220628","210532",1656468332],
    "mstop":  ["20220628","210532",1656468332],
    "dur":    ["00:50:27",3027]
}
```

`metrics/%OWNER%-%REPO%-%SERVER%-%MODE%-%DATETIME%.json`

## GitHub Repository Owner

This file should be named `owners/owner-github_username.json`:

```
{
    "owner": "github_username",
    "tokenfile": "tokens/token-github_username.json"
}
```

## GitHub Token

This file should be named `tokens/token-github_username.json`:

```
{
    "note":"not used, here for convenience to aid in identifying which token is being used",
    "token":"your personal access token goes here"
}
```

### User Owned Repositories

**This portion is under development and is likely to change.**

This file should be named `repos/repo-your_repository_name_on_github.json`:

```
{
    "name": "your_repository_name_on_github",
    "_comment00": [
        "each of the files below contains: destinaton path, ",
        "source root folder, and search tags."
    ],
    "stage": "repos/stage-%REPONAME%.json",
    "test": "repos/test-%REPONAME%.json",
    "live": "repos/live-%REPONAME%.json",
    "_comment01": "path to the location of the repository's local root folder",
    "reporoot": "../../",
    "_comment02": [
        "global exclusions, looks at changed files(paths included) and ",
        "if a match is found then that file is not copied to the server.",
        "use regex here, add entries to the array as needed"
    ],
    "exclude": ["/^folder_in_repo/i","/gitignore/i","/gitkeep/i","/readme/i"]
}
```

**Repository "Mode" Configuration Files**

Currently there are 3 "modes":

* stage - files will be uploaded to a "staging" area on your server. It's location and end-use is up to you.
* test - files will be uploaded to a "test" area on your server. For websites it would typically be located at `publc_html/test`.
* live - files will be uploaded to `public_html`.

Each mode can be configured to enable "backups". If enabled then files are backed up from the server just before uploading them to the server.

If you need to add more "modes":

* Add a function to `modes.php`. Then use that function name in the `"modes"` property in `run.json`. 
* Then create a JSON configuration file for the new mode using one of the existing files as a guide.
* Edit your repository's JSON file and add a new property using your new mode, and add a path to the mode file. For example:
  * `"newmode": "repos/newmode-%REPONAME%.json",` 

**"Stage" Mode Example Configuration**

```
{
    "_comment00": [
        "the entire repository root folder contents will go here.",
        "except if filtered out with the 'exclude' list."
    ],
    "dest": "%DOCROOT%temp/stage/",
    "sourceroot": "",
    "_comment01": "edit tags->beg and tags->end as needed",
    "tags": {
        "_comment00": [
            "can use 0.0.0 here, but",
            "requires that the repo has a 0.0.0 release, this ",
            "can be done at any time... create a tag 0.0.0 then ",
            "attached a release to the existing tag.",
            "NOTE: creating the 0.0.0 results in a PushEvent without",
            "any commit content."
        ],
        "beg":"2.1.4",
        "_comment01": [
            "if 'end' is present then use that tag instead of the repos last found tag",
            "and the beg=(last found - 1) will be what is found in the repo.",
            "actually HEAD is the only choice for 'end' other than empty"
        ],
        "end":"HEAD"
    },
    "backup": {
        "enable": false,
        "path": ""
    }
}
```

**"Test" Mode Example Configuration**

```
{
    "_comment": [
        "%DOCROOT% is 'docroot' in sftp-server.json, and everything ",
        "found in repo/'sourceroot' will be copied to 'dest'."
    ],
    "dest": "%DOCROOT%test/",
    "sourceroot": "public_html/",
    "tags": {
        "beg":"0.0.0",
        "end":"HEAD"
    },
    "backup": {
        "enable": true,
        "path": "backups/%SERVER%/%REPO%/%MODE%/%TIMEDATE%/"
    }
}
```

**"Live" Mode Example Configuration**

```
{
    "_comment": [
        "%DOCROOT% is 'docroot' in sftp-server.json, and everything ",
        "found in repo/'sourceroot' will be copied to 'dest'."
    ],
    "dest": "%DOCROOT%",
    "sourceroot": "public_html/",
    "tags": {
        "_comment": [
            "this will get all of the files in the repository. ",
            "the 'exclude' list will filter out unwanted files or folders."
        ]
        "beg":"0.0.0",
        "end":"HEAD"
    },
    "backup": {
        "enable": true,
        "path": "backups/%SERVER%/%REPO%/%MODE%/%TIMEDATE%/"
    }
}
```

The primary differences between the files are in the `"dest"` and `"sourceroot"` paths, and in `"tags":{}`. The other difference is in whether or not "backups" have been enabled. This can be done on a "per mode" basis.

### Backups

When enabled in a "mode", files are copied from the server to a location specified by `"path"` just prior to being overwritten.

```
    "backup": {
        "enable": true,
        "path": "backups/%SERVER%/%REPO%/%MODE%/%TIMEDATE%/"
    }
```

If no files are backed up the timestamped folder will be removed. 

### GitHub API Endpoints

This file is named `githubapi.json` and should not be modified :

```
{
    "releases": "https://api.github.com/repos/%OWNER%/%REPO%/releases",
    "compare":  "https://api.github.com/repos/%OWNER%/%REPO%/compare/%TAGOLD%...%TAGNEW%"
}
```

## SFTP Server

This file should be named `servers/server-yourserver.json`:

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

This file should be named `ssh/passphrase-yourserver.json`:

```
{
    "phrase": "yourserver-passphrase-goes-here"
}
```

# Running the Application

See [Required Preparation](#required_preparation) before proceeding, this application will not run successfully until the preparations are completed.

## Ready!

**Checklist:**

- [ ] **SSH/SFTP**: Set up SSH - 
  - [ ] downloaded private key file from your server, save it in the `ssh` folder and edit `"keyfile"` in `servers/server-yourserver.json`.
    - [ ] copy pass phrase into the `ssh/yourserver-passphrase.json` file and replace `yourserver` with something that identifies the server you will be connecting to.
    - [ ] edit `"phrasefile"` in `servers/server-yourserver.json`
- [ ] **GitHub** - 
  - [ ] Get a *personal access token*
  - [ ] Save the token in `tokens/token-github_username.json`
- [ ] **Other** - 
  - [ ] install [phpseclib](https://github.com/phpseclib/phpseclib)
  - [ ] copy `phpseclib/SFTP.php` to `vendor/phpseclib/phpseclib/phpseclib/Net/SFTP.php`

Replace the following occurrences of(*includes the renaming of files*):

- [ ] `yourserver` - replace with something that identifies the server you will be connecting to
- [ ] `.whatever` - the TLD of the server you will be connecting to
- [ ] `github_username` - use your GitHub user name
- [ ] `yourserver_login` - this is the login name used on your server for SSH/SFTP connections
- [ ] `your_repository_name_on_github` - this is the name of the repository that this application will access. Since a *personal access token* is used the repository can be public or private

# phpseclib Notes

The version of [phpseclib](https://github.com/phpseclib/phpseclib) used here is **3.0.14**. For the most part it appears to be bug free *so far*. However one bug has appeared and I have made a "fix" to the phpseclib source in this repository.

**Installation:** `composer require phpseclib/phpseclib:~3.0`

**Bug Description:** When "date preservation" is enabled a run-time exception occurs: `'Error setting file time'` when copying files to/from the server.

**Cause:** After `stat()` returns the code attempts to access the "file time" with `$stat['time']`. The problem is that there is no member in `$stat[]` named `'time'`. The correct name is `'mtime'`.

**Correction:** Change all occurences of `$stat['time']` to `$stat['mtime']`.

**Note:** This appears to be fixed in this [commit](https://github.com/phpseclib/phpseclib/commit/e700ac75612024c0aea72413d1f3731b0fa71910). It was created 10 days after the 3.0.14 release. As of this time (2022-06-21) there have been no new releases.

# Design Details

## Overview

## Build File List

## Copy Files to Server

## 

# Future

* Keep a log of which files were changed/uploaded
* TBD

---
<img src="http://webexperiment.info/extcounter/mdcount.php?id=github_repo-sftp-server_update">
