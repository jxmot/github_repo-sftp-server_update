<?php
/*
    Extends the phpseclib3\Net\SFTP class and 
    provides some convenience functions.
*/
namespace serverupdate;

// https://phpseclib.com/docs/sftp
require __DIR__ . '/vendor/autoload.php';
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

class supdSFTP extends SFTP {

    private $cfg = null;
    private $keyguts = null;
    private $pass = null;
    private $key = null;
    private $sftp = null;

    private $home = null;
    private $docroot = null;

    private $localhome = null;

    private $classname = null;

    /**
     * Default Constructor. Invokes the SFTP 
     * default constructor and connects to 
     * an SFTP server.
     *
     * @param string $bpath 
     * @param string $cfile
     * @throws \UnexpectedValueException on missing or bad JSON file
     * @throws \RuntimeException on failed login
     * @access public
     */
    public function __construct($bpath, $cfile) {
        $this->localhome = getcwd();
        $this->classname = str_replace(__NAMESPACE__ . '\\', '', get_class());
        // get configuration data
        if(($this->cfg = $this->getJData($bpath . $cfile)) === null) {
            // https://www.php.net/manual/en/spl.exceptions.php
            throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.' - missing or bad ' . $bpath . $cfile);
        }
        // get the contents of the private key file and the pass 
        // phrase, and then load the key.
        if(($this->keyguts = $this->getFile($bpath . $this->cfg->keyfile)) === null) {
            throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.' - missing or bad ' . $bpath . $this->cfg->keyfile);
        }
        if(($this->pass = $this->getJData($bpath . $this->cfg->phrasefile)) === null) {
            throw new \UnexpectedValueException('ERROR: '.$this->classname.' '.__FUNCTION__.' - missing or bad: ' . $bpath . $this->cfg->phrasefile);
        }
        $this->key = PublicKeyLoader::load($this->keyguts, $this->pass->phrase);
        // invoke the SFTP constructor
        parent::__construct($this->cfg->server);
        // log in to the server
        if (!$this->login($this->cfg->login, $this->key)) {
            throw new \RuntimeException('ERROR: '.$this->classname.' '.__FUNCTION__.' - Login Failed: ' . $this->cfg->login . ' - Error: ' . getLastSFTPError());
        }
        // Keep file date & time when transferring between 
        // the server and local
        $this->enableDatePreservation();
        // save locations
        $this->home = $this->cfg->home;
        $this->docroot = $this->cfg->docroot;
        // start in the server home folder
        //$this->chdir($this->home);
        parent::chdir($this->home);
    }

    public function __destruct() {
        //$this->chdir($this->localhome);
        parent::chdir($this->localhome);
        $this->disconnect();
    }
  
    /**
     * Get decoded JSON data from a file.
     *
     * @param string $file
     * @return object|null
     * @access private
     */
    private function getJData($file) {
        if(file_exists($file) !== false) {
            return json_decode(file_get_contents($file));
        } else return null;
    }

    /**
     * Get the contents a file.
     *
     * @param string $file
     * @return mixed|null
     * @access private
     */
    private function getFile($file) {
        if(file_exists($file) !== false) {
            return file_get_contents($file);
        } else return null;
    }

    /**
     * Uploads a file to the SFTP server. Calls 
     * SFTP::put() with SFTP::SOURCE_LOCAL_FILE
     *
     * @param string $rfile
     * @param string $lfile
     * @return bool
     * @access public
     */
    public function put($rfile, $lfile) {
        return parent::put($rfile, $lfile, SFTP::SOURCE_LOCAL_FILE);
    }

    /**
     * Creates a directory. Calls SFTP::mkdir() 
     * with $mode = 0755 and $recursive = true
     *
     * @param string $dir
     * @return bool
     * @access public
     */
    public function mkdir($rpath) {
        return parent::mkdir($rpath, 0755, true);
    }

    /**
     * Retreives the configured path to 
     * the "home" folder on the server.
     *
     * @return string
     * @access public
     */
    public function getHome() {
        return $this->home;
    }

    /**
     * Retreives the configured path to 
     * the "document root" folder on the 
     * server.
     *
     * @return string
     * @access public
     */
    public function getDocRoot() {
        return $this->docroot;
    }
}
?>