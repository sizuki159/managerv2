<?php

    namespace Librarys\File;

    if (defined('LOADED') == false)
        exit;

    final class FileCurl
    {

        private $url;
        private $ref;
        private $cookie;
        private $userAgent;
        private $header;
        private $timeout;
        private $autoRedirect;
        private $httpCode;
        private $msgError;
        private $errorInt;
        private $buffer;

        private $isUseCurl;

        private $headers;
        private $randIP;
        private $hostInfo;
        private $pathInfo;
        private $linkInfo;
        private $sslInfo;
        private $portInfo;

        private $timeStart;
        private $timeEnd;

        const USER_AGENT_DEFAULT = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36';
        const TIME_OUT_DEFAULT   = 30;
        const AUTO_REDIRECT_AUTO = true;

        const ERROR_NONE           = 0;
        const ERROR_URL_NOT_FOUND  = 1;
        const ERROR_FILE_NOT_FOUND = 2;
        const ERROR_AUTO_REDIRECT  = 3;
        const ERROR_CONNECT_FAILED = 4;

        public function __construct($url)
        {
            $this->makeRandIP();
            $this->setURL($url);
            $this->setTimeout(self::TIME_OUT_DEFAULT);
            $this->setAutoRedirect(self::AUTO_REDIRECT_AUTO);
            $this->setUseCurl(true);
        }

        public function setURL($url)
        {
            $this->url = addPrefixHttpURL($url);
        }

        public function getURL()
        {
            return $this->url;
        }

        public function setRef($ref)
        {
            $this->ref = $ref;
        }

        public function getRef()
        {
            return $this->ref;
        }

        public function setCookie($cookie)
        {
            $this->cookie = $cookie;
        }

        public function getCookie($cookie)
        {
            return $this->cookie;
        }

        public function setUserAgent($userAgent)
        {
            $this->userAgent = $userAgent;
        }

        public function getUserAgent()
        {
            return $this->userAgent;
        }

        public function setHeader($header)
        {
            $this->header = $header;
        }

        public function getHeader()
        {
            return $this->header;
        }

        public function setTimeout($timeout)
        {
            $this->timeout = $timeout;
        }

        public function getTimeout()
        {
            return $this->timeout;
        }

        public function setAutoRedirect($autoRedirect)
        {
            $this->autoRedirect = $autoRedirect;
        }

        public function getAutoRedirect()
        {
            return $this->autoRedirect;
        }

        public function setUseCurl($isUse)
        {
            if ($isUse && self::isSupportCurl())
                $this->isUseCurl = true;
            else
                $this->isUseCurl = false;
        }

        public function isUseCurl()
        {
            return $this->isUseCurl;
        }

        public static function isSupportCurl()
        {
            return function_exists('curl_init');
        }

        public function getBuffer()
        {
            return $this->buffer;
        }

        public function getBufferLength()
        {
            return strlen($this->buffer);
        }

        public function getHttpCode()
        {
            return $this->httpCode;
        }

        public function getErrorInt()
        {
            return $this->errorInt;
        }

        public function getMsgError()
        {
            return $this->msgError;
        }

        public function getTimeStartRun()
        {
            return $this->timeStart;
        }

        public function getTimeEndRun()
        {
            return $this->timeEnd;
        }

        public function getTimeRun()
        {
            $max = max($this->timeStart, $this->timeEnd);
            $min = min($this->timeStart, $this->timeEnd);

            return $max - $min;
        }

        public function curl()
        {
            set_time_limit(0);

            $this->buffer = null;
            $result       = true;

            if ($this->timeStart === null || $this->timeStart <= 0)
                $this->timeStart = time();

            $this->parseURL();
            $this->makeHeaders();

            if ($this->isUseCurl)
                $result = $this->useCurl();
            else
                $result = $this->useFsock();

            if ($this->timeEnd() && $result == false)
                return false;

            if ($this->timeEnd() && ($this->buffer === null || empty($this->buffer)))
                return false;

            if ($this->timeEnd() && self::matchLinkMediaFire($this->url))
                return $this->timeEnd() && $this->receiverLinkMediaFire();

            return $this->timeEnd();
        }

        private function timeEnd()
        {
            $this->timeEnd = time();

            return true;
        }

        private function makeRandIP()
        {
            $this->randIP = rand(1, 254) . "." . rand(1, 254) . "." . rand(1, 254) . "." . rand(1, 254);
        }

        private function parseURL()
        {
            $matches = parse_url($this->url);

            if (isset($matches['path']) == false)
                $matches['path'] = $this->url;

            if (isset($matches['host']) == false) {
                $path = $matches['path'];
                $pos  = strpos($path, '/');

                if ($pos !== false && $pos !== 0)
                    $matches['host'] = substr($path, 0, $pos);
                else
                    $matches['host'] = $path;
            }

            $indexNextSeparator = 0;

            if (stripos($this->url, 'http://') === 0)
                $indexNextSeparator = 7;
            else if (stripos($this->url, 'https://') === 0)
                $indexNextSeparator = 8;

            $posSeparator      = strpos($this->url, '/', $indexNextSeparator);
            $posSeparatorQuery = strpos($this->url, '/?', $indexNextSeparator);

            if ($posSeparator !== $posSeparatorQuery || $posSeparatorQuery === false)
                $matches['path'] = substr($this->url, $posSeparator);

            $matches['ssl'] = null;

            if (isset($matches['scheme']) && strcasecmp($matches['scheme'], 'https') === 0) {
                $matches['port'] = 443;
                $matches['ssl']  = 'ssl://';
            }

            $this->hostInfo = $matches['host'];

            if (isset($matches['path']))
                $this->pathInfo = $matches['path'];
            else
                $this->pathInfo = '/';

            $this->linkInfo = $this->pathInfo;

            if (isset($matches['query']))
                $this->linkInfo .= '?' . $matches['query'];

            if (isset($matches['fragment']))
                $this->linkInfo .= '#' . $matches['fragment'];

            if (isset($matches['port']) && empty($matches['port']) == false)
                $this->portInfo = intval($matches['port']);
            else
                $this->portInfo = 80;

            $this->sslInfo = $matches['ssl'];
        }

        private function makeHeaders()
        {
            if ($this->isUseCurl) {
                $this->headers = [
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                    "Accept-Language: en-us,en;q=0.5",
                    "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
                    "Content-type: application/x-www-form-urlencoded;charset=UTF-8",
                    "Keep-Alive: 300",
                    "Connection: Keep-Alive"
                ];
            } else {
                $this->headers = [
                    "GET {$this->linkInfo} HTTP/1.1",
                    "Host: {$this->hostInfo}"
                ];

                if ($this->ref != null)
                    $this->headers[] = "Referer: {$this->ref}";
                else
                    $this->headers[] = "Referer: http://www.google.com.vn/search?hl=vi&client=firefox-a&rls=org.mozilla:en-US:official&hs=hKS&q=video+clip&start=20&sa=N";

                $this->headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";

                if ($this->cookie != null)
                    $this->headers[] = "Cookie: {$this->cookie}";

                if ($this->userAgent != null)
                    $this->headres[] = "User-Agent: {$this->userAgent}";
                else
                    $this->headers[] = "User-Agent: " . self::USER_AGENT_DEFAULT;

                $this->headers[] = "X-Forwarded-For: {$this->randIP}";
                $this->headers[] = "Via: CB-Prx";
                $this->headers[] = "Connection: Close";
            }
        }

        private function useCurl()
        {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $this->url);

            if ($this->userAgent != null)
                curl_setopt($curl, CURLOPT_USERAGENT, $this->userAgent);
            else
                curl_setopt($curl, CURLOPT_USERAGENT, self::USER_AGENT_DEFAULT);

            if ($this->header)
                curl_setopt($curl, CURLOPT_HEADER, 1);
            else
                curl_setopt($curl, CURLOPT_HEADER, 0);

            curl_setopt($curl, CURLOPT_HTTPHEADER, $this->headers);

            if ($this->ref)
                curl_setopt($curl, CURLOPT_REFERER, $this->ref);
            else
                curl_setopt($curl, CURLOPT_REFERER, 'http://www.google.com.vn/search?hl=vi&client=firefox-a&rls=org.mozilla:en-US:official&hs=hKS&q=video+clip&start=20&sa=N');

            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            if (strncmp($this->url, 'https', 6))
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            if ($this->cookie != null)
                curl_setopt($curl, CURLOPT_COOKIE, $this->cookie);

            curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $this->autoRedirect);

            $this->buffer   = curl_exec($curl);
            $this->msgError = curl_error($curl);
            $this->httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $this->errorInt = self::ERROR_NONE;

            if ($this->httpCode === 0)
                $this->errorInt = self::ERROR_URL_NOT_FOUND;
            else if ($this->httpCode === 404)
                $this->errorInt = self::ERROR_FILE_NOT_FOUND;

            curl_close($curl);

            if ($this->errorInt == self::ERROR_NONE)
                return true;

            return false;
        }

        private function useFsock()
        {
            $handle         = FileInfo::fileSockOpen($this->sslInfo . $this->hostInfo, $this->portInfo, $errno, $errval, $this->timeout);
            $this->errorInt = self::ERROR_NONE;

            if ($handle === false) {
                $this->buffer   = null;
                $this->errorInt = self::ERROR_CONNECT_FAILED;
            } else {
                FileInfo::fileWrite($handle, @implode("\r\n", $this->headers) . "\r\n\r\n\r\n");

                while (!FileInfo::fileEndOfFile($handle))
                    $this->buffer .= FileInfo::fileGetsLine($handle, 4069);

                if (@preg_match("/^HTTP\/\d\.\d[[:space:]]+([0-9]+).*?(?:\r\n|\r|\n)+/is", $this->buffer, $matches) != false) {
                    $this->httpCode = intval($matches[1]);

                    if ($this->httpCode === 0) {
                        $this->errorInt = self::ERROR_URL_NOT_FOUND;
                    } else if ($this->httpCode === 404) {
                        $this->errorInt = self::ERROR_FILE_NOT_FOUND;
                    } else if ($this->httpCode === 200) {
                        if (($splits = preg_split("/(\r\n\r\n)+([a-zA-Z0-9]+\r\n)*/si", $this->buffer, 2)) != false && isset($splits[1]))
                            $this->buffer = $splits[1];

                        return true;
                    } else if (($this->httpCode === 301 || $this->httpCode === 302) && $this->autoRedirect) {
                        $location = null;

                        if (preg_match("/(?:\r\n|\r|\n)+Location:[[:space:]]+(.+?)(?:\r\n|\r|\n)+/si", $this->buffer, $locations) != false)
                            $location = trim($locations[1]);

                        if ($location == null)
                            return false;

                        if (strcmp($this->url, $this->ref) !== 0) {
                            FileInfo::fileClose($handle);

                            $this->ref = $this->url;
                            $this->url = $location;

                            if (isValidateURL($this->url) == false)
                                $this->url = addPrefixHttpURL($this->hostInfo . $location);

                            if ($this->curl())
                                return true;

                            $this->errorInt = self::ERROR_AUTO_REDIRECT;
                        } else {
                            $this->errorInt = self::ERROR_AUTO_REDIRECT;

                            return false;
                        }
                    }
                }

                FileInfo::fileClose($handle);
            }

            return false;
        }

        public static function matchLinkMediaFire($url)
        {
            return preg_match('/(?:http(s)\:\/\/)?(?:www\.mediafire\.com)+/i', $url);
        }

        private function receiverLinkMediaFire()
        {
            if (preg_match('/kNO[[:space:]]*=[[:space:]]*\"(.+?)\"/si', $this->buffer, $matches)) {
                if (isset($matches[1])) {
                    $this->ref = $this->url;
                    $this->url = trim($matches[1]);

                    if (isValidateURL($this->url) == false)
                        $this->url = addPrefixHttpURL($this->hostInfo . $location);

                    return $this->curl();
                }
            }

            return false;
        }

    }

?>