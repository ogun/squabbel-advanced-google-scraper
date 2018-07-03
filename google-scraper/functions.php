<?php
/* This code is free to use and modify as long as this comment is untouched
 * Original source and details: http://google-scraper.squabbel.com
 * All rights reserves, justone@squabbel.com
 */

/*
 * This is the API function for www.seo-proxies.com, currently supporting the "rotate" command
 * On success it will define the $PROXY variable, adding the elements ready,address,port,external_ip and return 1
 * On failure the return is <= 0 and the PROXY variable ready element is set to "0"
 */
function extractBody($response_str)
{
	$parts = preg_split('|(?:\r?\n){2}|m', $response_str, 2);
	if (isset($parts[1])) return $parts[1];
	return '';
}
function proxy_api($cmd,$x="")
{
	global $pwd;
	global $uid;
	global $PROXY;
	global $NL;
	$fp = fsockopen("www.seo-proxies.com", 80);
	if (!$fp)
	{
		echo "Unable to connect to proxy API $NL";
		return -1; // connection not possible
	} else
	{
		if ($cmd == "rotate")
		{
			$PROXY['ready']=0;
			fwrite($fp, "GET /api.php?api=1&uid=$uid&pwd=$pwd&cmd=rotate&randomness=1 HTTP/1.0\r\nHost: www.seo-proxies.com\r\nAccept: text/html, text/plain, text/*, */*;q=0.01\r\nAccept-Encoding: plain\r\nAccept-Language: en\r\n\r\n");
		 	stream_set_timeout($fp, 8);
			$res="";
			$n=0;
			while (!feof($fp))
			{
				if ($n++ > 4) break;
	  			$res .= fread($fp, 8192);
			}
		 	$info = stream_get_meta_data($fp);
		 	fclose($fp);

		 	if ($info['timed_out'])
			{
				echo 'API: Connection timed out! $NL';
				return -2; // api timeout
		  } else
			{
				if (strlen($res) > 1000) return -3; // invalid api response (check the API website for possible problems)
				$data=extractBody($res);
				$ar=explode(":",$data);
				if (count($ar) < 4) return -100; // invalid api response
				switch ($ar[0])
				{
					case "ERROR":
						echo "API Error: $res $NL";
						return 0; // Error received
					break;
					case "ROTATE":
						$PROXY['address']=$ar[1];
						$PROXY['port']=$ar[2];
						$PROXY['external_ip']=$ar[3];
						$PROXY['ready']=1;
						return 1;
					break;
					default:
						echo "API Error: Received answer $ar[0], expected \"ROTATE\"";
						return -101; // unknown API response
				}
	 		}
	 	} // cmd==rotate
	}
}



function dom2array($node)
{
  $res = array();
  if($node->nodeType == XML_TEXT_NODE)
  {
  	$res = $node->nodeValue;
  } else
  {
  	if($node->hasAttributes())
  	{
  		$attributes = $node->attributes;
  		if(!is_null($attributes))
  		{
  			$res['@attributes'] = array();
  			foreach ($attributes as $index=>$attr)
  			{
  				$res['@attributes'][$attr->name] = $attr->value;
  			}
  		}
  	}
  	if($node->hasChildNodes())
  	{
  		$children = $node->childNodes;
  		for($i=0;$i<$children->length;$i++)
  		{
  			$child = $children->item($i);
  			$res[$child->nodeName] = dom2array($child);
  		}
  		$res['textContent']=$node->textContent;
  	}
  }
  return $res;
}


function getContent(&$NodeContent="",$nod)
{
	$NodList=$nod->childNodes;
	for( $j=0 ;  $j < $NodList->length; $j++ )
	{
		$nod2=$NodList->item($j);
		$nodemane=$nod2->nodeName;
		$nodevalue=$nod2->nodeValue;
		if($nod2->nodeType == XML_TEXT_NODE)
		    $NodeContent .= $nodevalue;
		else
		{     $NodeContent .= "<$nodemane ";
		   $attAre=$nod2->attributes;
		   foreach ($attAre as $value)
		      $NodeContent .= "{$value->nodeName}='{$value->nodeValue}'" ;
		    $NodeContent .= ">";
		    getContent($NodeContent,$nod2);
		    $NodeContent .= "</$nodemane>";
		}
	}

}


function dom2array_full($node)
{
    $result = array();
    if($node->nodeType == XML_TEXT_NODE)
    {
    	$result = $node->nodeValue;
    } else
    {
    	if($node->hasAttributes())
    	{
    		$attributes = $node->attributes;
    		if((!is_null($attributes))&&(count($attributes)))
    			foreach ($attributes as $index=>$attr)
    		  	$result[$attr->name] = $attr->value;
    	}
    	if($node->hasChildNodes())
    	{
    		$children = $node->childNodes;
    		for($i=0;$i<$children->length;$i++)
    		{
    			$child = $children->item($i);
    			if($child->nodeName != '#text')
    			if(!isset($result[$child->nodeName]))
    				$result[$child->nodeName] = dom2array($child);
    			else
    			{
    				$aux = $result[$child->nodeName];
    				$result[$child->nodeName] = array( $aux );
    				$result[$child->nodeName][] = dom2array($child);
    			}
    		}
    	}
    }
    return $result;
}


function getip()
{
	global $PROXY;
	if (!$PROXY['ready']) return -1; // proxy not ready

	$curl_handle=curl_init();
	curl_setopt($curl_handle,CURLOPT_URL,'http://squabbel.com/ipxx.php'); // this site will return the plain IP address, great for testing if a proxy is ready
	curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,10);
	curl_setopt($curl_handle,CURLOPT_TIMEOUT,10);
	curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
	$curl_proxy = "$PROXY[address]:$PROXY[port]";
	curl_setopt($curl_handle, CURLOPT_PROXY, $curl_proxy);
	$tested_ip=curl_exec($curl_handle);

  if(preg_match("^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}^", $tested_ip))
  {
  	curl_close($curl_handle);
		return $tested_ip;
	}
  else
  {
  	$info = curl_getinfo($curl_handle);
  	curl_close($curl_handle);
    return 0; // possible error would be a wrong authentication IP
  }
}


function new_curl_session($ch=NULL)
{
	global $PROXY;
	if ((!isset($PROXY['ready'])) || (!$PROXY['ready'])) return $ch; // proxy not ready

	if (isset($ch) && ($ch != NULL))
		curl_close($ch);
  $ch = curl_init();
  curl_setopt ($ch, CURLOPT_HEADER, 0);
  curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER , 1);
  $curl_proxy = "$PROXY[address]:$PROXY[port]";
  curl_setopt($ch, CURLOPT_PROXY, $curl_proxy);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
  curl_setopt($ch, CURLOPT_TIMEOUT, 20);
  curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en; rv:1.9.0.4) Gecko/2009011913 Firefox/3.0.6");
	return $ch;
}




?>
