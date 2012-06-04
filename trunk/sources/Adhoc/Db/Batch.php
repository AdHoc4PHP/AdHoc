<?php

namespace Adhoc\Db;

class Batch extends ArrayObject
{
	/**
	 * Connection used for execute this batch.
	 * @var \Adhoc\Db\Connection
	 */
	protected $connection;
	
	/**
	 * Parser instance used by this batch object.
	 * @var \Adhoc\Db\Batch\Parser
	 */
	protected $parser;
	
	/**
	 * Options for parser.
	 *
	 * <p>Keys used: batch (string), delimiter (string), nogarbage (bool).</p>
	 *
	 * @var array
	 */
	protected $options = array(
		'batch'		=> '',
		'delimiter'	=> ';',
		'nogarbage'	=> true
	);
	
	public function __construct(\Adhoc\Db\Connection $connection, array $options = array())
	{
		$this->parser = new \Adhoc\Db\Batch\Parser();
		$this->options = array_merge($this->options, $options);
		
		$batch = array();
		
		if (is_string($this->options['batch']))
		{
			if (!empty($this->options['batch']))
			{
				$this->parser->setBatch($this->options['batch']);
				$this->parser->setDelimiter($this->options['delimiter']);
				$batch = $this->parser->fetchAll($this->options['nogarbage']);
			}
		}
		else if (is_array($this->options['batch']))
		{
			$batch = $this->options['batch'];
		}
		else
		{
			if (!is_null($this->options['batch'])) throw new \InvalidArgumentException('Argument $options[\'batch\'] must be a string, an array or null, '.gettype($this->options['batch']).' given.');
		}
		
		parent::__construct($batch);
	}
	
	public function getParser()
	{
		return $this->parser;
	}
	
	public function addBatch()
	{
		$batch = $this->parser->fetchAll($this->options['nogarbage']);
		foreach ($batch as $line)
		{
			$this[] = $line;
		}
		
		return $this;
	}
	
	public function add($line)
	{
		$this[] = $line;
		
		return $this;
	}
	
	public function run($detectSelects = false)
	{
		if (!$detectSelects)
		{
			foreach ($this as $sql)
			{
				$result = $this->connection->getBoundConnection()->getPDO()->exec($sql);
				if (!$result) break;
			}
		}
		else
		{
			$result = array();
			foreach ($this as $k=>$sql)
			{
				$sql = ltrim($sql);
				if (stripos($sql, 'SELECT') !== false)
				{
					$stmt = $this->connection->getBoundConnection()->getPDO()->query($sql);
					if (!$stmt) return false;
					
					$result[$k] = $stmt->fetchAll();
				}
				else
				{
					$resultExec = $this->connection->getBoundConnection()->getPDO()->exec($sql);
					if (!$resultExec) return false;
				}
			}
		}
		
		return $result;
	}
}