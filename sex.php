<?php
define('TARGET', 'What\'s in a name? That which we call a rose by any other name would smell as sweet.');
define('MUTATION_CHANCE', 0.1);
define('DOMINANCE_CHANCE', 0.7);
define('POPULATION_SIZE', 50);
define('MAX_AGE', 3);
define('NUM_TO_KILL', 40);

class Organism
{
	private
		$_age = 0,
		$_genes,
		$_fitness;
		
	public function __construct($genes=null)
	{
		$this->_genes = isset($genes) ? $genes : $this->getRandomGenes();
		$this->calculateFitness();
	}

	private function getRandomGenes()
	{
		$return = '';
		for ($i = 0; $i < strLen(TARGET); $i++) {
			$return .= $this->randomGene();
		}
		
		return $return;
	}
	
	private function randomGene()
	{
		static $possibleGenes;
		if (!isset($possibleGenes)){
			$possibleGenes = array_merge(range('A', 'Z'), array(' ', '\'', '?', '.'), range('a', 'z'));
		}
		
		return $possibleGenes[array_rand($possibleGenes)];
	}
	
	private function calculateFitness()
	{
		static $target;
		
		if (!isset($target)) {
			$target = str_split(TARGET);
		}
		
		$genes = str_split($this->getGenes());
		$distance = 0;
		
		for ($i = 0; $i < strLen(TARGET); $i++) {
			if ($target[$i] != $genes[$i]) {
				$distance++;
			}
		}
		
		$this->_fitness = $distance;
	}
	
	public function getFitness()
	{
		return $this->_fitness;
	}
	
	public function getGenes()
	{
		return $this->_genes;
	}
	
	public function getAge()
	{
		return $this->_age;
	}
	
	public function incrementAge()
	{
		$this->_age++;
	}
	
	public function shag(Organism $mate)
	{
		$child  = '';
		$length = strLen(TARGET);
		$target = str_split(TARGET);
		$female = str_split($mate->getGenes());
		$male   = str_split($this->getGenes());
		$mutationChance = intval(1/MUTATION_CHANCE);
		$dominanceChance = intval(1/DOMINANCE_CHANCE);
		
		for ($i = 0; $i < $length; $i++)
		{
			if ($male[$i] == $target[$i] && rand(0,$dominanceChance) == 0) {
				$gene = $male[$i];
			}
			else
			{
				if (rand(0,$mutationChance) == 0){
					$gene = $this->randomGene();
				} else if(rand(0, 10) < 6) {
					$gene = $male[$i];
				} else {
					$gene = $female[$i];
				}
			}
			$child .= $gene;
		}
		
		return new Organism($child);
	}
}

class Population
{
	private
		$_population = array();
		
	public function __construct()
	{
		for ($i = 0; $i < POPULATION_SIZE; $i++) {
			$this->_population[] = new Organism();
		}
	}
	
	private function sortByFitness()
	{
		usort($this->_population, function (Organism $a, Organism $b) {
			if ($a->getFitness() == $b->getFitness()) {
		        return 0;
		    }
		    return ($a->getFitness() > $b->getFitness()) ? -1 : 1;
		});
	}
	
	public function getFittest()
	{
		return array_slice($this->_population, -2);
	}
	
	public function cull()
	{
		foreach ($this->_population as $key => $organism)
		{
			if ($organism->getAge() > MAX_AGE) {
				unset($this->_population[$key]);
			}
		}
		
		for ($i=0; $i < NUM_TO_KILL; $i++) {
			array_shift($this->_population);
		}
	}
	
	public function getPopulationCount()
	{
		return count($this->_population);
	}
	
	public function growPopulation()
	{
		list($male, $female) = $this->getFittest();
		
		while($this->getPopulationCount() < POPULATION_SIZE)
		{
			$child = $male->shag($female);
			$this->addNewChild($child);
		}
		
		$this->sortByFitness();
	}
	
	public function addNewChild(Organism $child)
	{
		$this->_population[] = $child;
	}
	
	public function age()
	{
		foreach ($this->_population as $organism) {
			$organism->incrementAge();
		}
	}
}



$population = new Population(POPULATION_SIZE);
$generation = 0;


do {
	$generation++;
	list($male, $female) = $population->getFittest();

	$population->cull();
	$population->growPopulation();
	$population->age();
  
	if (!($generation % 10))
  	{
  		$fittnesPercent = number_format(((strLen(TARGET) - $male->getFitness()) / strLen(TARGET)) * 100, 2);
		echo "\nGeneration {$generation}\n";
		echo "Elite male:   {$male->getGenes()}\n";
		echo "Elite female: {$female->getGenes()}\n";
		echo "Male Fitness: {$fittnesPercent}%\n";
	}
} while($male->getFitness());

echo "\nMet target at generation {$generation} with:\n";
echo "Elite male:   {$female->getGenes()}\n";
echo "Elite female: {$male->getGenes()}\n";


