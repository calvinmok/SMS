











function metaItem(caption)
{
   return OOO(
   {
      caption: caption
   });
}

var meta = 
{
   invoice:
   {
      number: metaItem('編號'),
      datetime: metaItem('時間')
   },
   
   
   
};


var STOCKTYPE_QTY_THRESHOLD = 30;


var AJAX_URL = 'lib-ajax.php';







function Table_extend(table)
{
   table.prototype.__init = function(name)
   {
      this.__name = name;
      return this;
   };
   
   table.prototype.name = function() { return this.__name; };
   
   
   table.prototype.data = function(data)
   {
      if ('__data' in this === false)
         this.__data = this;

      if (typeof data === 'undefined')
         return this.__data;
   
      this.__data = data;
      return this;
   };
   


   table.prototype.__process = function(value) { return value; };

   table.prototype.__ajax = function(process, action, value, callback)
   {
      $.ajax(
      {
         url: AJAX_URL,   
         dataType: 'text',
         type: 'POST',      
         
         context : 
         {
            table: this,
            callback: callback,
            action: action
         },
         data : 
         {
            table: this.name(),
            action: action,
            value: value
         },
         success : function(response)
         {
            var parseSuccess = false;

            try
            {
               response = JSON.parse(response);
               parseSuccess = true;
            }
            catch (e)
            {
               console.log(this);            
               console.log(response);
               alert(response);
            }
            
            if (parseSuccess)
            {
               if (process === 1)
               {
                  if (response !== null)
                     response = this.table.__process(response);
               }
               if (process === 2)
               {
                  for (var i = 0; i < response.length; i++)
                     response[i] = this.table.__process(response[i]);                  
               }
               
               var data = this.table.data();           
               if (data === this)
                  this.callback.call(this.table, response);
               else
                  this.callback.call(this.table, response, data);
            }
         },
         error : function(jqXHR, textStatus, errorThrown)
         {
            console.log(jqXHR);         
            console.log(textStatus);
         }
      });
      
      return this;
   };
   
   
   
   
      
}




function createSystem() { return new System().__init(''); }

function System() { } Table_extend(System);

System.prototype.login = function(userCode, password, callback)
{
   return this.__ajax(0, "login", { userCode: userCode, password: password }, callback);
};

System.prototype.getCurrentUser = function(callback)
{
   return this.__ajax(0, "getCurrentUser", { }, callback);
};

System.prototype.logout = function(callback)
{
   return this.__ajax(0, "logout", { }, callback);
};






function createInvoiceTable() { return new InvoiceTable().__init('Invoice'); }

function InvoiceTable() { } Table_extend(InvoiceTable);

InvoiceTable.prototype.__process = function(invoice) 
{
   return invoice;
};

InvoiceTable.prototype.findAll = function(period, callback)
{
   return this.__ajax(0, "findAll", { period: period }, callback);
};

InvoiceTable.prototype.findByID = function(period, id, callback)
{
   return this.__ajax(1, "findByID", { period: period, id: id }, callback);
};

InvoiceTable.prototype.findAllUndelivered = function(year, callback)
{
   return this.__ajax(0, "findAllUndelivered", { year: year }, callback);
};

InvoiceTable.prototype.write = function(invoice, callback)
{
   return this.__ajax(0, "write", invoice, callback);
};

InvoiceTable.prototype.deLete = function(invoice, callback)
{
   return this.__ajax(0, "delete", { id: invoice.id, createDatetime: invoice.createDatetime }, callback);
};



function createRestockTable() { return new RestockTable().__init('Restock'); }

function RestockTable() { } Table_extend(RestockTable);

RestockTable.prototype.findAll = function(year, callback)
{
   return this.__ajax(2, "findAll", { year: year }, callback);
};

RestockTable.prototype.findByID = function(year, id, callback)
{
   return this.__ajax(1, "findByID", { year: year, id: id }, callback);
};

RestockTable.prototype.write = function(restock, callback)
{
   return this.__ajax(0, "write", restock, callback);
};

RestockTable.prototype.deLete = function(createDatetime, id, callback)
{
   return this.__ajax(0, "delete", { createDatetime: createDatetime, id: id }, callback);
};
  



function createStockTypeTable() { return new StockTypeTable().__init('StockType'); }

function StockTypeTable() { } Table_extend(StockTypeTable);

StockTypeTable.prototype.findAll = function(callback)
{
   return this.__ajax(2, "findAll", { }, callback);
};

StockTypeTable.prototype.findByID = function(id, callback)
{
   return this.__ajax(1, "findByID", { id: id }, callback);
};

StockTypeTable.prototype.write = function(stockType, callback)
{
   return this.__ajax(0, "write", stockType, callback);
};
StockTypeTable.prototype.deLete = function(id, callback)
{
   return this.__ajax(0, "delete",  { id: id }, callback);
};




function createCustomerTable() { return new CustomerTable().__init('Customer'); }

function CustomerTable() { } Table_extend(CustomerTable);

CustomerTable.prototype.findAll = function(callback)
{
   return this.__ajax(2, "findAll", { }, callback);
};

CustomerTable.prototype.findByID = function(id, callback)
{
   return this.__ajax(1, "findByID", { id: id }, callback);
};

CustomerTable.prototype.findAllByNameSegment = function(nameSegment, callback)
{
   return this.__ajax(2, "findAllByNameSegment", { nameSegment: nameSegment }, callback);
};

CustomerTable.prototype.findByTelephone = function(telephone, callback)
{
   return this.__ajax(1, "findByTelephone", { telephone: telephone }, callback);
};

CustomerTable.prototype.write = function(customer, callback)
{
   return this.__ajax(0, "write", customer, callback);
};

CustomerTable.prototype.deLete = function(id, callback)
{
   return this.__ajax(0, "delete", { id: id }, callback);
};

CustomerTable.prototype.salesReport = function(year, callback)
{
   return this.__ajax(0, "salesReport", { year: year }, callback);
};



function createUserTable() { return new UserTable().__init('User'); }

function UserTable() { } Table_extend(UserTable);

UserTable.prototype.findAll = function(callback)
{
   return this.__ajax(2, "findAll", { }, callback);
};

UserTable.prototype.findByIdOrNull = function(id, callback)
{
   return this.__ajax(1, "findByIdOrNull", { id: id }, callback);
};

UserTable.prototype.write = function(user, callback)
{
   return this.__ajax(0, "write", user, callback);
};



function SystemTable() { } Table_extend(SystemTable);

SystemTable.prototype.init = function(form)
{
   return this.__init('', form);
};






   
   
function systemGetCurrentUser(callback)
   {
      $.ajax(
      {
         url: AJAX_URL,   
         dataType: 'json',
         type: 'POST',      
         
         context : { callback: callback },
         data : 
         {
            table: '',
            action: 'getCurrentUser'
         },
         success : function(data)
         {
            this.callback(data);
         },
         error : function(jqXHR, textStatus, errorThrown)
         {
   console.log(jqXHR);         
   console.log(textStatus);         
         }
      });
   };






function backToMenu()
{
   window.location.href = 'page-menu.php';
}



function Invoice_empty()
{
   return OOO(
   {
      modifyDatetime: '',
      customerName: '',
      telephone: '',
      address: '',
      itemStockTypeList: [],
      itemOrderedList: [],
      itemUndeliveredList: [],
      itemPriceList: [],
      refund: 0.0,
      shipping: 0.0,
      paymentType: 'Cash',
      paymentDetail: '',
      remark: ''
      
   });
}

function Invoice_getItemList(invoice)
{
   var result = [];

   for (var i = 0; i < invoice.itemStockTypeList.length; i++)
   {
      result.push(
      {
         stockType: invoice.itemStockTypeList[i],
         ordered: invoice.itemOrderedList[i],
         undelivered: invoice.itemUndeliveredList[i],
         price: invoice.itemPriceList[i],
      });
   }
   
   return result;
}

function Invoice_addItem(invoice, item)
{
   if ('itemStockTypeList' in invoice === false) 
      invoice.itemStockTypeList = [];
   if ('itemOrderedList' in invoice === false) 
      invoice.itemOrderedList = [];
   if ('itemUndeliveredList' in invoice === false) 
      invoice.itemUndeliveredList = [];
   if ('itemPriceList' in invoice === false) 
      invoice.itemPriceList = [];
      
   invoice.itemStockTypeList.push(item.stockType);
   invoice.itemOrderedList.push(item.ordered);
   invoice.itemUndeliveredList.push(item.undelivered);
   invoice.itemPriceList.push(item.price.toFixed(1));
}

function Invoice_amount(invoice)
{
   var result = 0;
   for (var i = 0; i < invoice.itemStockTypeList.length; i++)
      result += (invoice.itemPriceList[i] * invoice.itemOrderedList[i]);
      
   return result;
}


function Invoice_undeliveredQty(invoice)
{
   var result = 0;
   for (var i = 0; i < invoice.itemStockTypeList.length; i++)
      result += invoice.itemUndeliveredList[i];
      
   return result;
}

function Invoice_undeliveredAmount(invoice)
{
   var result = 0;
   for (var i = 0; i < invoice.itemStockTypeList.length; i++)
      result += (invoice.itemPriceList[i] * invoice.itemUndeliveredList[i]);
      
   return result;
}

function Invoice_equal(a, b)
{
   var numberEqual = function(x, y)
   {
      if (typeof x === 'number') x = x.toFixed(1);
      if (typeof y === 'number') y = y.toFixed(1);
      return (x === y);
   };

   var arrayEqual = function(x, y, key)
   {
      if (x[key].length !== y[key].length) return false;
      for (var i = 0; i < x[key].length; i++)
         if (x[key][i] !== y[key][i]) return false;
      return true;
   };

   var arrayNumberEqual = function(x, y, key)
   {
      if (x[key].length !== y[key].length) return false;
      for (var i = 0; i < x[key].length; i++)
      {
         var a = x[key][i];
         var b = y[key][i];
         
         if (typeof a === 'number') a = a.toFixed(1);
         if (typeof b === 'number') b = b.toFixed(1);
         
         if (a !== b) return false;
      }
      return true;
   };

   if (a.id !== b.id) return false;
   if (a.number !== b.number) return false;
   if (a.modifyDatetime !== b.modifyDatetime) return false;
   if (a.customerName !== b.customerName) return false;
   if (a.telephone !== b.telephone) return false;
   if (a.address !== b.address) return false;
   if (!!!arrayEqual(a, b, 'itemStockTypeList')) return false;
   if (!!!arrayEqual(a, b, 'itemOrderedList')) return false;
   if (!!!arrayEqual(a, b, 'itemUndeliveredList')) return false;
   if (!!!arrayNumberEqual(a, b, 'itemPriceList')) return false;
   if (!!!numberEqual(a.refund, b.refund)) return false;
   if (!!!numberEqual(a.shipping, b.shipping)) return false;
   if (a.paymentType !== b.paymentType) return false;
   if (a.paymentDetail !== b.paymentDetail) return false;
   if (a.remark !== b.remark) return false;
   return true;
}

function StockTypeList_findByID(stockTypeList, id)
{
   for (var i = 0; i < stockTypeList.length; i++)
      if (stockTypeList[i].id === id)
         return stockTypeList[i];
         
   return null;
}

function StockType_getItemList(stockType)
{
   var result = [];
   
   for (var i = 0; i < stockType.ioPeriodList.length; i++)
   {
      result.push(
      {
         period: stockType.ioPeriodList[i],
         invoiceIn: stockType.invoiceInList[i],
         invoiceOut: stockType.invoiceOutList[i],
         restockIn: stockType.restockInList[i],
         restockOut: stockType.restockOutList[i]
      });
   }
   
   result.sort(function(a,b)
   {
      return (a.period.parseInt() - b.period.parseInt());
   });
   
   return result;
}

function StockType_isAllInOutZero(stockType)
{
   for (var i = 0; i < stockType.ioPeriodList.length; i++)
   {
      if (stockType.invoiceInList[i] > 0) return false;
      if (stockType.invoiceOutList[i] > 0) return false;
      if (stockType.restockInList[i] > 0) return false;
      if (stockType.restockOutList[i] > 0) return false;
   }
   return true;
}

function Restock_addItem(restock, item)
{
   if ('itemStockTypeList' in restock === false) 
      restock.itemStockTypeList = [];
   
   if ('itemQtyList' in restock === false) 
      restock.itemQtyList = [];

   restock.itemStockTypeList.push(item.stockType);
   restock.itemQtyList.push(item.qty);
}





function ajaxLogin(arg)
{
   $.ajax(
   {
      context : arg,
      data :
      {
         action : "Login",
         value :
         {
            username: arg.username, 
            password: arg.password
         }
      },
      success : function(data)
      {
         var context = ('context' in this) ? this.context : null;

         if (data.msg === 'Success!')
         {
            if ('success' in this)
               this.success(context);
         }
         else
         {
            if ('failed' in this)
               this.failed(data.msg, context);
         }
      },
      error : function(jqXHR, textStatus, errorThrown)
      {
         if ('error' in this)
            this.error(textStatus, ('context' in this) ? this.context : null);
         else
            form('error').loadAndShow(textStatus);
      }
   });
}


function ajaxLogout(arg)
{
   $.ajax(
   {
      context : arg,
      data :
      {
         action : "Logout",
      },
      success : function(data)
      {
         var context = ('context' in this) ? this.context : null;

         if (data.msg === 'Success!')
         {
            if ('success' in this)
               this.success(context);
         }
         else
         {
            if ('failed' in this)
               this.failed(data.msg, context);
         }
      },
      error : function(jqXHR, textStatus, errorThrown)
      {
         if ('error' in this)
            this.error(textStatus, ('context' in this) ? this.context : null);
         else
            form('error').loadAndShow(textStatus);
      }
   });
}























/*
$(document).ready(function()
{
   new Form('error').width(450).hide().call(function(errorForm)
   {
      errorForm.addFrame('top').vpad(100).hpad(25).call(function(frame)
      {
         frame.addPanel().call(function(panel)
         {
            panel.addTextBox('text').value('');
         }); 
         frame.addPanel().call(function(panel)
         {
            panel.addTextBox().value('try reload the page.');
         }); 
      });
      
      errorForm.loadAndShow = function(msg)
      {
         this.find('text').value('Internal Error! ('+msg+')');
         this.show();
      };
   });
});
*/

