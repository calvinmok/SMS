<?php


require_once('lib-utility.php');



abstract class TableFile implements JsonItem
{
   

   private $isRW;
   public function isRW() { return $this->isRW; }
   
   private $isWriteBegan = FALSE;
   public function isWriteBegan() { return $this->isWriteBegan; }
   
   private $filePath;
   private $fileHandle = NULL;


   public abstract function all();
   

   protected function openFile($filePath, $isRW = false)
   {
      if (touch(SUBX($filePath)) === FALSE)
         return FALSE;
   
      $this->isRW = $isRW;
      $this->filePath = S($filePath);
      
      $this->fileHandle = fopen(SUBX($filePath), ($isRW ? 'r+' : 'r'));
      if ($this->fileHandle === FALSE)
      {
         $this->fileHandle = NULL;
         return FALSE;
      }
      
      if (flock($this->fileHandle, ($isRW ? LOCK_EX : LOCK_SH)) === FALSE)
      {
         fclose($this->fileHandle);
         $this->fileHandle = NULL;
         return FALSE;
      }
         
      return TRUE;
   }
   

   public function __destruct() 
   {
      $this->closeWithoutSave();
   }
   protected function closeFile()
   {
      if ($this->fileHandle !== NULL)
      {
         flock($this->fileHandle, LOCK_UN);
         fclose($this->fileHandle);
         
         $this->fileHandle = NULL;
      }
      
      return $this;
   }
   public function closeWithoutSave()
   {
      return $this->closeFile();
   }
   
   
   
   
   

   public function readLine()
   {
      if ($this->fileHandle === NULL)
         return FALSE;
         
      if ($this->isWriteBegan)
         return NULL;
         
      $result = fgets($this->fileHandle);      
      return ($result === FALSE) ? FALSE : S($result)->remove("\n", "\r");
   }
   
   public function writeLine($line)
   {
      if ($this->fileHandle === NULL)
         return;

      if ($this->isWriteBegan === FALSE)
      {
         ftruncate($this->fileHandle, 0);
         fseek($this->fileHandle, 0);
         
         $this->isWriteBegan = TRUE;
      }   

      fwrite($this->fileHandle, SUBX($line));
      fwrite($this->fileHandle, "\n");
   }
   
   
   

   public function toJSON()
   {
      return $this->all()->toJSON();
   }   

      
  
}





class TableColumnType
{
   private $value;
   
   public function __v() { return S($this->value); }
   
   public function isString() { return $this->value == 'T'; }
   public function isInt() { return $this->value == 'I'; }
   public function isFloat() { return $this->value == 'F'; }
      
   
   public static function create($value)
   {
      $result = new TableColumnType();
      $result->value = $value;
      return $result;
   }
   

   public function defaultValue()
   {
      if ($this->isString()) return '';
      if ($this->isInt()) return 0;
      if ($this->isFloat()) return 0.0;
      return NULL;  
   }
   
   public function coerce($value)
   {
      if ($this->isString())
      {
         $result = S($value)->remove("\t", "\n", "\r");
         return $result;
      }

      if ($this->isInt())
      {
         if (is_int($value)) return $value;
         return S($value)->parseIntOr(0);
      }
      
      if ($this->isFloat())
      {
         if (is_float($value)) return $value;
         if (is_int($value)) return floatval($value);
         return S($value)->parseFloatOr(0.0);
      }
      
      XCP();
   }
   
   public function toString($value)
   {
      if ($this->isString()) return $value;
      if ($this->isInt())    return SFormatInt($value);
      if ($this->isFloat())    return SFormatFloat($value, 2);
   }
   
   
}




class TableColumn
{
   private $code;   
   private $name;
   private $isMulti;
   private $type;
   
   public function code() { return $this->code; }
   public function name() { return $this->name; }
   public function isMulti() { return $this->isMulti; }
   public function type() { return $this->type; }
   
   public static function create($code, $name, $typeAndIsMulti)
   {
      $typeAndIsMulti = S($typeAndIsMulti);
   
      $result = new TableColumn();
      $result->code = S($code);
      $result->name = S($name);
      $result->isMulti = $typeAndIsMulti->endWith('*');
      
      if ($typeAndIsMulti->startWith('Text')) $result->type = TableColumnType::create('T');
      if ($typeAndIsMulti->startWith('Int'))  $result->type = TableColumnType::create('I');
      if ($typeAndIsMulti->startWith('Float'))  $result->type = TableColumnType::create('F');
      return $result;
   }



   public function defaultValue()
   {
      if ($this->isMulti())
         return Lizt::box();
      else 
         return $this->type()->defaultValue();
   }
   

}

class TableColumnCollection
{
   private $columnList;
   private $columnMap;
   
   
   public static function create()
   {
      $result = new TableColumnCollection();
      $result->columnList = Lizt::create();
      
      foreach (func_get_args() as $value)
         $result->columnList->append($value);
      
      for ($i = 0; $i < $result->columnList->count(); $i++)
      {
         if ($result->columnList[$i]->code()->len() !== 1)
            return NULL;

         for ($j = $i + 1; $j < $result->columnList->count(); $j++)
            if ($result->columnList[$i]->code() === $result->columnList[$j]->code())
               return NULL;
      }
      
      $result->columnMap = Map::create();
      foreach ($result->columnList as $column)
         $result->columnMap[$column->name()] = $column;

      return $result;
   }
   
   public function lizt()
   {
      return $this->columnList;
   }

   public function getByName($name)
   {
      return $this->columnMap->getAt($name);
   }
   
   
}




abstract class TableRow implements JsonItem
{


   public abstract function columnCollection();
   
   public function getColumn($name) { return $this->columnCollection()->getByName($name); }
   public function columnList() { return $this->columnCollection()->lizt(); }
   

   
   private $valueMap;
   public function valueMap() { return $this->valueMap; }



   protected function init()
   {
      $this->valueMap = Map::create();
      foreach ($this->columnList() as $column)
         $this->valueMap[$column->name()] = $column->defaultValue();
         
      return $this;
   }
   

   public function get($name)
   {
      $column = $this->getColumn($name);
      return $this->valueMap[$column->name()];
   }
   public function __get($name)
   {
      return $this->get($name);
   }
   
   
   public function set($name, $value)
   {
      $column = $this->getColumn($name);
      if ($column->isMulti())
      {
         $this->valueMap[$name] = $column->defaultValue();
         foreach ($value as $v)
            $this->valueMap[$name][NULL] = $column->type()->coerce($v);
      }
      else
      {
         $this->valueMap[$name] = $column->type()->coerce($value);
      }
      
      return $this;
   }
   public function __set($name, $value)
   {
      return $this->set($name, $value);
   }

   public function clear($name)
   {
      $column = $this->getColumn($name);
      $this->valueMap[$name] = ($column->isMulti()) ? array() : $column->defaultValue();
   }
      
   
   
   public function addAt($name, $value)
   {
      return $this->setAt($name, NULL, $value);
   }
   
   public function setAt($name, $index, $value)
   {
      $column = $this->getColumn($name);
      if ($column->isMulti() === FALSE)
         throw new Exception();
         
      $this->valueMap[$name][$index] = $column->type()->coerce($value);
      return $this;
   }
   
   public function increaseAt($name, $index, $value)
   {
      $column = $this->getColumn($name);
      if ($column->type()->isInt() === FALSE)
         throw new Exception();
      
      if ($index !== NULL)
         $this->valueMap[$name][$index] += $value;
      else
         $this->valueMap[$name][] = $value;
         
      return $this;
   }
   
   
   public function setAsCurrentDatetime($name)
   {
      return $this->set($name, date('Y-m-d H:i:s'));
   }
   
   
   
   
   public function toLine()
   {
      $result = '';
      
      foreach ($this->columnList() as $column)
      {
         $code = $column->code();
         $value = $this->valueMap[$column->name()];
         
         if ($column->isMulti())
         {
            foreach ($value as $item)
               $result .= SSUBX($code, $column->type()->toString($item), "\t");
         }
         else
         {
            $result .= SSUBX($code, $column->type()->toString($value), "\t");
         }
      }

      return S($result);
   }
   
   public function fromLine($line)
   {
      foreach (S($line)->explode("\t") as $item)
      {
         foreach ($this->columnList() as $column)
         {
            if ($item->startWith($column->code()) === FALSE)
               continue;
            
            $value = $item->back(-$column->code()->len());
            $value = $column->type()->coerce($value);
            
            if ($column->isMulti())
               $this->valueMap[$column->name()][] = $value;
            else
               $this->valueMap[$column->name()] = $value;
         }
      }
      
      return $this;
   }
   
   
   public function fromValueMap($map)
   {
      foreach ($this->columnList() as $column)
      {
         $name = $column->name();
         
         if ($map->isKey($name) === FALSE)
            continue;
         
         if ($column->isMulti())
         {
            if ($map[$name] instanceof Lizt === FALSE)
               continue;
         
            $this->valueMap[$name] = Lizt::box();
            foreach ($map[$name] as $n => $v)
               $this->valueMap[$name][$n] = $column->type()->coerce($v);
         }
         else
         {
            $this->valueMap[$name] = $column->type()->coerce($map[$name]);
         }
      }
      
      return $this;      
   }
   
   public function toValueMap()
   {
      return $this->valueMap->clome();
   }
   
   
   public function toJSON()
   {
      return $this->valueMap->toJSON();
   }
   
   
   
}




?>