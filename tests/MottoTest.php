<?php
//namespace motto\tests;
require_once '../src/motto.php';
class ADT{
	static public $TRT=['proba1'=>'proba1','proba2'=>'proba2',];
}
trait proba1{
	
	public function ptoba1(){}
}
trait proba2{

	public function ptoba2(){}
}
class MottoTest extends PHPUnit_Framework_TestCase
{
 
  


    public function testTrait()
    {
    	$base=new motto\Motto();
    	$obj=$base->resOb();
        $this->assertEquals(method_exists($obj,'proba1'), true);
    }
}
