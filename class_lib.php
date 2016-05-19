<?php

class snapshot {

    protected $name;
    protected $url;
    protected $user;
    protected $passw;

    function __construct($name, $url, $user, $passw) {

        $this->name = $name;
        $this->url = $url;
        $this->user = $user;
        $this->passw = $passw;
    }

    public function doSnapshot() {
        $file = "/membri/repip/newcam/snapshot/" . $this->name . "_" . date("YmdHis") . ".jpg";
        $context = stream_context_create(array(
            'http' => array(
                'header' => "Authorization: Basic " . base64_encode("$this->user:$this->passw")
            )
        ));
        $data = file_get_contents($this->url, false, $context);
        file_put_contents($file, $data);
        $sz = filesize($file);
        mysql_query("INSERT INTO snapshot (name,file,size) VALUES('" . $this->name . "','" . $file . "'," . $sz . ")");
        $delete = mysql_query("SELECT file FROM `snapshot` where TIMESTAMPDIFF(HOUR,tm,NOW())>24 or size=0");
        while (list($filename) = mysql_fetch_array($delete)) {
            unlink($filename);
            mysql_query("DELETE FROM `snapshot` where file = '" . $filename . "'");
        }
    }

    public function count() {
        $countsnap = mysql_query("SELECT count(file) FROM `snapshot` where name='" . $this->name . "'");
        while (list($count) = mysql_fetch_array($countsnap)) {
            return $count;
        }
    }

    public function size() {
        $sumsize = mysql_query("SELECT sum(size) FROM `snapshot` where name='" . $this->name . "'");
        while (list($size) = mysql_fetch_array($sumsize)) {
            return $size;
        }
    }

    public function min() {
        $get = mysql_query("SELECT min(tm) FROM `snapshot` where name='" . $this->name . "'");
        while (list($min) = mysql_fetch_array($get)) {
            return $min;
        }
    }

    public function max() {
        $get = mysql_query("SELECT max(tm) FROM `snapshot` where name='" . $this->name . "'");
        while (list($max) = mysql_fetch_array($get)) {
            return $max;
        }
    }

    public function getSnaps($ora) {
        $snaps = array();
        $t1 = date_sub(date_create(date("Y-m-d") . " " . date("H") . ":00:00"), date_interval_create_from_date_string($ora . ' hour'))->format('Y-m-d H');
        $get = mysql_query("SELECT tm, file FROM `snapshot` WHERE name='" . $this->name . "' and tm between '" . $t1 . ":00:00' and '" . $t1 . ":59:59' order by tm DESC");
        while ($row = mysql_fetch_array($get, MYSQL_ASSOC)) {
            $arr = array('tm' => $row['tm'], 'file' => $row['file']);
            array_push($snaps, $arr);
        }
        return $snaps;
    }

}

class cURL {

    var $headers;
    var $user_agent;
    var $compression;
    var $cookie_file;
    var $proxy;

    function cURL($cookies = TRUE, $cookie = 'cookies.txt', $compression = 'gzip', $proxy = '') {
        $this->headers [] = 'Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg';
        $this->headers [] = 'Connection: Keep-Alive';
        $this->headers [] = 'Content-type: application/x-www-form-urlencoded;charset=UTF-8';
        $this->user_agent = 'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0 Iceweasel/31.8.0';
        $this->compression = $compression;
        $this->proxy = $proxy;
        $this->cookies = $cookies;
        if ($this->cookies == TRUE)
            $this->cookie($cookie);
    }

    function cookie($cookie_file) {
        if (file_exists($cookie_file)) {
            $this->cookie_file = $cookie_file;
        } else {
            fopen($cookie_file, 'w') or $this->error('The cookie file could not be opened. Make sure this directory has the correct permissions');
            $this->cookie_file = $cookie_file;
            fclose($this->cookie_file);
        }
    }

    function get($url, $cks) {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE)
            curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE)
            curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        if ($this->cookies == TRUE && !($cks))
            curl_setopt($process, CURLOPT_COOKIESESSION, true);
        curl_setopt($process, CURLOPT_ENCODING, $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy)
            curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        //curl_setopt ( $process , CURLOPT_PROXYTYPE , CURLPROXY_SOCKS5 );
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
        $return = curl_exec($process);
        curl_close($process);
        return $return;
    }

    function post($url, $data) {
        $process = curl_init($url);
        curl_setopt($process, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($process, CURLOPT_HEADER, 1);
        curl_setopt($process, CURLOPT_USERAGENT, $this->user_agent);
        if ($this->cookies == TRUE)
            curl_setopt($process, CURLOPT_COOKIEFILE, $this->cookie_file);
        if ($this->cookies == TRUE)
            curl_setopt($process, CURLOPT_COOKIEJAR, $this->cookie_file);
        if ($this->cookies == TRUE)
            curl_setopt($process, CURLOPT_COOKIESESSION, true);
        curl_setopt($process, CURLOPT_ENCODING, $this->compression);
        curl_setopt($process, CURLOPT_TIMEOUT, 30);
        if ($this->proxy)
            curl_setopt($process, CURLOPT_PROXY, $this->proxy);
        //curl_setopt ( $process , CURLOPT_PROXYTYPE , CURLPROXY_SOCKS5 );
        curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($process, CURLOPT_POSTFIELDS, $data);
        curl_setopt($process, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($process, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($process, CURLOPT_POST, 1);
        $return = curl_exec($process);
        curl_close($process);
        return $return;
    }

}
