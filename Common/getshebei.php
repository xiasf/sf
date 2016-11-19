<?php
function getshebei(){
    $UserAgent = $_SERVER['HTTP_USER_AGENT'];
    $userinfo  = array();

    if(preg_match('/(firefox|opera|ucbrowser|ubrowser|chrome|msie|safari).?([0-9]\.?)*/i', $UserAgent, $liu))
    $liu = $liu[0];
    else
    $liu = '未知';
    $userinfo[] = $liu;

    /*浏览器检查完毕，开始检查设备*/

    if (is_int(stripos($UserAgent, 'Windows'))){
        if(is_int(stripos($UserAgent, 'Windows NT 6.2'))){
            $shebei = 'Windows 8';
        if(is_int(stripos($UserAgent, 'WOW64')))
            $shebei .= ' 64bit';
        else
            $shebei .= " 32bit";
        }elseif(is_int(stripos($UserAgent, 'Windows NT 6.1'))){
            $shebei = 'Windows 7';
        if(is_int(stripos($UserAgent, 'WOW64')))
            $shebei .= ' 64bit';
        else
            $shebei .= " 32bit";
        }elseif(is_int(stripos($UserAgent, 'Windows NT 6.0'))){
            $shebei = 'Windows Vista';
        }elseif(is_int(stripos($UserAgent, 'Windows NT 5.2'))){
            $shebei = 'Windows 2003';
        }elseif(is_int(stripos($UserAgent, 'Windows NT 5.1'))){
            $shebei = 'Windows XP';
        }elseif(is_int(stripos($UserAgent, 'Windows NT 5.0'))){
            $shebei = 'Windows 2000';
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'Macintosh'))){
         if(preg_match('/(Mac OS).?\w?.?([0-9]\.?)*/i', $UserAgent, $Macintosh)){
            $shebei = $Macintosh[0];
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'iPhone'))){
        if(preg_match('/(iPhone OS).?([0-9]_?)*/i', $UserAgent, $iPhone)){
            $shebei = str_replace('_', '.', $iPhone[0]) ;
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'iPad'))){
         if(preg_match('/(OS).?([0-9]_?)*/i', $UserAgent, $iPad)){
            $shebei = str_replace('_', '.', $iPad[0]) ;
        }
        $userinfo[] = 'iPad '.$shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'Android'))){
       if(is_int(stripos($UserAgent, 'Galaxy Nexus')))
            $shebei = 'Galaxy Nexus '; 
       if(is_int(stripos($UserAgent, 'Nexus S')))
            $shebei = 'Nexus S ';  
       if(preg_match('/(Android).?([0-9]\.?)*/i', $UserAgent, $Android)){
            $shebei .= $Android[0] ;
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'Adr'))){
       if(is_int(stripos($UserAgent, 'Galaxy Nexus')))
            $shebei = 'Galaxy Nexus '; 
       if(is_int(stripos($UserAgent, 'Nexus S')))
            $shebei = 'Nexus S ';  
       if(preg_match('/(Adr).?([0-9]\.?)*/i', $UserAgent, $Adr)){
            $shebei .= str_replace('Adr', 'Android', $Adr[0]) ;
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'MeeGo'))){
        if(preg_match('/(Nokia).?(([0-9]\.?)*)/i', $UserAgent, $MeeGo)){
            $shebei = 'MeeGo Nokia '.$MeeGo[2] ;
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'BlackBerry'))){
        if(preg_match('/(BlackBerry ).?([0-9]\.?)*/i', $UserAgent, $BlackBerry)){
            $shebei = $BlackBerry[0];
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }

    if (is_int(stripos($UserAgent, 'PlayBook'))){     //可以看到双括号时以外括号为准
        if(preg_match('/(RIM Tablet OS).?(([0-9]\.?)*)/i', $UserAgent, $PlayBook)){
            $shebei = 'BlackBerry PlayBook '.$PlayBook[2];
        }
        $userinfo[] = $shebei;
        return $userinfo;
    }
    $shebei = '未知';
    $userinfo[] = $shebei;
    return $userinfo;
}