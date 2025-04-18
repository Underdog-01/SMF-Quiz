<?php

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * See http://www.bin-co.com/php/scripts/load/
 * @license BSD
 * Version : 2.00.A
 */
// @TODO replace with fetch_web_data?
function quizLoad($url, $options=array())
{
	$default_options = array(
		'method' => 'get',
		'return_info' => false,
		'return_body' => true,
		'cache' => false,
		'referer' => '',
		'headers' => array(),
		'session' => false,
		'session_close' => false,
	);

	// Sets the default options.
	foreach($default_options as $opt=>$value)
		if(!isset($options[$opt])) $options[$opt] = $value;

	$url_parts = parse_url($url);
	$ch = false;
	$info = array(//Currently only supported by curl.
		'http_code'    => 200
	);
	$response = '';

	// @TODO check $options['headers']?
	$send_header = array(
		'Accept' => 'text/*',
		'User-Agent' => 'BinGet/1.00.A (http://www.bin-co.com/php/scripts/load/)'
	) + $options['headers']; // Add custom headers provided by the user.

	if ($options['cache']) {
		$cache_folder = '/tmp/php-load-function/';
		if (isset($options['cache_folder']))
			$cache_folder = $options['cache_folder'];
		if (!file_exists($cache_folder))
		{
			$old_umask = umask(0); // Or the folder will not get write permission for everybody.
			mkdir($cache_folder, 0777);
			umask($old_umask);
		}

		$cache_file_name = md5($url) . '.cache';
		$cache_file = joinPath($cache_folder, $cache_file_name); //Don't change the variable name - used at the end of the function.

		if (file_exists($cache_file))
		{ // Cached file exists - return that.
			$response = file_get_contents($cache_file);

			//Seperate header and content
			$separator_position = strpos($response,"\r\n\r\n");
			$header_text = substr($response,0,$separator_position);
			$body = substr($response,$separator_position+4);

			foreach (explode("\n",$header_text) as $line)
			{
				$parts = explode(": ",$line);
				if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
			}
			$headers['cached'] = true;

			if (!$options['return_info'])
				return $body;
			else
				return array('headers' => $headers, 'body' => $body, 'info' => array('cached'=>true));
		}
	}

	///////////////////////////// Curl /////////////////////////////////////
	//If curl is available, use curl to get the data.
	if (function_exists("curl_init")  && (!(isset($options['use']) && $options['use'] == 'fsocketopen')))
	{ //Don't use curl if it is specifically stated to use fsocketopen in the options
		if (isset($options['post_data']))
		{ //There is an option to specify some data to be posted.
			$page = $url;
			$options['method'] = 'post';

			if (is_array($options['post_data']))
			{ //The data is in array format.
				$post_data = array();
				foreach($options['post_data'] as $key=>$value)
					$post_data[] = "$key=" . urlencode($value);

				$url_parts['query'] = implode('&', $post_data);
			}
			//Its a string
			else
				$url_parts['query'] = $options['post_data'];
		}
		else
		{
			if (isset($options['method']) && $options['method'] == 'post')
				$page = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'];
			else
				$page = $url;
		}

		if ($options['session'] && isset($GLOBALS['_binget_curl_session']))
			$ch = $GLOBALS['_binget_curl_session']; //Session is stored in a global variable
		else
			$ch = curl_init($url_parts['host']);

		curl_setopt($ch, CURLOPT_URL, $page) or die("Invalid cURL Handle Resouce");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //Just return the data - not print the whole thing.
		curl_setopt($ch, CURLOPT_HEADER, true); //We need the headers
		curl_setopt($ch, CURLOPT_NOBODY, !($options['return_body'])); //The content - if true, will not download the contents. There is a ! operation - don't remove it.
		if (isset($options['method']) && $options['method'] == 'post' && isset($url_parts['query']))
		{
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $url_parts['query']);
		}
		//Set the headers our spiders sends
		curl_setopt($ch, CURLOPT_USERAGENT, $send_header['User-Agent']); //The Name of the UserAgent we will be using ;)
		$custom_headers = array("Accept: " . $send_header['Accept'] );
		if (isset($options['modified_since']))
			array_push($custom_headers,"If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since'])));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
		if($options['referer']) curl_setopt($ch, CURLOPT_REFERER, $options['referer']);

		curl_setopt($ch, CURLOPT_COOKIEJAR, "/tmp/binget-cookie.txt"); //If ever needed...
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		if (isset($url_parts['user']) && isset($url_parts['pass']))
		{
			$custom_headers = array("Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']));
			curl_setopt($ch, CURLOPT_HTTPHEADER, $custom_headers);
		}

		$response = curl_exec($ch);
		$info = curl_getinfo($ch); //Some information on the fetch

		if ($options['session'] && !$options['session_close'])
			$GLOBALS['_binget_curl_session'] = $ch; //Dont close the curl session. We may need it later - save it to a global variable
		else
			curl_close($ch);  //If the session option is not set, close the session.

		//////////////////////////////////////////// FSockOpen //////////////////////////////
		}
		else
		{ //If there is no curl, use fsocketopen - but keep in mind that most advanced features will be lost with this approch.
			if(isset($url_parts['query']))
			{
				if(isset($options['method']) && $options['method'] == 'post')
					$page = $url_parts['path'];
				else
					$page = $url_parts['path'] . '?' . $url_parts['query'];
			}
			else
			{
				$page = $url_parts['path'];
			}

			if (!isset($url_parts['port']))
				$url_parts['port'] = 80;

			// @TODO silence error?
			$fp = fsockopen($url_parts['host'], $url_parts['port'], $errno, $errstr, 30);
			if ($fp)
			{
				$out = '';
				if (isset($options['method']) && $options['method'] == 'post' && isset($url_parts['query']))
					$out .= "POST $page HTTP/1.1\r\n";
				else
					$out .= "GET $page HTTP/1.0\r\n"; //HTTP/1.0 is much easier to handle than HTTP/1.1

				$out .= "Host: $url_parts[host]\r\n";
				$out .= "Accept: $send_header[Accept]\r\n";
				$out .= "User-Agent: {$send_header['User-Agent']}\r\n";
				if (isset($options['modified_since']))
					$out .= "If-Modified-Since: ".gmdate('D, d M Y H:i:s \G\M\T',strtotime($options['modified_since'])) ."\r\n";

				$out .= "Connection: Close\r\n";

				//HTTP Basic Authorization support
				if (isset($url_parts['user']) and isset($url_parts['pass']))
					$out .= "Authorization: Basic ".base64_encode($url_parts['user'].':'.$url_parts['pass']) . "\r\n";

				//If the request is post - pass the data in a special way.
				if (isset($options['method']) and $options['method'] == 'post' and $url_parts['query'])
				{
					$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
					$out .= 'Content-Length: ' . strlen($url_parts['query']) . "\r\n";
					$out .= "\r\n" . $url_parts['query'];
				}
				$out .= "\r\n";

			// @TODO silence error?
			// @TODO check is is_writable?
				fwrite($fp, $out);
				while (!feof($fp))
						$response .= fgets($fp, 128);

			// @TODO silence error?
				fclose($fp);
			}
		}

		//Get the headers in an associative array
		$headers = array();

		if($info['http_code'] == 404)
		{
			$body = "";
			$headers['Status'] = 404;
		}
		else
		{
			//Seperate header and content
			if (isset($info['header_size']))
			{
				$header_text = substr($response, 0, $info['header_size']);
				$body = substr($response, $info['header_size']);
				foreach(explode("\n",$header_text) as $line)
				{
					$parts = explode(": ",$line);
					if(count($parts) == 2) $headers[$parts[0]] = chop($parts[1]);
				}
			}
			else
			{
				// No header information, so just return the lot from the first < character (we know that is where body starts)
				$docStartPos = strpos($response, '<');
				$body = substr($response, $docStartPos);
			}
		}

		if (isset($cache_file)) //Should we cache the URL?
			file_put_contents($cache_file, $response);

		if ($options['return_info'])
			return array('headers' => $headers, 'body' => $body, 'info' => $info, 'curl_handle'=>$ch);

	return $body;
}
?>