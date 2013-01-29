<?php
class Timer {

	//output needs to be consistant because it is logged, used in notices, etc...

	private $startTime = null;
	
	public function start() {
		$mtime = microtime();
		$mtime = explode(' ', $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$this->startTime = $mtime; 
	}

	public function stop() {
		$mtime = microtime();
		$mtime = explode(' ',$mtime);
		$mtime = $mtime[1] + $mtime[0];
		$endtime = $mtime;
		return 1000*($endtime-$this->startTime);
	}
}
