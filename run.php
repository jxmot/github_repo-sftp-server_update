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

$chgdata = null;
$newfiles = [];
$modfiles = [];
$delfiles = [];

require_once 'utils.php';

getChangedFiles($runcfg->mode);

sortFiles();

/*
*/
$test = $runcfg->debug;

$reporoot = $ghrepo->getRepoRoot();

for($ix = 0; $ix < count($newfiles); $ix++) {
    if($test === true) {
        if($ghrepo->isPathExcluded($newfiles[$ix])) {
            echo "EXclude new " . $reporoot . $newfiles[$ix] . "\n";
        } else {
            echo "include new " . $reporoot . $newfiles[$ix] . "\n";
            if(strrpos($newfiles[$ix], '.sh') === (strlen($newfiles[$ix]) - 3)) {
                echo "CHMOD - " . $newfiles[$ix] . "\n";
            }
        }
    } else {
        $ret = copyToServer($newfiles[$ix], $runcfg->mode);
        if(!$ret) echo "new excluded - $newfiles[$ix]\n";
    }
}

for($ix = 0; $ix < count($modfiles); $ix++) {
    if($test === true) {
        if($ghrepo->isPathExcluded($modfiles[$ix])) {
            echo "EXclude mod " . $reporoot .$modfiles[$ix] . "\n";
        } else {
            echo "include mod " . $reporoot .$modfiles[$ix] . "\n";
            if(strrpos($modfiles[$ix], '.sh') === (strlen($modfiles[$ix]) - 3)) {
                echo "CHMOD - " . $modfiles[$ix] . "\n";
            }
        }
    } else {
        $ret = copyToServer($modfiles[$ix], $runcfg->mode);
        if(!$ret) echo "mod excluded - $modfiles[$ix]\n";
    }
}
?>