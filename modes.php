<?php
function stage($srcfile) {
    global $ghrepo, $sftp;
    if($ghrepo->getStage()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->getStage()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->getStage()->dest) . $srcfile;
};

function test($srcfile) {
    global $ghrepo, $sftp;
    if($ghrepo->getTest()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->getTest()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->getTest()->dest) . $srcfile;
};

function live($srcfile) {
    global $ghrepo, $sftp;
    if($ghrepo->getLive()->sourceroot !== '') {
        $srcfile = str_replace($ghrepo->getLive()->sourceroot, '', $srcfile);
    }
    return str_replace('%DOCROOT%', $sftp->getDocRoot(), $ghrepo->getLive()->dest) . $srcfile;
};
?>