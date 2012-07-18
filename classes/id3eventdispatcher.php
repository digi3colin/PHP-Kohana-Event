<?php
/**
 * digi3colin | Date: 12年7月18日 | Time: 上午10:37
 */
interface ID3EventDispatcher extends IEventDispatcher{
	public function when($eventType,$callback);
	public function once($eventType,$callback);
}
