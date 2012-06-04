<?php

namespace Adhoc\Db\Batch;

class Parser
{
	/**
	 * This keyword used to change the command delimiter string.
	 * @const string
	 */
	const DELIMITER_CMD = 'DELIMITER';
	
	/**
	 * Length of {@link DELIMITER_CMD}
	 * @const int
	 */
	const DELIMITER_CMD_LEN = 9;
	
	/**
	 * Opening and closing sequences for SQL comments.
	 * <p>Used: #, //, and &slash;* ... *&slash;</p>
	 * @var array [array[string]]
	 */
	protected $comment = array(
		'#'		=> array("\r\n", "\n\r", "\n", "\r"),
		'//'	=> array("\r\n", "\n\r", "\n", "\r"),
		'/*'	=> array('*/')
	);
	
	/**
	 * Opening sequences for SQL comments.
	 * @var array [string]
	 */
	protected $commentStarters = array();
	
	/**
	 * First chars of opening sequences for SQL comments.
	 * @var array [string]
	 */
	protected $commentStarters1stChars = array();
	
	/**
	 * Chars used as SQL apostrophes.
	 * @var array [string]
	 */
	protected $apostrophes = array(
		'"', "'"
	);
	
	/**
	 * Chars used as whitespaces in SQL batch
	 * @var array [string]
	 */
	protected $whitespaces = array(
		" ", "\t", "\n", "\r"
	);
	
	/**
	 * EOL chars list.
	 * @var array [string]
	 */
	protected $eolSequences = array(
		"\r\n", "\n\r", "\n", "\r"
	);
	
	/**
	 * Char used as SQL escape char.
	 * @var string
	 */
	protected $escapeChar = '\\';
	
	/**
	 * Indicates that a comment-block being started parsing.
	 * @var string|bool false if parser is outside of any comment block.
	 */
	protected $commentUsed = false;
	
	/**
	 * Indicates that if escaped sequence detected on parsing.
	 * @var bool
	 */
	protected $unresolvedEscapeSequence = false;
	
	/**
	 * Indicates that parser is within a string.
	 * @var string|bool false if not in any string.
	 */
	protected $withinString = false;
	
	/**
	 * Actual string used as delimiter.
	 * @var string
	 */
	protected $delimiter = ';';
	
	/**
	 * Original delimiter string used to restore after parsing.
	 * @var string
	 */
	protected $delimiterOriginal = ';';
	
	/**
	 * The SQL batch being parsed*
	 * @var string
	 */
	protected $batch = '';
	
	/**
	 * Length of SQL batch.
	 * @var int
	 */
	protected $batchLength = 0;
	
	/**
	 * Starting position of parser.
	 * @var int
	 */
	protected $lastPosition = 0;
	
	/**
	 * Last parsed char.
	 * @var string
	 */
	protected $lastChar = '';
	
	/**
	 * Last fetched SQL command or statement.
	 * @var string|bool false if parsing not started
	 */
	protected $lastFetched = false;
	
	/**
	 * Constructor
	 *
	 * @param string $batch SQL batch to parse
	 * @param string $delimiter The delimiter used as {@link $originalDelimiter original delimiter}
	 */
	public function __construct($batch = '', $delimiter = ';')
	{
		$this->batch = $batch;
		$this->batchLength = strlen($batch);
		$this->delimiter = $delimiter;
		$this->delimiterOriginal = $delimiter;
		$this->commentStarters = array_keys($this->comment);
		foreach ($this->commentStarters as $seq) $this->commentStarters1stChars[] = $seq{0};
	}
	
	/**
	 * Changes the delimiter and the original delimiter.
	 *
	 * @param string $delimiter
	 * @throws \RuntimeException When parsing started you cannot change the delimiter.
	 */
	public function setDelimiter($delimiter)
	{
		if ($this->lastFetched !== false) throw new \RuntimeException('It\'s not possible to change the delimiter after parsing started.');
		
		$this->delimiter = $delimiter;
		$this->delimiterOriginal = $delimiter;
	}
	
	/**
	 * Sets the batch to parse.
	 *
	 * @param string $batch
	 * @throws \RuntimeException When parsing started you cannot set the batch.
	 */
	public function setBatch($batch)
	{
		if ($this->lastFetched !== false) throw new \RuntimeException('It\'s not possible to set the batch to parse after parsing started.');
		
		$this->batch = $batch;
		$this->batchLength = strlen($batch);
	}
	
	/**
	 * Fetches the next one command or statement.
	 *
	 * @param bool $withNoGarbage false if you want include EOLs and comments to parser output.
	 * @return string|bool false if parse ends successfully
	 */
	public function fetch($withNoGarbage = true)
	{
		if ($this->lastPosition >= $this->batchLength) return false;
		
		$result = '';
		
		for ($pos = $this->lastPosition; $pos < $this->batchLength; $pos++)
		{
			$char = $this->batch{$pos};
			
			if (!$this->withinString and $this->commentUsed)
			{
				if (!$withNoGarbage) $result .= $char;
				if (strlen($this->commentUsed) == 1)
				{
					$needle = $char;
				}
				else
				{
					$needle = $this->lastChar.$char;
				}
				
				if (!in_array($needle, $this->comment[$this->commentUsed])) continue;
				
				$this->commentUsed = false;
				$this->lastChar = $char;
				
				if (!$withNoGarbage)
				{
					$this->lastPosition = $pos + 1;
					$this->lastFetched = $result;
					return $result;
				}
				
				continue;
			}
			
			if (!$this->withinString and !$this->commentUsed and in_array($char, $this->whitespaces))
			{
				if (!$withNoGarbage)
				{
					$isEol = false;
					$eolLength = 1;
					if ($pos + 1 >= $this->batchLength)
					{
						$isEol = true;
					}
					else
					{
						if (in_array($char, array("\r", "\n")))
						{
							$isEol = true;
							$nextChar = $this->batch{$pos + 1};
							
							// for example: "\n\n" is not a 2 byte long EOL sequence but "\r\n" is!
							if (in_array($nextChar, array("\r", "\n")) and in_array($char.$nextChar, $this->eolSequences))
							{
								$eolLength = 2;
							}
						}
					}
					
					if (!$isEol)
					{
						$result .= $char;
						continue;
					}
					else
					{
						$this->lastPosition = $pos + $eolLength;
						$this->lastFetched = $result;
						return $result;
					}
				}
				
				continue;
			}
			
			if (!$this->withinString and !$this->commentUsed and substr($this->batch, $pos, self::DELIMITER_CMD_LEN) === self::DELIMITER_CMD)
			{
				$pos += self::DELIMITER_CMD_LEN;
				$this->delimiter = '';
				for ($partial = $pos; $partial < $this->batchLength; $partial++)
				{
					$partialChar = $this->batch{$partial};
					
					if (in_array($partialChar, $this->whitespaces)) continue;
					if (in_array($partialChar, array("\r", "\n")))
					{
						if (empty($this->delimiter)) throw new \RuntimeException('Fetching SQL batch found '.self::DELIMITER_CMD.' with no value!');
						
						$pos = $partial + 1;
						break; // new delimiter set
					}
					
					$this->delimiter .= $partialChar;
				}
				
				continue;
			}
			
			if (!$this->withinString and !$this->commentUsed and $char === $this->delimiter)
			{
				$this->lastPosition = $pos + strlen($this->delimiter);
				$this->lastFetched = $result;
				
				return $result;
			}
			
			if (!$this->withinString and !$this->commentUsed)
			{
				if (in_array($char, $this->commentStarters1stChars))
				{
					if (isset($this->comment[$char]))
					{
						$this->commentUsed = $char;
						$commentStarterLength = 1;
					}
					else if (isset($this->comment[substr($batch, $pos, 2)]))
					{
						$this->commentUsed = substr($batch, $pos, 2);
						$commentStarterLength = 2;
					}
					
					if ($this->commentUsed)
					{
						if (!$withNoGarbage)
						{
							$result .= $this->commentUsed;
						}
						
						$pos += $commentStarterLength;
						continue;
					}
				}
			}
			
			if (!$this->withinString and !$this->commentUsed and in_array($char, $this->apostrophes))
			{
				$this->withinString = $char;
				$result .= $char;
				continue;
			}
			
			if ($this->withinString and $char === $this->escapeChar)
			{
				$this->unresolvedEscapeSequence = true;
				$result .= $char;
				continue;
			}
			
			if ($this->withinString === $char)
			{
				$result .= $char;
				if (!$this->unresolvedEscapeSequence) $this->withinString = false;
				$this->unresolvedEscapeSequence = false;
				continue;
			}
			
			$result .= $char;
			$this->lastPosition = $pos + 1;
			$this->lastChar = $char;
		}
		
		$this->lastFetched = $result;
		return $result;
	}
	
	/**
	 * Resets this parser absolutly.
	 */
	public function reset()
	{
		$this->lastPosition = 0;
		$this->lastChar = '';
		$this->lastFetched = false;
		$this->delimiter = $this->delimiterOriginal;
		$this->unresolvedEscapeSequence = false;
		$this->withinString = false;
		$this->commentUsed = false;
	}
	
	/**
	 * Fetches all command or statement.
	 *
	 * @param bool $withNoGarbage false if you want include EOLs and comments to parser output.
	 * @return array
	 * @throws \RuntimeException When parsing started you cannot use fetchAll method.
	 */
	public function fetchAll($withNoGarbage = true)
	{
		if ($this->lastFetched !== false) throw new \RuntimeException('It\'s not possible to use '.__METHOD__.' after parsing started.');
		
		$result = array();
		
		while ($command = $this->fetch($withNoGarbage))
		{
			$result[] = $command;
		}
		
		$this->reset();
		
		return $result;
	}
}