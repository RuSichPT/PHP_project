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