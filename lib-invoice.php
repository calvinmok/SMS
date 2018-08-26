<?php


require_once('lib-table.php');


require_once("lib-stockType.php");
require_once("lib-customer.php");
require_once("lib-restock.php");
require_once("lib-user.php");










function isInvoiceNumber($str)
{
   return S($str)->len() == 11;
}

function parseInvoiceNumber($str)
{
   $result = Map::create();
   $result['period'] = Period::parse($str->front(6));
   $result['seq']    = $str->back(5)->parseInt();
   return $result;
}
function formatInvoiceNumber($period, $seq)
{
   return SS($period->yyyymm(), SFormatInt($seq, 5)); 
}







class OldInvoice extends TableRow
{




   public static $tableColumnCollection = NULL;

   public function columnCollection()
   {
      if (self::$tableColumnCollection === NULL)
      {
         self::$tableColumnCollection = TableColumnCollection::create
         (
            TableColumn::create('N', 'number', 'Text'),
            TableColumn::create('D', 'datetime', 'Text'),
            
            TableColumn::create('T', 'telephone', 'Text'),
            TableColumn::create('C', 'customerName', 'Text'),
            TableColumn::create('A', 'address', 'Text'),
            
            TableColumn::create('S', 'itemStockTypeList', 'Int*'),
            TableColumn::create('O', 'itemOrderedList', 'Int*'),
            TableColumn::create('U', 'itemUndeliveredList', 'Int*'),
            TableColumn::create('P', 'itemPriceList', 'Float*'),
            
            TableColumn::create('H', 'shipping', 'Float'),
            TableColumn::create('F', 'refund', 'Float'),

            TableColumn::create('E', 'paymentType', 'Text'),
            TableColumn::create('L', 'paymentDetail', 'Text'),
            TableColumn::create('R', 'remark', 'Text'),

            TableColumn::create('V', 'user', 'Int')
         );
      }   
      
      return self::$tableColumnCollection;
   }


   public static function create($number = NULL)
   {
      $result = new OldInvoice();
      $result->init();
      
      if ($number !== NULL)
         $result->number = $number;

      return $result;
   }
   
   
   public function itemCount()
   {
      return $this->itemStockTypeList->count();
   }
   
   public function item($index)
   {
      return Map::create()
         ->setAt('stockType',   $this->itemStockTypeList[$index])
         ->setAt('ordered',     $this->itemOrderedList[$index])
         ->setAt('undelivered', $this->itemUndeliveredList[$index])
         ->setAt('price',       $this->itemPriceList[$index]);
   }
   
   public function itemList()
   {
      $result = Lizt::create();
   
      for ($i = 0; $i < $this->itemCount(); $i++)
         $result[] = $this->item($i);
      
      return $result;
   }
   
   
            
   public function isUndelivered()
   {
      for ($i = 0; $i < $this->itemCount(); $i++)
         if ($this->itemUndeliveredList[$i] > 0)
            return TRUE;
         
      return FALSE;
   }
   
   
   
   
   
}







class OldInvoiceFile extends TableFile
{
   private $period;
   public function period() { return $this->period; }
   
   
   private $nextID = 1;
   private $invoiceList;

   public function all() { return $this->invoiceList; }



   public function findByID($id)
   {
      return $this->all()->getAt($this->findIndexByID($id));
   }
   public function findByNumber($number)
   {
      return $this->all()->getAt($this->findIndexByNumber($number));
   }
   public function findByNumberOr($number, $def)
   {
      $index = $this->findIndexByNumberOr($number, NULL);
      if ($index === NULL) return $def;
      return $this->all()->getAt($index);
   }

   public function findAllUndelivered()
   {
      $result = Lizt::create();
      foreach ($this->all() as $index => $invoice)
         if ($invoice->isUndelivered())
            $result[] = $invoice;
      return $result;
   }
   
      
   
   
   

   public static function create($folder, $period)
   {
      $filePath = pathCombine($folder, SS('invoice', $period->yyyymm(), '.old.text'));
   
      $result = new OldInvoiceFile();
      $result->period = $period;
      $result->invoiceList = Lizt::create();

      if ($result->openFile($filePath, FALSE) === FALSE)
         return NULL;

      while(true)
      {
         $line = $result->readLine();
         if ($line === FALSE || $line->len() === 0)
            break;

         if ($line->startWith('#'))
            $result->nextID = $line->back(-1)->parseIntOr(1);
         
         if ($line->startWith('I'))
            $result->invoiceList[] = OldInvoice::create()->fromLine($line->back(-1));
      }
      
      
      return $result;
   }
   
   public function close()
   {

      
      $this->closeFile();
   }
   
   
   
   

}













class Invoice extends TableRow
{




   public static $tableColumnCollection = NULL;

   public function columnCollection()
   {
      if (self::$tableColumnCollection === NULL)
      {
         self::$tableColumnCollection = TableColumnCollection::create
         (
            TableColumn::create('I', 'id', 'Int'),
            TableColumn::create('N', 'number', 'Text'),
            TableColumn::create('B', 'createDatetime', 'Text'),
            TableColumn::create('D', 'modifyDatetime', 'Text'),
            
            TableColumn::create('T', 'telephone', 'Text'),
            TableColumn::create('C', 'customerName', 'Text'),
            TableColumn::create('A', 'address', 'Text'),
            
            TableColumn::create('S', 'itemStockTypeList', 'Int*'),
            TableColumn::create('O', 'itemOrderedList', 'Int*'),
            TableColumn::create('U', 'itemUndeliveredList', 'Int*'),
            TableColumn::create('P', 'itemPriceList', 'Float*'),
            
            TableColumn::create('H', 'shipping', 'Float'),
            TableColumn::create('F', 'refund', 'Float'),

            TableColumn::create('E', 'paymentType', 'Text'),
            TableColumn::create('L', 'paymentDetail', 'Text'),
            TableColumn::create('R', 'remark', 'Text'),

            TableColumn::create('V', 'user', 'Int')
         );
      }   
      
      return self::$tableColumnCollection;
   }


   public static function create($number = NULL)
   {
      $result = new Invoice();
      $result->init();
      
      if ($number !== NULL)
         $result->number = $number;

      return $result;
   }
   
   
   public function itemCount()
   {
      return $this->itemStockTypeList->count();
   }
   
   public function item($index)
   {
      return Map::create()
         ->setAt('stockType',   $this->itemStockTypeList[$index])
         ->setAt('ordered',     $this->itemOrderedList[$index])
         ->setAt('undelivered', $this->itemUndeliveredList[$index])
         ->setAt('price',       $this->itemPriceList[$index]);
   }
   
   public function itemList()
   {
      $result = Lizt::create();
   
      for ($i = 0; $i < $this->itemCount(); $i++)
         $result[] = $this->item($i);
      
      return $result;
   }
   
   
   public function totalAmount()
   {
      $result = $this->shipping - $this->refund;
   
      foreach ($this->itemList() as $item)
         $result += $item->ordered * $item->price;
      
      return $result;
   }
   
            
   public function isUndelivered()
   {
      for ($i = 0; $i < $this->itemCount(); $i++)
         if ($this->itemUndeliveredList[$i] > 0)
            return TRUE;
         
      return FALSE;
   }
   
   
   
   
   
   
   public static function parseYearCode($code)
   {
      return 2000 + S('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')->lenBeforeOr($code, NULL);
   }
   public static function formatYearCode($year)
   {
      return S('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ')->front(1, $year - 2000);
   }
   
   
   
   public static $cmpNumber = array('Invoice', 'cmpNumber');
   public static function cmpNumber($x, $y)
   {
      return String::cmp($x->number, $y->number) * -1;
   }
       
   
   
   
   public static function isNumber($str)
   {
      return ($str->len() == 6);
   }

   public static function parseNumber($str)
   {
      $year = self::parseYearCode($str->front(1));
      $month = $str->front(2, 1)->parseInt();

      $section = 0;
      for (; $month > 12; $month -= 12) $section++;
      
      return Map::create()->setAt('period', Period::create($year, $month))
                          ->setAt('seq',    $str->front(3, 3)->parseInt() + ($section * 1000));
   }

   public static function formatNumber($period, $seq)
   {
      $year = $period->year();
      $month = $period->month();
      for (; $seq > 999; $seq -= 1000) $month += 12;
      return SS(self::formatYearCode($year), SFormatInt($month, 2), SFormatInt($seq, 3)); 
   }
   
   private static function _testInvoiceNumber($number, $year, $month, $seq) 
   {
      $ary = Invoice::parseNumber(S($number));
      if ($ary['period']->ne($year, $month) || $ary['seq'] !== $seq) XCP($ary);
      
      $n = Invoice::formatNumber(PRDC($year, $month), $seq);
      if ($n->ne($number)) XCP($n);
   }
      
   public static function selfTest()
   {
      self::_testInvoiceNumber('305032', 2003, 5,   32);
      self::_testInvoiceNumber('317032', 2003, 5, 1032);
      self::_testInvoiceNumber('C05032', 2012, 5,   32);
      self::_testInvoiceNumber('C17032', 2012, 5, 1032);
   }
   
}



Invoice::selfTest();





class InvoiceFile extends TableFile
{
   private $period;
   public function period() { return $this->period; }
   
   
   private $nextID = 1;
   private $invoiceList;

   public function all() { return $this->invoiceList; }





   public function findIndexByID($id)
   {
      foreach ($this->all() as $index => $invoice)
         if ($invoice->id === $id)
            return $index;
      
      XCP();
   }
   
   public function findIndexByNumber($number)
   {
      $r = $this->findIndexByNumberOr($number, NULL); if ($r === NULL) XCP(); return $r;
   }

   public function findIndexByNumberOr($number, $def)
   {
      foreach ($this->all() as $index => $invoice)
         if ($invoice->number->eq($number))
            return $index;
      
      return $def;
   }


   public function findByID($id)
   {
      return $this->all()->getAt($this->findIndexByID($id));
   }
   public function findByNumber($number)
   {
      return $this->all()->getAt($this->findIndexByNumber($number));
   }
   public function findByNumberOr($number, $def)
   {
      $index = $this->findIndexByNumberOr($number, NULL);
      if ($index === NULL) return $def;
      return $this->all()->getAt($index);
   }

   public function findAllUndelivered()
   {
      $result = Lizt::create();
      foreach ($this->all() as $index => $invoice)
         if ($invoice->isUndelivered())
            $result[] = $invoice;
      return $result;
   }
   
   
   public function add($user, $createDatetime)
   {
      $period = $this->period();
      
      $invoice = Invoice::create();
      $invoice->id = $this->nextID++;
      $invoice->user = $user;
      $invoice->createDatetime = $createDatetime->iso8601();
      
      $invoice->number = Invoice::formatNumber($period, $invoice->id);
      for ($i = 1; $i <= $invoice->id; $i++)
      {
         $number = Invoice::formatNumber($period, $i);
         if ($this->findByNumberOr($number, NULL) == NULL)
         {
            $invoice->number = $number;
            break;
         }
      }

      $this->invoiceList[] = $invoice;      
      return $invoice;
   }
   
   public function addOld($oldInvoice)
   {
      $period = $this->period();
         
      $invoice = $this->add($oldInvoice->user, Datetine::parse($oldInvoice->datetime));
      $invoice->id             = parseInvoiceNumber($oldInvoice->number)->getAt('seq');
      $invoice->number         = Invoice::formatNumber($period, $invoice->id);
      $invoice->createDatetime = $oldInvoice->datetime;
      $invoice->modifyDatetime = $oldInvoice->datetime;
      
      $invoice->telephone      = $oldInvoice->telephone;
      $invoice->customerName   = $oldInvoice->customerName;
      $invoice->address        = $oldInvoice->address;
      
      $invoice->itemStockTypeList   = $oldInvoice->itemStockTypeList;
      $invoice->itemOrderedList     = $oldInvoice->itemOrderedList;
      $invoice->itemUndeliveredList = $oldInvoice->itemUndeliveredList;
      $invoice->itemPriceList       = $oldInvoice->itemPriceList;
      
      $invoice->shipping      = $oldInvoice->shipping;
      $invoice->refund        = $oldInvoice->refund;
      $invoice->paymentType   = $oldInvoice->paymentType;
      $invoice->paymentDetail = $oldInvoice->paymentDetail;
      $invoice->remark        = $oldInvoice->remark;      

      return $this;
   }
   
   public function addOldFile($file)
   {
      foreach ($file->all() as $invoice)
         $this->addOld($invoice);
   }
   
   
   
   public function remove($id)
   {
      $this->invoiceList->removeAt($this->findIndexByID($id));
      return $this;
   }
   
   public function clear()
   {
      $this->invoiceList->clear();
      return $this;
   }
   
   
   

   public static function create($folder, $period, $isRW = false)
   {
      $filePath = pathCombine($folder, SS('invoice', $period->yyyymm(), '.text'));
   
      $result = new InvoiceFile();
      $result->period = $period;
      $result->invoiceList = Lizt::create();

      if ($result->openFile($filePath, $isRW) === FALSE)
         return NULL;

      while(true)
      {
         $line = $result->readLine();
         if ($line === FALSE || $line->len() === 0)
            break;

         if ($line->startWith('#'))
            $result->nextID = $line->back(-1)->parseIntOr(1);
         
         if ($line->startWith('I'))
            $result->invoiceList[] = Invoice::create()->fromLine($line->back(-1));
      }
      

      $result->invoiceList->sort(Invoice::$cmpNumber);
            
      
      return $result;
   }
   
   public function close()
   {
      if ($this->isRW())
      {
         $this->writeLine('#' . "$this->nextID");
         
         foreach ($this->invoiceList as $invoice)
         {
            $line = $invoice->toLine();
            $this->writeLine(SSUBX('I', $line));
         }
      }
      
      return $this->closeFile();
   }
   
   
   
   

}









?>