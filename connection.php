<?php

class Net{
	public $layers = [];
	public $learning_rate = 0;

	function __construct($hidden_layers, $n_hidden, $n_in, $n_out, $l_r){
		$this->learning_rate = $l_r;
		array_push($this->layers, new Layer($n_in, $l_r));
		for ($i = 0; $i < $hidden_layers; $i++){
			array_push($this->layers, new Layer($n_hidden, $l_r));
		}
		array_push($this->layers, new Layer($n_out, $l_r));
	}

	function __toString(){
		$rv = "Net:\n";
		for ( $i = 0; $i < count($this->layers); $i++ ){
			$rv .= "\t";
			$rv .= (string)$this->layers[$i];
		}
		return $rv;
	}
}

class Layer{
	function __construct($n_in, $l_r){}
	
	function __toString(){
		return "Layer:\n";
	}
}

class Neuron{
}

class Connection{
}	


function test_construct(){
	//Create net and print layers
	$net = new Net(3,5,5,5,.5);

	echo $net;
}

test_construct();
