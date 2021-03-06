<?php
namespace serverupdate;

class GitHubRepo {
    private $owner = null;
    private $token = null;
    private $repo  = null;

    private $classname = null;

    /**
     * Default Constructor. Invokes the SFTP 
     * default constructor and connects to 
     * an SFTP server.
     *
     * @param object $Owner
     * @param string $bpath
     * @param string $cfile
     * @throws \UnexpectedValueException on missing or bad JSON file
     * @throws \RuntimeException on failed login
     * @access public
     */
    public function __construct($Owner, $bpath, $rfile) {
        $this->classname = str_replace(__NAMESPACE__ . '\\', '', get_class());
        $this->owner = $Owner->GetOwner();
        $this->token = $Owner->GetToken();
        $this->repo  = $this->getJData($bpath . $rfile);

        if($this->repo === null) {
            // https://www.php.net/manual/en/spl.exceptions.php
            throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.'() - missing or bad ' . $bpath . $rfile);
        }

        // read the JSON file named in the string repo->stage, then 
        // replace repo->stage with the object created from the file.
        $this->repo->stage = $this->getJData($bpath . str_replace('%REPONAME%', $this->repo->name, $this->repo->stage));
        $this->repo->test  = $this->getJData($bpath . str_replace('%REPONAME%', $this->repo->name, $this->repo->test));
        $this->repo->live  = $this->getJData($bpath . str_replace('%REPONAME%', $this->repo->name, $this->repo->live));
    }

    private function makeHeader($tok = null) {
        if($tok !== null) {
            $header = array(
                'http' => array(
                    'method' => "GET",
                    'header' => "Accept: application/vnd.github.v3+json\r\n" .
                    "Authorization: token $tok\r\n" .
                    "user-agent: custom\r\n" .
                    "Content-Type: application/x-www-form-urlencoded\r\n" .
                    "Content-Encoding: text\r\n"
                )
            );
            return $header;
        } else return null;
    }

    private function getJData($file) {
        if(file_exists($file) !== false) {
            return json_decode(file_get_contents($file));
        } else return null;
    }

    private function getGitData($url) {
        $response = @file_get_contents($url, false, stream_context_create($this->makeHeader($this->token)));
        preg_match('{HTTP\/\S*\s(\d{3})}', $http_response_header[0], $match);
        if($match[1] !== '200') return null;
        else return $response;
    }

    public function getRepoReleases() {
        $url = $this->getGHURL('releases', $this->owner, $this->repo->name);
        return $this->getGitData($url);
    }

    public function isPathExcluded($path) {
        $excl = false;
        // check the exclusion list
        if(count($this->repo->exclude) > 0) {
            for($ix = 0;$ix < count($this->repo->exclude);$ix++) {
                if(preg_match($this->repo->exclude[$ix], $path) !== 0) {
                    // exclude this one
                    $excl = true;
                    break;
                }
            }
        }
        return $excl;
    }

    public function getRepoChanges($begtag, $endtag) {
        $url = $this->getGHURL('compare', $this->owner, $this->repo->name, $begtag, $endtag);
        return $this->getGitData($url);
    }

    private function getAPI($file = 'githubapi.json') {
        return $this->getJData($file);
    }

    private function getGHURL($type, $owner, $repo, $tagold = null, $tagnew = null) {
        if(($api = $this->getAPI()) !== null) {
            $url = $api->$type;
            $url = str_replace(['%OWNER%','%REPO%'], [$owner,$repo], $url);
            if(($type === 'compare') && ($tagold !== null) && ($tagnew !== null)) {
                $url = str_replace(['%TAGOLD%','%TAGNEW%'], [$tagold,$tagnew], $url);
            }
            return $url;
        } else {
            return '';
        }
    }

    public function getStage() {
        return $this->repo->stage;
    }

    public function getLive() {
        return $this->repo->live;
    }

    public function getTest() {
        return $this->repo->test;
    }

    public function getRepoRoot() {
        return $this->repo->reporoot;
    }

    public function getTags($mode) {
// TODO: return the $this->repo->{$mode}->tags object instead

        $tags = [];

        if(isset($this->repo->{$mode}->tags->beg) && ($this->repo->{$mode}->tags->beg !== "")) {
            array_push($tags, $this->repo->{$mode}->tags->beg);
        } else array_push($tags, "");
        if(isset($this->repo->{$mode}->tags->end) && ($this->repo->{$mode}->tags->end !== "")) {
            array_push($tags, $this->repo->{$mode}->tags->end);
        } else array_push($tags, "");
        return $tags;
    }

    public function getName() {
        return $this->repo->name;
    }
}
?>