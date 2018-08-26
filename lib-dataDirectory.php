<?php



require_once("lib-stockType.php");
require_once("lib-customer.php");
require_once("lib-invoice.php");
require_once("lib-restock.php");
require_once("lib-user.php");




class DataDirectory
{


   public static function createDef()
   {
      return DataDirectory::create('data');
   }


   private $directory;
   private $fileList;
   
   
   public static function create($directory)
   {
      $result = new DataDirectory();      
      $result->directory = S($directory);
      $result->fileList = Lizt::create();
      return $result;
   }
   

   
   private function add($file)
   {
      $this->fileList[] = $file;
      return $file;
   }
   
   public function close()
   {
      foreach ($this->fileList as $file)
         $file->close();
         
      $this->fileList->clear();
      return $this;
   }
   
   
   public function getCustomer($isRW = FALSE)
   {
      return $this->add(CustomerFile::create($this->directory, $isRW));
   }
   
   


   public function getOldInvoice($period, $isRW = FALSE)
   {
      return $this->add(OldInvoiceFile::create($this->directory, $period, $isRW));
   }   
   
   public function getInvoice($period, $isRW = FALSE)
   {
      return $this->add(InvoiceFile::create($this->directory, $period, $isRW));
   }
   
   public function getStockType($isRW = FALSE)
   {
      return $this->add(StockTypeFile::create($this->directory, $isRW));
   }

   public function getRestock($year, $isRW = FALSE)
   {      
      return $this->add(RestockFile::create($this->directory, $year, $isRW));
   }

   public function getUser($isRW = FALSE)
   {
      return $this->add(UserFile::create($this->directory, $isRW));
   }   
}



?>