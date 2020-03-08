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
<br/>
<?php
$p = "example.xml";// Файл в той же папки где 2.php
function importXml($a)
{	
	if (!file_exists($a))
	{
		echo " файл: $a не найден ";
		return false;
	}
	$xml = simplexml_load_file($a); // Получаем объект XML			
	$mysqli = new mysqli('127.0.0.1', 'root', '', 'test_samson');// Создаем объект MySQL
	// Считываем данные из XML в массив $Продукт
	$Продукт = [];
	$i = 0;
	foreach ($xml as $Товар)
	{
		$Продукт[$i]["Код"] = (int)$Товар->attributes()["Код"];
		$Продукт[$i]["Название"] = (string)$Товар->attributes()["Название"];
		foreach ($Товар->Цена as $Цена)
		{			
			$Продукт[$i]["Цена"][(string)$Цена->attributes()] = (int)$Цена;
		}				
		$Продукт[$i]["Свойства"] = (array)$Товар->Свойства;
		$Продукт[$i]["Раздел"]=(array)$Товар->Разделы->Раздел;
		$i++;
	}
	print_r ($Продукт);
	//
	// Таблица Категории
	$Категории = [];
	$j = 0;
	foreach ($Продукт as $key => $attribut)
	{	
		for ($i=0; $i < count($attribut["Раздел"]); $i++)
		{ 			
			if (!in_array ($attribut["Раздел"][$i],$Категории))// Исключаем повторяющиеся категории
			{				
				$Категории[$j] = $attribut["Раздел"][$i];
				$j++;
			}
		}		
	}	
	foreach ($Категории as $value) // Создаем sql запрос на добавлении Таблицы Категории
	{		
		$Название_кат ="'".$value."'";
		$sql_proverka = "SELECT * FROM `a_category` WHERE `Название` = $Название_кат;";// SQL запрос на проверку наличия в БД
		$sql_rez_prov = $mysqli->query($sql_proverka);		
		if ($sql_rez_prov->num_rows==0)
		{
			$sql_cat .= "INSERT INTO `a_category` (`Название`,`id_parent`)
			VALUES ($Название_кат,0);";
			echo "Категория ".$value." успешно добавлена в БД \n";
		} else
		{
			echo "Нельзя добавить в БД. Категория - ".$value." существует \n";
		}		
	}	
	if(isset ($sql_cat))
	{
		$mysqli->multi_query($sql_cat);// Отправка sql запроса			
	}	
	$mysqli->close();
	//
	// Все остальные Таблицы
    $mysqli = new mysqli('127.0.0.1', 'root', '', 'test_samson');	
	foreach ($Продукт as $key => $attribut)
	{
		$Код = $attribut["Код"];		
		$Название = "'".$attribut["Название"]."'";
		$Раздел = "'".$attribut["Раздел"][0]."'";		
		//Таблица Продукты
		$sql_proverka = "SELECT * FROM `a_product` WHERE `Код` = $Код;";// SQL запрос на проверку наличия в БД
		$sql_rez_prov = $mysqli->query($sql_proverka);	
		if ($sql_rez_prov->num_rows==0)
		{	
			$sql_prod .= "INSERT INTO `a_product` (`Код`,`Название`) VALUES ($Код, $Название);";
		}
		else
		{
			echo "Товар с кодом = ".$Код." в БД существует \n";
		}
		//
		//Таблица Свойства
		$Значение_СВ = "";
		foreach ($attribut["Свойства"] as $key_at=> $value)
		{					
			if (count($value)>1)
			{
				for ($i=0; $i < count($value); $i++)
					{ 
						$Значение_СВ .= $key_at." = ".$value[$i]." ";							
					}
			}else
			{
				$Значение_СВ .= $key_at." = ".$value." ";				
			}			
		}
		$Значение_СВ = "'".$Значение_СВ."'";
		$sql_id_cat = "SELECT `id` FROM `a_category` WHERE `Название` = $Раздел;";			
		$sql_rez = $mysqli->query($sql_id_cat);		
		$id_cat = $sql_rez->fetch_assoc()["id"];		
		$sql_prop .= "INSERT INTO `a_property` (`Товар`,`Значение свойства`,`id_cat`) VALUES ($Название,$Значение_СВ,$id_cat);";
		//Таблица Цена
		foreach ($attribut["Цена"]  as $key_at => $Цена)
		{
			$Тип_цены ="'".$key_at."'";					
			$sql_price .= "INSERT INTO `a_price` (`Связь товар`,`Тип цены`,`Цена`) VALUES ($Название,$Тип_цены,$Цена);";	
		}					
	}	
	var_dump($mysqli->multi_query($sql_prod.$sql_prop.$sql_price));
}
importXml($p);
?>
