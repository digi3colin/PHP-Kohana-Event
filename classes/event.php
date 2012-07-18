<?php
/**
 * digi3colin | Date: 12年7月18日 | Time: 上午10:37
 */
class Event{
	const CAPTURING_PHASE	= 1;
	const AT_TARGET			= 2;
	const BUBBLING_PHASE	= 3;

	public $type;//read only.
	public $target;//read only
	public $currentTarget;//read only
	public $eventPhase;//read only
	public $bubbles;//read only
	public $cancelable;//read only
//	public $timeStamp;//read only

	public $isEventStopped = false;
	public $allowDefaultBehaviour = true;

	public function __construct($eventTypeArg,$canBubbleArg=false, $cancelableArg=false) {
		$this->type       = $eventTypeArg;
		$this->bubbles    = $canBubbleArg;
		$this->cancelable = $cancelableArg;
	}

	public function stopPropagation(){
		$this->isEventStopped = true;
	}

	/*Many events have associated behaviors that are carried out by default. For example, if a user types a character into a text field, the default behavior is that the character is displayed in the text field. Because the TextEvent.TEXT_INPUT event's default behavior can be canceled, you can use the preventDefault() method to prevent the character from appearing.*/
	public function preventDefault(){
		if($this->cancelable == false)return;
		$this->allowDefaultBehaviour = false;
	}
}