<?php
namespace serverupdate;

class GitHubOwner {
    private $owner  = null;
    private $token = null;

    private $classname = null;

    /**
     * Default Constructor. 
     *
     * @param string $cfile
     * @throws \UnexpectedValueException on missing or bad JSON file
     * @throws \RuntimeException on failed login
     * @access public
     */
    public function __construct($bpath = '', $ufile = null) {
        $this->classname = str_replace(__NAMESPACE__ . '\\', '', get_class());

        if($ufile !== null) {
            if(($udata = $this->getJData($bpath . $ufile)) !== null) {
                $this->owner = $udata->owner;
                $this->token = $this->setToken($bpath . $udata->tokenfile);
                if($this->token === null) {
                    // https://www.php.net/manual/en/spl.exceptions.php
                    throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.' - missing or bad ' . $bpath . $udata->tokenfile);
                }
            } else {
                throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.' - missing or bad ' . $bpath . $ufile);
            }
        } else {
            throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.' - missing argument');
        }
    }

    private function getJData($file) {
        if(file_exists($file) !== false) {
            return json_decode(file_get_contents($file));
        } else return null;
    }

    private function setToken($file) {
        if(($data = $this->getJData($file)) === null) {
            return null;
        } else return $data->token;
    }

    public function GetToken() {
        return $this->token;
    }

    public function GetOwner() {
        return $this->owner;
    }
}
?>