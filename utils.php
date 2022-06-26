<?php
require_once 'modes.php';

function copyToServer($srcfile, $mode) {
global $ghrepo, $sftp;

    // excluded?
    if($ghrepo->isPathExcluded($srcfile)) return false;
    // copy from reporoot/repofile to docroot/staging/repofile
    $src  = $ghrepo->getRepoRoot() . $srcfile;

    // handle the current mode
    if(function_exists($mode) === true) {
        $dest = $mode($srcfile);
    } else {
        throw new \UnexpectedValueException('ERROR: '.__FUNCTION__.'() unknown mode - '.$mode);
    }

    echo "copy from $src to $dest\n";

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

    $rtags = $ghrepo->getTags($mode);

    if($rtags[0] !== "" && $rtags[1] !== "") {
        echo $rtags[0]." to ".$rtags[1]."\n";
        $chgdata = json_decode($ghrepo->getRepoChanges($rtags[0],$rtags[1]));
        echo "total = ".count($chgdata->files)."\n";
    } else {
        $reldata = json_decode($ghrepo->getRepoReleases());
        if($reldata === null) {
            // https://www.php.net/manual/en/spl.exceptions.php
            throw new \UnexpectedValueException('ERROR: reldata is null');
        }
        if(count($reldata) > 2) {
            echo "tag 0 - " . $reldata[0]->tag_name . "\n";
            echo "tag 1 - " . $reldata[1]->tag_name . "\n";
            $chgdata = json_decode($ghrepo->getRepoChanges($reldata[1]->tag_name,$reldata[0]->tag_name));
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
    echo "new - " . count($newfiles) . "\n";
    echo "mod - " . count($modfiles) . "\n";
    echo "del - " . count($delfiles) . "\n";
}
?>