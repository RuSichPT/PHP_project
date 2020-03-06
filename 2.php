<?php
function convertString($a, $b)
{
	if (substr_count($a,$b)>=2)
	{
		$str_array = explode($b,$a); // разделили на части строку $a делитель $b
		$inv_b = strrev($b); // инверсия строки
		foreach ($str_array as $key => $value)
		{
			if ($key==1)
			{
				$str_end.=$str_array[$key].$inv_b;
			}
			elseif ($key==count($str_array)-1)
			{
				$str_end.=$str_array[$key];
			}
			else
			{
				$str_end.=$str_array[$key].$b;	
			}		
		}				
	}
	else
	{
		$str_end=$a; 
	} 
	return $str_end;	
}
echo convertString("This is a test", "a")
?>
<br/>
<?php
echo convertString("This is a test", "is")
?>
<br/>
<?php
$data[] = array('volume' => 67, 'edition' => 2);
$data[] = array('volume' => 86, 'edition' => 1);
$data[] = array('volume' => 85, 'edition' => 5);

$data1[] = array('volume' => 98, 'edition' => 2);
$data1[] = array('volume' => 86, );
$data1[] = array('volume' => 67, 'edition' => 7);
echo '<pre>';
print_r($data);
print_r($data1);
echo '</pre>';
function mySortForKey($a, $b)
{	
	foreach ($a as $key => $value) // Проверка существования ключа  $b
	{
		if (!array_key_exists($b,$a[$key]))
		{
			throw new Exception("Индекс не правильного массива: ".$key);
		}		
	}
	$ar_tmp=array_column($a,$b);
	array_multisort($ar_tmp,$a);
	return $a;
}
try
{	
	echo '<pre>';
	print_r(mySortForKey($data,'edition'));
	print_r(mySortForKey($data1,'edition'));
	echo '</pre>';
}catch(Exception $e) 
{
	echo $e->getMessage();
}
?>