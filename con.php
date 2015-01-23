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

	function test_net( $inputs, $outputs ){
		$total_sum = 0;
		for ( $i = 0; $i < count($inputs); $i++){
			$pattern_sum = 0;
			$this->run_net($inputs[$i]);
			$actual = $this->get_output();
			for( $j = 0; $j < count($actual); $j++ ){
				$pattern_sum += ( $actual[$j]-$outputs[$i][$j] );
			}
			$pattern_avg = $pattern_sum/(count($actual));

			$total_sum += pow($pattern_avg, 2);
		}
		$rsme = sqrt((1/(2*count($inputs)))*$total_sum);
		return $rsme;
	}

	function learn( $input, $expected_output ){
		$this->run_net( $input );
		for ( $i = 0; $i < count($this->layers); $i++ ){
			#Need the backwards index
			$index = count($this->layers)-1-$i;
			if ($this->layers[$index]->previousLayer != null){
				$this->layers[$index]->compute_deltas($expected_output);
			}
		}
		for ( $i = 1; $i < count($this->layers); $i++ ){
			$this->layers[$i]->change_weights();
		}
	}

	function run_net($inputs){
		if ( count($inputs) != count($this->layers[0]->neurons)){
			echo 'Data input size does not match network input size';
		}else{
			# Set up initial inputs
			for ( $i = 0; $i < count($this->layers[0]->neurons); $i++){
				$this->layers[0]->neurons[$i]->output = $inputs[$i];
			}

			#Traverse layers (except for input) and compute outputs
			for ( $i = 1; $i < count($this->layers); $i++ ){
				$this->layers[$i]->run_layer();
			}
		}

		return $this->get_output();
	}

	function wire_connections(){
		for ( $i = 1; $i < count($this->layers); $i++){
			$this->layers[$i]->add_input_layer($this->layers[$i-1]);
		}
	}

	function get_output(){
		$rv = [];
		$output_layer = $this->layers[count($this->layers)-1];
		for ( $i = 0; $i < count($output_layer->neurons); $i++){
			array_push($rv,$output_layer->neurons[$i]->output);
		}
		return $rv;
	}
}

class Layer{
	public $alpha = 1;
	public $learning_rate = 0;
	public $nextLayer = null;
	public $previousLayer = null;
	public $neurons = [];

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

	function add_input_layer($input_layer){
		$this->previousLayer = $input_layer;
		$this->previousLayer->nextLayer = $this;
		for ( $i = 0; $i < count($this->neurons); $i++){
			for ( $j = 0; $j < count($input_layer->neurons); $j++){
				$con = new Connection($input_layer->neurons[$j], $this->neurons[$i]);
				$this->neurons[$i]->add_input($con);
				$this->previousLayer->neurons[$j]->add_output($con);
			}
		}
	}

	function compute_deltas($expected){
		if( $this->nextLayer == null ){
			for ( $i = 0; $i < count($this->neurons); $i++ ){
				$this->neurons[$i]->compute_output_delta($expected[$i]);
			}
		}else{
			for ( $i = 0; $i < count($this->neurons); $i++ ){
				$this->neurons[$i]->compute_delta();
			}
		}
	}

	function change_weights(){
		for( $i = 0; $i < count($this->neurons); $i++ ){
			$this->neurons[$i]->change_weights();
			$this->neurons[$i]->change_bias();
		}
	}

	function run_layer(){
		for ( $i = 0; $i < count($this->neurons); $i++ ){
			$this->neurons[$i]->calculate_output();
		}
	}

	function next(){
		return $this->nextLayer;
	}

	function previous(){
		return $this->previousLayer;
	}
}

class Neuron{
	public $alpha = 1;
	public $input_connections = [];
	public $output_connections = [];
	public $bias = 0;
	public $net_input = 0;
	public $output = 0;
	public $delta = 0;
	public $layer = null;

	function __construct($l){
		$this->layer = $l;
		$this->bias  = .5;
	}

	function __toString(){
		return "Neuron:\n";
	}

	function sigmoid($x){
		return 1/(1+pow(2.71828,(-1*$this->alpha * $x)));
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
			$c = $this->input_connections[$i];
			$this->net_input += $c->weight * $c->n_from->output;
		}
		$this->net_input += $this->bias;
	}

	function calculate_output(){
		$this->calculate_net_input();
		$this->output = $this->sigmoid($this->net_input);
	}

	function compute_output_delta($expected){
		$this->delta = $this->output*(1-$this->output)*($expected-$this->output);
	}

	function compute_delta(){
		$delta_sum = 0;
		for( $i = 0; $i < count($this->layer->next()->neurons); $i++){
			$nlayer_neuron = $this->layer->next()->neurons[$i];
			$delta_sum += $nlayer_neuron->delta*$this->output_connections[$i]->weight;
		}
		$this->delta = $this->output*(1-$this->output)*$delta_sum;
	}

	function change_weights(){
		if ($this->layer->previousLayer != null){
			for ($i = 0 ; $i < count($this->input_connections); $i++ ){
				$this->input_connections[$i]->weight += $this->input_connections[$i]->n_from->output * $this->delta * $this->layer->learning_rate;
			}
		}
	}

	function change_bias(){
		$this->bias += $this->layer->learning_rate * $this->delta;
	}
}

class Connection{
	public $n_from = null;
	public $n_to = null;
	public $weight = 0;

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

function test_functionality(){
	$learning_rate = .6;
	for ( $t = 0; $t < 200; $t++ ){
		$rmse_tot = 0;
		for ( $n = 0; $n < 10; $n++){
		$net = new Net(1,2,2,1,$learning_rate);
		$net->wire_connections();
		$sample_input = [1,1];
		$expected_output = [0];
		$sample_input2 = [0,0];
		$expected_output2 = [0];
		$sample_input3 = [0,1];
		$expected_output3 = [1];
		$sample_input4 = [1,0];
		$expected_output4 = [1];
		for ( $i = 0; $i < 1000; $i++ ){
			$net->learn($sample_input, $expected_output);
			$net->learn($sample_input2, $expected_output2);
			$net->learn($sample_input3, $expected_output3);
			$net->learn($sample_input4, $expected_output4);
		}
		$inputs = [];
		$outputs = [];
		array_push($inputs, $sample_input);
		array_push($inputs, $sample_input2);
		array_push($inputs, $sample_input3);
		array_push($inputs, $sample_input4);

		array_push($outputs, $expected_output);
		array_push($outputs, $expected_output2);
		array_push($outputs, $expected_output3);
		array_push($outputs, $expected_output4);

		$rmse_tot += $net->test_net($inputs, $outputs);
	}
		$rmse_eq = $rmse_tot/10;
		echo $learning_rate;
		echo ",";
		echo $rmse_eq;
		echo "\n";
		$learning_rate += .02;
	}
}

test_functionality();
