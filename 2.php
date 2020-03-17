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
echo convertString("This is a test", "a");
echo "<br/>";
echo convertString("This is a test", "is");
echo "<br/>";
//
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
//
$p = "example1.xml";// Файл в той же папки где 2.php
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
			$Продукт[$i]["Цена"][(string)$Цена->attributes()] = (float)$Цена;
		}				
		$Продукт[$i]["Свойства"] = (array)$Товар->Свойства;
		//$Продукт[$i]["Раздел"]=(array)$Товар->Разделы->Раздел;
		$j = count($Товар->Разделы->Раздел)-1;		
		foreach ($Товар->Разделы->Раздел as $раздел)
		{					
			$Продукт[$i]["Раздел"][(string)$раздел]=(int)$раздел->attributes()["Код"];
			if (count($Товар->Разделы->Раздел)>1 )
			{
				$Продукт[$i]["Родитель"][(string)$раздел] = $j--;// Цифра означает поколение 	
			}else
			{
				$Продукт[$i]["Родитель"][(string)$раздел] = 0; 	
			}							
		}		
		$i++;
	}	
	//Проверка атрибутов и полей
	foreach ($Продукт as $key => $value)
	{
		// Если атрибут код отсутствует, то код = 0;
		// Если атрибут Название отсутствует, то название = "";
		// Если атрибут код у категории отсутствует то код =0;				
		if ($value["Цена"] == null)
		{
			echo "Отсутсвует ключевое поле - Цена"; 
			return false;
		}
		if ($value["Свойства"] == null)
		{
			echo "Отсутсвует ключевое поле - Свойства"; 
			return false;
		}
		if ($value["Раздел"] == null)
		{
			echo "Отсутсвует ключевое поле - Раздел"; 
			return false;
		}
	}
	//
	// Таблица Категории	
	$Категории = [];
	$j = 0;	
	foreach ($Продукт as $key => $attribut)
	{		
		arsort($attribut["Раздел"]);// Изменяем порядок на обратный	
		foreach ($attribut["Раздел"] as $key => $value)
		{						
			if (!in_array ($key,$Категории))// Исключаем повторяющиеся категории
			{				
				$Категории[$j] = $key;
				$Код_Категории[$j] = $value;
				$Поколение = $attribut["Родитель"][$key];
				if($Поколение!=0)
				{
					$Родитель[$key] = array_search(0,$attribut["Родитель"]);
				}else
				{
					$Родитель[$key] = array_search($Поколение,$attribut["Родитель"]);;
				}				
					
				$j++;
			}
		}		
	}	
	//var_dump($Код_Категории);
	//var_dump($Категории);
	//var_dump($Родитель);
	foreach ($Категории as $key => $value) // Создаем sql запрос на добавлении Таблицы Категории
	{	
		$Название_кат = "'".$mysqli->real_escape_string($value)."'";
		$sql_proverka = "SELECT * FROM `a_category` WHERE `Name` = $Название_кат;";// SQL запрос на проверку наличия в БД
		$sql_rez_prov = $mysqli->query($sql_proverka);		
		if ($sql_rez_prov->num_rows==0)
		{			
			if($Родитель[$value]==$value)
			{
				$sql_cat = "INSERT INTO `a_category` (`Cod`,`Name`,`id_parent`)
				VALUES ($Код_Категории[$key],$Название_кат,0);";
				$mysqli->query($sql_cat);	
				echo "Категория ".$value." успешно добавлена в БД \n";
			}else
			{				
				$Name_parent = "'".$mysqli->real_escape_string($Родитель[$value])."'";
				$Name_parent;
				$sql_parent = "SELECT `id` FROM `a_category` WHERE `Name` = $Name_parent;";// SQL запрос получения id_parent
				$sql_rez = $mysqli->query($sql_parent);	
				$id_parent = $sql_rez->fetch_assoc();				
				$sql_cat = "INSERT INTO `a_category` (`Cod`,`Name`,`id_parent`)
				VALUES ($Код_Категории[$key],$Название_кат,{$id_parent["id"]});";
				$mysqli->query($sql_cat);			
				echo "Категория ".$value." успешно добавлена в БД \n";
			}
		} else
		{
			echo "Нельзя добавить в БД. Категория - ".$value." существует \n";
		}		
	}	
	$mysqli->close();
	//
	// Все остальные Таблицы
    $mysqli = new mysqli('127.0.0.1', 'root', '', 'test_samson');
	foreach ($Продукт as $key => $attribut)
	{
		$Код = $attribut["Код"];		
		$Название = "'".$mysqli->real_escape_string($attribut["Название"])."'";			
		//Таблица Продукты
		$sql_proverka = "SELECT * FROM `a_product` WHERE `Cod` = $Код;";// SQL запрос на проверку наличия в БД
		$sql_rez_prov = $mysqli->query($sql_proverka);	
		if ($sql_rez_prov->num_rows==0)
		{	
			$sql_prod = "INSERT INTO `a_product` (`Cod`,`Name`) VALUES ($Код, $Название);";
			$mysqli->query($sql_prod);			
		}
		else
		{
			echo "Товар с кодом = ".$Код." в БД существует \n";
		}		
		//
		//Таблица Свойства
		// Формируем текст свойства
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
		$Значение_СВ ="'".$mysqli->real_escape_string($Значение_СВ)."'";		
		// Запрос sql
		$sql_proverka_prop = "SELECT * FROM `a_property` WHERE `Product` = $Название and			  `Property value` =  $Значение_СВ;";// SQL запрос на проверку наличия в БД			
		$sql_rez_prov_prop = $mysqli->query($sql_proverka_prop);		 	
		if ($sql_rez_prov_prop->num_rows==0)
		{	
			$sql_prop .= "INSERT INTO `a_property` (`Product`,`Property value`) VALUES ($Название,$Значение_СВ);";
		}else
		{
			echo "Товар = $Название  с таким свойством = $Значение_СВ в БД существует \n";
		}
		//var_dump($sql_prop);
		//Таблица связи категории с товаром
		foreach ($attribut["Раздел"] as $key => $value)
		{			
			$Раздел = "'".$mysqli->real_escape_string($key)."'";
			$sql_id_cat = "SELECT `id` FROM `a_category` WHERE `Name` = $Раздел;";			
			$sql_rez = $mysqli->query($sql_id_cat);
			$id_cat = $sql_rez->fetch_assoc()["id"];
			$sql_proverka_conect = "SELECT * FROM `a_connection` WHERE `Name` = $Название and			  `id_cat` =  $id_cat;";// SQL запрос на проверку наличия в БД			
			$sql_rez_prov_conect = $mysqli->query($sql_proverka_conect);

			if ($sql_rez_prov_conect->num_rows==0)
			{	
				$sql_connect .= "INSERT INTO `a_connection` (`Name`,`id_cat`) VALUES ($Название,
				$id_cat);";	
			}else
			{
				echo "Товар с таким id = $id_cat в БД существует \n";
			}				

		}		
		//Таблица Цена		
		foreach ($attribut["Цена"]  as $key_at => $Цена)
		{
			// Получаем id товара из БД
			$sql_id_prod = "SELECT `id` FROM `a_product` WHERE `Name` = $Название;";
			$sql_rez = $mysqli->query($sql_id_prod);
			$id_prod = $sql_rez->fetch_assoc()["id"];
			//	
			$Тип_цены ="'".$mysqli->real_escape_string($key_at)."'";
			$sql_proverka_price = "SELECT * FROM `a_price` WHERE `id_product` = $id_prod and 
			`Type price` =  $Тип_цены and `Price` = $Цена;";// SQL запрос на проверку наличия в БД	
			$sql_rez_prov_price = $mysqli->query($sql_proverka_price);
			if ($sql_rez_prov_price->num_rows==0)
			{		
				$sql_price .= "INSERT INTO `a_price` (`id_product`,`Type price`,`Price`) VALUES ($id_prod,$Тип_цены,$Цена);";
			}else
			{
				echo "Товар с таким типом цены = $Тип_цены в БД существует \n";
			}	
		}					
	}	
	var_dump($mysqli->multi_query($sql_prop.$sql_connect.$sql_price));
	$mysqli->close();
}
importXml($p);
?>
<br/>
<?php
$cod_cat = 105;
$p = "D:\Program Files\OSPanel\domains\\test.ru\PHP_project";
function exportXml($a, $b)
{
	$mysqli = new mysqli('127.0.0.1', 'root', '', 'test_samson');
	// Таблица Категорий
	$b = (int)$b;	
	$sql_сat = "SELECT * FROM `a_category` WHERE `Cod` = $b;";
	$sql_rez_cat = $mysqli->query($sql_сat);
	if ($sql_rez_cat->num_rows==0)
	{
		echo "Такой код - $b в БД отсутствует";
		return false;
	}
	$Table_cat[0] = $sql_rez_cat->fetch_assoc();
	$i=0;
	while ($Table_cat[$i]["id_parent"]!=0 )	
	{		
		$sql_сat = "SELECT * FROM `a_category` WHERE `id` = {$Table_cat[$i]["id_parent"]};";
		$sql_rez_cat = $mysqli->query($sql_сat);
		$i++;
		$Table_cat[$i]  = $sql_rez_cat->fetch_assoc();		
	}
	//var_dump($Table_cat);
	// Таблица Связи товара и категории
	$sql_connect = "SELECT * FROM `a_connection` WHERE `id_cat` = {$Table_cat[0]["id"]};";		
	$sql_rez_connect = $mysqli->query($sql_connect);
	$Table_connect = $sql_rez_connect->fetch_all();
	if ($sql_rez_connect->num_rows == 0)
	{
		echo "Экспорт невозможен БД пуста ";
		return false;
	}
	//var_dump($Table_connect);
	// Таблица Продуктов
	foreach ($Table_connect as $key => $conect)
	{
		$Name = "'".$mysqli->real_escape_string($conect[0])."'";
		$sql_prod = "SELECT * FROM `a_product` WHERE `Name` = $Name;";
		$sql_rez_prod = $mysqli->query($sql_prod);
		$Table_prod[$key] = $sql_rez_prod->fetch_assoc();
	}	
	//var_dump($Table_prod);	
	// Таблица Цены
	foreach ($Table_prod as $key => $product)
	{		
		$sql_price = "SELECT * FROM `a_price` WHERE `id_product` = {$product["id"]};";
		$sql_rez_price = $mysqli->query($sql_price);
		$Table_price[$key] = $sql_rez_price->fetch_all();
	}	
	//var_dump($Table_price);
	// Таблица Свойств
	foreach ($Table_prod as $key => $product)
	{
		$Name = "'".$mysqli->real_escape_string($product["Name"])."'";
		$sql_prop = "SELECT * FROM `a_property` WHERE `Product` = $Name;";			
		$sql_rez_prop = $mysqli->query($sql_prop);	
		$Table_prop[$key]  = $sql_rez_prop->fetch_assoc();			
	}
	//var_dump($Table_prop);
	// Создание xml
	$xml = new DomDocument("1.0","utf-8");
	$xml_Товары = $xml->appendChild($xml->createElement("Товары"));
	foreach ($Table_prod as $key => $product)
	{
		$xml_Товар = $xml_Товары->appendChild($xml->createElement("Товар"));
		$xml_Товар->setAttribute("Код",$product["Cod"]);	
		$xml_Товар->setAttribute("Название",$product["Name"]);		
		foreach ($Table_price[$key] as $key_pr => $price)
		{			
			$xml_Цена = $xml_Товар->appendChild($xml->createElement("Цена"));
			$xml_Цена->setAttribute("Тип",$price[1]);
			$xml_Цена->appendChild($xml->createTextNode($price[2]));	
		}		
		$xml_Свойства = $xml_Товар->appendChild($xml->createElement("Свойства"));
		// Заполняем свойства товара
		$str_prop = explode(" ",$Table_prop[$key]["Property value"]);// разбиваем строку свойства на массив
		unset($str_prop[count($str_prop)-1]); //удаляем в конце ''		
		$i=0;$j=1;
		foreach ($str_prop  as $key => $value)
		{				
			if($key == $i*3)
			{
				$xml_Св = $xml_Свойства->appendChild($xml->createElement($value));				
				$i++;
				if ($value=="Белизна")
				{
					$xml_Св->setAttribute("ЕдИзм","%");	
				} 				
			}
			if($key == $j*3-1)
			{				
				$xml_Св->appendChild($xml->createTextNode($value));
				$j++;
			}
		}
		//
		$xml_Разделы = $xml_Товар->appendChild($xml->createElement("Разделы"));
		foreach ($Table_cat as $key => $value)
		{
			$xml_Раздел = $xml_Разделы->appendChild($xml->createElement("Раздел"));
			if ($Table_cat[$key]["Cod"]!=0)
			{
				$xml_Раздел->setAttribute("Код",$Table_cat[$key]["Cod"]);
			}			
			$xml_Раздел->appendChild($xml->createTextNode($Table_cat[$key]["Name"]));
		}		
	}	
	$xml->save($a."\my_xml.xml");
	echo"Экспорт прошел успешно";
}
exportXml($p,$cod_cat );
?>

