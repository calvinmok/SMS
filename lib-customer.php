<?php




require_once('lib-invoice.php');



 

class Customer extends TableRow
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
            TableColumn::create('T', 'telephone', 'Text'),
            TableColumn::create('A', 'addressList', 'Text*'),
            TableColumn::create('L', 'lastDatetime', 'Text')
         );
      }   
      
      return self::$tableColumnCollection;
   }


   public static function create($id = NULL)
   {
      $result = new Customer();
      $result->init();
      
      if ($id !== NULL)
         $result->id = $id;

      return $result;
   }
   

   public function setByInvoice($invoice)
   {
      $this->telephone = $invoice->telephone;
      $this->name = $invoice->customerName;
      $this->addAddressIfNew($invoice->address);
      $this->updateLastDatetime();
   }
   
   
   public function addAddressIfNew($value)
   {
      if ($value->len() === 0)
         return;
   
      foreach ($this->addressList as $address)
         if ($value->eq($address))
            return;
      
      $this->addressList->append($value);
   }
   
   public function updateLastDateTime()
   {
      $this->lastDatetime = date('Y-m-d H:i:s');
   }   
   
   
   
   public static $cmpName = array('Customer', 'cmpName');
   public static function cmpName($x, $y)
   {
      return String::cmp($x->name, $y->name);
   }
   
   
}





class CustomerFile extends TableFile
{
   private $nextID = 1;
   private $customerList;
   
   
   public function all() { return $this->customerList; }
   
   
   
   public function findIndexByID($id)
   {
      foreach ($this->customerList as $index => $customer)
         if ($customer->id === $id)
            return $index;
      
      XCP();
   }
   
   
   public function findByID($id)
   {
      return $this->all()->getAt($this->findIndexByID($id));
   }
   
   public function findByTelephoneOr($telephone, $def)
   {
      foreach ($this->all() as $customer)
         if ($customer->telephone->eq($telephone))
            return $customer;

      return $def;
   }
   
   public function findAllByNameSegment($nameSegment)
   {
      $result = Lizt::create();
      
      foreach ($this->all() as $customer)
         if ($customer->name->contain($nameSegment))
            $result[] = $customer;
            
      return $result;
   }
   
   
   public function add($name = '', $telephone = '')
   {
      $customer = Customer::create($this->nextID++);
      $customer->name = $name;
      $customer->telephone = $telephone;
      $this->customerList[] = $customer;
      return $customer;
   }
   
   public function remove($id)
   {
      $this->all()->removeAt($this->findIndexByID($id));
      return $this;
   }
   
   
   public function updateByInvoice($invoice)
   {
      if ($invoice->telephone->len() === 0)
         return $this;
   
      $customer = $this->findByTelephoneOr($invoice->telephone, NULL);
      if ($customer === NULL)
         $customer = $this->add();
         
      $customer->setByInvoice($invoice);
      return $this;
   }
   
   
   

   
   public static function create($folder, $isRW = false)
   {
      $filePath = pathCombine($folder, 'customer.text');
   
      $result = new CustomerFile();
      $result->customerList = Lizt::create();

      if ($result->openFile($filePath, $isRW) === FALSE)
         return NULL;

      while(true)
      {
         $line = $result->readLine();
         if ($line === false || $line->len() === 0)
            break;

         if ($line->startWith('#'))
            $result->nextID = $line->back(-1)->parseIntOr(1);
         
         if ($line->startWith('C'))
            $result->customerList[] = Customer::create()->fromLine($line->back(-1));
      }


      $result->customerList->sort(Customer::$cmpName);
       
               
      
      return $result;
   }
   
   public function close()
   {
      if ($this->isRW())
      {
         $this->writeLine('#' . "$this->nextID");
         
         foreach ($this->customerList as $customer)
         {
            $line = $customer->toLine();
            $this->writeLine(SSUBX('C', $line));
         }
      }
      
      $this->closeFile();
   }
   
   

}





?>