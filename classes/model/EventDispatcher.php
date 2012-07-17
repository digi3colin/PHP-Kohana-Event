<?php 
//http://www.w3.org/TR/DOM-Level-2-Events/events.html

interface IEventDispatcher{
	public function addEventListener($type, $callback, $useCapture = false, $priority = 0);
	public function dispatchEvent(Event &$event);
	public function removeEventListener($type, $callback, $useCapture = false);
}

class EventDispatcher implements IEventDispatcher{
	private static $__id=0;
	/** @var int */
	private $id;
	
	public function __construct(){
		$this->id = EventDispatcher::$__id++;
	}

	public function addEventListener($type, $callback, $useCapture = false, $priority = 0){
		//check callback
		if(count($callback)!=2)throw new Exception('invalid callback format');
		EventListenerList::instance()->add($this->id,$type,$callback,$useCapture,$priority);
	}

	public function removeEventListener($type, $callback, $useCapture = false){
		EventListenerList::instance()->remove($this->id,$type,$callback,$useCapture);
	}

	public function dispatchEvent(Event &$event){
		$event->target = $this;
		$event->currentTarget = $this;
		EventListenerList::instance()->dispatch($this->id,$event);
		
		//A value of true if the event was successfully dispatched. A value of false indicates failure or that preventDefault() was called on the event.
		return $event->allowDefaultBehaviour;
	}
}

class EventListenerList{
	/** @var EventListenerList */
	private static $ins;
	public static function instance(){
		if(empty(EventListenerList::$ins)){
			EventListenerList::$ins = new EventListenerList();
		}
		return EventListenerList::$ins;
	}
	/** @var array */
	private $queue;	
	public function __construct(){
		$this->queue = array();
	}
	
	public function add($dispatcherId, $eventName, &$callback, $useCapture = false, $priority = 0){
		if(empty($this->queue[$dispatcherId]))$this->queue[$dispatcherId] = array();
		if(empty($this->queue[$dispatcherId][$eventName]))$this->queue[$dispatcherId][$eventName]= array();

		//prevent add twice of same event name and handler;
		if($this->isAdded($dispatcherId,$eventName,$callback,$useCapture))return;
		$this->queue[$dispatcherId][$eventName][] = $callback;
	}

	private function isAdded($dispatcherId, $eventName, &$callback, $useCapture = false){
		$callbacks = &$this->queue[$dispatcherId][$eventName];
		$count = count($callbacks);
		for($i=0;$i<$count;$i++){
			if($callbacks[$i][0]===$callback[0] && $callbacks[$i][1]==$callback[1])return true;
		}
		return false;
	}

	public function remove($dispatcherId, $eventName, &$callback, $useCapture = false){
		if(empty($this->queue[$dispatcherId]))return;
		if(empty($this->queue[$dispatcherId][$eventName]))return;

		$callbacks = &$this->queue[$dispatcherId][$eventName];
		$count = count($callbacks);
		for($i=0;$i<$count;$i++){
			if($callbacks[$i][0] === $callback[0] && $callbacks[$i][1]==$callback[1]){
				array_splice($callbacks,$i,1);
				return;
			}
		}
	}

	public function dispatch($dispatcherId, Event &$event){
		$eventName = $event->type;

		//capture phase
		/*php not have dom like structure, it may not require the capture and bubble phase*/
		//target phase

		if(isset($this->queue[$dispatcherId])&&isset($this->queue[$dispatcherId][$eventName])){
			//copy the array because cannot guarantee the callbacks change by removeEventListener
			$callbacks = $this->queue[$dispatcherId][$eventName];
			$count = count($callbacks);
			for($i=0;$i<$count;$i++){
				call_user_func($callbacks[$i],$event);
				if($event->isEventStopped)break;
			}
		}
		/*php not have dom like structure, it may not require the capture and bubble phase*/
		//if($event->bubbles == true){$event->currentTarget=xxx;}
	}
}

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

interface ID3EventDispatcher extends IEventDispatcher{
	public function when($eventType,$callback);
	public function once($eventType,$callback);
}

class D3EventDispatcher extends EventDispatcher implements ID3EventDispatcher{
	private $once_callbacks;
	private $once_callback;

	public function __construct(){
		parent::__construct();

		$this->once_callbacks=array();
		$this->once_callback=array(&$this,'run_once');
	}

	public function when($eventType,$callback){
		$this->addEventListener($eventType,$callback,false,0);
		return $this;
	}

	public function once($eventType,$callback){
		//if event dispatched, it will remove after dispatched.
		if(empty($this->once_callbacks[$eventType])){
			$this->once_callbacks[$eventType] = array();//this array<function> cannot release until gc;

			$this->addEventListener($eventType, $this->once_callback, false, 0);
		}

		$this->once_callbacks[$eventType][]=$callback;

		return $this;
	}

	public function run_once(Event &$event){
		$this->removeEventListener($event->type, $this->once_callback);
		//the callback may call once() again
		//then the dict[e.type] will increase;
		//prevent this by clone the array;
		$callbacks = &$this->once_callbacks[$event->type];
		$count = count($callbacks);
		for($i=0;$i<$count;$i++){
			call_user_func($callbacks[$i],$event);
			if($event->isEventStopped)break;
		}
		unset($this->once_callbacks[$event->type]);
	}
}
