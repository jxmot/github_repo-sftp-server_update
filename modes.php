<?php
require_once 'rightnow.php';

function isBackupEnabled($mode) {
    global $ghrepo;
    $func = 'get'.ucwords($mode);
    return $ghrepo->{$func}()->backup->enable;
}

function getBackupPath($mode) {
    global $ghrepo, $sftp;
    if(isBackupEnabled($mode)) {
        $func = 'get'.ucwords($mode);
        $path = str_replace(['%SERVER%','%REPO%','%MODE%','%DATETIME%'], 
                            [$sftp->getServer(), $ghrepo->getName(), $mode, rightnow('path')], 
                            $ghrepo->{$func}()->backup->path);
        return $path;
    } else return '';
}

function getRepoDest($mode) {
    global $ghrepo, $sftp;
    $func = 'get'.ucwords($mode);
    $tmp = $ghrepo->{$func}()->dest;
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $tmp);
}

function getModeDest($mode, $srcfile) {
    global $ghrepo, $sftp;
    $func = 'get'.ucwords($mode);
    if($ghrepo->{$func}()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->{$func}()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->{$func}()->dest) . $srcfile;
}
?>