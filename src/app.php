<?php
namespace motto;
defined( '_MOTTO' ) or die( 'Restricted access' );
/**
A $ADT val kicseréli az ADT-t. A $parT-öt recursiven hozzáadja. A $parT['chparT']-ben felsorolt kulcsú 
$parT mezőkkel kicseréli ADT ugyinlyen kulcsú mezőit.Ha '['-jelet használunk evalt használ
PL.$parT['chparT']=['view','[\'parT\'][\'Ico\']']; kicseréli az ADT['view'] és a ADT['parT']['Ico']-t
 továbbá lefuttatja az ApppInit() és az evalSTR-et, valamint a funcT-ben felsorolt funkciókat ha ez nincs akkor 
 a TRT tömb kulcsaiból állítja elő. Ha a funcT-ben(vagy a TRTkulcsaiban) zárójel van akkor evalt használ. 
 */
class App_Base 
{
    public $ADT=[];
  
    public function __construct($parT=[],$ADT=[]){
		 $this->ApppInitBase($parT,$ADT); 
		 $this->ApppInit();
    }
  /**
alapvető funkciok a construktor hívja meg 
   */  
public function ApppInitBase($parT,$ADT){
    	if(!empty($ADT)){$this->ADT=$ADT;}
    	$this->ADT=array_merge_recursive($this->ADT,$parT);
    	//chparT azokat a mezőket tartalmazza amiket nem rekurzíven kell egyesíteni hanem felülírni 
    	if(isset($parT['chparT']))
    	{
    		foreach ($parT['chparT'] as $chpar) {
    			if(substr($chpar, 0,1)=='[')
    			{eval('$this->ADT'.$chpar.'=$parT'.$chpar.';');}
    			else
    			{
    				$this->ADT[$chpar]=$parT[$chpar];
    			}
    		}
    	}
    	$this->ApppInit();
    	if(isset($this->ADT['ADT']['evalSTR'])){eval($this->ADT['ADT']['evalSTR']);}
    	$funcT=$this->ADT['funcT'] ?? [];
    	if(empty($funcT)){$funcT=array_keys($this->ADT['TRT']);}
    	
    	foreach ($funcT as $func)
    	{
    		if(substr($func, 0,6)!='nofunc')
    		{
    			if(substr($func,-1)==')')
    			{ eval('$this->'.$func.';'); }
    			else
    			{$this->$func(); }
    		}
    	}	 
    }
/**
plusz init funkciok, alapesetben üres a gyermek osztály számára van.
 */    
public function ApppInit(){
    	
    }
/**
az app generáló osztály ezt hívja meg
 */    
public function Res(){
	//echo 'res-----------';
    	 return $this->ADT['view'];
    	 
    }
}
//echo '---------';
class App_S
{	
	static  public function recIncTRT($incTRT)
	{
		foreach ($incTRT as $trt) {
			$incTRT[]=$trt;
			$nev=array_pop(explode('\\',$trt));
			$nev.='TRT';
			if(isset($value::$$nev)){	
				
			}
				$incTRT2=self::recIncTRT($value::$$nev);
				$incTRT=array_merge($incTRT,$incTRT2);
			}
		return $incTRT;
	}
/**
ellenőrzi a TRT tömb traitjeinek függőségeit létrehozza az $incTRT tömböt ami alapján le lehet generálni az osztályt
 */
static  public function getIncTRTbase($TRT)
	{
		$incTRT=[];
		foreach ($TRT as $value) {
			$incTRT[]=$value;
			$nevT=explode('\\',$value);
			$nev=array_pop($nevT);
			$nev.='TRT';
			if(isset($value::$$nev)){
				$incTRT2=self::getIncTRT($value::$$nev);
				$incTRT=array_merge($incTRT,$incTRT2);	
		}
		}
	return	$incTRT;
	}
	static  public function getIncTRT($TRT)
	{
		$incTRT=self::getIncTRTbase($TRT);
		foreach ($incTRT as $value) {
			$value=trim($value,'\\');
			$incTRT2[]='\\'.$value;
		}
		$incTRT=$incTRT2 ?? [];
		$incTRT=array_unique($incTRT);
		return	$incTRT;
	}
	
	
	
/**
change 'baseTRT' key of TRT arr, with CONF::$baseTRT[$key]
 */	
static public function changeTRT($TRT){
	
		foreach($TRT as $key=>$nms){
			if($nms=='baseTRT' && isset(CONF::$baseTRT[$key]))
			{$TRT[$key]= CONF::$baseTRT[$key];}
		}
		return $TRT;
	}
/**
return  merged ADT with TSK, ELL. setTRT,changeTRT and make $ADT['incTRT'] (TRT for class generate);
 */	
static 	public function getADT($appname_full)
	{
		$ADT=[];
		if(class_exists($appname_full.'_ADT')){$ADT=array_merge($ADT,get_class_vars($appname_full.'_ADT')); }
	 	if(isset($ADT['ADT'])){$ADT=array_merge($ADT,$ADT['ADT']); unset($ADT['ADT']);}
		
		if(class_exists($appname_full.'_TSK')){$ADT['TSK']=get_class_vars($appname_full.'_TSK'); }
		//if(isset($ADT::$TSK)){$ADT['TSK']=$ADT::$TSK; }
	
		if(class_exists($appname_full.'_ELL')){$ADT['TSK']=get_class_vars($appname_full.'_ELL'); }

		$ADT['TRT']=$ADT['TRT'] ?? [];
		
		$ADT['TRT']=self::changeTRT($ADT['TRT']);
		
		$ADT['incTRT']=self::getIncTRT($ADT['TRT']);

	return $ADT;
	}
	
/**
$class variation eg.(MOtto\app\tabla) : tabla, motto\app\tabla, mottoapp\tabla 
if $class= tabla first it is trying ROOT\app\tabla after MOtto\app\tabla, case insensitiv
 */
    static public function getNamespace_full($classname)
    { 
    	$classname=str_replace('|','\\' ,$classname);
    	$namespace=trim($classname,'\\');
    	if(substr($namespace,0,1)=='*')
    	{
    		$namespace=substr($namespace, 1);
    		
    		$namespace_full='motto\app\\'.$namespace;}
    	else
    	{$namespace_full='root0\app\\'.$namespace;}
    	
    	
    	$t=explode('\\',$namespace_full);
    	$class=array_pop($t);
    	$namespace_full=strtolower($namespace_full).'\\'.ucfirst($class);
    
   //	echo 'nnn'.$namespace_full;
        return $namespace_full;
    }
/**
return a unique name for the eval class generate
 */
    static public function uniqName($appname)
    {
    	$c=true; $i=1;
    	//$classnev='app_'.str_replace('\\','_' , $classname);
    	while ($c==true)
    	{
    		if(!class_exists($appname.$i)){$classnev=$appname.$i;$c=false; }
    		$i++;
    	}
    return 	$classnev;
    }

    static public function Res($namespace,$parT=[]) 
    {   
    	$res='';// echo  	$namespace ;
    	$namespace_full=self::getNamespace_full($namespace);
//echo  	$namespace_full	.'llllll';
    	if(class_exists('\\'.$namespace_full.'_S'))
    	{
//echo  	$namespace_full	.'llllll';
    		eval('$res=\\'.$namespace_full.'_S::Res($parT);');
   
    	}
    	else{
    		$ADT=self::getADT($namespace_full);
 //echo  	$namespace_full.'-------------------------------------------'	;   		
 // print_r($ADT) ; 		
    		$t=explode('\\',$namespace_full);
    		$class=array_pop($t);
    		$classnev=self::uniqName($class);
    		$ADT['uniqName']=$classnev;
    		$classnev=str_replace('\\\\','\\' , $classnev);
//echo $classnev.'::'.$ADT['incTRT'][0].'-----------';
			if(!class_exists($namespace_full)){$namespace_full='\motto\App_Base';}
//echo  	$namespace_full.'kkk'	;		
			$ADT=array_merge_recursive($ADT,$parT);
			if(isset($ADT['incTRT'])){
//echo $classnev;
				eval(\motto\lib\base\Ob_TrtS::str( $classnev,$ADT['incTRT'],$namespace_full));
//echo	\motto\lib\base\Ob_TrtS::str( $classnev,$ADT['incTRT'],$namespace_full)	;		
				$$classnev= new $classnev([],$ADT);
			$res=$$classnev->Res();
			}
    		
    		
    	}
    	return $res;
    } 
}




//$app=new App();
