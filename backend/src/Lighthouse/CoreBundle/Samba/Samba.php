<?php

namespace Lighthouse\CoreBundle\Samba;

/**
 * Class Samba
 * TODO: Код чужой. Многие моменты лучше переписать
 * @package Lighthouse\CoreBundle\Samba
 */

class Samba
{
    const SMB4PHP_AUTHMODE = "arg"; // set to 'env' to use USER environment variable
    const SMB4PHP_SMBOPTIONS = "TCP_NODELAY IPTOS_LOWDELAY SO_KEEPALIVE SO_RCVBUF=8192 SO_SNDBUF=8192";
    const SMB4PHP_SMBCLIENT = "smbclient";
    const SMB4PHP_VERSION = "0.8";

    public static $smb_cache = array('stat' => array (), 'dir' => array ());
    
    public static $regexp = array(
        '^added interface ip=(.*) bcast=(.*) nmask=(.*)$' => 'skip',
        'Anonymous login successful' => 'skip',
        '^Domain=\[(.*)\] OS=\[(.*)\] Server=\[(.*)\]$' => 'skip',
        '^\tSharename[ ]+Type[ ]+Comment$' => 'shares',
        '^\t---------[ ]+----[ ]+-------$' => 'skip',
        '^\tServer   [ ]+Comment$' => 'servers',
        '^\t---------[ ]+-------$' => 'skip',
        '^\tWorkgroup[ ]+Master$' => 'workg',
        '^\t(.*)[ ]+(Disk|IPC)[ ]+IPC.*$' => 'skip',
        '^\tIPC\\\$(.*)[ ]+IPC' => 'skip',
        '^\t(.*)[ ]+(Disk)[ ]+(.*)$' => 'share',
        '^\t(.*)[ ]+(Printer)[ ]+(.*)$' => 'skip',
        '([0-9]+) blocks of size ([0-9]+)\. ([0-9]+) blocks available' => 'skip',
        'Got a positive name query response from ' => 'skip',
        '^(session setup failed): (.*)$' => 'error',
        '^(.*): ERRSRV - ERRbadpw' => 'error',
        '^Error returning browse list: (.*)$' => 'error',
        '^tree connect failed: (.*)$' => 'error',
        '^(Connection to .* failed)$' => 'error',
        '^NT_STATUS_(.*) ' => 'error',
        '^NT_STATUS_(.*)\$' => 'error',
        'ERRDOS - ERRbadpath \((.*).\)' => 'error',
        'cd (.*): (.*)$' => 'error',
        '^cd (.*): NT_STATUS_(.*)' => 'error',
        '^\t(.*)$' => 'srvorwg',
        '^([0-9]+)[ ]+([0-9]+)[ ]+(.*)$' => 'skip',
        '^Job ([0-9]+) cancelled' => 'skip',
        '^[ ]+(.*)[ ]+([0-9]+)[ ]+(Mon|Tue|Wed|Thu|Fri|Sat|Sun)[ ](Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[ ]+([0-9]+)[ ]+([0-9]{2}:[0-9]{2}:[0-9]{2})[ ]([0-9]{4})$' => 'files',
        '^message start: ERRSRV - (ERRmsgoff)' => 'error',
        '^Connection to (.+) failed \(Error (.+)\)$' => 'error',
    );

    public function parseUrl($url)
    {
        $parsedUrl = parse_url(trim($url));
        foreach (array('domain', 'user', 'pass', 'host', 'port', 'path', 'scheme') as $i) {
            if (!isset($parsedUrl[$i])) {
                $parsedUrl[$i] = '';
            }
        }
        if (count($userDomain = explode(';', urldecode($parsedUrl['user']))) > 1) {
            list ($parsedUrl['domain'], $parsedUrl['user']) = $userDomain;
        }
        $path = preg_replace(array('/^\//', '/\/$/'), '', urldecode($parsedUrl['path']));
        list ($parsedUrl['share'], $parsedUrl['path']) = (preg_match('/^([^\/]+)\/(.*)/', $path, $regs))
            ? array($regs[1], preg_replace('/\//', '\\', $regs[2]))
            : array($path, '');
        $parsedUrl['type'] =
            $parsedUrl['path']
            ? 'path'
            : ($parsedUrl['share'] ? 'share' : ($parsedUrl['host'] ? 'host' : '**error**'));

        if (!($parsedUrl['port'] = intval($parsedUrl['port']))) {
            $parsedUrl['port'] = 139;
        }

        return $parsedUrl;
    }


    public function look($purl)
    {
        return $this->client('-L ' . escapeshellarg($purl['host']), $purl);
    }


    public function execute($command, $purl)
    {
        return $this->client(
            '-d 0 '
            . escapeshellarg('//' . $purl['host'] . '/' . $purl['share'])
            . ' -c ' . escapeshellarg($command),
            $purl
        );
    }

    public function client($params, $purl)
    {
        if (self::SMB4PHP_AUTHMODE == 'env') {
            putenv("USER={$purl['user']}%{$purl['pass']}");
            $auth = '';
        } else {
            $auth = ($purl['user'] != '' ? (' -U ' . escapeshellarg($purl['user'] . '%' . $purl['pass'])) : '');
        }
        if ($purl['domain'] != '') {
            $auth .= ' -W ' . escapeshellarg($purl['domain']);
        }
        $port = ($purl['port'] != 139 ? ' -p ' . escapeshellarg($purl['port']) : '');
        $options = '-O ' . escapeshellarg(self::SMB4PHP_SMBOPTIONS);
        $output = $this->getProcessResource($params, $auth, $options, $port);
        $info = array();
        while (($line = $this->fgets($output)) !== false) {
            $tag = 'skip';
            $regs = array();
            $i = array();

            reset(self::$regexp);
            foreach (self::$regexp as $regexp => $t) {
                if (preg_match('/' . $regexp . '/', $line, $regs)) {
                    $tag = $t;
                    break;
                }
            }
            switch ($tag) {
                case 'skip':
                    continue;
                case 'shares':
                    $mode = 'shares';
                    break;
                case 'servers':
                    $mode = 'servers';
                    break;
                case 'workg':
                    $mode = 'workgroups';
                    break;
                case 'share':
                    list($name, $type) = array(
                        trim(substr($line, 1, 15)),
                        trim(strtolower(substr($line, 17, 10)))
                    );
                    $i = ($type != 'disk' && preg_match('/^(.*) Disk/', $line, $regs))
                        ? array(trim($regs[1]), 'disk')
                        : array($name, 'disk');
                    break;
                case 'srvorwg':
                    list ($name, $master) = array(
                        strtolower(trim(substr($line, 1, 21))),
                        strtolower(trim(substr($line, 22)))
                    );
                    $i = (isset($mode) && $mode == 'servers')
                        ? array($name, "server")
                        : array($name, "workgroup", $master);
                    break;
                case 'files':
                    list ($attr, $name) = preg_match("/^(.*)[ ]+([D|A|H|S|R]+)$/", trim($regs[1]), $regs2)
                        ? array(trim($regs2[2]), trim($regs2[1]))
                        : array('', trim($regs[1]));
                    list ($his, $im) = array(
                        explode(':', $regs[6]),
                        1 + strpos("JanFebMarAprMayJunJulAugSepOctNovDec", $regs[4]) / 3
                    );
                    $i = ($name != '.' && $name != '..')
                        ? array(
                            $name,
                            (strpos($attr, 'D') === false) ? 'file' : 'folder',
                            'attr' => $attr,
                            'size' => intval($regs[2]),
                            'time' => mktime($his[0], $his[1], $his[2], $im, $regs[5], $regs[7])
                        )
                        : array();
                    break;
                case 'error':
                    throw new SambaWrapperException($regs[0]);
            }
            if ($i) {
                switch ($i[1]) {
                    case 'file':
                    case 'folder':
                        $info['info'][$i[0]] = $i;
                        $info[$i[1]][] = $i[0];
                        break;
                    case 'disk':
                    case 'server':
                    case 'workgroup':
                        $info[$i[1]][] = $i[0];
                        break;
                }
            }
        }
        $this->closeProcessResource($output);

        return $info;
    }


    # stats

    public function url_stat($url, $flags = STREAM_URL_STAT_LINK)
    {
        if ($statFromCache = $this->getstatcache($url)) {
            return $statFromCache;
        }

        $stat = array();
        $parsedUrl = $this->parseUrl($url);

        switch ($parsedUrl['type']) {
            case 'host':
                if ($lookInfo = $this->look($parsedUrl)) {
                    $stat = stat("/tmp");
                } else {
                    throw new SambaWrapperException("url_stat(): list failed for host '{$parsedUrl['host']}'");
                }
                break;
            case 'share':
                if ($lookInfo = $this->look($parsedUrl)) {
                    $found = false;
                    $lowerShare = strtolower($parsedUrl['share']); # fix by Eric Leung
                    foreach ($lookInfo['disk'] as $share) {
                        if ($lowerShare == strtolower($share)) {
                            $found = true;
                            $stat = stat("/tmp");
                            break;
                        }
                    }
                    if (!$found) {
                        throw new SambaWrapperException(
                            "url_stat(): disk resource '{$lowerShare}' not found in '{$parsedUrl['host']}'"
                        );
                    }
                }
                break;
            case 'path':
                if ($output = $this->execute('dir "' . $parsedUrl['path'] . '"', $parsedUrl)) {
                    $path = explode("\\", $parsedUrl['path']);
                    $name = $path[count($path) - 1];
                    if (isset ($output['info'][$name])) {
                        $stat = $this->addstatcache($url, $output['info'][$name]);
                    } else {
                        throw new SambaWrapperException("url_stat(): path '{$parsedUrl['path']}' not found");
                    }
                } else {
                    throw new SambaWrapperException("url_stat(): dir failed for path '{$parsedUrl['path']}'");
                }
                break;
            default:
                throw new SambaWrapperException('error in URL');
        }

        return $stat;
    }

    public function addstatcache($url, $info)
    {
        $isFile = (strpos($info['attr'], 'D') === false);
        $stat = ($isFile) ? stat('/etc/passwd') : stat('/tmp');
        $stat[7] = $stat['size'] = $info['size'];
        $stat[8] = $stat[9] = $stat[10] = $stat['atime'] = $stat['mtime'] = $stat['ctime'] = $info['time'];

        return self::$smb_cache['stat'][$url] = $stat;
    }

    public function getstatcache($url)
    {
        return isset(self::$smb_cache['stat'][$url]) ? self::$smb_cache['stat'][$url] : false;
    }

    public function clearstatcache($url = '')
    {
        if ($url == '') {
            self::$smb_cache['stat'] = array();
        } else {
            unset (self::$smb_cache['stat'][$url]);
        }
    }


    # commands

    public function unlink($url)
    {
        $pu = $this->parseUrl($url);
        if ($pu['type'] != 'path') {
            throw new SambaWrapperException('unlink(): error in URL');
        }
        $this->clearstatcache($url);

        return $this->execute('del "' . $pu['path'] . '"', $pu);
    }

    public function rename($url_from, $url_to)
    {
        list ($from, $to) = array($this->parseUrl($url_from), $this->parseUrl($url_to));
        if ($from['host'] != $to['host'] ||
            $from['share'] != $to['share'] ||
            $from['user'] != $to['user'] ||
            $from['pass'] != $to['pass'] ||
            $from['domain'] != $to['domain']
        ) {
            throw new SambaWrapperException('rename(): FROM & TO must be in same server-share-user-pass-domain');
        }
        if ($from['type'] != 'path' || $to['type'] != 'path') {
            throw new SambaWrapperException('rename(): error in URL');
        }
        $this->clearstatcache($url_from);

        return $this->execute('rename "' . $from['path'] . '" "' . $to['path'] . '"', $to);
    }

    public function mkdir($url, $mode, $options)
    {
        $pu = $this->parseUrl($url);
        if ($pu['type'] != 'path') {
            throw new SambaWrapperException('mkdir(): error in URL');
        }

        return $this->execute('mkdir "' . $pu['path'] . '"', $pu);
    }

    public function rmdir($url)
    {
        $pu = $this->parseUrl($url);
        if ($pu['type'] != 'path') {
            throw new SambaWrapperException('rmdir(): error in URL');
        }
        $this->clearstatcache($url);

        return $this->execute('rmdir "' . $pu['path'] . '"', $pu);
    }

    /**
     * @param $params
     * @param $auth
     * @param $options
     * @param $port
     * @return resource
     */
    public function getProcessResource($params, $auth, $options, $port)
    {
        $output = popen(
            self::SMB4PHP_SMBCLIENT . " -N {$auth} {$options} {$port} {$options} {$params} 2>/dev/null",
            'r'
        );

        return $output;
    }

    /**
     * @param $output
     * @return string
     */
    public function fgets($output)
    {
        return $line = fgets($output, 4096);
    }

    /**
     * @param $output
     */
    public function closeProcessResource($output)
    {
        pclose($output);
    }
}