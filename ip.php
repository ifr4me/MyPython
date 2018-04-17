<?php
error_reporting(0);
$ip = $_GET['ip'];

$pat = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))\.){3}((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))$/";

if(!preg_match($pat,$ip)){
	echo "not ip, use your ip.<br>";
	$ip = $_SERVER['REMOTE_ADDR'];
}

if (substr($ip, 0, 3) == "10." || substr($ip, 0, 7) == "192.168" ||substr($ip, 0, 6)== "172.16" || $ip == "0.0.0.0" ||$ip == "255.255.255.255"||substr($ip, 0, 4) == "127."){
	header('Location:https://www.baidu.com/s?wd='.$ip);
	exit;
}
#header('Content-type:text/json'); 
echo "$ip"."<br>";
echo "GeoLite=================";


include("GeoIP/geoip-api-php-master/src/geoipcity.inc");
include("GeoIP/geoip-api-php-master/src/geoipregionvars.php");
$gi = geoip_open("GeoIP/GeoLiteCity.dat",GEOIP_STANDARD);

$record = geoip_record_by_addr($gi, $ip);

print $record->country_name . " " .$GEOIP_REGION_NAME[$record->country_code][$record->region] . " " .$record->city . "<br>";
print $record->latitude . "," .$record->longitude . "<br><br>";

geoip_close($gi);


echo "qqwry===================";

define("IPDATA_PATH", "GeoIP/qqwry.dat");//ip归属地数据库地址
print convertip($ip, IPDATA_PATH);

echo "<br><br>GeoIP2==================";

require_once 'GeoIP/vendor/autoload.php';
use GeoIp2\Database\Reader;

// This creates the Reader object, which should be reused across
// // lookups.
 $reader = new Reader('/usr/local/share/GeoIP/GeoIP2-City.mmdb');

 $record = $reader->city($ip);
 
print($record->country->name . "\n"); // 'United States'
print($record->mostSpecificSubdivision->name . "\n"); // 'Minnesota'
print($record->city->name . "<br>"); // 'Minneapolis'
print($record->location->latitude . ","); 
print($record->location->longitude . "\n"); 
echo "<br>";

//使用蓝莲花XSS平台解析qqwry代码
function convertip($ip, $ipdatafile) {
    $ipaddr = '未知';
    if (preg_match("/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/", $ip)) {
        $iparray = explode('.', $ip);
        if ($iparray[0] == 10 || $iparray[0] == 127 || ($iparray[0] == 192 && $iparray[1] == 168) || ($iparray[0] == 172 && ($iparray[1] >= 16 && $iparray[1] <= 31))) {
            $ipaddr = '局域网';
        } elseif ($iparray[0] > 255 || $iparray[1] > 255 || $iparray[2] > 255 || $iparray[3] > 255) {
            $ipaddr = '错误ip';
        } else {
            if (@file_exists($ipdatafile)) {
                if (!$fd = @fopen($ipdatafile, 'rb')) {
                    return 'ip库出错';
                }
                
                $ip    = explode('.', $ip);
                $ipNum = $ip[0] * 16777216 + $ip[1] * 65536 + $ip[2] * 256 + $ip[3];
                
                if (!($DataBegin = fread($fd, 4)) || !($DataEnd = fread($fd, 4)))
                    return;
                @$ipbegin = implode('', unpack('L', $DataBegin));
                if ($ipbegin < 0)
                    $ipbegin += pow(2, 32);
                @$ipend = implode('', unpack('L', $DataEnd));
                if ($ipend < 0)
                    $ipend += pow(2, 32);
                $ipAllNum = ($ipend - $ipbegin) / 7 + 1;
                
                $BeginNum = $ip2num = $ip1num = 0;
                $ipAddr1  = $ipAddr2 = '';
                $EndNum   = $ipAllNum;
                
                while ($ip1num > $ipNum || $ip2num < $ipNum) {
                    $Middle = intval(($EndNum + $BeginNum) / 2);
                    
                    fseek($fd, $ipbegin + 7 * $Middle);
                    $ipData1 = fread($fd, 4);
                    if (strlen($ipData1) < 4) {
                        fclose($fd);
                        return '系统错误';
                    }
                    $ip1num = implode('', unpack('L', $ipData1));
                    if ($ip1num < 0)
                        $ip1num += pow(2, 32);
                    
                    if ($ip1num > $ipNum) {
                        $EndNum = $Middle;
                        continue;
                    }
                    
                    $DataSeek = fread($fd, 3);
                    if (strlen($DataSeek) < 3) {
                        fclose($fd);
                        return '系统错误';
                    }
                    $DataSeek = implode('', unpack('L', $DataSeek . chr(0)));
                    fseek($fd, $DataSeek);
                    $ipData2 = fread($fd, 4);
                    if (strlen($ipData2) < 4) {
                        fclose($fd);
                        return '系统错误';
                    }
                    $ip2num = implode('', unpack('L', $ipData2));
                    if ($ip2num < 0)
                        $ip2num += pow(2, 32);
                    
                    if ($ip2num < $ipNum) {
                        if ($Middle == $BeginNum) {
                            fclose($fd);
                            return '未知';
                        }
                        $BeginNum = $Middle;
                    }
                }
                
                $ipFlag = fread($fd, 1);
                if ($ipFlag == chr(1)) {
                    $ipSeek = fread($fd, 3);
                    if (strlen($ipSeek) < 3) {
                        fclose($fd);
                        return '系统错误';
                    }
                    $ipSeek = implode('', unpack('L', $ipSeek . chr(0)));
                    fseek($fd, $ipSeek);
                    $ipFlag = fread($fd, 1);
                }
                
                if ($ipFlag == chr(2)) {
                    $AddrSeek = fread($fd, 3);
                    if (strlen($AddrSeek) < 3) {
                        fclose($fd);
                        return '系统错误';
                    }
                    $ipFlag = fread($fd, 1);
                    if ($ipFlag == chr(2)) {
                        $AddrSeek2 = fread($fd, 3);
                        if (strlen($AddrSeek2) < 3) {
                            fclose($fd);
                            return '系统错误';
                        }
                        $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                        fseek($fd, $AddrSeek2);
                    } else {
                        fseek($fd, -1, SEEK_CUR);
                    }
                    
                    while (($char = fread($fd, 1)) != chr(0))
                        $ipAddr2 .= $char;
                    
                    $AddrSeek = implode('', unpack('L', $AddrSeek . chr(0)));
                    fseek($fd, $AddrSeek);
                    
                    while (($char = fread($fd, 1)) != chr(0))
                        $ipAddr1 .= $char;
                } else {
                    fseek($fd, -1, SEEK_CUR);
                    while (($char = fread($fd, 1)) != chr(0))
                        $ipAddr1 .= $char;
                    
                    $ipFlag = fread($fd, 1);
                    if ($ipFlag == chr(2)) {
                        $AddrSeek2 = fread($fd, 3);
                        if (strlen($AddrSeek2) < 3) {
                            fclose($fd);
                            return '系统错误';
                        }
                        $AddrSeek2 = implode('', unpack('L', $AddrSeek2 . chr(0)));
                        fseek($fd, $AddrSeek2);
                    } else {
                        fseek($fd, -1, SEEK_CUR);
                    }
                    while (($char = fread($fd, 1)) != chr(0))
                        $ipAddr2 .= $char;
                }
                fclose($fd);
                
                $ipAddr1 = iconv("gb18030", "utf-8//IGNORE", $ipAddr1);
                if ($ipAddr2) {
                    if (ord($ipAddr2{0}) == 2)
                        $ipAddr2 = "";
                    else
                        $ipAddr2 = iconv("gb18030", "utf-8//IGNORE", $ipAddr2);
                }
                
                if (preg_match('/http/i', $ipAddr2)) {
                    $ipAddr2 = '';
                }
                
                $ipaddr = $ipAddr1 . $ipAddr2;
                $ipaddr = preg_replace('/CZ88\.NET/is', '', $ipaddr);
                $ipaddr = preg_replace('/^\s*/is', '', $ipaddr);
                $ipaddr = preg_replace('/\s*$/is', '', $ipaddr);
                if (preg_match('/http/i', $ipaddr) || $ipaddr == '') {
                    $ipaddr = '未知';
                }
                return htmlspecialchars($ipaddr, ENT_QUOTES, 'UTF-8');
            }
        }
    }
    return $ipaddr;
}

#header("Refresh:7;url=http://yyisi.wanmei.com/ip/getData?ip=".$ip);
# http://yyisi.wanmei.com/ip/getData?ip=111.11.111.11
#	$content = file_get_contents("http://yyisi.wanmei.com/ip/getData?ip=".$ip);  
#	print_r($content);

?>
<html>
<br>
<br>
<a href="http://yyisi.wanmei.com/ip/getData?ip=<?echo $ip?>">yyisi.wanmei.com</a><br>
<a href="https://www.opengps.cn/Data/IP/LocHighAcc.aspx" >openGps高精度IP定位</a>
</html>
