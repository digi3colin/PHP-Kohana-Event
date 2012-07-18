<?php
/**
 * digi3colin | Date: 12年7月18日 | Time: 上午10:35
 */
interface IEventDispatcher{
	public function addEventListener($type, $callback, $useCapture = false, $priority = 0);
	public function dispatchEvent(Event &$event);
	public function removeEventListener($type, $callback, $useCapture = false);
}