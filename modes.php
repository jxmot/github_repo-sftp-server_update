<?php
require_once 'rightnow.php';

function stage($srcfile) {
    global $ghrepo, $sftp;
    if($ghrepo->getStage()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->getStage()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->getStage()->dest) . $srcfile;
}

function test($srcfile) {
    global $ghrepo, $sftp;
    if($ghrepo->getTest()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->getTest()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->getTest()->dest) . $srcfile;
}

function live($srcfile) {
    global $ghrepo, $sftp;
    if($ghrepo->getLive()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->getLive()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->getLive()->dest) . $srcfile;
}

function isBackupEnabled($mode) {
    global $ghrepo;
    $func = 'get'.ucwords($mode);
    return $ghrepo->{$func}()->backup->enable;
}

function getBackupPath($mode) {
    global $ghrepo, $sftp;
    if(isBackupEnabled($mode)) {
        $func = 'get'.ucwords($mode);
        $path = str_replace(['%SERVER%','%REPO%','%MODE%','%TIMEDATE%'], 
                            [$sftp->getServer(), $ghrepo->getName(), $mode, rightnow('path')], 
                            $ghrepo->{$func}()->backup->path);
        return $path;
    } else return '';
}
?>