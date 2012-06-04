<?php

namespace Adhoc;

/**
 * Description of Util
 *
 * @author prometheus
 */
class Util {
	/**
	 * Supported tags and labels for {@link version}.
	 * <ul>
	 * <li>rc, dev</li>
	 * <li>a, alpha</li>
	 * <li>b, beta</li>
	 * <li>c, g, gamma</li>
	 * <li>d, delta</li>
	 * <li>e, eps, epsilon</li>
	 * </ul>
	 *
	 * @var array
	 */
	protected static $versionTagValues = array(
		'rc'		=> '.-8.',
		'dev'		=> '.-0.',
		'a'			=> '.-1.',
		'alpha'		=> '.-1.',
		'b'			=> '.-2.',
		'beta'		=> '.-2.',
		'c'			=> '.-3.',
		'g'			=> '.-3.',
		'gamma'		=> '.-3.',
		'd'			=> '.-4.',
		'delta'		=> '.-4.',
		'e'			=> '.-5.',
		'eps'		=> '.-5.',
		'epsilon'	=> '.-5.',
	);
	
	/**
	 * Recursive {@link http://php.net/glob glob}.
	 *
	 * @param string $pattern
	 * @param int $flags
	 * @return array
	 */
	public static function globr($pattern, $flags = NULL)
	{
		$files = glob($pattern, $flags);
		$dirPath = dirname($pattern);
		$dirs = Util::getDir($dirPath);
		if (is_array($dirs))
		{
			foreach( $dirs as $dir )
			{
				$items = Util::globr($file. str_replace($dirPath, '', $pattern), $flags);
				$files = array_merge($files, $items);
			}
		}
		return $files;
	}
	
	/**
	 * Returns all subfolders (except "." and "..") under $sDir in a non-recursive way.
	 *
	 * @param string $sDir
	 * @return array
	 */
	public static function getDir($path)
	{
		$i=0;
		$result = false;

		if (is_dir($path))
		{
			if ($rs = opendir($path))
			{
				while ($item = readdir($rs))
				{
					if (is_dir($path.'/'.$item ))
					{
						if ($item != '.' && $item != '..')
						{
							$result[$i] = $path.'/'.$item ;
							$i++;
						}
					}
				}
			}
		}
		return $result;
	}
	
	/**
	 * Returns the given PHP INI setting key's value as bool true or false.
	 *
	 * @param string $key
	 * @return bool
	 */
	public static function iniGetBool($key)
	{
		$value = ini_get($key);

		switch (strtolower($value))
		{
			case 'on':
			case 'yes':
			case 'true':
				return true;
			
			case 'off':
			case 'no':
			case 'false':
				return false;

			default:
				return (bool)(int)$value;
		}
	}
	
	/**
	 * Strips all backslashes (unescapes) all level of the passed array.
	 *
	 * @param array $array
	 * @return array
	 */
	public static function unquoteAll($array)
	{
		$it = new RecursiveArrayIterator($array);
		$result = array();

		foreach ($it as $k=>$element)
		{
			if (!$it->hasChildren())
			{
				$result[$k] = stripslashes($element);
			}
			else
			{
				$result[$k] = Util::unquoteAll($element);
			}
		}

		unset($it);
		return $result;
	}
	
	/**
	 * Formats an Exception very well.
	 *
	 * <p>Returns message with detailed traceback and {@link showCode highlighted} codesnipets.</p>
	 *
	 * @param \Exception $e
	 * @return string
	 */
	public static function showException(\Exception $e)
	{
		$tplMain = '<style type="text/css"><!--
		body, html
		{
			font-family: Arial, Tahoma;
			font-size: 14px;
			line-height: 14pt;
		}
	--></style>
	<h1>Probléma lépett fel az oldal futása közben</h1>
	<p>
		<b>Egy <span style="color:#b00;">$eClass</span> kivétel keletkezett a működés során, az alábbi hibaüzenettel:</b><br/>
		&nbsp;&bull; $eMessage
	</p>
	<h2>Részletek</h2>
	<p>
		<b>Forrás fájl:</b> $eFile<br/>
		<b>A kivételt okozó sor:</b> $eLine<br/>
		<b>Kiemelt kódrészlet:</b><br/>
		<div style="font-size:11px;">A kivétel keletkezésének helye:</div>
		$ePreviewCodeThrow
		<div style="font-size:11px;">A kivétel feltételezhető okozója:</div>
		$ePreviewCodeExceptor
	</p>
	<h2>Nyomkövetési információk</h2>
	<p>
		<ol>
		$eDebugTrace
		</ol>
	</p>
	<h2>Lekérések</h2>
	<p>
		<ol>
		$eQueryLog
		</ol>
	</p>';
		$tplTraceRow = '<li><b>$eClass$eType$eFunction($eParams)</b> hívás a(z) <u>$eFile</u> fájlban, a(z) <b>$eLine-ik</b> sorban.</li>';
		$tplParams = '<u>$eClass</u>';
		$tplQuery = '<li><code>$eQuery</code></li>';

		$sDebugTrace = '<li><b>Nem érhető el nyomkövetési információ!</b></li>';
		$aDebugTrace = $e->getTrace();
		$eExcFile = '';
		$eExcLine = '';
		if (is_array($aDebugTrace) and count($aDebugTrace) > 0)
		{
			$aFormatTrace = array();
			$sDebugTrace = '';
			$eExcFile = $aDebugTrace[0]['file'];
			$eExcLine = $aDebugTrace[0]['line'];
			$aDebugTrace = array_reverse($aDebugTrace);
			foreach ($aDebugTrace as $traceLine)
			{
				$sParams = '';
				if (is_array($traceLine['args']) and count($traceLine['args']) > 0)
				{
					$aParams = array();
					foreach ($traceLine['args'] as $arg)
					{
						$aParamItem = '';
						if (is_object($arg))
						{
							$aParamItem = str_replace('$eClass', '<i>'.get_class($arg).'</i>', $tplParams);
						}
						else
						{
							$aParamItem = str_replace(
								'$eClass',
								'<i>'.gettype($arg).'</i> '.
								(is_string($arg)?
								"'".htmlspecialchars($arg)."'" :
									(is_array($arg)?
									htmlspecialchars(print_r($arg, TRUE)) :
										(is_null($arg)?
										'null' :
											(is_bool($arg)?
												($arg? 'true' : 'false') :
												htmlspecialchars((string)$arg)
											)
										)
									)
								),
								$tplParams
							);
						}
						$aParams[] = $aParamItem;
					}
					$sParams = implode(', ', $aParams);
				}
				$repTraceLine = array(
					'$eClass'	=> (isset($traceLine['class'])? $traceLine['class'] : ''),
					'$eType'	=> (isset($traceLine['type'])? $traceLine['type'] : ''),
					'$eFunction'=> $traceLine['function'],
					'$eParams'	=> $sParams,
					'$eFile'	=> (isset($traceLine['file'])? Util::trimPath($traceLine['file']) : 'nem azonosítható'),
					'$eLine'	=> (isset($traceLine['line'])? $traceLine['line'] : '???')
				);
				$aFormatTrace[] = str_replace(
					array_keys($repTraceLine),
					array_values($repTraceLine),
					$tplTraceRow
				);
			}
			$sDebugTrace = implode("\n", $aFormatTrace);
		}
		$eFile = $e->getFile();
		$eLine = $e->getLine();
		$ePreviewThrow = '<b>Nincs megtekinthető kódrészlet!</b>';
		$ePreviewExceptor = '<b>Nincs megtekinthető kódrészlet!</b>';
		if (!empty($eFile) and !empty($eLine))
		{
			$lineStart = ($eLine - 7 < 0? 0 : $eLine - 7);
			$lineEnd = $eLine + 7;
			$content = file_get_contents($eFile);
			$exploded = explode(Util::getEol($content), $content);
			$lines = array();
			foreach ($exploded as $line=>$row)
			{
				if ($line >= $lineStart and $line <= $lineEnd) $lines[] = $row;
			}
			$ePreviewThrow = Util::formatSource($lines, $lineStart, $eLine);
		}
		if (!empty($eExcFile) and !empty($eExcLine))
		{
			$lineStart = ($eExcLine - 7 < 0? 0 : $eExcLine - 7);
			$lineEnd = $eExcLine + 7;
			$content = file_get_contents($eExcFile);
			$exploded = explode(Util::getEol($content), $content);
			$lines = array();
			foreach ($exploded as $line=>$row)
			{
				if ($line >= $lineStart and $line <= $lineEnd) $lines[] = $row;
			}
			$ePreviewExceptor = Util::formatSource($lines, $lineStart, $eExcLine);
		}
		$eQueryLog = '<li>A lekérésinformációk nem érhetőek el!</li>';
		$repMain = array(
			'$eClass'				=> get_class($e),
			'$eMessage'				=> $e->getMessage(),
			'$eFile'				=> Util::trimPath($eFile),
			'$eLine'				=> $eLine,
			'$ePreviewCodeThrow'	=> $ePreviewThrow,
			'$ePreviewCodeExceptor'	=> $ePreviewExceptor,
			'$eDebugTrace'			=> $sDebugTrace,
			'$eQueryLog'			=> $eQueryLog,
		);
		$result = str_replace(
			array_keys($repMain),
			array_values($repMain),
			$tplMain
		);

		return $result;
	}
	
	/**
	 * Formats the given $code very well.
	 *
	 * @param string $code The PHP source code without leading &lt?php
	 * @param int $startLine The line no. where the snipet starts
	 * @param int $hLine The line no. to highlight (like selected line in an editor)
	 * @param int $displayLines How many lines to display
	 * @return string
	 */
	public static function formatSource($code, $startLine=1, $hlLine=FALSE, $displayLines=TRUE)
	{
		if (is_array($code) or is_string($code))
		{
			if (is_string($code))
			{
				$code = preg_split("!(\r\n|\n\r|\r|\n)!", $code);
			}
			$countLines = count($code);
			$result = '<style type="text/css">
	<!--
		div.line div.linenum,
		div.hled div.linenum
		{
			text-align: right;
			background: #FDECE1;
			padding: 0 1px 0 1px;
			font-family: "Courier New", Courier;
			font-size: 10pt;
			float: left;
			width: 37px;
		}
		
		div.line
		{
			width: 20000px;
		}
		
		div.hled
		{
			width: 20000px;
			background: #fbb;
		}
		
		div.hled div.linenum
		{
			background: #fbb;
		}

		code
		{
			font-size: 10pt;
			font-family: "Courier New", Courier;
		}
		
		div.code
		{
			float: left;
		}
		
		div.dump
		{
			width: 700px;
			overflow: hidden;
			text-align: left;
			background: white;
			border: 1px solid #cc6666;
			border-left: 3px solid #cc6666;
			padding: 0 1px 0 0;
			font-family: "Courier New", Courier;
			margin: 3px 0 30px 21px;
		}
		div.clear
		{
			clear: both;
			float: none;
		}
	-->
	</style>';
			
			// highlighting
			$highlighted = highlight_string('<?php'."\n".implode("\n", $code), TRUE);
			
			// storing and removing original "noise"
			$arrOrig = array();
			preg_match('!^\<code\>\<span([^\>]*?)\>(.*)\</span\>\s*\</code\>$!s', $highlighted, $arrOrig);
			$highlighted = preg_replace('!^\<code\>\<span([^\>]*?)\>(.*)\</span\>\s*\</code\>$!s', '$2', $highlighted);
			
			// throw all br tags to its right place
			$arrCorrect = array();
			$arrBrs = array();
			preg_match_all('!\</span\>\<br /\>!', $highlighted, $arrCorrect);
			preg_match_all('!\<br /\>!', $highlighted, $arrBrs);
			while (count($arrCorrect[0]) != count($arrBrs[0]))
			{
				$highlighted = preg_replace('!\<span([^\>]*?)\>((.*?)[^\>])?\<br /\>!', '<span$1>$2</span><br /><span$1>', $highlighted);
				preg_match_all('!\</span\>\<br /\>!', $highlighted, $arrCorrect);
				preg_match_all('!\<br /\>!', $highlighted, $arrBrs);
			}
			
			// split by br tags
			$highlightedLines = explode('<br />', $highlighted);
			$highlighted = '';
			unset($highlightedLines[0]); // remove PHP open tag
			
			// decorate highlighted lines
			foreach ($highlightedLines as $k=>$codeLine)
			{
				$lineNo = $k + $startLine;
				if ($hlLine === $lineNo)
				{
					$line = '<div class="hled">';
				}
				else
				{
					$line = '<div class="line">';
				}
				
				if ($displayLines)
				{
					$line .= '<div class="linenum">'.(string)$lineNo.'</div>';
				}
				
				$line .= '<div class="code"><code><span'.$arrOrig[1].'>'.$codeLine.'</span></code></div><div class="clear"></div>';
				$line .= '</div>'."\n";
				
				$highlightedLines[$k] = $line;
			}
			
			$highlighted = implode('', $highlightedLines);
			
			$result .= '<div class="dump">'.$highlighted.'</div>';
		}
		else
		{
			$result = '<strong><span style="color: red">A kód megjelenítése nem lehetséges, mert a $code argumentum típusa nem megfelelő!</span></strong>';
		}
		
		return $result;
	}
	
	/**
	 * Trims passed file path - returns that w/o AdhocPHP's root path.
	 *
	 * <p>This is a secure way to display file paths in the output (for the user) because
	 * the evil one don't give the chance to kwnow the <i>egsact</i> path of the files in
	 * the system.</p>
	 *
	 * @param string $path Local file path
	 * @return string
	 */
	public static function trimPath($path)
	{
		return str_replace(ADHOC_ROOT_DIR, '', $path);
	}
	
	/**
	 * Returns with the EOL char(s) used in passed content.
	 *
	 * @param string $s
	 * @return string It's "\r\n", "\n\r", "\n" or "\r".
	 */
	public static function getEol($s)
	{
		if (strpos($s, "\r\n") !== FALSE) return "\r\n";
		if (strpos($s, "\n\r") !== FALSE) return "\n\r";
		if (strpos($s, "\n") !== FALSE) return "\n";
		if (strpos($s, "\r") !== FALSE) return "\r";
		return "\n";
	}
	
	/**
	 * Replaces $search with $replace recursively on $array.
	 *
	 * @param string $search
	 * @param string $replace
	 * @param array|string $array
	 * @param bool $extended If true, extends the replace both to keys and values
	 * @return array|string Result type depends on the type of passed $array argument
	 */
	public static function replaceTree($search='', $replace='', $array=false, $extended=false)
	{
		if (!is_array($array))
		{
			return str_replace($search, $replace, $array);
		}
		 
		$newArr = array();
		foreach ($array as $k=>$v)
		{
			$addKey = $k;
			if ($extended)
			{
				$addKey = str_replace($search, $replace, $k);
			}
		 
			$newArr[$addKey] = self::replaceTree($search, $replace, $v, $extended);
		}
		return $newArr;
	}
	
	/**
	 * Returns a comperable value-hash.
	 *
	 * <p>This method gives a way to you to use language compare operators on version
	 * numbers. For example you can:
	 * <code>&lt;?php
	 * use \Adhoc\Util as Util;
	 *
	 * if (Util::version('5.1') > Util::version('5.0.1')) echo '5.1 > 5.0.1 and ';
	 * if (Util::version('1.0-rc1') &lt; Util::version('1.1-rc2')) echo '1.0-rc1 &lt; 1.1-rc2 and ';
	 * if (Util::version('1.0-rc1') &lt; Util::version('1.0-rc2')) echo '1.0-rc1 &lt; 1.0-rc2 and ';
	 * if (Util::version('2.1') > Util::version('2.1b')) echo '2.1 > 2.1b';</code>
	 * This will echo that: 5.1 > 5.0.1 and 1.0-rc1 &lt; 1.1-rc2 and 1.0-rc1 &lt; 1.0-rc2 and 2.1 > 2.1b
	 *
	 * @param string $vernum Version number - supported tags and suffixes are specified in
	 * {@link $versionTagValues}.
	 * @return string A 33 characters long numerical hash started with an 'x' just for
	 * PHP converting reasons (this guarantees that returned hash must interpreted as a string).
	 */
	public static function version($vernum)
	{
		$result = '';
		$prepared = array(
			'0000',
			'0000',
			'0000',
			'0000',
			'ffff',
			'0000',
			'0000',
			'0000',
		);
		
		// normalize formula
		$vernum = preg_replace('![^\.\da-z]!', '.', $vernum);
		
		// replace possible tags
		$stdver = str_replace(array_keys(self::$versionTagValues), array_values(self::$versionTagValues), $vernum);
		$hasTag = ($stdver !== $vernum);
		
		// cleaning
		$stdver = preg_replace('![^\d\.\-]!', '', $stdver);
		$stdver = preg_replace('!\.+!', '.', $stdver);
		
		// explode
		$items = explode('.', $stdver);
		
		// iterate over (optimized way)
		if (!$hasTag)
		{
			foreach ($items as $k=>$val)
			{
				$prepared[$k] = str_pad(dechex((int)$val), 4, '0', STR_PAD_LEFT);
			}
		}
		else
		{
			$len = count($items);
			for ($k = 0; $k < $len; $k++)
			{
				$val = $items[$k];
				
				if ($val{0} == '-') break;
				
				$prepared[$k] = str_pad(dechex((int)$val), 4, '0', STR_PAD_LEFT);
			}
			
			$kk = 4;
			$k++;
			//var_dump($val);
			$prepared[$kk] = str_pad(dechex(-1 * (int)$val), 4, '0', STR_PAD_LEFT);
			for ($i = 0; $k < $len; $i++, $k++)
			{
				$val = $items[$k];
				$kk++;
				$prepared[$kk] = str_pad(dechex((int)$val), 4, '0', STR_PAD_LEFT);
			}
		}
		
		return 'x'.implode('', $prepared);
	}
	
	/**
	 * Returns the classname of given object (or classname string) without
	 * namespace path.
	 *
	 * @param object|string $object
	 * @return string
	 */
	public static function getClassName($object)
	{
		if (is_object($object)) $object = get_class($object);
		if (!is_string($object)) throw new \InvalidArgumentException('Object or string expected on $object but '.gettype($object).' given.');
		
		if (strpos($object, '\\') === false) return $object;
		return substr(strrchr($object, '\\'), 1);
	}
	
	public static function parseUrl($url)
	{
		$re =
		'%^'.
			'(?P<schema>[a-zA-Z0-9_][-a-zA-Z0-9_]*?)://'.						// identify the schema
			'(((?P<user1>[^:@]+?)@)|((?P<user2>[^:@]+?):(?P<pass>[^:]*?)@)|)'.	// gets user or user and password if specified
			'((?P<host1>[^:@/]*?)|(?P<host2>[^:@/]*?):(?P<port>\d+?))'.			// gets host or host and port if specified
			'(?P<path>/[^\?#]*?)'.												// gets the path (it's a "/" at least)
			'(\?(?P<query>.*?))?'.												// gets the query if specified
			'(#(?P<fragment>.*))?'.												// gets the fragment if present
		'$%';
		preg_match($re, $url, $matches);
		$result = array();
		foreach ($matches as $k=>$v)
		{
			// don't want empty values
			if (empty($v)) continue;
			// we need only one "user" key if user specified with or without password
			if ($k === 'user1' or $k === 'user2')
			{
				$result['user'] = $v;
				continue;
			}
			// like with "user" keys...
			if ($k === 'host1' or $k === 'host2')
			{
				$result['host'] = $v;
				continue;
			}
			// we don't like numeric keys so they kicked out
			if (!is_int($k)) $result[$k] = $v;
		}
		unset($result['user1'], $result['user2'], $result['host1'], $result['host2']);
		
		return $result;
	}
	
	/**
	 * This method tries to mask the password in the passed URL if specified.
	 *
	 * <p>If no password specified in the passed URL, simply returns that. If passed
	 * argument is not a string, this method returns a message "*Not a String!*".</p>
	 *
	 * @return string
	 */
	public static function securedUrl($url)
	{
		$re = '%^([^:@]*?:)([^@]*?)(@.*$)%';
		if (!is_string($url))
		{
			$result = '*Not a String!*';
		}
		else
		{
			$result = preg_replace($re, '$1********$3', $url);
		}
		
		return $result;
	}
	
	/**
	 * Checks $lazy if it's an object instance of $base or a string contains class's name
	 * match the same rule.
	 *
	 * @param string|object $lazy
	 * @param string $base
	 * @param callback $instanceCb Callback with one argument ($lazy)
	 * @return $lazy or instance of $lazy
	 */
	public static function lazyClass($lazy, $base, $instanceCb = null)
	{
		if (is_null($instanceCb)) $instanceCb = function(){throw new \BadFunctionCallException('You must specify a callback for \Adhoc\Util::lazyClass!');};
		if (!is_string($lazy) or !is_object($lazy)) throw new \InvalidArgumentException('Argument $lazy must be a string or object in method '.__METHOD__.' but '.gettype($lazy).' given.');
		if (!is_string($base)) throw new \InvalidArgumentException('Argument $base must be a string in method '.__METHOD__.' but '.gettype($base).' given.');
		if ($base{0} !== '\\') throw new \InvalidArgumentException('Argument $base must started with "\\" in method '.__METHOD__.' but "'.$base.'" given.');
		if (!is_callable($instanceCb)) throw new \InvalidArgumentException('Argument $instanceCb (type of '.gettype($instanceCb).') is not callable in method '.__METHOD__.'.');
		
		$result = false;
		$namespace = substr($base, 0, strrpos($base, '\\') + 1);
		
		if (is_string($lazy))
		{
			if ($lazy{0} !== '\\')
			{
				$lazy = $namespace.$lazy;
			}
			else
			{
				$parents = class_parents($lazy);
				if (array_search($base, $lazy) !== false)
				{
					$result = $instanceCb($lazy);
				}
			}
			
		}
		else if (is_object($lazy) and ($lazy instanceof $base))
		{
			$result = $lazy;
		}
		
		if ($result === false)
		{
			throw new \InvalidArgumentException('Argument $instance of method '.$method.' has invalid type. Valid types are: string, object ('.$base.').');
		}
	}
}

?>