

if (typeof String.prototype.startWith != 'function') 
{
   String.prototype.startWith = function(str)
   {
      if (str.length > this.length) return false;
      return this.slice(0, str.length) == str;
   };
}

if (typeof String.prototype.endWith != 'function')
{
   String.prototype.endWith = function(str)
   {
      if (str.length > this.length) return false;
      return this.slice(-str.length) == str;
   };
}


if (typeof String.prototype.frontMidBack != 'function')
{
   String.prototype.frontMidBack = function(f, m, b)
   {
      if (f != null) f = (f < 0) ? this.length + f : f;
      if (m != null) m = (m < 0) ? this.length + m : m;
      if (b != null) b = (b < 0) ? this.length + b : b;
      
      var idx = (f == null) ? this.length - m - b : f;
      var len = (m == null) ? this.length - f - b : m;
      
      if (len == 0) return '';      
      return this.substr(idx, len);
   };
}


String.prototype.front = function(len, skip)
{
   if (typeof skip == 'undefined') skip = 0;
   return this.frontMidBack(skip, len, null);
};
String.prototype.mid = function(front, back)
{
   return this.frontMidBack(front, null, back);
};
String.prototype.back = function(len, skip)
{
   if (typeof skip == 'undefined') skip = 0;
   return this.frontMidBack(null, len, skip);
};









if (typeof String.prototype.parseInt != 'function')
{
   String.prototype.parseInt = function(def)
   {
      var result = parseInt(this);
      return (isNaN(result)) ? def : result;
   };
}

if (typeof String.prototype.parseFloat != 'function')
{
   String.prototype.parseFloat = function(def)
   {
      var result = parseFloat(this);
      return (isNaN(result)) ? def : result;
   };
}


function makePeriod(year, month)
{
   if (month == 1) return '' + year + '01';
   if (month == 2) return '' + year + '02';
   if (month == 3) return '' + year + '03';
   if (month == 4) return '' + year + '04';
   if (month == 5) return '' + year + '05';
   if (month == 6) return '' + year + '06';
   if (month == 7) return '' + year + '07';
   if (month == 8) return '' + year + '08';
   if (month == 9) return '' + year + '09';
   if (month == 10) return '' + year + '10';
   if (month == 11) return '' + year + '11';
   if (month == 12) return '' + year + '12';
}


function OOO(obj) { return obj; }








function eachOF(collection)
{
   if ($.isArray(collection))
   {
      var next = function()
      {
         var result = (++this.index <= this.collection.length - 1);
         this.val = (result) ? this.collection[this.index] : null;
         return result;
      };
      
      return OOO({ collection:collection, index:-1, val:null, next:next });
   }
   else
   {
      var keys = [];
      for (var key in collection)
         keys.push(key);
         
      var next = function()
      {
         var result = (++this.index <= this.keys.length - 1);
         this.key = (result) ? this.keys[this.index] : null;
         this.val = (result) ? this.collection[this.key] : null;
         return result;
      };
      
      return OOO({ collection:collection, keys:keys, index:-1, key:null, val:null, next:next });
   }
   
}






function Array_firstOr(ary, def)
{
   return (ary.length > 0) ? ary[0] : def;
} 
function Array_lastOr(ary, def)
{
   return (ary.length > 0) ? ary[ary.length - 1] : def;
} 



function Array_findWith(ary, data, callback, def)
{
   for (var i = 0; i < ary.length; i++)
      if (callback(data, ary[i]))
         return ary[i];
   
   return def;
}

function Array_findAllWith(ary, data, callback)
{
   var result = [];
   for (var i = 0; i < ary.length; i++)
      if (callback(data, ary[i]))
         result.push(ary[i]);
   
   return result;
}


function Array_findAllIndexWith(ary, data, callback)
{
   var result = [];
   for (var i = 0; i < ary.length; i++)
      if (callback(data, ary[i]))
         result.push(i);
   
   return result;
}

function Array_removeAnyWith(ary, data, callback)
{
   var indexList = Array_findAllIndexWith(ary, data, callback);
   for (var i = 0; i < ary.length; i++)
      this.childList.splice(indexList[i], 1);
}


function Array_map(callback)
{
   var result = [];
   for (var i = 0; i < ary.length; i++)
      result.push(callback(ary[i]));
   return result;
}









