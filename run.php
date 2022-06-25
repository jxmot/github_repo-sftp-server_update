<?php
define('_DEVTEST', 'devtest/');
$bpath = realpath('.') . '/' . (defined('_DEVTEST') ? _DEVTEST : '');

if($argc < 2) {
//    $runit = $bpath . 'run.json';
    $runit = $bpath . 'rundevtest.json';
} else {
    $runit = $bpath . strtolower($argv[1]) . '.json';
}

if(file_exists($runit) === false) {
    throw new \RuntimeException("ERROR: {$runit} does not exist.");
}

$runcfg = json_decode(file_get_contents($runit));


require_once 'init.php';



$move = [];

function copyFiles($srcfile) {
global $ghrepo;
global $sftp;
global $move;
    // excluded?
    if($ghrepo->isPathExcluded($srcfile)) return false;
    // copy from sourceroot/repofile to docroot/staging/repofile
    $src  = $ghrepo->getSourceRoot() . $srcfile;
    $dest = $sftp->getDocRoot() . $ghrepo->getStaging() . $srcfile;
    $targ = $sftp->getDocRoot() . $srcfile;
    array_push($move, [$dest, $targ]);

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




$chgdata = null;
$rtags = $ghrepo->getTags();

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
    //echo count($reldata) . "\n";
    if(count($reldata) > 2) {
        echo "tag 0 - " . $reldata[0]->tag_name . "\n";
        echo "tag 1 - " . $reldata[1]->tag_name . "\n";
        $chgdata = json_decode($ghrepo->getRepoChanges($reldata[1]->tag_name,$reldata[0]->tag_name));
    } else {
        echo ">" . count($reldata) . "\n";
        die();
    }
}




$newfiles = [];
$modfiles = [];
$delfiles = [];

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





/*
*/
$test = $runcfg->debug;

$srcroot = $ghrepo->getSourceRoot();

for($ix = 0; $ix < count($newfiles); $ix++) {
    if($test === true) {
        if($ghrepo->isPathExcluded($newfiles[$ix])) {
            echo "EXclude new " . $srcroot .$newfiles[$ix] . "\n";
        } else {
            echo "include new " . $srcroot . $newfiles[$ix] . "\n";
            if(strrpos($newfiles[$ix], '.sh') === (strlen($newfiles[$ix]) - 3)) {
                echo "CHMOD - " . $newfiles[$ix] . "\n";
            }
        }
    } else {
        $ret = copyFiles($newfiles[$ix]);
        if(!$ret) echo "new excluded - $newfiles[$ix]\n";
    }
}

for($ix = 0; $ix < count($modfiles); $ix++) {
    if($test === true) {
        if($ghrepo->isPathExcluded($modfiles[$ix])) {
            echo "EXclude mod " . $srcroot .$modfiles[$ix] . "\n";
        } else {
            echo "include mod " . $srcroot .$modfiles[$ix] . "\n";
            if(strrpos($modfiles[$ix], '.sh') === (strlen($modfiles[$ix]) - 3)) {
                echo "CHMOD - " . $modfiles[$ix] . "\n";
            }
        }
    } else {
        $ret = copyFiles($modfiles[$ix]);
        if(!$ret) echo "mod excluded - $modfiles[$ix]\n";
    }
}

if(count($move) > 0) {
}
?>