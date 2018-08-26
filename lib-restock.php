<?php


require_once('lib-table.php');










class Restock extends TableRow
{

   public static $tableColumnCollection = NULL;

   public function columnCollection()
   {
      if (self::$tableColumnCollection === NULL)
      {
         self::$tableColumnCollection = TableColumnCollection::create
         (                           
            TableColumn::create('I', 'id', 'Int'),
            TableColumn::create('D', 'createDatetime', 'Text'),
            TableColumn::create('M', 'modifyDatetime', 'Text'),
            TableColumn::create('S', 'itemStockTypeList', 'Int*'),
            TableColumn::create('O', 'itemQtyList', 'Int*'),
            TableColumn::create('R', 'remark', 'Text'),

            TableColumn::create('U', 'user', 'Int')
         );
      }   
      
      return self::$tableColumnCollection;
   }


   public static function create()
   {
      $result = new Restock();
      $result->init();
      return $result;
   }
   
   
   public function itemCount()
   {
      return $this->itemStockTypeList->count();
   }
   
   public function item($index)
   {
      return Map::create()
         ->setAt('stockType', $this->itemStockTypeList[$index])
         ->setAt('qty',       $this->itemQtyList[$index]);
   }
   
   public function itemList()
   {
      $result = Lizt::create();
   
      for ($i = 0; $i < $this->itemCount(); $i++)
         $result[] = $this->item($i);
      
      return $result;
   }
   

   
   public static $cmpCreateDatetime = array('Restock', 'cmpCreateDatetime');
   public static function cmpCreateDatetime($x, $y)
   {
      return String::cmp($x->createDatetime, $y->createDatetime);
   }
   
      
}


class RestockFile extends TableFile
{
   private $year;
   public function year() { return $this->year; }
   
   private $nextID = 1;
   private $restockList;

   public function all() { return $this->restockList; }

   



   
   public function findIndexByID($id)
   {
      $r = $this->findIndexByIDOr($id, NULL); if ($r === NULL) XCP(); return $r;
   }

   public function findIndexByIDOr($id, $def)
   {
      foreach ($this->all() as $index => $restock)
         if ($restock->id === $id)
            return $index;
      
      return $def;
   }
   
   
   
   
   public function findByID($id)
   {
      return $this->all()->getAt($this->findIndexByID($id));      return $this;
   }

   public function add($user, $createDatetime)
   {
      $restock = Restock::create();
      $restock->id = $this->nextID++;
      $restock->user = $user;
      $restock->createDatetime = $createDatetime->iso8601();

      $this->restockList[] = $restock;
      return $restock;
   }

   
   
   public function remove($id)
   {
      $this->all()->removeAt($this->findIndexByID($id));
      return $this;
   }
   
   
   

   public static function create($folder, $year, $isRW = false)
   {
      $filePath = pathCombine($folder, SS('restock', SFormatInt($year), '.text'));
   
      $result = new RestockFile();
      $result->year = $year;
      $result->restockList = Lizt::create();

      if ($result->openFile($filePath, $isRW) === FALSE)
         return NULL;

      while(true)
      {
         $line = $result->readLine();
         if ($line === FALSE || $line->len() === 0)
            break;

         if ($line->startWith('#'))
            $result->nextID = $line->back(-1)->parseIntOr(1);
         
         if ($line->startWith('R'))
            $result->restockList[] = Restock::create()->fromLine($line->back(-1));
      }


      $result->restockList->sort(Restock::$cmpCreateDatetime);

      return $result;
   }
   
   public function close()
   {
      if ($this->isRW())
      {
         $this->writeLine(SSUBX('#', SFormatInt($this->nextID)));
         
         foreach ($this->restockList as $restock)
         {
            $line = SUBX($restock->toLine());
            $this->writeLine('R' . "$line");
         }
      }
      
      return $this->closeFile();
   }
   

}









?>