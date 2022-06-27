<?php
function appEcho($text) {
    global $runcfg;
    if($runcfg->verbose === true) echo $text;
}
?>