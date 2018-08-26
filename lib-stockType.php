<?php




require_once('lib-table.php');


require_once('lib-invoice.php');
require_once('lib-restock.php');



 

class StockType extends TableRow
{
   public static $tableColumnCollection = NULL;

   public function columnCollection()
   {
      if (self::$tableColumnCollection === NULL)
      {
         self::$tableColumnCollection = TableColumnCollection::create
         (
            TableColumn::create('I', 'id', 'Int'),
            TableColumn::create('N', 'name', 'Text'),
            TableColumn::create('D', 'defPrice', 'Float'),
            TableColumn::create('Q', 'currentQty', 'Int'),

            TableColumn::create('P', 'ioPeriodList', 'Text*'),
            TableColumn::create('A', 'invoiceInList', 'Int*'),
            TableColumn::create('B', 'invoiceOutList', 'Int*'),
            TableColumn::create('X', 'restockInList', 'Int*'),
            TableColumn::create('Y', 'restockOutList', 'Int*')
         );
      }   
      
      return self::$tableColumnCollection;
   }


   public static function create($id = NULL)
   {
      $result = new StockType();
      $result->init();
            
      if ($id !== NULL)
         $result->id = $id;

      return $result;
   }
   
   public function getMapWithoutIO($withCurrentQty)
   {
      $result = Map::create();
      $result['id'] = $this->id;
      $result['name'] = $this->name;
      $result['defPrice'] = $this->defPrice;
      
      if ($withCurrentQty)
         $result['currentQty'] = $this->currentQty;
      
      return $result;
   }
   
   
   public function findIndexOfIOPeriodOrAdd($period)
   {
      $yyyymm = $period->yyyymm();
      
      for ($i = $this->ioPeriodList->count() - 1; $i >= 0; $i--)
         if ($this->ioPeriodList[$i]->eq($yyyymm))
            return $i;
   
      $this->setAt('ioPeriodList', NULL, $yyyymm);
      $this->setAt('invoiceInList', NULL, 0);
      $this->setAt('invoiceOutList', NULL, 0);
      $this->setAt('restockInList', NULL, 0);
      $this->setAt('restockOutList', NULL, 0);

      return $this->ioPeriodList->lastIndex();
   }
   
         
         
      

      
   public function resetInvoiceInOut($period)
   {
      $index = $this->findIndexOfIOPeriodOrAdd($period);
      $this->setAt('invoiceInList', $index, 0);
      $this->setAt('invoiceOutList', $index, 0);
   }   
   public function increaseInvoiceInOut($period, $value)
   {
      if ($value === 0)
         return;
         
      $index = $this->findIndexOfIOPeriodOrAdd($period);      
      $this->increaseAt(($value > 0) ? 'invoiceInList' : 'invoiceOutList', $index, abs($value));
   }
               
      
   public function resetRestockInOut($year)
   {
      for ($month = 1; $month <= 12; $month++)
      {
         $period = Period::create($year, $month);
         $index = $this->findIndexOfIOPeriodOrAdd($period);
         $this->setAt('restockInList', $index, 0);
         $this->setAt('restockOutList', $index, 0);
      }
   }   
   public function increaseRestockInOut($period, $value)
   {
      if ($value === 0)
         return;
         
      $index = $this->findIndexOfIOPeriodOrAdd($period);
      $this->increaseAt(($value > 0) ? 'restockInList' : 'restockOutList', $index, abs($value));
   }

   
   
   
   
   public function calculateQty()
   {
      $qty = 0;

      for ($i = 0; $i < $this->ioPeriodList->count(); $i++)
      {
         $qty += $this->invoiceInList[$i] - $this->invoiceOutList[$i];
         $qty += $this->restockInList[$i] - $this->restockOutList[$i];
      }

      $this->currentQty = $qty;
   }
   
   
   public function isAllInOutZero()
   {
      for ($i = 0; $i < $this->ioPeriodList->count(); $i++)
      {
         if ($this->invoiceInList[$i] != 0) return FALSE;
         if ($this->invoiceOutList[$i] != 0) return FALSE;
         if ($this->restockInList[$i] != 0) return FALSE;
         if ($this->restockOutList[$i] != 0) return FALSE;
      }
      
      return TRUE;
   }
   
   

   
   public static $cmpName = array('StockType', 'cmpName');
   public static function cmpName($x, $y)
   {
      return String::cmp($x->name, $y->name);
   }

   
   
}














class StockTypeFile extends TableFile
{
   private $nextID = 1;
   private $stockTypeList;
   
   public function all() { return $this->stockTypeList; }
   
   
   public function getAllMapWithoutIO($withCurrentQty)
   {
      $result = Lizt::create();
      foreach ($this->all() as $stockType)
         $result[] = $stockType->getMapWithoutIO($withCurrentQty);
      return $result;
   }
   
   
   public function add($name = '', $defPrice = 0, $currentQty = 0)
   {
      $stockType = StockType::create($this->nextID++);
      $stockType->name = S($name);
      $stockType->defPrice = $defPrice;
      $stockType->currentQty = $currentQty;

      $this->stockTypeList[] = $stockType;
      return $stockType;
   }
   
   
   



   
   public function findIndexByID($id)
   {
      $r = $this->findIndexByIDOr($id, NULL); if ($r === NULL) XCP(); return $r;
   }

   public function findIndexByIDOr($id, $def)
   {
      foreach ($this->all() as $index => $stockType)
         if ($stockType->id === $id)
            return $index;
      
      return $def;
   }
   
   
      

   public function findByID($id)
   {
      return $this->all()->getAt($this->findIndexByID($id));
   }
   
   public function remove($id)
   {
      $this->all()->removeAt($this->findIndexByID($id));
      return $this;
   }
   
      
   
   
   public function updateRestockInOut($restockFile)
   {
      foreach ($this->all() as $stockType)
         $stockType->resetRestockInOut($restockFile->year());

      foreach ($restockFile->all() as $restock)
      {
         $period = Datetine::parse($restock->createDatetime)->period();

         foreach ($restock->itemList() as $item)
         {
            $stockType = $this->findByID($item['stockType'], NULL);
            if ($stockType !== NULL)
               $stockType->increaseRestockInOut($period, $item['qty']);
         }
      }

      foreach ($this->all() as $stockType)
         $stockType->calculateQty();
   }
   
   
   public function updateInvoiceInOut($invoiceFile)
   {
      foreach ($this->stockTypeList as $stockType)
         $stockType->resetInvoiceInOut($invoiceFile->period());
      
      foreach ($invoiceFile->all() as $invoice)
      {
         foreach ($invoice->itemList() as $item)
         {
            $stockType = $this->findByID($item['stockType'], NULL);
            if ($stockType !== NULL)
               $stockType->increaseInvoiceInOut($invoiceFile->period(), -($item['ordered'] - $item['undelivered']));
         }
      }

      foreach ($this->all() as $stockType)
         $stockType->calculateQty();   
   }
   
   
   public static function create($directory, $isRW = false)
   {
      $filePath = pathCombine($directory, 'stockType.text');
   
      $result = new StockTypeFile();
      $result->stockTypeList = Lizt::create();

      if ($result->openFile($filePath, $isRW) === FALSE)
         return NULL;

      while(true)
      {
         $line = $result->readLine();
         if ($line === FALSE || $line->len() == 0)
            break;

         if ($line->startWith('#'))
         {
            $result->nextID = $line->back(-1)->parseIntOr(1);
         }
         
         if ($line->startWith('S'))
         {
            $stockType = StockType::create();
            $stockType->fromLine($line->back(-1));
            $result->stockTypeList[] = $stockType;
         }
      }

      $result->stockTypeList->sort(StockType::$cmpName);
       
      return $result;
   }
   
   public function close()
   {
      if ($this->isRW())
      {
         $this->writeLine('#' . "$this->nextID");
         
         foreach ($this->stockTypeList as $stockType)
         {
            $line = SUBX($stockType->toLine());
            $this->writeLine('S' . "$line");
         }
      }
      
      $this->closeFile();
   }
   
   

}








?>