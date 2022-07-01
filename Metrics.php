<?php
namespace serverupdate;

require_once 'rightnow.php';

class MetricsData {
    public $owner, $repo, $server, $mode, $debug;
    public $files, $tags;
    public $mstart, $mstop, $dur;
}

class Metrics {
    private $data;
    private $fname;

    public function __construct($owner, $repo, $server, $mode, $debug) {
        $this->data = new MetricsData();
        $this->data->files = new \stdClass();
        $this->data->tags = new \stdClass();

        $this->data->owner  = $owner;
        $this->data->repo   = $repo;
        $this->data->server = str_replace('.', '_', $server);
        $this->data->mode   = $mode;
        $this->data->debug  = $debug;
        $this->fname = str_replace(['%OWNER%','%REPO%','%SERVER%','%MODE%','%DATETIME%'], 
                                   [$this->data->owner,$this->data->repo,$this->data->server,$this->data->mode,rightnow('name2')], 
                                   './metrics/%OWNER%-%REPO%-%SERVER%-%MODE%-%DATETIME%.json');
    }

    public function setFiles($new, $mod, $del) {
        $this->data->files->new = $new;
        $this->data->files->mod = $mod;
        $this->data->files->del = $del;
    }

    public function setTags($beg, $end) {
        $this->data->tags->beg = $beg;
        $this->data->tags->end = $end;
    }

    public function start() {
        $this->data->mstart = json_decode(rightnow('json2'));
    }

    public function stop() {
        $this->data->mstop = json_decode(rightnow('json2'));
        $elapsed = $this->data->mstop[2] - $this->data->mstart[2];
        $tmp = secToHMS($elapsed);
        $this->data->dur = json_decode('["'.$tmp.'",'.$elapsed.']');
    }

    public function getDataObj() {
        return $this->data;
    }

    public function getDataJSON() {
        return json_encode($this->data);
    }

    public function getFileName() {
        return $this->fname;
    }

    public function recordMetrics() {
        file_put_contents($this->fname, json_encode($this->data, JSON_PRETTY_PRINT));
    }
}
?>