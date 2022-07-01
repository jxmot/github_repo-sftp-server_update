<?php
require_once 'GitHubOwner.php';
use serverupdate\GitHubOwner;
$ghuser = new GitHubOwner($bpath, $runcfg->owner);

appEcho("Owner - {$ghuser->GetOwner()}\n");
appEcho("Token - {$ghuser->GetToken()}\n");

require_once 'GitHubRepo.php';
use serverupdate\GitHubRepo;
$ghrepo = new GitHubRepo($ghuser, $bpath, $runcfg->repo);

require_once 'supdSFTP.php';
use serverupdate\supdSFTP;
$sftp = new supdSFTP($bpath, $runcfg->server);

appEcho("Home    - {$sftp->getHome()}\n");
appEcho("DocRoot - {$sftp->getDocRoot()}\n");


require_once 'Metrics.php';
use serverupdate\Metrics;

if($runcfg->metrics) {
    $metrics = new Metrics($ghuser->GetOwner(), 
                           $ghrepo->getName(),
                           $sftp->getServer(),
                           $runcfg->mode,
                           $runcfg->debug);

    appEcho("metrics file - " . $metrics->getFileName() . "\n");
} else {
    $metrics = null;
}
?>