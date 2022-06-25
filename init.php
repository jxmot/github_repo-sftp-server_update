<?php

require_once 'GitHubOwner.php';
use serverupdate\GitHubOwner;
$ghuser = new GitHubOwner($bpath, $runcfg->owner);

echo 'Owner - ' . $ghuser->GetOwner() . "\n";
echo 'Token - ' . $ghuser->GetToken() . "\n";

require_once 'GitHubRepo.php';
use serverupdate\GitHubRepo;
$ghrepo = new GitHubRepo($ghuser, $bpath, $runcfg->repo);

require_once 'supdSFTP.php';
use serverupdate\supdSFTP;
$sftp = new supdSFTP($bpath, $runcfg->server);

echo 'Home    - ' . $sftp->getHome() . "\n";
echo 'DocRoot - ' . $sftp->getDocRoot() . "\n";
?>