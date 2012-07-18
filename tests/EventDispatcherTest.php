<?php

function my_autoloader($class) {
	include '../classes/'.strtolower($class).'.php';
}

spl_autoload_register('my_autoloader');

class MockEventDispatcher extends D3EventDispatcher{
	const EVENT_CHANGE = "EVENT_CHANGE";
	const EVENT_UPDATE = 'EVENT_UPDATE';
	public function __construct(){
		parent::__construct();
	}

	public function change(){
		$this->dispatchEvent(new Event(MockEventDispatcher::EVENT_CHANGE));
	}

	public function update(){
		$this->dispatchEvent(new Event(MockEventDispatcher::EVENT_UPDATE));
	}
}

class EventDispatcherTest extends PHPUnit_Framework_TestCase
{
	/** @var MockEventDispatcher */
	private $evt;
	private $invokedCount=0;
	private $callback;
	private $callbackChange;
	private $callbackUpdate;
	private $callbackStopPropagate;

	public function setUp(){
		parent::setUp();
		$this->evt = new MockEventDispatcher();
		$this->invokedCount =0;
		$this->callbackChange = array($this,'changeCallback');
		$this->callbackUpdate = array($this,'updateCallback');
		$this->callback = array($this,'callback');
		$this->callbackStopPropagate = array($this,'stopPropagationCallback');
	}

	public function tearDown(){
		parent::tearDown();
		unset($this->evt);
		unset($this->invokedCount);
	}

	public function callback(Event &$e){
		$this->invokedCount++;
		$this->assertTrue($e->target===$this->evt);
	}

	public function changeCallback(Event &$e){
		$this->invokedCount++;
		$this->assertEquals(MockEventDispatcher::EVENT_CHANGE,$e->type);
		$this->assertTrue($e->target===$this->evt);
	}

	public function updateCallback(Event &$e){
		$this->invokedCount++;
		$this->assertEquals(MockEventDispatcher::EVENT_UPDATE, $e->type);
		$this->assertTrue($e->target===$this->evt);
	}

	public function stopPropagationCallback(Event &$e){
		$this->invokedCount++;
		$this->assertTrue($e->target===$this->evt);
		$e->stopPropagation();
	}

	public function testAddEventListener(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback run once');
	}

	public function testAddEventListenerRunTwice(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback run once');
		$this->evt->change();
		$this->assertEquals(2,$this->invokedCount, 'the callback run 2nd time');
	}

	public function testRemoveEventListener(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->removeEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(0,$this->invokedCount, 'the callback should not run');
	}

	public function testAddEventListenerToTwoEvents(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_UPDATE,$this->callbackUpdate);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount);
		$this->evt->update();
		$this->assertEquals(2,$this->invokedCount);
	}

	public function testAddTwice(){
		//If multiple identical EventListeners are registered on the same EventTarget with the same parameters the duplicate instances are discarded. They do not cause the EventListener to be called twice and since they are discarded they do not need to be removed with the removeEventListener method.
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback should run 1 time');
	}

	public function testAddTwiceRemoveOnce(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->removeEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(0,$this->invokedCount, 'the callback should not run');
	}

	public function testRemoveMoreThanOnce(){
		$this->evt->removeEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(0,$this->invokedCount, 'the callback should not run');
	}

	public function testAddOnceRemoveMoreThanOnce(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->removeEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->removeEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(0,$this->invokedCount, 'the callback should not run');
	}

	public function testWhenMethod(){
		$this->evt->when(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback should 1 time');
	}

	public function testWhenMixWithAddEventListenerMethod(){
		$this->evt->when(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback run 1st time');
		$this->evt->change();
		$this->assertEquals(2,$this->invokedCount, 'the callback run 2nd time');
	}

	public function testOnceMethod(){
		$this->evt->once(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback run 1st time');
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount, 'the callback just run 1 time');
	}

	public function testOnceMixAddEventMethod(){
		$this->evt->once(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(2,$this->invokedCount);
		$this->evt->change();
		$this->assertEquals(3,$this->invokedCount);
	}

	public function testOnceMixAddEventMethod2(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(1,$this->invokedCount);
		$this->evt->once(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->change();
		$this->assertEquals(3,$this->invokedCount);
		$this->evt->change();
		$this->assertEquals(4,$this->invokedCount);
	}

	public function testStopPropagation(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callbackStopPropagate);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,$this->callback);
		$this->evt->change();
		$this->assertEquals(2,$this->invokedCount);
	}

	public function testStopPropagationWithOnce(){
		$this->evt->once(MockEventDispatcher::EVENT_CHANGE,$this->callbackChange);
		$this->evt->once(MockEventDispatcher::EVENT_CHANGE,$this->callbackStopPropagate);
		$this->evt->once(MockEventDispatcher::EVENT_CHANGE,$this->callback);
		$this->evt->change();
		$this->assertEquals(2,$this->invokedCount);
	}

	private $priorityRunning=0;
	public function _testPriorityCallback1(Event $e){
		$this->assertEquals(0,$this->priorityRunning);
		$this->priorityRunning=1;
	}

	public function _testPriorityCallback2(Event $e){
		$this->assertEquals(1,$this->priorityRunning);
		$this->priorityRunning=2;
	}

	public function _testPriorityCallback3(Event $e){
		$this->assertEquals(2,$this->priorityRunning);
		$this->priorityRunning=3;
	}

	public function testDefaultPriority(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback1'));
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback2'));
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback3'));
		$this->evt->change();
	}

	public function testUserPriority(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback2'),false,2);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback3'),false,1);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback1'),false,3);
		$this->evt->change();
	}

	public function testUserPriorityWithSamePriority(){
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback3'),false);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback1'),false,2);
		$this->evt->addEventListener(MockEventDispatcher::EVENT_CHANGE,array($this,'_testPriorityCallback2'),false,2);
		$this->evt->change();
	}


/*	public function testAddDuringDispatch(){
		//This method allows the registration of event listeners on the event target. If an EventListener is added to an EventTarget while it is processing an event, it will not be triggered by the current actions but may be triggered during a later stage of event flow, such as the bubbling phase.
	}
*/
}