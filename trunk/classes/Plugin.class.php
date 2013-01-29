<?php
abstract class Plugin {
	public static $rawInput;

	//output needs to be consistant because it is logged, used in notices, etc...
	public static $output = array(
		'responseTimeMs'=>0,				//response time in milliseconds
		'returnContent'=>'',				//specfic error message
		'currentStatus'=>0,					//0-error,1 ok
		'measuredValue'=>'',				//varchar(100)
		'htmlEmail' => 0,					//defaults to text only email notices, many plugins will want to use html emails
	);

	public abstract function about();

	/*
	$input is array taken from single input field of values to be used
	*/
	public abstract function runPlugin($input=array());
}

