<?php
/**
 * digi3colin | Date: 12年7月18日 | Time: 上午10:37
 */
class ErrorEvent extends Event{
	public $text;
	public $id;
	public function __construct($eventTypeArg,$canBubbleArg=false, $cancelableArg=false,$text='',$id=0) {
		parent::__construct($eventTypeArg,$canBubbleArg, $cancelableArg);
		$this->text = $text;
		$this->id = $id;
	}
}