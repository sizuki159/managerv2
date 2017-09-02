<?php

    namespace Librarys\Detection;

    class SimpleDetect
    {

        private $detect         = null;
        private $ipAddress      = null;
        private $ipUrl          = null;
        private $ipInfo         = null;
        private $ipInfoError    = false;
        private $ipInfoSource   = null;
        private $ipInfoHostname = null;
        private $ipInfoOrg      = null;
        private $ipInfoCountry  = null;

        const DEVICE_TYPE_MOBILE   = 'mobile';
        const DEVICE_TYPE_TABLET   = 'tablet';
        const DEVICE_TYPE_COMPUTER = 'computer';

        public function __construct()
        {
            $this->detect = new MobileDetect();
            $this->detect->setDetectionType(MobileDetect::DETECTION_TYPE_EXTENDED);
            $this->getIp();
        }

        private function getIp()
        {
            $this->ipAddress = takeIP();

            if (in_array($this->ipAddress, array('::1', '127.0.0.1', 'localhost'))) {
                $this->ipAddress = 'localhost';
                $this->ipUrl = '';
            } else {
                $this->ipUrl = '/' . $this->ipAddress;
            }
        }

        public function isMobile()
        {
            return $this->detect->isMobile();
        }

        public function isTablet()
        {
            return $this->detect->isTablet();
        }

        public function isPhone()
        {
            if ($this->detect->isMobile() && $this->detect->isTablet() == false)
                return true;

            return false;
        }

        public function isComputer()
        {
            if ($this->detect->isMobile())
                return false;

            return true;
        }

        public function getDeviceType()
        {
            if ($this->detect->isTablet())
                return self::DEVICE_TYPE_TABLET;
            else if ($this->detect->isMobile())
                return self::DEVICE_TYPE_MOBILE;
            else
                return self::DEVICE_TYPE_COMPUTER;
        }

        public function getVersion($var)
        {
            return $this->detect->version($var);
        }

        public static function isEdge()
        {
            $agent = $_SERVER['HTTP_USER_AGENT'];

            return preg_match('/Edge\/\d+/', $agent);
        }

        public function getOS()
        {
            $agent    = $_SERVER['HTTP_USER_AGENT'];
            $version  = '';
            $codeName = '';
            $os       = 'Unknown OS';

            foreach($this->detect->getOperatingSystems() AS $name => $regex) {
                $check = $this->detect->version($name);

                if ($check !== false)
                    $os = $name . ' ' . $check;

                break;
            }

            if ($this->detect->isAndroidOS()) {
                if ($this->detect->version('Android') !== false) {
                    $version = $this->detect->version('Android');

                    switch (true) {
                        case $version >= 7.0:
                            $codeName = ' (Nougat)';
                            break;

                        case $version >= 6.0:
                            $codeName = ' (Marshmallow)';
                            break;

                        case $version >= 5.0:
                            $codeName = ' (Lollipop)';
                            break;

                        case $version >= 4.4:
                            $codeName = ' (KitKat)';
                            break;

                        case $version >= 4.1:
                            $codeName = ' (Jelly Bean)';
                            break;

                        case $version >= 4.0:
                            $codeName = ' (Ice Cream Sandwich)';
                            break;

                        case $version >= 3.0:
                            $codeName = ' (Honeycomb)';
                            break;

                        case $version >= 2.3:
                            $codeName = ' (Gingerbread)';
                            break;

                        case $version >= 2.2:
                            $codeName = ' (Froyo)';
                            break;

                        case $version >= 2.0:
                            $codeName = ' (Eclair)';
                            break;

                        case $version >= 1.6:
                            $codeName = ' (Donut)';
                            break;

                        case $version >= 1.5:
                            $codeName = ' (Cupcake)';
                            break;

                        default:
                            $codeName = '';
                            break;
                    }
                }

                $os = 'Android' . $version . $codeName;
            } else if (preg_match('/Linux/', $agent)) {
                $os = 'Linux';
            } else if (preg_match('/Mac OS X/', $agent)) {
                if (preg_match('/Mac OS X 10_11/', $agent) || preg_match('/Mac OS X 10.11/', $agent))
                    $os = 'OS X (El Capitan)';
                else if (preg_match('/Mac OS X 10_10/', $agent) || preg_match('/Mac OS X 10.10/', $agent))
                    $os = 'OS X (Yosemite)';
                else if (preg_match('/Mac OS X 10_9/', $agent) || preg_match('/Mac OS X 10.9/', $agent))
                    $os = 'OS X (Mavericks)';
                else if (preg_match('/Mac OS X 10_8/', $agent) || preg_match('/Mac OS X 10.8/', $agent))
                    $os = 'OS X (Mountain Lion)';
                else if (preg_match('/Mac OS X 10_7/', $agent) || preg_match('/Mac OS X 10.7/', $agent))
                    $os = 'Mac OS X (Lion)';
                else if (preg_match('/Mac OS X 10_6/', $agent) || preg_match('/Mac OS X 10.6/', $agent))
                    $os = 'Mac OS X (Snow Leopard)';
                else if (preg_match('/Mac OS X 10_5/', $agent) || preg_match('/Mac OS X 10.5/', $agent))
                    $os = 'Mac OS X (Leopard)';
                else if (preg_match('/Mac OS X 10_4/', $agent) || preg_match('/Mac OS X 10.4/', $agent))
                    $os = 'Mac OS X (Tiger)';
                else if (preg_match('/Mac OS X 10_3/', $agent) || preg_match('/Mac OS X 10.3/', $agent))
                    $os = 'Mac OS X (Panther)';
                else if (preg_match('/Mac OS X 10_2/', $agent) || preg_match('/Mac OS X 10.2/', $agent))
                    $os = 'Mac OS X (Jaguar)';
                else if (preg_match('/Mac OS X 10_1/', $agent) || preg_match('/Mac OS X 10.1/', $agent))
                    $os = 'Mac OS X (Puma)';
                else if (preg_match('/Mac OS X 10/', $agent))
                    $os = 'Mac OS X (Cheetah)';
            } else if ($this->detect->isWindowsPhoneOS()) {
                $icon = 'windowsphone8';

                if ($this->detect->version('WindowsPhone') !== false)
                    $version = ' ' . $this->detect->version('WindowsPhoneOS');

                $os = 'Windows Phone' . $version;
            } else if ($this->detect->version('Windows NT') !== false) {
                switch ($this->detect->version('Windows NT')) {
                    case 10.0:
                        $codeName = ' 10';
                        break;

                    case 6.3:
                        $codeName = ' 8.1';
                        break;

                    case 6.2:
                        $codeName = ' 8';
                        break;

                    case 6.1:
                        $codeName = ' 7';
                        break;

                    case 6.0:
                        $codeName = ' Vista';
                        break;

                    case 5.2:
                        $codeName = ' Server 2003; Windows XP x64 Edition';
                        break;

                    case 5.1:
                        $codeName = ' XP';
                        break;

                    case 5.01:
                        $codeName = ' 2000, Service Pack 1 (SP1)';
                        break;

                    case 5.0:
                        $codeName = ' 2000';
                        break;

                    case 4.0:
                        $codeName = ' NT 4.0';
                        break;

                    default:
                        $codeName = ' NT v' . $this->detect->version('Windows NT');
                        break;
                }

                $os = 'Windows' . $codeName;
            }

            return $os;
        }

        public function getBrowser()
        {
            $agent   = $_SERVER['HTTP_USER_AGENT'];
            $browser = 'Unknown Browser';

            if (preg_match('/Edge\/\d+/', $agent)) {
                $browser = 'Microsoft Edge ' . str_replace('12', '20', $this->detect->version('Edge'));
            } else if ($this->detect->version('Trident') !== false && preg_match('/rv:11.0/', $agent)) {
                $browser = 'Internet Explorer 11';
            } else {
                $found = false;

                foreach ($this->detect->getBrowsers() AS $name => $regex) {
                    $check = $this->detect->version($name);

                    if ($check !== false && $found == false) {
                        $browser = $name . ' ' . $check;
                        $found   = true;
                    }
                }
            }

            return $browser;
        }

        public function ieCountdown($prependHTML = '', $appendHTML = '')
        {
            $ieCountdownHTML = '';

            if ($this->detect->version('IE') !== false && $this->detect->version('IE') <= 9) {
                $ieCountdownHTML = $prependHTML . '<a href="';

                if ($this->detect->version('IE') <= 6)
                    $ieCountdownHTML .= 'http://www.ie6countdown.com';
                else if ($this->detect->version('IE') <= 7)
                    $ieCountdownHTML .= 'http://www.theie7countdown.com/ie-users-info';
                else if ($this->detect->version('IE') <= 8)
                    $ieCountdownHTML .= 'http://www.theie8countdown.com/ie-users-info';
                else if ($this->detect->version('IE') <= 9)
                    $ieCountdownHTML .= 'http://www.theie9countdown.com/ie-users-info';

                $ieCountdownHTML .= '" target="_blank"><strong>YOU ARE USING AN OUTDATED BROWSER</strong><br />It is limiting your experience.<br />Please upgrade your browser,<br />or click this link to read more.</a>' . $appendHTML;
            }

            return $ieCountdownHTML;
        }

        public function ip()
        {
            if ($this->ipAddress == 'localhost' && is_null($this->ipInfo) && $this->ipInfoError == false)
                $this->getIpInfo();

            return $this->ipAddress;
        }

        private function getIpInfo()
        {
            try {
                $this->ipInfo         = json_decode(file_get_contents('http://ipinfo.io' . $this->ipUrl . '/json'));
                $this->ipAddress      = $this->ipInfo->ip;
                $this->ipInfoHostname = $this->ipInfo->hostname;
                $this->ipInfoOrg      = $this->ipInfo->org;
                $this->ipInfoCountry  = $this->ipInfo->country;
                $this->ipInfoSource   = 'ipinfo.io';
                $this->ipInfoError    = false;

                return true;
            } catch (Exception  $e) {
                try {
                    $this->ipInfo        = json_decode(file_get_contents('http://freegeoip.net/json' . $this->ipUrl));
                    $this->ipAddress     = $this->ipInfo->ip;
                    $this->ipInfoCountry = $this->ipInfo->country_code;
                    $this->ipInfoSource  = 'freegeoip.net';
                    $this->ipInfoError   = false;

                    return true;
                } catch (Exception  $e) {
                    $this->ipInfo       = null;
                    $this->ipInfoSource = null;
                    $this->ipInfoError  = true;

                    return false;
                }
            }
        }

        public function ipInfoSrc()
        {
            if (is_null($this->ipInfo) && $this->ipInfoError == false)
                $this->getIpInfo();

            return $this->ipInfoSource;
        }

        public function ipHostname()
        {
            if (is_null($this->ipInfo) && $this->ipInfoError == false)
                $this->getIpInfo();

            return $this->ipInfoHostname;
        }

        public function ipOrg()
        {
            if (is_null($this->ipInfo) && $this->ipInfoError == false)
                $this->getIpInfo();

            return $this->ipInfoOrg;
        }

        public function ipCountry()
        {
            if (is_null($this->ipInfo) && $this->ipInfoError == false)
                $this->getIpInfo();

            return $this->ipInfoCountry;
        }

    }

?>