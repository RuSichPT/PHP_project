<?php
namespace Test3;

class newBase
{
    static private $count = 0;
    static private $arSetName = [];    
    /**
     * @param string $name
     */
    function __construct(int $name = 0)    
    {
        if (empty($name))// 0-если существует
        {
            //while (array_search(self::$count, self::$arSetName) != false)// ключ или false
            while (array_search(self::$count, self::$arSetName) !== false)// ключ или false
            {
                ++self::$count;
            }
            $name = self::$count;            
        }
        $this->name = $name;
        //self::$arSetName[] = $this->name;
        if (array_search($name, self::$arSetName)===false)
        {
            self::$arSetName[] = $this->name;
        }       
    }
    private $name;
    /**
     * @return string
     */
    public function getName(): string
    {
        return '*' . $this->name  . '*';
    }
    protected $value;
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this; // Добавил         
    }
    /**
     * @return string
     */
    public function getSize()
    {

        $size = strlen(serialize($this->value));
        //return strlen($size) + $size;
        return $size;
    }
    public function __sleep()
    {
        return ['value'];       
    }
    /**
     * @return string
     */
    public function getSave(): string
    {        
        //$value = serialize($value); 
        //return $this->name . ':' . sizeof($value) . ':' . $value;
        $value = serialize($this->value);
        return $this->name . ':' . strlen($value) . ':' . $value;
    }
    /**
     * @return newBase
     */
    //static public function load(string $value): newBase
    static public function load(string $value)
    {
        $arValue = explode(':', $value);
        //return (new newBase($arValue[0]))
            //->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                //+ strlen($arValue[1]) + 1), $arValue[1]));
        return (new newBase($arValue[0]))
            ->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1), [ "allowed_classes" => (bool)$arValue[1]]));
    }
}
class newView extends newBase
{
    private $type = null;
    private $size = 0;
    private $property = null;
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        parent::setValue($value);
        $this->setType();
        $this->setSize();
        return $this;// Добавил
    }
    public function setProperty($value)
    {
        $this->property = $value;
        return $this; 
    }
    private function setType()
    {
        $this->type = gettype($this->value);
    }
    private function setSize()
    {
        //if (is_subclass_of($this->value, "Test3\newView")) {
        if (is_subclass_of($this->value, "Test3\\newView")) { // true если ребенок
            $this->size = parent::getSize() + 1 + strlen($this->property);
        } elseif ($this->type == 'test') {
            $this->size = parent::getSize();
        } else {
            $this->size = strlen($this->value);
        }
    }
    /**
     * @return string
     */
    public function __sleep()
    {             
        return array_merge(parent::__sleep(),['property']);
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        //if (empty($this->name)) {
        if (empty(parent::getName())) {
            //throw new Exception('The object doesn\'t have name');
            throw new \Exception('The object doesn\'t have name');
        }
        //return '"' . $this->name  . '": ';
        return '"' . parent::getName(). '": ';
    }
    /**
     * @return string
     */
    public function getType(): string
    {
        return ' type ' . $this->type  . ';';
    }
    /**
     * @return string
     */
    public function getSize(): string
    {
        return ' size ' . $this->size . ';';
    }
    public function getInfo()
    {
        try {
            echo $this->getName()
                . $this->getType()
                . $this->getSize()
                . "\r\n";
        //} catch (Exception $exc) {
        } catch (\Exception $exc) {
            echo 'Error: ' . $exc->getMessage();
        }
    }
    /**
     * @return string
     */
    public function getSave(): string
    {
        if ($this->type == 'test') {
            //$this->value = $this->value->getSave(); 
            $value = $this->value->getSave();  //??? зачем тогда это. так и не понял          
        }  
        return parent::getSave() . serialize($this->property);
    }
    /**
     * @return newView
     */
    //static public function load(string $value): newBase
    static public function load(string $value): newView
    {    
        $arValue = explode(':', $value);
        //return (new newBase($arValue[0]))
            //->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                // + strlen($arValue[1]) + 1), $arValue[1]))
        return (new newView($arValue[0]))
            ->setValue(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1),[ "allowed_classes" => (bool)$arValue[1]]))
            ->setProperty(unserialize(substr($value, strlen($arValue[0]) + 1
                + strlen($arValue[1]) + 1 + $arValue[1])));
    }
}
function gettype($value): string
{
    if (is_object($value))
    {
        $type = get_class($value);
        do {
            //if (strpos($type, "Test3\newBase") !== false)
            if (strpos($type, "Test3\\newBase") !== false) //false или номер
            {
                return 'test';
            }
        } while ($type = get_parent_class($type));// false если нет родителя
    }
    //return gettype($value); 
    return ""; 
}

$obj = new newBase('12345');
$obj->setValue('text');

//$obj2 = new \Test3\newView('O9876');
$obj2 = new \Test3\newView('09876');
$obj2->setValue($obj);
$obj2->setProperty('field');
$obj2->getInfo();

$save = $obj2->getSave();

$obj3 = newView::load($save);

var_dump($obj2->getSave() == $obj3->getSave());

