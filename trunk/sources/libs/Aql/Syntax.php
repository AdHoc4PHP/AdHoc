<?php

/**
 * This is a syntax checker and transformer for Adhoc Query Language Objects.
 * <p>A syntax descriptor is a formal language to describe the syntax of an SQL
 * statement like you see it in a document.</p>
 * <p>Accepted syntax elements are:</p>
 * <ul>
 * <li><code>FOO ITEM</code> means that "FOO ITEM" is a required first-level
 * part of the query.</li>
 * <li><code>&lt;FOO BAR&gt;</code> means that "FOO BAR" is a required option or
 * clause in the query at any level.</li>
 * <li><code>[OPTIONAL PART]</code> means that "OPTIONAL PART" is an optional
 * clause or item in the query at any level</li>
 * <li><code>,...</code> within a block at the end of the closer bracket flags
 * that the block can be repeated in the query.</li>
 * </ul>
 * <p>Named expressions</p>
 * 
 * 
 * 
 * @author prometheus
 *
 */
class Aql_Syntax
{
	/**
	 * A syntax descriptor.
	 * @var string
	 */
	protected $descriptor = '';
	
	/**
	 * A PCRE pattern without replaced placeholders, delimiters and PCRE
	 * modifiers.
	 * @var string
	 */
	protected $pattern = '';
	
	/**
	 * The fully prepared syntax descriptor checker PCRE pattern.
	 * @var string
	 */
	protected $fullPattern = '';
	
	/**
	 * An associative list of placeholders (named expressions)
	 * @var array
	 */
	protected $placeholders = array();
	
	/**
	 * This is a complicated PCRE pattern used to identify blocked structures
	 * within the syntax descriptor.
	 * @var string
	 */
	protected $syntaxMatcher = '';
	
	/**
	 * Constructs a syntax checker and executer object.
	 * @param unknown_type $descriptor
	 */
	public function __construct($descriptor)
	{
		$this->syntaxMatcher =
			'!'.
			'(?P<opener>'.				// $opener, $1 = do: match an acceptable opening character
				'(\[)'.					//   $2 = match if: "["
				'|'.					//   or
				'(\<)'.					//   $3 = match if: "<"
			')'.						// end do.
										// followed by...
			'(?P<content>'.				// $content, $4 = do: match acceptable content enclosed with brackets "["..."]" or "<"...">"
				'(?:'.					//   $5 = do: once only
					'(?:'.				//     $6 = do: once only (match optionally list separator)
						'|\s*\|\s*'.	//       match if: nothing or "|" with optional whitespaces before and after
					')'.				//     end do.
										//     followed by...
					'\s*'.				//     optional whitespaces and...
					'('.				//     $7 = do: match acceptable list item
										//
										//       /* checking if group opened by "[": */
						'?(2)'.			//       if isset($2):
							'['.		//         accepted chars are: "<", ">", ".", "/", ",", " ", "_", ":", A-Z, 0-9 
								'\<\>\.'.'/,'.' _:'.'a-z'.'0-9'.
							']+'.		//         accepted chars matches one or more times
						'|'.			//       else: (this means that $3 presents because $opener ($2 or $3) is required)
							'['.		//         accepted chars are: "[", "]", ".", "/", ",", " ", "_", ":", A-Z, 0-9
								'\[\]\.'.'/,'.' _:'.'a-z'.'0-9'.
							']+'.		//         accepted chars matches one or more times
										//       end if.
					')'.				//     end do.
					'\s*'.              //     matching optional whitespaces after valid item ($7)
					'|'.                //     or
					'(?R)'.             //     do recursive if not matched a valid item
				')+'.					//   end do (many count acceptable)
			')'.						// end do (matching valid item or block)
										// valid item closed by...
			'('.						// $8 = do: matchng valid closing character
				'?(2)'.					//   if isset($2):
					'\]'.				//     match "]"
				'|'.					//   else:
					'\>'.				//     match "]"
										//   end if.
			')'.						// end do.
			'!i';
		$this->setSyntax($descriptor);
	}
	
	/**
	 * Changes the used syntax descriptor to the passed one and converts it to
	 * PCRE syntax to prepare it to an {@link execute execution}.
	 * @param string $descriptor
	 */
	public function setSyntax($descriptor)
	{
		$this->descriptor = '/statement:'.trim($descriptor);
		$this->pattern = $this->convert($this->normailze($this->descriptor));
		$this->fullPattern = '';
	}
	
	/**
	 * Binds a placeholder (named expression) with its PCRE syntaxed value.
	 * @param string $placeholder Name of the placeholder like in the
	 * syntax descriptor. It could contain: a-z, A-Z, 0-9 and underscores. 
	 * @param string $value A PCRE syntaxed value which can matches the expression.
	 */
	public function bind($placeholder, $value)
	{
		$this->placeholders['/'.$placeholder] = $value;
	}
	
	/**
	 * Unbinds a placeholder (named expression).
	 * @param string $placeholder
	 */
	public function unbind($placeholder)
	{
		if (isset($this->placeholders['/'.$placeholder]))
		{
			unset($this->placeholders['/'.$placeholder]);
		}
	}
	
	/**
	 * Unbinds all placeholders.
	 */
	public function unbindAll()
	{
		$this->placeholders = array();
	}
	
	/**
	 * Executes the syntax descriptor on the passed argument with replacing
	 * all placeholders.
	 * @param string $on
	 * @return false|array Returns an associative array with all syntactically
	 * highlited parts and named expressions if succeded.
	 */
	public function execute($on)
	{
		$result = false;
		$matches = null;
		if (preg_match($this->getPattern(), $on, $matches))
		{
			$result = array();
			foreach ($matches as $k=>$v)
			{
				if (!is_int($k))
				{
					$result[$k] = $v;
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Executes syntax checking on the passed argument.
	 * @param $on
	 * @return bool True if passed argument is valid (validation based on the
	 * syntax descriptor), false otherwise.
	 */
	public function check($on)
	{
		return preg_match($this->getPattern(), $on);
	}
	
	/**
	 * Caches and returns the fully prepared PCRE pattern.
	 * @return string
	 */
	protected function getPattern()
	{
		if (!empty($this->fullPattern)) return $this->fullPattern;
		
		$this->fullPattern = '!'.str_replace(array_keys($this->placeholders), array_values($this->placeholders), $this->pattern).'!i';
		return $this->fullPattern;
	}
	
	/**
	 * Prepares a syntax descriptor with formal normalization for
	 * {@link convert converting}.
	 * @param $desc
	 * @return string
	 */
	protected function normailze($desc)
	{
		// Making all whitespace-blocks to one space character.
		$desc = preg_replace('!\s+!', ' ', $desc);
		
		// Clear all spaces within all blocks recursivly.
		$desc = preg_replace_callback($this->syntaxMatcher, array($this, 'recbPrepareBlocks'), $desc);
		
		// Clear spaces between all non alphabetic parts of descriptor.
		$desc = preg_replace('!([^a-z0-9_]) ([^a-z0-9_])!i', '$1$2', $desc);
		
		// Remove spaces after all alphabetic parts of descriptor.
		$desc = preg_replace('!(^|[\>\]])( )?([a-z0-9_ ]+)( ([\[\<])|$)!i', '$1$2$3$5', $desc);
		
		// Add spaces after all opening brackets followed by an expression.
		$desc = preg_replace('!([\[\<\|])([a-z0-9_ /]+)!i', '$1 $2', $desc);
		
		// Add <...> brackets and convert spaces temporarly in all named items.
		$desc = preg_replace_callback('!/([a-z0-9_ ]+:[a-z0-9_ ]+?)( )?([\[\]\<\>]|$)!i', array($this, 'recbPrepNamedItems'), $desc);
		
		// Make all simple named items to </name:/name> form.
		$desc = preg_replace('!(^| |[^-a-z0-9_/:])(/)?([-a-z0-9_][-a-z0-9_ ]+)( |[^-a-z0-9_/:]|$)!i', '$1</$3:$2$3>$4', $desc);
		
		// Replace all temporarly marked spaces to spaces.
		$result = str_replace('-', ' ', $desc);
		
		return $result;
	}
	
	/**
	 * Converts a {@link normalize normalized} syntax descriptor to PCRE syntax. 
	 * @param $normalized
	 * @return string
	 */
	protected function convert($normalized)
	{
		// Convert all blocks to PCRE syntax.
		$normalized = preg_replace_callback($this->syntaxMatcher, array($this, 'recbConvertBlocks'), $normalized);
		
		// Convert named items to PCRE syntax: /name:expression to ?P<name>expression  
		$normalized = preg_replace('!/([a-z0-9 _]+):([[a-z0-9 _/]+)([^a-z0-9_/]|$)!i', '?P<$1>$2$3', $normalized);
		
		// Convert all named items spaces temporarly.
		$normalized = preg_replace_callback('!\?P\<[^\>]*?\>!', array($this, 'recbConvertNamedItems'), $normalized);
		
		// Convert all spaces to "has many whitespaces" PCRE-form.
		$normalized = str_replace(' ', '\s+', $normalized);
		
		// Convert all temporarly converted spaces to underscores.
		$result = str_replace('-', '_', $normalized);
		
		return $result;
	}
	
	/**
	 * A PCRE callback function to clear all spaces within all blocks recursivly.
	 * @param $arg
	 * @return string
	 */
	protected function recbPrepareBlocks($arg)
	{
		$content = $arg['content'];
		if (preg_match($this->syntaxMatcher, $content))
		{
			$content = preg_replace_callback($this->syntaxMatcher, array($this, 'recbPrepareBlocks'), $content);
		}
		$content = $arg['opener'].$content.($arg['opener']=='['? ' ]' : ' >');
		$result = str_replace(' ', '', $content);
		
		return $result;
	}
	
	/**
	 * A PCRE callback function to add &lt;...&gt; and convert spaces in named expressions.
	 * <p>All spaces in a <code>/expression name</code> or
	 * <code>/item name:ITEM</code> converted to "-". It's a temporarly used
	 * function, "-" are converted to "_" (underscores) later in the convert
	 * process.
	 * @param $arg
	 * @return string
	 */
	protected function recbPrepNamedItems($arg)
	{
		return '</'.str_replace(' ', '-', $arg[1]).'>'.$arg[3];
	}
	
	/**
	 * A PCRE callback function to convert brackets recursivly.
	 * <p>Conversions:</p>
	 * <ul>
	 * <li><code>&lt;expressions&gt;</code> and <code>[expressions]</code> to
	 * <code>(expressions)</code> and <code>(expressions)?</code></li>
	 * <li><code>&lt;expressions,...&gt;</code> and <code>[expressions,...]</code>
	 * to <code>((expressions)+)</code> and <code>((expressions)+)?</code></li>
	 * </ul>
	 * @param $arg
	 * @return string
	 */
	protected function recbConvertBlocks($arg)
	{
		$content = $arg['content'];
		if (preg_match($this->syntaxMatcher, $content))
		{
			$content = preg_replace_callback($this->syntaxMatcher, array($this, 'recbConvertBlocks'), $content);
		}
		if ($hasMany = (substr($content, -4) == ',...'))
		{
			$content = substr($content, 0, -4);
		}
		$opener = '('.($hasMany? '(' : '');
		$closer = ($hasMany? ')+' : '').($arg['opener']=='<'? ')' : ')?');
		
		$result = $opener.$content.$closer;
		//var_dump($arg[0]);
		return $result;
	}
	
	protected function recbConvertNamedItems($arg)
	{
		return str_replace(' ', '-', $arg[0]);
	}
}