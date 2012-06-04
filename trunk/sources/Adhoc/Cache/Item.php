<?php

namespace Adhoc\Cache;

/**
 * Class for representing a cached data.
 * <p>Class uses following {@link AdhocExpandManager expander(s)}:</p>
 * <ul>
 * <li>{@link \Adhoc\Expanders\EventExpander Event}: to serve event-handling for these events:
 * 		<ul>
 * 		<li><code>change</code>({@link Item}, <em>string</em> method):
 * 		fires if data changed. Method values: set, inc, dec.</li>
 * 		<li><code>remove</code>({@link Item}): fires if item marked
 * 		as removed.</li>
 * 		<li><code>revalidate</code>({@link Item}): fires if item
 * 		needs revalidation (if it is expired for example).</li>
 * 		</ul></li>
 * </ul>
 * 
 * @property \Adhoc\Expanders\EventExpander $Event
 * 
 * @author prometheus
 */
class Item
{
	/**
	 * Constant used for indicate that {@link data} is type of a primitive
	 * (int, float, string, array of primitives, object from stdClass).
	 */
	const TYPE_PRIMITIVE	= 0;
	
	/**
	 * Constant used for indicate that {@link data} is not primitive, or it is
	 * an array (or object) with non-primitive contents (items).
	 */
	const TYPE_COMPLEX		= 1;
	
	/**
	 * Flagged as <code>true</code> for indicate if this Item was not
	 * cached before.
	 * @var bool
	 */
	public $dirty = true;
	
	/**
	 * Represents the index identifier of this item in the owner
	 * {@link Engine}.
	 * @var string
	 */
	protected $id = '';
	
	/**
	 * Represents the creation time (in unix format) of this item.
	 * @var int
	 */
	protected $created = 0;
	
	/**
	 * Represents the time of last update (in unix format) of this item.
	 * @var int
	 */
	protected $updated = 0;
	
	/**
	 * Represents the time (in unix format) when this item becomes a removed
	 * item. 
	 * @var int
	 */
	protected $deleted = 0;
	
	/**
	 * Represents the expiration time of this item in unix format.
	 * @var int
	 */
	protected $expires = 0;
	
	/**
	 * Represents the expiration period of this item in unix format.
	 * @var int
	 */
	protected $period = 0;
	
	/**
	 * Indicates that if this item is garbage.
	 * @var bool
	 */
	protected $garbage = false;
	
	/**
	 * Indicates the type of this item.
	 * @var int
	 * @see TYPE_PRIMITIVE
	 * @see TYPE_COMPLEX
	 */
	protected $type = 0;
	
	/**
	 * Stores the data what going to cache.
	 * @var mixed
	 */
	protected $data;
	
	/**
	 * @var \Adhoc\ExpandManager
	 */
	protected $expnaders;
	
	/**
	 * Constructs this item from previously available data or blank.
	 * @param mixed $data
	 * @param object $from
	 * @param int $expires {@see expires}
	 * @param int $forceType Force a type for this item. See {@link type}.
	 * @throws \InvalidArgumentException
	 */
	public function __construct($data=null, $from=null, $expires = null, $forceType=null)
	{
		if (is_null($from))
		{
			if (is_resource($data))
			{
				throw new \InvalidArgumentException('Cache item cannot be a resource reference!');
			}
			
			$this->created = time();
			$this->type = (is_null($forceType)? ((is_object($data) && get_class($data) != 'stdClass')? Item::TYPE_COMPLEX : Item::TYPE_PRIMITIVE) : $forceType);
			$this->data = $data;
			if (isset($expires))
			{
				$this->expires = $expires;
				$this->period = $this->expires - $this->created;
			}
		}
		else
		{
			if (is_object($from))
			{
				$this->id = $from->id;
				$this->created = $from->created;
				$this->updated = $from->updated;
				$this->deleted = $from->deleted;
				$this->expires = $from->expires;
				$this->period = $from->period;
				$this->garbage = $from->garbage;
				$this->type = $from->type;
				$this->data = $from->data;
			}
		}
		
		if ($this->expires !== 0 and ($this->created >= $this->expires))
		{
			$this->garbage = true;
		}
		
		// Add \Adhoc\Expanders\EventExpander class to manage events
		$this->expnaders = new \Adhoc\ExpandManager($this);
		$this->expnaders[] = 'Event';
		// register event handlers
		$this->Event->register('revalidate', 'change', 'remove');
	}
	
	/**
	 * Returns a simple object with this item's properties with or without
	 * the data property.
	 * @param bool $withData
	 * @return object
	 */
	public function toObject($withData=true)
	{
		if ($this->expires !== 0 and ($this->created >= $this->expires))
		{
			$this->garbage = true;
		}
		
		$result = new stdClass();
		$result->id = $this->id;
		$result->created = $this->created;
		$result->updated = $this->updated;
		$result->deleted = $this->deleted;
		$result->expires = $this->expires;
		$result->period = $this->period;
		$result->garbage = $this->isGarbage;
		$result->type = $this->type;
		if ($withData) $result->data = $this->data;
		
		return $result;
	}
	
	/**
	 * Returns an associative array with this item's properties with or without
	 * the data property.
	 * @param $withData
	 * @return array
	 */
	public function toArray($withData=true)
	{
		if ($this->expires !== 0 and ($this->created >= $this->expires))
		{
			$this->garbage = true;
		}
		
		$result = array(
			'id'		=> $this->id,
			'created'	=> $this->created,
			'updated'	=> $this->updated,
			'deleted'	=> $this->deleted,
			'expires'	=> $this->expires,
			'period'	=> $this->period,
			'garbage'	=> $this->garbage,
			'type'		=> $this->type,
		);
		
		if ($withData) $result['data'] = $this->data;
		
		return $result;
	}
	
	/**
	 * Returns this item's properties (with or without the data property) in a
	 * JSON formatted string. Data property will be {@link dump dumped} if you
	 * needed that.
	 * @param bool $withData
	 * @return string
	 */
	public function toJSON($withData=true)
	{
		$result = $this->toObject($withData);
		if ($withData)
		{
			$result->data = $this->dump();
		}
		
		$result = json_encode($result);
		
		return $result;
	}
	
	/**
	 * Returns the data property as a primitive or a serialized string if
	 * {@link type} is {@link TYPE_COMPLEX}.
	 * @return mixed
	 */
	public function dump()
	{
		$result = $this->data;
		
		if ($this->type == Item::TYPE_COMPLEX)
		{
			$result = serialize($this->data);
		}
		
		return $result;
	}
	
	/**
	 * Sets the data, the {@link expires expiration time} and/or the {@link type}
	 * of this item.
	 * @param mixed $data
	 * @param int $expires
	 * @param int $forceType
	 * @throws \InvalidArgumentException
	 */
	public function set($data, $expires=null, $forceType=null)
	{
		if (is_resource($data))
		{
			throw new \InvalidArgumentException('Cache item cannot be a resource reference!');
		}
		
		$this->updated = time();
		$this->type = (is_null($forceType)? ((is_object($data) && get_class($data) != 'stdClass')? Item::TYPE_COMPLEX : Item::TYPE_PRIMITIVE) : $forceType);
		$this->data = $data;
		if (isset($expires))
		{
			$this->expires = $expires;
			$this->period = $this->expires - $this->updated;
		}
		else if ($this->expires !== 0)
		{
			$this->expires = $this->updated + $this->period;
		}
		
		if ($this->expires !== 0 and ($this->created >= $this->expires))
		{
			$this->garbage = true;
		}
		
		$this->Event->fire('change', array($this, 'set'));
	}
	
	/**
	 * Returns the cached data.
	 * @throws Exteptions\ItemIsGarbage
	 * @return mixed
	 */
	public function get()
	{
		if (!$this->skipValidation and !$this->isValid())
		{
			throw new Exteptions\ItemIsGarbage();
		}
		
		return $this->data;
	}
	
	/**
	 * Marks this item to removed.
	 */
	public function remove()
	{
		$this->deleted = time();
		$this->Event->fire('remove', array($this));
	}
	
	/**
	 * Sets the index identifier in the owner.
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}
	
	/**
	 * Returns the index identifier of this item.
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}
	
	/**
	 * Returns <code>true</code> if item is valid (not marked as garbage or
	 * as removed). Otherwise returns <code>false</code>.
	 * @return bool
	 */
	public function isValid()
	{
		if ($this->deleted > 0)
		{
			$this->garbage = true;
		}
		
		if ($this->expires !== 0 and ($this->created >= $this->expires))
		{
			$this->garbage = true;
		}
		
		// removed items cannot be revalidated
		if ($this->garbage and $this->deleted == 0)
		{
			$this->garbage = false;
			$this->skipValidation = true;
			
			$this->Event->fire('revalidate', array($this));
			
			$this->skipValidation = false;
		}
		
		return !$this->garbage;
	}
	
	/**
	 * Returns <code>true</code> if item marked as removed, <code>false</code>
	 * otherwise.
	 * @return bool
	 */
	public function isRemoved()
	{
		return ($this->deleted > 0);
	}
	
	/**
	 * Tries to increment this item if it is a
	 * {@link http://php.net/is_numeric numeric} data.
	 * @throws \Exception
	 * @param numeric $step
	 */
	public function inc($step=1)
	{
		if (!is_numeric($this->data))
		{
			throw new \Exception('This item is NaN, cannot be incremented.');
		}
		
		if (is_string($this->data))
		{
			if ($this->data == (string)(float)$this->data)
			{
				$this->data = (string)(((float)$this->data) + $step);
			}
			else if ($this->data == (string)(int)$this->data)
			{
				$this->data = (string)(((int)$this->data) + $step);
			}
		}
		else
		{
			$this->data = $this->data + 1;
		}
		
		$this->Event->fire('change', array($this, 'inc'));
	}
	
	/**
	 * Tries to decrement this item if it is a
	 * {@link http://php.net/is_numeric numeric} data.
	 * @throws \Exception
	 * @param numeric $step
	 */
	public function dec($step=1)
	{
		if (!is_numeric($this->data))
		{
			throw new \Exception('This item is NaN, cannot be decremented.');
		}
		
		if (is_string($this->data))
		{
			if ($this->data == (string)(float)$this->data)
			{
				$this->data = (string)(((float)$this->data) - $step);
			}
			else if ($this->data == (string)(int)$this->data)
			{
				$this->data = (string)(((int)$this->data) - $step);
			}
		}
		else
		{
			$this->data = $this->data - 1;
		}
		
		$this->Event->fire('change', array($this, 'dec'));
	}
	
	public function __call($method, $args)
	{
		return $this->expnaders->handleCall($method, $args);
	}
	
	public function __get($name)
	{
		return $this->expnaders->handleGet($name);
	}
}