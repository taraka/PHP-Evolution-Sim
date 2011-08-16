<?php
define('TARGET', 'METHINKS IT IS LIKE A WEASEL');
define('MUTATION_CHANCE', 0.04);
define('POPULATION_SIZE', 10);
define('MAX_AGE', 3);

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
		static $possibleGenes;
		if (!isset($possibleGenes)){
			$possibleGenes = array_merge(range('A', 'Z'), array(' '));
		}
		
		for ($i = 0; $i < strLen(TARGET); $i++)
		{
			$population[$i] .= $possibleGenes[array_rand($possibleGenes)];
		}
	}
	
	private function calculateFitness()
	{
		$this->_fitness = levenshtein($this->_genes, TARGET);
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
}

class Population
{
	private
		$_population = array();
		
	public function __construct($size)
	{
		for ($i = 0; $i < $size; $i++) {
			$this->_population[] = new Organism();
		}
	}
}

function removeFittest(&$population){
  $fittest = '';
  $fittestKey = null;
  foreach ($population as $key => $string){
    if (fitness($string, TARGET) < fitness($fittest, TARGET)){
      $fittest = $string;
      $fittestKey = $key;
    }
  }
  unset($population[$fittestKey]);
  return $fittest;
}

function newPopulation($female, $male, $size){
  $population = array();
  for ($i = 0; $i < $size; $i++){
    $population[] = sex($female, $male, MUTATION_CHANCE);
  }
  return $population;
} 

function sex($female, $male, $mutationChance){
  $child  = '';
  $length = strLen($female);
  $female = str_split($female);
  $male   = str_split($male);
  $mutationChance = intval(1/$mutationChance);

  for ($i = 0; $i < $length; $i++){
    if (rand(0,$mutationChance) == 0){
      $gene = randomGene();
    } else if(rand(0, 1)) {
      $gene = $female[$i];
    } else {
      $gene = $male[$i];
    }
    $child .= $gene;
  }
  return $child;
}

$generation = 0;
do {
  $generation++;
  $eliteFemale = removeFittest($population);
  $eliteMale   = removeFittest($population);
  $population  = newPopulation($eliteFemale, $eliteMale, POPULATION_SIZE);

  if (!($generation % 10)){
    echo "\nGeneration {$generation}\n";
    echo "Elite female: {$eliteFemale}\n";
    echo "Elite male:   {$eliteMale}\n";
  }
} while(fitness($eliteFemale, TARGET));

echo "\nMet target at generation {$generation} with:\n";
echo "Elite female: {$eliteFemale}\n";
echo "Elite male:   {$eliteMale}\n";


