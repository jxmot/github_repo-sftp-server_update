<?php
function appEcho($text) {
    global $runcfg;
    if($runcfg->verbose === true) {
        if($runcfg->tstamp === true) {
            echo rightnow('log') . " - " .$text;
        } else echo $text;
    }
}
?>