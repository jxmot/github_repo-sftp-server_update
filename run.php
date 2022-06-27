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

appEcho('backup is ' . (isBackupEnabled($runcfg->mode) ? 'ON' : 'OFF') . "\n");

if(isBackupEnabled($runcfg->mode)) {
    $bupath = getBackupPath($runcfg->mode);
    appEcho("backup path - $bupath\n");
    // backup folder will be made even if nothing goes in it.
    if($debug === false) mkdir($bupath, 0755, true);
} else {
    $bupath = '';
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

// if the backup destination is empty then 
// remove the empty timestamped folder
if(isBackupEnabled($runcfg->mode) && ($debug === false)) {
    $res = scandir($bupath);
    if(count($res) <= 2) {
        appEcho("removing empty dir - $bupath\n");
        if($debug === false) rmdir($bupath);
    }
}
?>