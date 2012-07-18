<?php 
//http://www.w3.org/TR/DOM-Level-2-Events/events.html

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
//internal class for event dispatcher
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

		$this->queue[$dispatcherId][$eventName][] = array('callback'=>$callback,'priority'=>$priority);
	}

	private function isAdded($dispatcherId, $eventName, &$callback, $useCapture = false){
		$callbacks = &$this->queue[$dispatcherId][$eventName];
		$count = count($callbacks);
		for($i=0;$i<$count;$i++){
			//if callback[0], object is same on the list and callback[2], method ,is same on the list, it's added
			if($callbacks[$i]['callback'][0]===$callback[0] && $callbacks[$i]['callback'][1]==$callback[1])return true;
		}
		return false;
	}

	public function remove($dispatcherId, $eventName, &$callback, $useCapture = false){
		if(empty($this->queue[$dispatcherId]))return;
		if(empty($this->queue[$dispatcherId][$eventName]))return;

		$callbacks = &$this->queue[$dispatcherId][$eventName];
		$count = count($callbacks);
		for($i=0;$i<$count;$i++){
			if($callbacks[$i]['callback'][0] === $callback[0] && $callbacks[$i]['callback'][1]==$callback[1]){
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
			//sort by priority;

			usort($callbacks, array($this,'comparePriorityDesc'));

			for($i=0;$i<$count;$i++){
				call_user_func($callbacks[$i]['callback'],$event);
				if($event->isEventStopped)break;
			}
		}
		/*php not have dom like structure, it may not require the capture and bubble phase*/
		//if($event->bubbles == true){$event->currentTarget=xxx;}
	}

	private function comparePriorityDesc($a, $b)
	{
		return ($a['priority'] <= $b['priority']) ? 1 : -1;
	}
}