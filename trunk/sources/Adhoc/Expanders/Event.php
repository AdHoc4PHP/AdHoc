<?php

namespace \Adhoc\Expanders;

class Event extends \Adhoc\Expander
{
	protected $events = array();
	
	protected $subscribers = array();
	
	protected $locked = array();
	
	public function register()
	{
		$this->events = func_get_args();
	}
	
	/**
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function on($event, $subscriber)
	{
		if (!is_callable($subscriber))
		{
			throw new \InvalidArgumentException('Subscriber is not callable for "'.$event.'" event.');
		}
		if (!in_array($event, $this->events))
		{
			throw new \Exception('Event "'.$event.'" not registered.');
		}
		
		if (!isset($this->subscribers[$event]))
		{
			$this->subscribers[$event] = array();
		}
		
		if (array_search($subscriber, $this->subscribers[$event], true) !== false)
		{
			$this->subscribers[$event][] = $subscriber;
		}
	}
	
	public function isOn($event, $subscriber)
	{
		return (array_search($subscriber, $this->subscribers[$event], true) !== false);
	}
	
	/**
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function mass($event, $subscribers)
	{
		if (!in_array($event, $this->events))
		{
			throw new \Exception('Event "'.$event.'" not registered.');
		}
		
		foreach ($subscribers as $subscriber)
		{
			if (!is_callable($subscriber))
			{
				throw new \InvalidArgumentException('Subscriber is not callable for "'.$event.'" event.');
			}
			if (!isset($this->subscribers[$event]))
			{
				$this->subscribers[$event] = array();
			}
			
			if (array_search($subscriber, $this->subscribers[$event], true) !== false)
			{
				$this->subscribers[$event][] = $subscriber;
			}
		}
	}
	
	public function un($event, $subscriber=null)
	{
		if (isset($this->subscribers[$event]))
		{
			if (!isset($subscriber))
			{
				unset($this->subscribers[$event]);
			}
			else
			{
				$idx = array_search($subscriber, $this->subscribers[$event], true);
				if ($idx !== false)
				{
					unset($this->subscribers[$event][$idx]);
				}
			}
		}
	}
	
	/**
	 * @throws \Exception
	 */
	public function fire($event, $args=array())
	{
		if (!in_array($event, $this->events))
		{
			throw new \Exception('Event "'.$event.'" not registered.');
		}
		if (isset($this->locked[$event]))
		{
			return true;
		}
		
		if (isset($this->subscribers[$event]))
		{
			foreach ($this->subscribers[$event] as $subscriber)
			{
				if (call_user_func_array($subscriber, $args) === false)
				{
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function getSubscribers($event)
	{
		if (!isset($this->subscribers[$event])) return array();
		return array_merge(array(), $this->subscribers[$event]);
	}
	
	public function moveFrom(EventExpander $eventExpander)
	{
		foreach ($this->events as $event)
		{
			$subscribers = $eventExpander->getSubscribers($event);
			$eventExpander->un($event);
			$this->mass($event, $subscribers);
		}
	}
	
	/**
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function lock($event=null)
	{
		if (!isset($event))
		{
			$event = array_merge(array(), $this->events);
		}
		
		if (is_string($event))
		{
			$event = array($event);
		}
		
		if (!is_array($event))
		{
			throw new \InvalidArgumentException('Passed argument must be NULL, a string, or an array.');
		}
		
		foreach ($event as $e)
		{
			if (!in_array($e, $this->events))
			{
				throw new \Exception('Event "'.(string)$e.'" is not registered therefor cannot be locked.');
			}
			$this->locked[$e] = $e;
		}
	}
	
	/**
	 * @throws \InvalidArgumentException
	 * @throws \Exception
	 */
	public function release($event)
	{
		if (!isset($event))
		{
			$event = array_merge(array(), $this->events);
		}
		
		if (is_string($event))
		{
			$event = array($event);
		}
		
		if (!is_array($event))
		{
			throw new \InvalidArgumentException('Passed argument must be NULL, a string, or an array.');
		}
		
		foreach ($event as $e)
		{
			if (!in_array($e, $this->events))
			{
				throw new \Exception('Event "'.$e.'" is not registered therefor cannot be released.');
			}
			if (isset($this->locked[$e]))
			{
				$this->locked[$e];
			}
		}
	}
	
	public function isLocked($event)
	{
		return isset($this->locked[$event]);
	}
}