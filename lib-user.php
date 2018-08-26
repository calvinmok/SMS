<?php




require_once('lib-table.php');



 

class User extends TableRow
{
   public static $tableColumnCollection = NULL;

   public function columnCollection()
   {
      if (self::$tableColumnCollection === NULL)
      {
         self::$tableColumnCollection = TableColumnCollection::create
         (
            TableColumn::create('I', 'id', 'Int'),
            TableColumn::create('C', 'code', 'Text'),
            TableColumn::create('P', 'password', 'Text'),
            TableColumn::create('N', 'name', 'Text'),
            TableColumn::create('M', 'permission', 'Text')
         );
      }
      
      return self::$tableColumnCollection;
   }


   public static function create($id = NULL)
   {
      $result = new User();
      $result->init();
      
      if ($id !== NULL)
         $result->id = $id;

      return $result;
   }
   
   
   public function isAdmin()
   {
      return ($this->permission->eq('admin'));
   }
   
   
   public function isOwner($id)
   {
      return ($this->id === $id || $this->isAdmin());
   }
   
   
   
   
   public static $cmpCode = array('User', 'cmpCode');
   public static function cmpCode($x, $y)
   {
      return String::cmp($x->code, $y->code);
   }
          
   
}





class UserFile extends TableFile
{
   private $nextID = 1;
   private $userList;
   
   
   public function all() { return $this->userList; }
   
   
   
   
   

   
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
   
   public function findIndexByCode($code)
   {
      $r = $this->findIndexByCodeOr($code, NULL); if ($r === NULL) XCP(); return $r;
   }

   public function findIndexByCodeOr($code, $def)
   {
      $code = $code->toLower();
      foreach ($this->all() as $index => $user)
         if ($user->code->toLower()->eq($code))
            return $index;
      
      return $def;
   }
   
   
   
   
   public function findById($id) { $r = $this->findByIdOr($id, NULL); if ($r === NULL) XCP(); return $r; }
   public function findByIdOr($id, $def)
   {
      $index = $this->findIndexByIDOr($id, NULL);
      if ($index === NULL) return $def;
      return $this->all()->getAt($index);
   }
   
   public function findByCode($code) { $r = $this->findByCode($code, NULL); if ($r === NULL) XCP(); return $r; }
   public function findByCodeOr($code, $def)
   {
      $index = $this->findIndexByCodeOr($code, NULL);
      if ($index === NULL) return $def;
      return $this->all()->getAt($index);
   }
   
   
   public function clearAllPassword()
   {
      foreach ($this->all() as $user)
         $user->password = S();
         
      return $this;
   }
   
      
   public function add($code = '', $password = '', $name = '', $permission = '')
   {
      $user = User::create($this->nextID++);
      $user->code = S($code);
      $user->name = S($name);
      $user->password = S($password);
      $user->permission = S($permission);
      
      $this->userList[] = $user;
      return $user;
   }
   
   public function remove($id)
   {
      $this->userList->removeAt($this->findIndexByID($id));
      return $this;
   }
   

   
   public static function create($directory, $isRW = false)
   {
      $filePath = pathCombine($directory, 'user.text');
   
      $result = new UserFile();
      $result->userList = Lizt::create();

      if ($result->openFile($filePath, $isRW) === FALSE)
         return NULL;

      while(true)
      {
         $line = $result->readLine();

         if ($line === FALSE || $line->len() === 0)
            break;

         if ($line->startWith('#'))
            $result->nextID = $line->back(-1)->parseIntOr(1);

         if ($line->startWith('U'))
            $result->userList[] = User::create()->fromLine($line->back(-1));
      }

      if ($result->all()->count() === 0)
      {
         $result->add('admin', '999', 'Administrator', 'admin');
      }

      $result->userList->sort(User::$cmpCode);
      
      return $result;
   }
   
   public function close()
   {
      if ($this->isRW())
      {
         $this->writeLine('#' . "$this->nextID");
         foreach ($this->userList as $user)
         {
            $line = SUBX($user->toLine());    
            $this->writeLine('U' . "$line");
         }
      }
      
      return $this->closeFile();
   }
   
   

}













function login($userCode, $password, $userFile)
{
   $user = $userFile->findByCodeOr($userCode, NULL);
   
   if ($user !== NULL)
      if ($password->eq($user->password))
      {
         SESSION_set('userID', $user->id);
         return S('Success!' );
      }
      else
         return S('Wrong Password!');
   else
      return S('Unknown User Name!');
}

function getLoginUserOr($userFile, $def)
{
   $userId = SESSION_getOr('userID', NULL);
   if ($userId !== NULL)
   {
      $user = $userFile->findByIdOr($userId, NULL);
      if ($user !== NULL)
         return $user;
      else
         return $def;
   }
   else
      return $def;
}

function logout()
{
   SESSION_set('userID', NULL);
   return S('Success!');   
}








?>