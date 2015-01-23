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
	private $alpha = 1;
	private $learning_rate = 0;
	private $nextLayer = null;
	private $previousLayer = null;
	private $neurons = [];

	function __construct($n_in_layer, $l_r){
		$this->learning_rate = $l_r;
		for ( $i = 0; $i < $n_in_layer; $i++){
			array_push($this->neurons, new Neuron($this));
		}
	}
	
	function __toString(){
		$rv =  "Layer:\n";
		for ( $i = 0; $i < count($this->neurons); $i++){
			$rv .= "\t\t";
			$rv .= (string)$this->neurons[$i];
		}
		return $rv;
	}
}

class Neuron{
	private $alpha = 1;
	private $input_connections = [];
	private $output_connections = [];
	private $bias = 0;
	private $net_input = 0;
	private $output = 0;
	private $delta = 0;
	private $layer = null;

	function __construct($l){
		$this->layer = $l;
		$this->bias  = .5;
	}

	function __toString(){
		return "Neuron:\n";
	}

	function sigmoid($x){
		return 1/(1+pow(2.71828,(-*$this->alpha * $x)));
	}

	function add_input($connection){
		array_push($this->input_connections, $connection);
	}

	function add_output($output){
		array_push($this->output_connections, $output);
	}

	function calculate_net_input(){
		$this->net_input = 0;
		for( $i = 0; $i < count($this->input_connections); $i++){
			$c = input_connections[i];
			$net_input += $c->weight * $c->n_from.output;
		}
		$this->net_input += $this->$bias;
	}

	function calculate_output(){
		$this->calculate_net_input();
		$this->output = $this->sigmoid($this->net_input);
	}

	function compute_output_delta($expected){
		$this->delta = $this->output(1-$this.output)*($this->expected-$this->output);
	}

	function compute_delta(){
		$delta_sum = 0;
		for( $i = 0; $i < count($this->layer->next()->neurons); $i++){
			$nlayer_neuron = $layer->next()->neurons[i];
			$delta_sum += $nlayer_neuron->delta*$this->output_connections[i]->weight;
		}
		$this->delta = $output*(1-$output)*$delta_sum;
	}

	function change_weights(){
		//TO BE IMPLEMENTED
	}
}

class Connection{
	private $n_from = null;
	private $n_to = null;
	private $weight = 0;

	function __construct($n_f, $n_t){
		$rand_weight = ((float)rand()/(float)getrandmax()) - .5; 
		$this->n_from = $n_f;
		$this->n_to = $n_t;
		$this->weight = $rand_weight;
	}
}	


function test_construct(){
	//Create net and print layers
	$net = new Net(3,5,5,5,.5);

	echo $net;
}

test_construct();
