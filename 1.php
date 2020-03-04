<?php
function findSimple($a, $b)
{		
	// Решето эратосфена
	for($i=0; $i<=$b;$i++)
	{
		$tmp[$i]=1;
	}
	for($i=2;$i<=floor(sqrt($b));$i++)
	{
		if($tmp[$i]==1)
		{
			for($j=$i*$i;$j<=$b; $j+=$i )
			{
				$tmp[$j] = 0;
			}			
		}
	}
	//
	$k = 0;
	foreach ($tmp as $key => $value)
	{ 
		if ($value==1 && $key!=0 && $key!=1 && $key>=$a)
		 {
		 	$arr[$k] = $key;
		 	$k++;
		 } 	
	}
	return $arr;  
}
print_r(findSimple(10,25));
?>
<br/>
<?php
function createTrapeze($a)
{	
	$str = "abc";
	for ($i= 0; $i<count($a)/3;$i++)
	{
		for ($j = 3*$i; $j<3*($i+1); $j++)
			{
				$arr[$i][$str[$j%3]]=$a[$j];
			}
	}
	return $arr;	
}
$rez_CT= createTrapeze([3,4,6,15,5,4,11,6,13,5,4,1]);
print_r($rez_CT);
?>
<br/>
<?php
function squareTrapeze($a)
{
	for ($i=0; $i<count($a); $i++)
	{
		$a[$i]["s"] = 1/2*($a[$i]["a"]+$a[$i]["b"])*$a[$i]["c"];
	}
	 return $a;
}
$rez_ST = squareTrapeze($rez_CT);
print_r($rez_ST);
?>
<br/>
<?php
function  getSizeForLimit($a, $b)
{	
	$max = $a[0]["s"];
	$max_array=$a[0];
	foreach ($a as $key => $array)
	{		
		if ($array["s"]<=$b)
		{	
			if ($array["s"]>$max)
			{
				$max =  $array["s"];
				$max_array = $array;
			}		
		}
	}
	if ($max_array["s"]>$b)
	{
		return 0;
	}
	return $max_array;	
}
$rez_gSFL = getSizeForLimit($rez_ST, 34);
echo "Max s: ".$rez_gSFL["s"]." Size: a =".$rez_gSFL["a"]." b =".$rez_gSFL["b"]." 
c =".$rez_gSFL["c"];
?>
<br/>
<?php 
function  getMin($a)
{	
	$min = reset($a);	
	foreach ($a as $key => $value)
	{
		if ($value<$min)
			$min = $value;		
	}
	return $min;	
}
$M = getMin($rez_gSFL);
echo"Min: ".$M;
?>
<br/>
<?php
function printTrapeze($a)
{	 
	echo "<table border=\"1\">";
	echo "<tr>";
	foreach ($a[0] as $key => $value)
	{
		echo "<td>".$key."</td>";
	}
    echo "</tr>";
	foreach ($a as $rows => $value_r)
	{			
		echo "<tr>";		
		foreach($a[0] as $cols => $value_c )
		{
			if ($a[$rows][$cols]%2!=0 && $cols=="s")
			{
				echo "<td><mark>".$a[$rows][$cols]."</mark></td>";
			}else
			{
				echo "<td>".$a[$rows][$cols]."</td>";
			}
		}
		echo "</tr>";	
	}
	echo "</table>";
}
printTrapeze($rez_ST)
?>
<br/>
<?php
 abstract class BaseMath
{
	function exp1($a, $b, $c)
	{
		return $a*($b**$c);
	}
	function exp2($a, $b, $c)
	{
		return ($a/$b)**$c;
	}
	function getValue()
	{
		echo $this->f;
	}
}
class F1 extends BaseMath
{	
	public  $f=0;
	function __construct($a, $b, $c) 
	{
		$rez =parent::exp1($a, $b, $c)+((parent::exp2($a, $b, $c))%3)**min($a, $b, $c);
		$this->f = $rez;		
	}
}
$a = new F1(2,5,4);
$a->getValue();
?>