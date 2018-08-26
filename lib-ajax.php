<?php



require_once("lib-dataDirectory.php");


session_start();

   
$dataDir = DataDirectory::createDef();


$currentDatetime = Datetine::current();      
$currentPeriod = $currentDatetime->period();


$table = SOR(POST_getOr('table', ''), S());
$action = SOR(POST_getOr('action', ''), S());
$value = Collection::create(POST_getOr('value', array()));


$userFile = $dataDir->getUser()->close();
$loginUser = getLoginUserOr($userFile, NULL);


if ($table->eq(''))
{
   if ($action->eq('login'))
   {
      $userCode = SOR($value->getAtOr('userCode', ''), S());
      $password = SOR($value->getAtOr('password', ''), S());

      if ($userCode->eq('sys') && $password->eq('mig'))
         foreach (array(9, 10, 11) as $month)
         {
            $oldInvoiceFile = $dataDir->getOldInvoice(PRDC(2013, $month));
            $invocieFile = $dataDir->getInvoice(PRDC(2013, $month), TRUE);
         
            $invocieFile->clear();
            $invocieFile->addOldFile($oldInvoiceFile);
         }
      else
      {
         echo login($userCode, $password, $userFile)->toJSON();
      }
   }
   if ($action->eq('logout'))
   {
      echo logout()->toJSON();
   }
   if ($action->eq('getCurrentUser'))
   {
      echo ($loginUser !== NULL) ? $loginUser->set('password', S())->toJSON() : json_encode(NULL);
   }
}
else if ($loginUser === NULL)
{
   echo json_encode(array('msg' => 'Unauthoerizd!'));
}
else
{
   if ($table->eq('Invoice'))
   {
      if ($action->eq('findAll'))
      {
         $period = Period::parseOr($value->getAtOr('period', S()), $currentPeriod);         
         
         $invoiceList = Lizt::create();
         foreach ($dataDir->getInvoice($period)->all() as $invoice)
            if ($loginUser->isOwner($invoice->user)) $invoiceList[] = $invoice;

         $stockTypeFile = $dataDir->getStockType();
         $stockTypeList = $stockTypeFile->getAllMapWithoutIO($loginUser->isAdmin());         
         echo Map::create()->setAt('invoice', $invoiceList)->setAt('stockType', $stockTypeList)->toJSON();
      }
      if ($action->eq('findByID'))
      {
         $period = Period::parse($value['period']);
         $invoice = $dataDir->getInvoice($period)->findByID($value['id']->parseInt());
         echo $invoice->toJSON();
      }
      if ($action->eq('findAllUndelivered'))
      {
         $invoiceList = Lizt::create();
         for ($year = $value['year'], $month = 1; $month <= 12; $month++)
            $invoiceList->appendCollection($dataDir->getInvoice(PRDC($year, $month))->findAllUndelivered());
         
         $stockTypeFile = $dataDir->getStockType();
         $stockTypeList = $stockTypeFile->getAllMapWithoutIO($loginUser->isAdmin());         
         echo Map::create()->setAt('invoice', $invoiceList)->setAt('stockType', $stockTypeList)->toJSON();
      }
      if ($action->eq('write'))
      {
         $id = $value->getAtOr('id', S())->parseIntOr(NULL);
         $createDatetime = ($id !== NULL) ? Datetine::parse($value['createDatetime']) : $currentDatetime;

         $file = $dataDir->getInvoice($createDatetime->period(), TRUE);
         $invoice = ($id !== NULL) ? $file->findByID($id) : $file->add($loginUser->id, $createDatetime);
         $newNumber = ($id !== NULL) ? NULL : $invoice->number;

         if ($loginUser->isOwner($invoice->user))
         {
            $invoice->fromValueMap($value)->set('modifyDatetime', $currentDatetime->iso8601());
            if ($newNumber !== NULL) $invoice->number = $newNumber;
            
            $dataDir->getCustomer(TRUE)->updateByInvoice($invoice);
            $dataDir->getStockType(TRUE)->updateInvoiceInOut($file);         
            echo $invoice->toJSON();
         }
      }
      if ($action->eq('delete'))
      {
         $id = $value['id']->parseInt();
         $createDatetime = Datetine::parse($value['createDatetime']);
         $file = $dataDir->getInvoice($createDatetime->period(), TRUE);
         $invoice = $file->findByID($id);
         if ($loginUser->isOwner($invoice->user))
         {
            $file->remove($id);
            $dataDir->getStockType(TRUE)->updateInvoiceInOut($file);
            echo $invoice->toJSON();
         }
      }
   }
   if ($table->eq('Restock'))
   {
      if ($action->eq('findAll'))
      {
         $year = $value->getAtOr('year', S())->parseIntOr($currentPeriod->year());
         echo $dataDir->getRestock($year)->all()->toJSON();
      }
      if ($action->eq('findByID'))
      {
         $year = $value['year']->parseInt();
         $restock = $dataDir->getRestock($year)->findByID($value['id']->parseInt());
         echo $restock->toJSON();
      }
      if ($action->eq('write'))
      {
         $id = $value->getAtOr('id', S())->parseIntOr(NULL);
         $createDatetime = ($id !== NULL) ? Datetine::parse($value['createDatetime']) : $currentDatetime;
      
         $file = $dataDir->getRestock($createDatetime->year(), TRUE);
         $restock = ($id !== NULL) ? $file->findByID($id) : $file->add($loginUser->id, $createDatetime);
         if ($loginUser->isOwner($restock->user))
         {
            $restock->fromValueMap($value)->set('modifyDatetime', $currentDatetime->iso8601());
            $dataDir->getStockType(TRUE)->updateRestockInOut($file);
            echo $restock->toJSON();
         }
      }
      if ($action->eq('delete'))
      {
         $id = $value['id']->parseInt();
         $createDatetime = Datetine::parse($value['createDatetime']);
         $file = $dataDir->getRestock($createDatetime->year(), TRUE);
         $restock = $file->findByID($id);
         if ($loginUser->isOwner($restock->user))
         {
            $file->remove($id);
            $dataDir->getStockType(TRUE)->updateRestockInOut($file);
            echo $restock->toJSON();
         }
      }
   }
   if ($table->eq('StockType'))
   {
      if ($action->eq('findAll'))
      {
         $file = $dataDir->getStockType();
         echo $file->getAllMapWithoutIO($loginUser->isAdmin())->toJSON();
      }
      if ($action->eq('findByID') && $loginUser->isAdmin())
      {
         echo $dataDir->getStockType()->findByID($value['id']->parseInt())->toJSON();
      }
      if ($action->eq('write') && $loginUser->isAdmin())
      {
         $id = $value->isKey('id') ? $value['id']->parseInt() : NULL;
         $file = $dataDir->getStockType(TRUE);
         $stockType = ($id !== NULL) ? $file->findByID($id) : $file->add();
         $stockType->set('name', $value['name']);
         $stockType->set('defPrice', $value['defPrice']);
         echo $stockType->toJSON();
      }
      if ($action->eq('delete') && $loginUser->isAdmin())
      {
         $id = $value['id']->parseInt();
         $file = $dataDir->getStockType(TRUE);
         $stockType = $file->findByID($id);
         if ($stockType->isAllInOutZero())
         {
            $file->remove($id);
            echo $stockType->toJSON();
         }
      }
   }
   if ($table->eq('Customer'))
   {
      if ($action->eq('findAll'))
      {   
         echo $dataDir->getCustomer()->all()->toJSON();
      }
      if ($action->eq('findAllByNameSegment'))
      {
         $customerList = $dataDir->getCustomer()->findAllByNameSegment($value['nameSegment']);
         echo $customerList->toJSON();
      }
      if ($action->eq('findByTelephone'))
      {
         $customer = $dataDir->getCustomer()->findByTelephoneOr($value['telephone'], NULL);
         echo ($customer !== NULL) ? $customer->toJSON() : json_encode(NULL);
      }
      if ($action->eq('write'))
      {
         $id = $value->isKey('id') ? $value['id']->parseInt() : NULL;
         $file = $dataDir->getCustomer(TRUE);
         $customer = ($id != NULL) ? $file->findByID($id) : $file->add();
         $customer->fromValueMap($value);
         echo $customer->toJSON();
      }
      if ($action->eq('delete'))
      {
         $id = $value['id']->parseInt();
         $file = $dataDir->getCustomer(TRUE);
         $customer = $file->findByID($id);
         $file->remove($id);
         echo $customer->toJSON();
      }
      if ($action->eq('salesReport') && $loginUser->isAdmin())
      {
         $customerFile = $dataDir->getCustomer();
         
         $customerList = Lizt::create();
         foreach ($customerFile->all() as $c)
            $customerList[] = Map::create()->setAt('name', $c->name)->setAt('telephone', $c->telephone);         
         
         $year = $value->getAtOr('year', S())->parseIntOr($currentPeriod->year());
         
         $sales = Map::create();
         
         foreach (array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) as $month)
         {
            $map = Map::create();
            foreach ($customerFile->all() as $customer)
               $map[$customer->telephone] = 0;
            
            $invoiceFile = $dataDir->getInvoice(PRDC($year, $month));
            
            foreach ($invoiceFile->all() as $invoice)
               if ($map->containKey($invoice->telephone))
                  $map[$invoice->telephone] += $invoice->totalAmount();
            
            $sales[SFormatInt($month)] = $map;
         }
         
         echo Map::create()->setAt('customer', $customerList)->setAt('sales', $sales)->toJSON();
      }
      
      
      
      
   }
   if ($table->eq('User'))
   {
      if ($action->eq('findAll'))
      {
         echo $userFile->clearAllPassword()->all()->toJSON();
      }   
      if ($action->eq('findByIdOrNull'))
      {      
         $id = $value['id']->parseInt();      
         $user = $userFile->findByIdOr($id, NULL);
         echo ($user !== NULL) ? $user->set('password', S())->toJSON() : json_encode(NULL);
      }
      if ($action->eq('write') && $loginUser->isAdmin())
      {
         $id = $value->isKey('id') ? $value['id']->parseInt() : NULL;
         $file = $dataDir->getUser(TRUE);
         $user = ($id !== NULL) ? $file->findByID($id) : $file->add();
         $oldPwd = $user->password;
         $user->fromValueMap($value);

         if ($id !== NULL && $user->password->len() == 0)
            $user->password = $oldPwd;
         
         echo $user->toJSON();
      }
      if ($action->eq('delete') && $loginUser->isAdmin())
      {
         $id = $value['id']->parseInt();
         $file = $dataDir->getUser(TRUE);
         $user = $file->findByID($id);
         $file->remove($id);
         echo $user->toJSON();
      }
   }
}




$dataDir->close();

?>