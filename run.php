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

/*
*/
$debug = $runcfg->debug;
if($debug) echo "DEBUG mode is ON\n";
if($runcfg->verbose) echo "VERBOSE is ENABLED\n\n";

require_once 'rightnow.php';
require_once 'appecho.php';
require_once 'init.php';

$chgdata = null;
$newfiles = [];
$modfiles = [];
$delfiles = [];

require_once 'utils.php';

getChangedFiles($runcfg->mode);

sortFiles();

if(isBackupEnabled($runcfg->mode)) {
    $bupath = getBackupPath($runcfg->mode);
    appEcho("backup is ON, path = $bupath\n");
    // backup folder will be made even if nothing goes in it.
    if($debug === false) mkdir($bupath, 0755, true);
    // but if it's empty when we're done then the timestamped 
    // folder will be removed and the rest of the path will 
    // remain intact.
} else {
    appEcho("backup is OFF\n");
    $bupath = '';
}

appEcho(">>>>> BEGIN\n");

if(($metrics !== null) && ($runcfg->metrics)) {
    $metrics->start();
}

$reporoot = $ghrepo->getRepoRoot();

for($ix = 0; $ix < count($newfiles); $ix++) {
    if($debug === true) {
        if($ghrepo->isPathExcluded($newfiles[$ix])) {
            appEcho("EXclude new $reporoot$newfiles[$ix]\n");
        } else {
            appEcho("include new $reporoot$newfiles[$ix]\n");
            if(strrpos($newfiles[$ix], '.sh') === (strlen($newfiles[$ix]) - 3)) {
                appEcho("CHMOD - $newfiles[$ix]\n");
            }
        }
    } else {
        $ret = copyToServer($newfiles[$ix], $runcfg->mode);
        if(!$ret) appEcho("new excluded - $newfiles[$ix]\n");
    }
}

for($ix = 0; $ix < count($modfiles); $ix++) {
    if($debug === true) {
        if($ghrepo->isPathExcluded($modfiles[$ix])) {
            appEcho("EXclude mod $reporoot$modfiles[$ix]\n");
        } else {
            appEcho("include mod $reporoot$modfiles[$ix]\n");
            if(strrpos($modfiles[$ix], '.sh') === (strlen($modfiles[$ix]) - 3)) {
                appEcho("CHMOD - $modfiles[$ix]\n");
            }
        }
    } else {
        $ret = copyToServer($modfiles[$ix], $runcfg->mode, false);
        if(!$ret) appEcho("mod excluded - $modfiles[$ix]\n");
    }
}

// TODO: See what happens if a files is deleted from the repo and 
// if we can see it here. If so, then delete the file from the server.

// if the backup destination is empty then remove the empty timestamped folder
if(isBackupEnabled($runcfg->mode) && ($debug === false)) {
    $res = scandir($bupath);
    if(count($res) <= 2) {
        appEcho("removing empty folder - $bupath\n");
        if($debug === false) rmdir($bupath);
    }
}

if(($metrics !== null) && ($runcfg->metrics)) {
    $metrics->stop();
    $metrics->recordMetrics();
}
appEcho(">>>>> END\n");
?>