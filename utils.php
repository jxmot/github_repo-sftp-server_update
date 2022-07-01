<?php
require_once 'modes.php';

function backupFromServer($mode, $dest) {
global $ghrepo, $sftp, $bupath;

    // if this mode has backups enabled then create 
    // the path and the local folders to contain the 
    // backed up files.
    if(isBackupEnabled($mode) && $sftp->file_exists($dest)) {
        $budest = $bupath . str_replace(getRepoDest($mode), '', $dest);

        appEcho("bu fr $dest\n");
        appEcho("bu to $budest\n");

        // make path...
        // split path and file
        $pos  = strrpos($budest, '/');
        $path = substr($budest, 0, $pos + 1);
        // if the path exists then don't mkdir again
        if(is_dir($path) === false) {
            // make the folders recursively
            mkdir($path, 0755, true);
        }
        // get the file
        $sftp->get($dest, $budest);
        return true;
    } else return false;
}

function copyToServer($srcfile, $mode, $newfile = true) {
global $ghrepo, $sftp, $bupath;

    // excluded?
    if($ghrepo->isPathExcluded($srcfile)) return false;

    // copy from reporoot/repofile to docroot/staging/repofile
    $src  = $ghrepo->getRepoRoot() . $srcfile;

    // handle the current mode...
    $dest = getModeDest($mode, $srcfile);

    // if this mode has backups enabled then create 
    // the path and the local folders to contain the 
    // backed up files.
    if($newfile === false) backupFromServer($mode, $dest);

    appEcho("copy from $src to $dest\n");

    // make path
    // split path and file
    $pos  = strrpos($dest, '/');
    $path = substr($dest, 0, $pos + 1);
    // make the folders recursively
    $sftp->mkdir($path);
    // copy the file to the server
    $sftp->put($dest, $src);
    // make shell script files executable 
    if(strrpos($srcfile, '.sh') === (strlen($srcfile) - 3)) {
        $sftp->chmod(0755, $dest);
    }
    return true;
}

function getChangedFiles($mode) {
global $ghrepo, $chgdata;

// TODO: return the $this->repo->{$mode}->tags object instead
    $rtags = $ghrepo->getTags($mode);

    if($rtags[0] !== "" && $rtags[1] !== "") {
        appEcho("{$rtags[0]} to {$rtags[1]}\n");
        $chgdata = json_decode($ghrepo->getRepoChanges($rtags[0],$rtags[1]));
        appEcho("total = ".count($chgdata->files)."\n");
    } else {
        $reldata = json_decode($ghrepo->getRepoReleases());
        if($reldata === null) {
            // https://www.php.net/manual/en/spl.exceptions.php
            throw new \UnexpectedValueException('ERROR: reldata is null');
        }
        if(count($reldata) > 2) {
            $tags = array();
            foreach($reldata as $rel) {
                // skip tags with alpha characters
                if(!preg_match("/[a-z]/i", $rel->tag_name)) {
                    array_push($tags, $rel->tag_name);
                }
            }
            // sort descending
            rsort($tags);
            appEcho("tag 0 - {$tags[0]}\n");
            appEcho("tag 1 - {$tags[1]}\n");
            $chgdata = json_decode($ghrepo->getRepoChanges($tags[1],$tags[0]));
            $metrics->setTags($tags[1],$tags[0]);
        } else {
            throw new \UnexpectedValueException('ERROR: reldata count is bad ' . count($reldata));
        }
    }
}

function sortFiles() {
global $chgdata;
global $newfiles;
global $modfiles;
global $delfiles;

    for($ix = 0;$ix < count($chgdata->files);$ix++) {
        switch(strtolower($chgdata->files[$ix]->status)) {
            case 'added':
                array_push($newfiles, $chgdata->files[$ix]->filename);
                break;
    
            case 'modified':
                array_push($modfiles, $chgdata->files[$ix]->filename);
                break;
    
            case 'deleted':
                array_push($delfiles, $chgdata->files[$ix]->filename);
                break;
    
            default:
                throw new \UnexpectedValueException('ERROR: unknown status - '.$chgdata->files[$ix]->status);
                break;
        }
    }
    appEcho("new - ".count($newfiles)."\n");
    appEcho("mod - ".count($modfiles)."\n");
    appEcho("del - ".count($delfiles)."\n");
}
?>