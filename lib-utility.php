<?php




ini_set('display_errors', 1);














function XCP($value = NULL)
{
   $value = SUBXOr($value, $value);
   
   $msg = S(print_r($value, TRUE));

   throw new Exception($msg->value());
}

function XCPAssert($assertion, $xcpValue= NULL)
{
   if ($assertion === FALSE)
      XCP($xcpValue);
}

function XCPAssertStr($assertion, $other)
{
   if ($assertion->ne($other))
      XCP(SS($assertion, ' :: ', $other));
}



function GET_get($key)         { if (isset($_GET[$key])) return $_GET[$key]; XCP(); }
function GET_getOr($key, $def) { if (isset($_GET[$key])) return $_GET[$key]; return $def; }

function POST_get($key)         { if (isset($_POST[$key])) return $_POST[$key]; XCP(); }
function POST_getOr($key, $def) { if (isset($_POST[$key])) return $_POST[$key]; return $def; }

function SESSION_get($key)         { if (isset($_SESSION[$key])) return $_SESSION[$key]; XCP(); }
function SESSION_getOr($key, $def) { if (isset($_SESSION[$key])) return $_SESSION[$key]; return $def; }
function SESSION_set($key, $value) { $_SESSION[$key] = $value; }








interface JsonItem
{
   public function toJSON();
}











class String implements JsonItem, ArrayAccess
{

   public static $cmp = array('String', 'cmp');
   public static function cmp($x, $y) 
   {
      return strcasecmp(SUBX($x), SUBX($y));
   }
   
   


   public $___ = '';
   public function value() { return $this->___; }
   public function valueAt($i) { return $this->___[$i]; }
   
   private static function create($value = '')
   {
      $result = new String();
      $result->___ = $value;
      return $result;
   }



   public static function boxOr($value, $def)
   {
      if (is_string($value))        return String::create($value);
      if ($value instanceof String) return $value;
      return $def; 
   }

   public static function unboxOr($value, $def)
   {
      if (is_string($value))        return $value;
      if ($value instanceof String) return $value->value();
      return $def;
   }

   
   
   
   
   public function len() { return strlen($this->value()); }
   public function hasLen() { return strlen($this->value()) > 0; }
   public function isEmpty() { return strlen($this->value()) === 0; }
   
   public function isIndex($index) { return ($index < $this->len()); }   
   
   public function charAt($index) { $c = $this->charAtOr($index, NULL); if ($c === NULL) XCP(); return $c; }
   public function charAtOr($index, $def) { $l = $this->len(); return ($l > 0 && $index <= $l - 1) ? $this->valueAt($index) : $def; }
   
   public function firstIndexOr($def) { $l = $this->len(); return ($l > 0) ? 0      : $def; }
   public function lastIndexOr($def)  { $l = $this->len(); return ($l > 0) ? $l - 1 : $def; }
   public function firstIndex()       { $l = $this->len(); return ($l > 0) ? 0      : XCP(); }
   public function lastIndex()        { $l = $this->len(); return ($l > 0) ? $l - 1 : XCP(); }   
   
   public function lenBefore($str) { $r = $this->lenBeforeOr($str, NULL); if ($r !== NULL) return $r; XCP(); }
   public function lenBeforeOr($str, $def) { $result = strpos($this->value(), SUBX($str)); return ($result !== FALSE) ? $result : $def; }
   
   public function lenAfter($str) { $r = $this->lenAfterOr($str, NULL); if ($r !== NULL) return $r; XCP(); }
   public function lenAfterOr($str, $def) { $s = SUBX($str); $r = strrpos($this->value(), $s); return ($r !== FALSE) ? $this->len() - $r - strlen($s) : $def; }
   
   
   
   
   public function eq($other) { return ($this->value() === SUBX($other)); }
   public function ne($other) { return ($this->value() !== SUBX($other)); }
   public function assert($str) { if ($this->eq($str) === FALSE) XCP(); }
   
   public function eqAny() { foreach (func_get_args() as $s) if ($this->value() === SUBX($s)) return TRUE; return FALSE; }
   // public function neAny() { foreach (func_get_args() as $s) if ($this->value() === SUBX($s)) return FALSE; return TRUE; }
      
   public function startWith($str) { $s = SUBX($str); return !strncmp($this->value(), $s, strlen($s)); }
   public function endWith($str)   { $s = SUBX($str); $l = strlen($s); return ($l != 0) ? (substr($this->value(), -$l) === $s) : TRUE; }
   
   
   
   public function contain($str) { $s = SUBX($str); return (strlen($s) == 0) ? TRUE : (strpos($this->value(), $s) !== FALSE); }
   public function containAny()  { foreach (func_get_args() as $str) if ($this->contain($str) === TRUE)  return TRUE;  return FALSE; }
   public function containAll()  { foreach (func_get_args() as $str) if ($this->contain($str) === FALSE) return FALSE; return TRUE; }
   
   
   
   
   
   private function frontMidBack($f, $m, $b)
   {   
      $l = $this->len();      
      if ($f !== NULL) $f = ($f < 0) ? $l + $f : $f;
      if ($m !== NULL) $m = ($m < 0) ? $l + $m : $m;
      if ($b !== NULL) $b = ($b < 0) ? $l + $b : $b;
      
      $idx = ($f === NULL) ? $l - $m - $b : $f;
      $len = ($m === NULL) ? $l - $f - $b : $m;
      
      if ($len === 0) return '';

      $result = substr($this->value(), $idx, $len);
      return ($result !== FALSE) ? $result : '';
   }

   public function front($len, $skip = 0) { return S($this->frontMidBack($skip, $len, NULL)); }
   public function mid($front, $back)     { return S($this->frontMidBack($front, NULL, $back)); }
   public function back($len, $skip = 0)  { return S($this->frontMidBack(NULL, $len, $skip)); }

   
   /*      
   public function append1($str) { $this->value .= String::unbox($str); return $this; }
   public function append() { foreach (func_get_args() as $str) $this->append1($str); return $this; }
   
   public function insert($str) { $this->value = String::unbox($str) . $this->value; return $this; }
   */
   
   
   public function remove()
   {
      return S(str_replace(func_get_args(), '', $this->value()));
   }
   
   public function replace($search, $replace)
   {
      return S(str_replace(SUBX($search), SUBX($replace), $this->value()));
   }
   
   
   
   
   
   public function toLower()
   {
      $lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
      $upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
      return S(str_replace($upper, $lower, $this->value()));
   } 
   public function toUpper()
   {
      $lower = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
      $upper = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
      return S(str_replace($lower, $upper, $this->value())); 
   } 
   
   
   
   public function parseInt() 
   { 
      $r = $this->parseIntOr(NULL); if ($r === NULL) XCP($this->value()); return $r;
   }
   public function parseIntOr($def)
   {
      return is_numeric($this->value()) ? intval($this->value()) : $def;
   }
   public static function formatInt($value, $length = NULL)
   {
      if (is_int($value) === FALSE) XCP();
   
      $result = strval($value);
      
      if ($length !== NULL)
         while (strlen($result) < $length)
            $result = '0' . $result;
         
      return S($result);
   }
   
   
   public function parseFloat() 
   { 
      $r = $this->parseFloatOr(NULL); if ($r === NULL) XCP($this->value()); return $r;
   }
   public function parseFloatOr($def)
   {
      return is_numeric($this->value()) ? floatval($this->value()) : $def;   
   }
   public static function formatFloat($value, $length = 0)
   {
      if (is_float($value) === FALSE) XCP();
      
      $result = number_format($value, $length, '.', '');
      return S($result);
   }
   
   
   
   
   private function charAt_eq2($index, $ch1, $ch2)
   {
      $ch = $this->valueAt($index);
      return ($ch === $ch1 || $ch === $ch2);
   }

   public function parseBool($def) { $r = $this->parseBoolOr(NULL); if ($r === NULL) XCP(); return $r; }
   public function parseBoolOr($def)
   {
      $len = $this->len();
      if ($len === 0) return $def;
      
      $t = $this->charAt_eq2(0, 't', 'T'); $f = $this->charAt_eq2(0, 'f', 'F');
      $y = $this->charAt_eq2(0, 'y', 'Y'); $n = $this->charAt_eq2(0, 'n', 'N');
      
      if ($len === 1 && ($t || $y)) return TRUE;
      if ($len === 1 && ($f || $n)) return FALSE;
      
      if ($t && $len === 4)
      {
         $r = $this->charAt_eq2(1, 'r', 'R'); $u = $this->charAt_eq2(2, 'u', 'U'); $e = $this->charAt_eq2(3, 'e', 'E');
         if ($r && $u && $e) return TRUE;
      }
      if ($f && $len === 5)
      {
         $a = $this->charAt_eq2(1, 'a', 'A');
         $l = $this->charAt_eq2(2, 'l', 'L');
         $s = $this->charAt_eq2(3, 's', 'S');
         $e = $this->charAt_eq2(4, 'e', 'E');
         if ($a && $l && $s && $e) return FALSE;
      }
      if ($y && $len === 3)
      {
         if ($this->charAt_eq2(1, 's', 'S') && $this->charAt_eq2(2, 'e', 'E')) return TRUE;
      }
      if ($n && $len === 2)
      {
         if ($this->charAt_eq2(1, 'o', 'O')) return FALSE;
      }
      
      return $def;
   }
   
   public static function formatBool($value)
   {
      if ($value === TRUE) return S('true');
      if ($value === FALSE) return S('false');
      XCP();
   }
      
   
   
   public function explode($delimiter = ',')
   {
      $result = explode(SUBX($delimiter), $this->value());
      for ($i = 0; $i < count($result); $i++)
         $result[$i] = S($result[$i]);
         
      return Lizt::create($result);
   }
   
   public function join($value)
   {
      $result = '';
      $count = 0;
      foreach ($value as $str)
      {
         if ($count++ != 0)
            $result .= $this->value();

         $result .= SUBX($str);
      }
      return S($result);
   }


   
   
   
   public function toJSON()
   {
      return SSUBX('"', $this->replace('"', '\\"')->replace('\\', '\\\\'), '"');
   }
   



   public function offsetSet($index, $value) { XCP(); }
   public function offsetExists($index)      { XCP(); }
   public function offsetUnset($index)       { XCP(); }
   public function offsetGet($index)         { XCP(); }
   
}


function S($str = '')    { $r = String::boxOr($str, NULL); if ($r === NULL) XCP(); return $r; }
function SOR($str, $def) { return String::boxOr($str, $def); }
function SUBX($str)         { $r = String::unboxOr($str, NULL); if ($r === NULL) XCP(); return $r; }
function SUBXOR($str, $def) { return String::unboxOr($str, $def); }


function SS()    { $result = ''; foreach (func_get_args() as $str) $result .= SUBX($str); return S($result); }
function SSUBX() { $result = ''; foreach (func_get_args() as $str) $result .= SUBX($str); return $result; }

function SFormatInt($value, $length = NULL) { return String::formatInt($value, $length); }
function SFormatFloat($value, $length = 0)  { return String::formatFloat($value, $length); }

function SFormatBool($value)                { return String::formatBool($value); }


function pathCombine()
{
   $result = '';
   foreach (func_get_args() as $str)
   {
      if (strlen($result) > 0)
         $result .= DIRECTORY_SEPARATOR;
      
      $result .= SUBX($str);
   }
   
   return S($result);
}






function String_test()
{

   $HELLO = S('HELLO');
   
   if ($HELLO->len() != 5) XCP();
   
   if ($HELLO->ne('HELLO')) XCP();
   if ($HELLO->eq('ABCDE')) XCP();
   if (SUBX($HELLO)  != 'HELLO') XCP();
   if (SUBX('HELLO') != 'HELLO') XCP();
   
   if (SS('HELLO', 'WORLD')->ne('HELLOWORLD')) XCP();
   if (SS($HELLO,  'WORLD')->ne('HELLOWORLD')) XCP();
         
   if ($HELLO->front(2   )->ne('HE')) XCP();
   if ($HELLO->front(2, 1)->ne('EL')) XCP();
   if ($HELLO->front(-2  )->ne('HEL')) XCP();
   if ($HELLO->mid(2, 1  )->ne('LL')) XCP();
   if ($HELLO->mid(-3, 1 )->ne('LL')) XCP();
   if ($HELLO->mid(-4, -3)->ne('EL')) XCP();
   if ($HELLO->back(2    )->ne('LO')) XCP();
   if ($HELLO->back(2, 1 )->ne('LL')) XCP();
   if ($HELLO->back(-2   )->ne('LLO')) XCP();
   
   XCPAssertStr(S('-')->join(S('1.2.3')->explode('.')), '1-2-3');
   XCPAssertStr(S('-')->join(S('1.2.3.')->explode('.')), '1-2-3-');
   XCPAssertStr(S('-')->join(S('.1.2.3.')->explode('.')), '-1-2-3-');
   
   
   if (S('')->parseIntOr(NULL) != NULL) XCP();
   if (S('0')->parseIntOr(NULL) != 0) XCP();
   if (S('2.2')->parseIntOr(NULL) != 2) XCP();
   if (S('0.9')->parseIntOr(NULL) != 0) XCP();
   if (S('0004')->parseIntOr(NULL) != 4) XCP();
   
   
   XCPAssert(S('0')->parseFloat() === 0.0);
   XCPAssert(S('3.0')->parseFloat() === 3.0);
   
   SFormatFloat(S('3.00')->parseFloat(), 2)->assert('3.00');
   SFormatFloat(S('3.45')->parseFloat(), 2)->assert('3.45');
   SFormatFloat(S('3.01999')->parseFloat(), 2)->assert('3.02');
   
   

   if (S() instanceof JsonItem === FALSE) XCP();
   
   
   S('aBc')->toLower()->assert('abc');
   S('aBc')->toUpper()->assert('ABC');
   
   
   
   Datetine::selftest();
   Period::selftest();
   
   return S('Success!');
}







abstract class Collection implements Iterator, JsonItem
{

   protected $subject = array();
   protected function setSubject($subject)
   {
      $this->subject = $subject;
      return $this;
   }

   
   

   public function count() { return count($this->subject); }   
   public function allValue() { return Lizt::create(array_values($this->subject)); }
   public function clear() { $this->subject = array(); return $this; }
   
   
   public static function create($subject)
   {
      $result = NULL;
      
      if (is_array($subject))
      {
         $isList = TRUE;
         for ($i = 0; $i < count($subject) && (($isList)); $i++)
            if (isset($subject[$i]) === FALSE)
               $isList = FALSE;

         foreach ($subject as $k => $v)
            if (is_array($v))
               $subject[$k] = Collection::create($v);
            else if (is_string($v))
               $subject[$k] = S($v);
               
         if ($isList && count($subject) > 0)
            $result = Lizt::create($subject);
         else
            $result = Map::create($subject);
      }
      
      return $result;
   }
   
   
   
   
   public static function parseJSON($text)
   {
      $json = json_decode($text, true);
      if (is_array($json))
         return Collection::create($json);
      else
         return $json;
   }
   
   
   
/*
   public function any($callback) { foreach ($this as $value) if ($callback($value) ==  TRUE) return TRUE;  return FALSE; }
   public function all($callback) { foreach ($this as $value) if ($callback($value) == FALSE) return FALSE; return TRUE;  }
   
   
   
   
   public function find($callback) { foreach ($this as $i) if ($callback($i)) return $i; XCP(); }
   public function findOr($callback, $def) { foreach ($this as $i) if ($callback($i)) return $i; return $def; }

   public function findAll($callback) { $r = Lizt::create(); foreach ($this as $i) if ($callback($i)) $r[] = $i; return $r; }
   
   
   
   
   public function findAllKey($callback)
   {
      $result = Lizt::create();
      foreach ($this->subject as $k => $v)
         if ($callback($v))
            $result[] = $k;
      return $result;
   }
   public function removeAny($callback)
   {
      $keyList = $this->findAllKey($callback);
      return $this->removeAt($keyList);
   }
   public function removeOnly($callback)
   {
      $keyList = $this->findAllKey($callback);
      if ($keyList->count() != 1) XCP();
      return $this->removeAt($keyList);
   }
   public function removeExist($callback)
   {
      $keyList = $this->findAllKey($callback);
      if ($keyList->count() == 0) XCP();
      return $this->removeAt($keyList);
   }
   
   
   
   public function map($callback)
   {
      $result = Lizt::create();
      foreach ($this->subject as $k => $v)
         $result[] = $callback($v);
      return $result;
   }

*/





   public function assert()
   {
      $i = 0;
      foreach ($this->subject as $v)
         S(func_get_arg($i++))->assert($v);
   }





   public function rewind()  { }
   public function valid()   { return FALSE; }   
   public function key()     { return NULL; }
   public function current() { return NULL; }
   public function next()    { return FALSE; }

}


class Lizt extends Collection implements ArrayAccess
{


   public static function create($subject = array())
   {
      $result = new Lizt();
      $result->subject = $subject;
      return $result;
   }
      

   public static function box($value = NULL)
   {
      if ($value === NULL)
         return Lizt::create();
      if (is_array($value))
         return Lizt::create($value);
      if ($value instanceof Lizt)
         return $value;
      
      XCP();
   }

   public static function unbox($value)
   {
      if (is_array($value))
         return $value;
      else if ($value instanceof Lizt)
         return $value->subject;
      else
         XCP();
   }   
   
   public function clome()
   {
      return Lizt::box($this->subject);
   }
   
         

   public function count() { return count($this->subject); }
   
   public function firstIndexOr($def) { $c = count($this->subject); return ($c != 0) ? 0             : $def; }
   public function lastIndexOr($def)  { $c = count($this->subject); return ($c != 0) ? $c - 1        : $def; }   
   public function firstOr($def)      { $c = count($this->subject); return ($c != 0) ? $this[0]      : $def; }
   public function lastOr($def)       { $c = count($this->subject); return ($c != 0) ? $this[$c - 1] : $def; }

   public function firstIndex() { $c = count($this->subject); if ($c != 0) return 0            ; XCP(); }
   public function lastIndex()  { $c = count($this->subject); if ($c != 0) return $c - 1       ; XCP(); }   
   public function first()      { $c = count($this->subject); if ($c != 0) return $this[0]     ; XCP(); }
   public function last()       { $c = count($this->subject); if ($c != 0) return $this[$c - 1]; XCP(); }   


   public function isIndex($index)
   {
      if (is_int($index) === FALSE || $index < 0) XCP();
      return ($index < $this->count());
   }
   public function isIndexOrNew($index)
   {
      if (is_int($index) === FALSE || $index < 0) XCP();
      return ($index <= $this->count());
   }


   public function getAt($index)
   {
      if ($this->isIndex($index)) return $this->subject[$index]; XCP();
   }
   public function getAtOr($index, $def)
   {
      return $this->isIndex($index) ? $this->subject[$index] : $def;
   }
   
   
   
   
   public function setAt($index, $value)
   {
      if ($value === $this) throw new Exception();
      
      if ($index === NULL)
      {
         $this->subject[] = $value;
      }
      else
      {
         if ($this->isIndexOrNew($index) === FALSE) XCP();
         $this->subject[$index] = $value;
      }
      
      return $this;
   }
   
   
   
   
   public function append()
   {
      foreach (func_get_args() as $value) $this[] = $value;   
      return $this;
   }
   public function appendCollection()
   {
      foreach (func_get_args() as $collection)
         foreach ($collection as $value)         
            $this[] = $value;   
      return $this;
   }
   

   public function insert($value)
   {
      array_unshift($this->subject, NULL);
      return $this->setAt(0, $value);
   }
   
   
   
   public function reverse()
   {
      return Lizt::create(array_reverse($this->subject));
   }
   
   
   

   
   public function removeAt($index)
   {
      if ($index instanceof Lizt)
      {
         for ($i = $index->lastIndexOr(-1); $i >= 0; $i--)
            $this->removeAt($index[$i]);
      }
      else
      {
         if ($this->isIndex($index) === FALSE) XCP();
         array_splice($this->subject, $index, 1);
      }
      
      return $this;
   }
   
   
   
   public function slice($index, $length)
   {
      return array_slice($this->subject, $index, $length);
   }
   
   
   
   
   
   public function sort($compare)
   {
      usort($this->subject, $compare);
      return $this;
   }
   
   
   
      
   public function toJSON()
   {
      $result = '[';
      
      foreach ($this->subject as $k => $v)
      {
         if ($k > 0)
            $result .= ',';
            
         if ($v instanceof JsonItem)
            $result .= SUBX($v->toJSON());
         else
            $result .= json_encode($v);
      }
      
      $result .= ']';

      return $result;
   }
   




   public function rewind()  { reset($this->subject); }
   public function valid()   { $k = key($this->subject); return ($k !== NULL && $k !== FALSE); }   
   public function key()     { return key($this->subject); }
   public function current() { return current($this->subject); }
   public function next()    { return next($this->subject); }

   public function offsetSet($index, $value) { $this->setAt($index, $value); }
   public function offsetExists($index)      { return $this->isIndex($index); }
   public function offsetUnset($index)       { $this->removeAt($index); }
   public function offsetGet($index)         { return $this->getAt($index); }

   
}



function Lizt_test()
{
   $ANIMALS = Lizt::create()->append(S('Cat'), S('Dog'), S('Bat'));
   
   XCPAssert($ANIMALS->count() == 3);
   XCPAssert($ANIMALS[0]->eq('Cat'));
   XCPAssert($ANIMALS[2]->eq('Bat'));
   
   $i = 0;
   foreach ($ANIMALS as $animal)
   {
      if ($i == 0) XCPAssertStr($animal, 'Cat');
      if ($i == 1) XCPAssertStr($animal, 'Dog');
      if ($i == 2) XCPAssertStr($animal, 'Bat');
      $i++;
   }
   
   $a = $ANIMALS->clome();
   $a[] = S('Pig');
   XCPAssert($ANIMALS->count() == 3);
   XCPAssert($a[3]->eq('Pig'));

   $a->removeAt(1);
   XCPAssert($a[1]->eq('Bat'));
   
   

   S('Cat,Dog,Bat')->explode()->reverse()->assert('Bat', 'Dog', 'Cat');

   
   S('Cat,Dog,Bat')->explode()->assert('Cat', 'Dog', 'Bat');
   S('Cat,Dog,Bat')->explode()->sort(String::$cmp)->assert('Bat', 'Cat', 'Dog');
   
   
   
   
   
   return S('Success!');
}









class Map extends Collection implements ArrayAccess
{

   public static function create($subject = array())
   {
      $result = new Map();
      $result->subject = $subject;
      return $result;
   }
   
   

   public static function box($value = NULL)
   {
      if ($value === NULL)
         return Map::create();
      if (is_array($value))
         return Map::create($value);
      if ($value instanceof Lizt)
         return $value;
      
      XCP();
   }

   public static function unbox($value)
   {
      if (is_array($value))
         return $value;
      else if ($value instanceof Map)
         return $value->subject;
      else
         XCP($value);
   }   

   public function clome()
   {
      return Map::box($this->subject);
   }
   
   
   
   
   
   public function count()
   {
      return count($this->subject);
   }
   
   public function getAt($key) 
   {
      if ($this->isKey($key)) return $this->subject[SUBX($key)]; XCP();
   }
   
   public function getAtOr($key, $def)
   {
      return $this->isKey($key) ? $this->subject[SUBX($key)] : $def;
   }
   
   
   public function setAt($key, $value)
   {
      $this->subject[SUBX($key)] = $value;
      return $this;
   }
   

   public function __get($key) { return $this->getAt($key); }
   public function __set($key, $value) { return $this->setAt($key, $value); }
   
   

   public function reverse()
   {
      return Lizt::create(array_reverse($this->subject, TRUE));
   }
   
   
   

   public function removeAt($key)
   {
      if ($key instanceof Lizt)
      {
         for ($i = $indexList->lastIndexOr(-1); $i >= 0; $i--)
            $this->removeAt($key[$i]);
      }
      else
      {
         if ($this->isKey($key) === FALSE) XCP();
         unset($this->subject[$key]);
      }
      
      return $this;
   }
   
   
   
   
   public function isKey($key) { return array_key_exists(SUBX($key), $this->subject); }
   public function containKey($key) { return array_key_exists(SUBX($key), $this->subject); }
   
   
   public function allKey()
   {
      $result = array_keys($this->subject);
      
      for ($i = 0; $i < count($result); $i++)
         if (is_int($result[$i]))
            $result[$i] = SFormatInt($result[$i]);
         else
            $result[$i] = S($result[$i]);
      
      return Lizt::create($result);
   }
   
   
   
   public function toJSON()
   {
      $result = '{';
      
      $count = 0;
      foreach ($this->subject as $k => $v)
      {
         if ($count++ > 0)
            $result .= ', ';
            
         $result .= '"';
         $result .= $k;
         $result .= '":';
         
         if ($v instanceof JsonItem)
            $result .= SUBX($v->toJSON());
         else
            $result .= json_encode($v);         
      }
      
      $result .= '}';

      return $result;
   }
      

   public function rewind()  { reset($this->subject); }
   public function valid()   { $k = key($this->subject); return ($k !== NULL && $k !== FALSE); }   
   public function key()     { return key($this->subject); }
   public function current() { return current($this->subject); }
   public function next()    { return next($this->subject); }
   
   public function offsetSet($key, $value) { $this->setAt($key, $value); }
   public function offsetExists($key)      { return $this->isKey($key); }
   public function offsetUnset($key)       { $this->removeAt($key); }
   public function offsetGet($key)         { return $this->getAt($key); }

}







function currentDatetime()
{
   
}



date_default_timezone_set('Asia/Hong_Kong');


class Datetine
{
   private static $__current;


   private $year;
   public function year() { return $this->year; }
   
   private $month;
   public function month() { return $this->month; }

   private $day;
   public function day() { return $this->day; }

   private $hour;
   public function hour() { return $this->hour; }

   private $minute;
   public function minute() { return $this->minute; }

   private $second;
   public function second() { return $this->second; }
   
   public function period() { return Period::create($this->year, $this->month); }
   
   public static function create($year, $month, $day, $hour = 0, $minute = 0, $second = 0)
   {
      $result = new Datetine();
      $result->year = $year;
      $result->month = $month;
      $result->day = $day;
      $result->hour = $hour;
      $result->minute = $minute;
      $result->second = $second;
      return $result;
   }
   
      
   public static function current($isUpdated = FALSE)
   {
      if ($isUpdated)
      {
         $d = getdate();
         return Datetine::create($d['year'], $d['mon'], $d['mday'], $d['hours'], $d['minutes'], $d['seconds']);
         //return Datetine::create($d['year'], 9, $d['mday'], $d['hours'], $d['minutes'], $d['seconds']);
      }
      else
      {
         if (Datetine::$__current === NULL)
            Datetine::$__current = Datetine::current(TRUE);
            
         return Datetine::$__current;
      }
   } 
   
   public function iso8601()
   {
      $y = SFormatInt($this->year, 4)->value();   
      $m = SFormatInt($this->month, 2)->value();   
      $d = SFormatInt($this->day, 2)->value();   
      $h = SFormatInt($this->hour, 2)->value();   
      $i = SFormatInt($this->minute, 2)->value();   
      $s = SFormatInt($this->second, 2)->value();   
      return S("$y-$m-$d $h:$i:$s");
   }
   
   
   public static function parse($str)
   {
      $d = Datetine::parseOr($str, NULL); if ($d === NULL) XCP(); return $d;
   }
   public static function parseOr($str, $def)
   {
      $str = S($str)->remove('-', ' ', '/', ':');
   
      if ($str->len() < 8)
         return $def;
         
      $y = $str->front(4, 0)->parseIntOr(NULL);
      $m = $str->front(2, 4)->parseIntOr(NULL);
      $d = $str->front(2, 6)->parseIntOr(NULL);
      $h = ($str->len() >= 10) ? $str->front(2, 8)->parseIntOr(NULL) : 0;
      $i = ($str->len() >= 12) ? $str->front(2, 10)->parseIntOr(NULL) : 0;
      $s = ($str->len() >= 14) ? $str->front(2, 12)->parseIntOr(NULL) : 0;
      
      if ($y === NULL || $m === NULL || $d === NULL || $h === NULL || $i === NULL || $s === NULL)
         return $def;
      
      return Datetine::create($y, $m, $d, $h, $i, $s);       
   }
   
   
   public static function selftest()
   {
      XCPAssert(Datetine::create(2003, 2, 4)->iso8601()->eq('2003-02-04 00:00:00'));
   }
   

}






class Period
{
   public static function create($year, $month)
   {
      $result = new Period();
      $result->year = $year;
      $result->month = $month;
      return $result;
   }
   
   public static function current()
   {
      $d = Datetine::current();
      return Period::create($d->year(), $d->month());
   }
   
   public static function parse($str)
   {
      $result = Period::parseOr($str, NULL); if ($result !== NULL) return $result; XCP();
   }
   public static function parseOr($str, $def)
   {
      if ($str->len() != 6)
         return $def;
         
      $year  = $str->front(4, 0)->parseIntOr(NULL);
      $month = $str->front(2, 4)->parseIntOr(NULL);
      
      if ($year === NULL || $month === NULL)
         return $def;
      
      return Period::create($year, $month);      
   }
   
   
   private $year;
   public function year() { return $this->year; }
   
   private $month;
   public function month() { return $this->month; }
   
   
   
   
   public function yyyymm()
   {
      $year = $this->year;
      while ($year < 0) $year += 10000;
      while ($year > 9999) $year -= 10000;

      $month = $this->month;
      while ($month <= 0) $month += 12;
      while ($month > 12) $month -= 12;

      return SS(SFormatInt($year, 4), SFormatInt($month, 2));   
   }
   
   
   public function prev()
   {
      $year = $this->year;
      $month = $this->month;
      
      if ($month === 1)
      {
         $year -= 1;
         $month = 12;
      }
      else
      {
         $month -= 1;
      }
      
      return Period::create($year, $month);
   }

   public function next()
   {
      $year = $this->year;
      $month = $this->month;
      
      if ($month === 12)
      {
         $year += 1;
         $month = 1;
      }
      else
      {
         $month += 1;
      }
      
      return Period::create($year, $month);
   }
   
   public function eq($periodOrYear, $month = NULL)
   {
      if ($month === NULL)
         return $this->eq($periodOrYear->year(), $periodOrYear->month());
      else
         return ($periodOrYear == $this->year() && $month == $this->month());
   }
   public function ne($periodOrYear, $month = NULL) { return $this->eq($periodOrYear, $month) === FALSE; }
   
   
   public static function selftest()
   {
      XCPAssert(Period::create(2003,  2)->yyyymm()->eq('200302'));
      XCPAssert(Period::create(2003, 12)->yyyymm()->eq('200312'));
   }
   
   
}


function PRDC($y, $m) { return Period::create($y, $m); }





class FSNode implements ArrayAccess
{

   private $__path;
   
   public function path() { return $this->__path; }
   public function _path() { return SUBX($this->__path); }
   
   protected function setPath($path)
   {
      $path = S($path);
      if ($path->endWith(DIRECTORY_SEPARATOR))
         $path = $path->front(-1 * S(DIRECTORY_SEPARATOR)->len());
            
      $this->__path = S(realPath(SUBX($path)));

      return $this;
   }


   public function _childPath($name)
   {
      return SSUBX($this->_path(), DIRECTORY_SEPARATOR, $name);
   }



   private static function create($path)
   {
      $result = new FSNode();
      return $result->setPath($path);
   }
   
   
   
   
   
   
   public function baseName() { return S(basename($this->_path())); }
   
   public function parent() { return S(dirname($this->_path())); }
   public function owner() { $pwuid = posix_getpwuid(fileowner($this->_path())); return $pwuid['name']; }
   
   
   
   
   public function touch() { touch($this->_path()); }
   
   
   public function isExist() { return file_exists($this->_path()); }
   public function isFile() { return is_file($this->_path()); }
   public function isDir() { return is_dir($this->_path()); }
   



   public function getDir($name)
   {
      $path = $this->_childPath($name);
      if (is_file($path)) XCP();
      if (is_dir($path) === FALSE) mkdir($path);
      return FSNode::create($path);
   }
   
   public function getFile($name)
   {
      $path = $this->_childPath($name);
      if (is_dir($path)) XCP();
      if (is_file($path) === FALSE) touch($path);
      return FSNode::create($path);   
   }
   
   
   public function containFile($name) { return is_file($this->_childPath($name));  }
   


   
   public function offsetSet($key, $value) { XCP(); }
   public function offsetExists($key)      { XCP(); }
   public function offsetUnset($key)       { XCP(); }
   public function offsetGet($key)         { XCP();  }
         
   
}



class Diractory extends FSNode
{
   public static function current()
   {
      $result = new Diractory();
      return $result->setPath('.');
   }
   
   public static function create($path)
   {
      $result = new Diractory();
      return $result->setPath($path);
   }
   
   
   public function dir($name)
   {
      $path = $this->_childPath($name);
      if (is_file($path)) XCP();
      if (is_dir($path) === FALSE) mkdir($path);
      return Diractory::create($path);
   }
   
   public function file($name)
   {
      $path = $this->_childPath($name);
      if (is_dir($path)) XCP();
      if (is_file($path) === FALSE) touch($path);
      return File::create($path);   
   }
   
   
   
   public function makeDir($name)
   {
      $path = $this->_childPath($name);
      mkdir($path);
      return Diractory::create($path);
   }
   
   public function makeFile($name)
   {
      $path = $this->_childPath($name);
      touch($path);
      return File::create($path);
   }
   
   
   public function remove($name)
   {
      $path = $this->_childPath($name);
      if (is_dir($path)) rmdir($path);
      if (is_file($path)) unlink($path);
      return $this;
   }
   
   
   
   
   public function offsetSet($key, $value) { XCP(); }
   public function offsetExists($key)      { XCP(); }
   public function offsetUnset($key)       { XCP(); }
   public function offsetGet($key)         
   {
      $path = $this->_childPath($key);
      if (is_dir($path)) return Diractory::create($path);
      if (is_file($path)) return File::create($path);
      XCP();
   }

   
   
}

$Diractory_current = Diractory::current();





class File extends FSNode
{


   public static function create($path)
   {
      $result = new File();
      return $result->setPath($path);
   }
   
   

   public function read()          { return S(file_get_contents($this->_path())); }
   public function write($content) { file_put_contents($this->_path(), SUBX($content)); }
   

   
}


















?>