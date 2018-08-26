<!DOCTYPE html>
<html>

<head>
   
   <?php include("pagehead.php"); ?>

   <title>存貨管理﹣發票</title>
   <meta name="viewport" content="width=640">

</head>





<body style="background-color:gray;">
   
   <script>
   
      g_currentUser = null;
      g_stockTypeList = null;
   
      $(document).ready(function()
      {
         var firstForm = createForm('invoiceList').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).spacing(10).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('back').width(80).value('選單').click(function(b) { backToMenu(); });
                  panel.addButton('new' ).width(80).value('新增').click(function(b) { b.form('invoiceDetail').loadAndShow(); });
               });

               topPanel.addQueue().spacing(10).call(function(panel)
               {
                  var currentYear = <?php echo Period::current()->year(); ?>;
                  var yearSelect = panel.addSelect('year').label('搜尋年份').width(100).change(function(b) { b.form().loadAndShow(); });
                  for (var i = 0; i < 20; i++)
                     yearSelect.addOption(''+(currentYear - i));

                  var month = ['00', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
                  var monthSelect = panel.addSelect('month').label('月份 (或 欠貨)').width(100).change(function(b) { b.form().loadAndShow(); });
                  
                  monthSelect.addOption('', '欠貨');
                  for (var i = 1; i <= 12; i++)
                     monthSelect.addOption(month[i]);
                     
                  yearSelect.value(''+currentYear);
                  monthSelect.value(month[<?php echo Period::current()->month(); ?>]);
                  
                  panel.addButton('salesReport').width(80).value('銷售').click(function(b)
                  {
                     this.form('salesReport').loadAndShow(this.data('invoiceList'));
                  });
                  panel.addButton('undeliveredReport').width(80).value('欠貨').click(function(b)
                  {
                     this.form('undeliveredReport').loadAndShow(this.data('invoiceList'));
                  });
               });
            });

            this.addStack('list');
            
            
            this.showInvoiceList = function(invoiceList)
            {
               this.data('invoiceList', invoiceList);
               
               var listPanel = this.find('list').removeAll();
               
               listPanel.addQueue('total').hpad(25).vpad(10).call(function(queue)
               {
                  queue.addTextBox('number'     ).align('right').value('本月:');
                  queue.addTextBox('amount'     ).align('right').value('').width(120);
                  queue.addTextBox('undelivered').align('right').value('').width(120);
                  queue.addSpace().length(10);
                  queue.addButton().show(false);
               });
               
               listPanel.addQueue().hpad(25).vpad(10).call(function(queue)
               {
                  queue.addTextBox('number'     ).align('left' ).value('編號');
                  queue.addTextBox('amount'     ).align('right').value('合計').width(120);
                  queue.addTextBox('undelivered').align('right').value('欠貨').width(120);
                  queue.addSpace().length(10);
                  queue.addButton().show(false);
               });
               
               var amountTotal = 0;
               var undeliveredAmountTotal = 0;

               for (var invoice = eachOF(invoiceList); invoice.next(); )
               {
                  var amount = Invoice_amount(invoice.val) + invoice.val.shipping - invoice.val.refund;
                  var undeliveredAmount = Invoice_undeliveredAmount(invoice.val);
                  listPanel.data('amount', amount).data('undeliveredAmount', undeliveredAmount);

                  listPanel.addQueue().hpad(25).vpad(10).data('invoice', invoice.val).call(function(panel)
                  {
                     panel.addTextBox('number'     ).value(panel.data('invoice').number);
                     panel.addTextBox('amount'     ).width(120).align('right').value(panel.data('amount').toFixed(1));
                     panel.addTextBox('undelivered').width(120).align('right').value(panel.data('undeliveredAmount').toFixed(1));
                     panel.addSpace().length(10);
                     panel.addButton().click(function(b) { b.form('invoiceDetail').loadAndShow(b.data('invoice')); } );
                  });
                  
                  amountTotal += amount;
                  undeliveredAmountTotal += undeliveredAmount;
               }
               
               listPanel.find('total').find('amount'     ).value(amountTotal);
               listPanel.find('total').find('undelivered').value(undeliveredAmountTotal);
               
               this.showMe();            
            };
            
            this.loadAndShow = function()
            {
               this.find('list').removeAll();

               var callback = function(result, f)
               {
                  g_stockTypeList = result.stockType;
                  f.showInvoiceList(result.invoice);
               };
               
               var year = this.find('year').value();
               var month = this.find('month').value(); 
               if (month.length > 0)
                  createInvoiceTable().data(this).findAll(year + month, callback);
               else
                  createInvoiceTable().data(this).findAllUndelivered(year, callback);
            };
            

            this.prevInvoice = function(currentInvoice)
            {
               var prev = null;
               for (var invoice = eachOF(this.data('invoiceList')); invoice.next(); )
               {
                  if (invoice.val.number === currentInvoice.number && prev !== null)
                  {
                     this.form('invoiceDetail').loadAndShow(prev);
                     return;
                  }
                     
                  prev = invoice.val;
               }
            };
            
            this.nextInvoice = function(currentInvoice)
            {
               var found = false;
               for (var invoice = eachOF(this.data('invoiceList')); invoice.next(); )
               {
                  if (found)
                  {
                     this.form('invoiceDetail').loadAndShow(invoice.val);
                     return;
                  }
                  
                  if (invoice.val.number === currentInvoice.number)
                     found = true;
               }
            };
            
            
            
         });
         
         
         
         
         createForm('salesReport').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).spacing(10).bgColor('CornflowerBlue').call(function()
            {
               this.addQueue().spacing(20).call(function()
               {
                  this.addButton('cancel').width(80).value('取消').click(function(b) { b.form('invoiceList').loadAndShow(); });
               });
            });
            
            this.addQueue().hpad(25).vpad(10).call(function(queue)
            {
               queue.addTextBox().align('left' ).value('貨物');
               queue.addTextBox().width(120).align('right').value('數量');
               queue.addTextBox().width(120).align('right').value('金額');
            });
            
            this.addStack('list');
            
            this.addQueue().hpad(25).vpad(10).call(function(queue)
            {
               queue.addTextBox(             ).borderTop(5, 1).borderBottom(5, 0).align('left' );
               queue.addTextBox(             ).borderTop(5, 1).borderBottom(5, 0).width(120).align('right');
               queue.addTextBox('totalAmount').borderTop(5, 1).borderBottom(5, 2).width(120).align('right');
            });
            
            this.addSpace().length(30);
            
            this.loadAndShow = function(invoiceList)
            {
               var qtyMap = {};
               var amountMap = {};
               
               for (var invoice = eachOF(invoiceList); invoice.next(); )
               {
                  for (var item = eachOF(Invoice_getItemList(invoice.val)); item.next(); )
                  {
                     var ordered = item.val.ordered;
                     var amount = ordered * item.val.price;
                  
                     if (item.val.stockType in qtyMap) qtyMap[item.val.stockType] += ordered;
                     else                              qtyMap[item.val.stockType] =  ordered;
                     
                     if (item.val.stockType in amountMap) amountMap[item.val.stockType] += amount;
                     else                                 amountMap[item.val.stockType] =  amount;
                  }
               }
               
               var totalAmount = 0.0;
               var listPanel = this.find('list').removeAll();
               for (var stockType = eachOF(g_stockTypeList); stockType.next(); )
               {
                  if (stockType.val.id in qtyMap === false || stockType.val.id in amountMap === false)
                     continue;
                     
                  var queue = listPanel.addQueue().hpad(25).vpad(10);
                  queue.addTextBox().align('left' ).value(stockType.val.name);
                  queue.addTextBox().width(120).align('right').value(qtyMap[stockType.val.id]);
                  queue.addTextBox().width(120).align('right').value(amountMap[stockType.val.id].toFixed(1));
                  
                  totalAmount += amountMap[stockType.val.id];
               }

               this.find('totalAmount').value(totalAmount.toFixed(1));
               
               this.showMe();
               return this;
            };            
            
         });
         
         

         createForm('undeliveredReport').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).spacing(10).bgColor('CornflowerBlue').call(function()
            {
               this.addQueue().spacing(20).call(function()
               {
                  this.addButton('cancel').width(80).value('取消').click(function(b) { b.form('invoiceList').loadAndShow(); });
               });
            });
            
            
            this.addStack('list');
            
            
            this.addSpace().length(30);
            
            this.loadAndShow = function(invoiceList)
            {
               var listPanel = this.find('list').removeAll();
               for (var invoice = eachOF(invoiceList); invoice.next(); )
               {
                  if (Invoice_undeliveredQty(invoice.val) === 0)
                     continue;
                     
                  var rowPanel = listPanel.addQueue().hpad(25).vpad(10).spacing(10).data('invoice', invoice.val);
                  
                  rowPanel.addTextBox().valign('top').width(160).align('left').value(invoice.val.number);
                  
                  var itemPanel = rowPanel.addStack().valign('top');
                  for (var item = eachOF(Invoice_getItemList(invoice.val)); item.next(); )
                  {
                     if (item.val.undelivered === 0)
                        continue;
                        
                     itemPanel.addQueue().call(function()
                     {
                        this.addTextBox().value(StockTypeList_findByID(g_stockTypeList, item.val.stockType).name);
                        this.addTextBox().align('right').width(50).value(item.val.undelivered);
                     });
                  }
                  
                  rowPanel.addButton().valign('top').click(function(b) { b.form('invoiceDetail').loadAndShow(b.data('invoice')); } );
               }

               this.showMe();
               return this;
            };            
            
         });


         createForm('invoiceDetail').width(640).call(function()
         {                     
            this.addQueue('top').vpad(25).hpad(25).spacing(20).bgColor('CornflowerBlue').call(function(panel)
            {
               panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('invoiceList').loadAndShow(); });
               panel.addButton('delete').width(80).value('移除').click(function(b) { b.form().onDelete(); });
               panel.addButton('print' ).width(80).value('列印').click(function(b) { b.form().onPrint(); });
               panel.addButton('submit').width(80).value('儲存').click(function(b) { b.form().onSubmit(); });
               panel.addButton('next').width(80).value('<<').click(function(b) { b.form('invoiceList').nextInvoice(this.data('originalInvoice')); });
               panel.addButton('prev').width(80).value('>>').click(function(b) { b.form('invoiceList').prevInvoice(this.data('originalInvoice')); });
            });
            
            this.addStack('main').vpad(25).hpad(25).spacing(15).call(function(mainPanel)
            {
               mainPanel.addQueue().spacing(15).call(function(panel) 
               {
                  panel.addTextInput('number'  ).width(160).label('編號'   );
                  panel.addTextInput('datetime').width(200).label('日期時間').enable(false); 
               });
               
               mainPanel.addQueue().spacing(10).call(function(panel) 
               {
                  panel.addTextInput('customerName').label('客戶名稱');
                  panel.addButton().click(function(b)
                  {
                     var name = b.form().find('customerName').value();
                     createCustomerTable().data(b.form()).findAllByNameSegment(name, function(customerList, f) 
                     {
                        f.form('customerList').loadAndShow(customerList);
                     });
                  });
               });
               
               mainPanel.addQueue().spacing(10).call(function(panel) 
               {
                  panel.addTextInput('telephone').label('電話'); 
                  panel.addButton().click(function(b)
                  {
                     var telephone = b.form().find('telephone').value();
                     createCustomerTable().data(b.form()).findByTelephone(telephone, function(customer, f)
                     {
                        f.setCustomer(customer);
                     });
                  });
               });
               
               mainPanel.addQueue().spacing(10).call(function(panel) 
               {
                  panel.addTextInput('address').label('地址');
                  panel.addButton('selectAddress').click(function(b) 
                  { 
                     if (b.data('customer').addressList.length > 1)
                        b.form('addressList').loadAndShow(b.data('customer').addressList); 
                  });
               });

               mainPanel.addStack().spacing(20).label('項目').frame(true).call(function(stack)
               {
                  stack.addStack('itemList').spacing(10);
                  
                  stack.addQueue().call(function(panel)
                  {
                     panel.addButton('add').value('+').click(function(b) { b.form('invoiceItem').loadAndShow(); });
                     panel.addSpace('space').length(220 + 190);
                     panel.addTextBox('itemTotal').align('right').width(80);                     
                  });
                  
                  stack.addQueue().call(function(panel)
                  {
                     panel.addSpace('space').length(190);
                     panel.addTextBox(                   ).align('right' ).width(50).value('運費');
                     panel.addSpace().length(5);
                     panel.addTextInput('totalQty'       ).align('right' ).width(70).enable(false);
                     panel.addTextBox(                   ).align('center').width(30).value('X');
                     panel.addTextInput('shippingPerUnit').align('right' ).width(70).value('0.0').change(function(t) { t.form().updateAmountAndTotal(); });
                     panel.addTextBox(                   ).align('center').width(30).value('=');
                     panel.addTextInput('shipping'       ).align('right' ).width(80).enable(false);
                  });
                  
                  stack.addQueue().spacing(5).call(function(panel)
                  {
                     panel.addSpace('space').length(185);
                     panel.addTextBox(          ).align('right').width(70).value('回款');
                     panel.addTextInput('refund').align('right').width(80).change(function(t) { t.form().updateAmountAndTotal(); });                     
                     panel.addSpace().length(35);
                     panel.addTextBox(                ).align('right').width(50).value('總數');
                     panel.addTextInput('invoiceTotal').align('right').width(80).enable(false);                     
                  });
               });

               mainPanel.addQueue().spacing(10).call(function(panel)
               {
                  panel.addSelect('paymentType').width(180).label('付款方式');                  
                  panel.addTextInput('paymentDetail');
               });

               mainPanel.addQueue().spacing(10).call(function(panel)
               {
                  panel.addTextInput('remark').label('備註');                  
               });

               mainPanel.addQueue().spacing(10).call(function(panel)
               {
                  panel.addTextInput('user').label('經手人').enable(false);                  
               });
            });
                  
                  
                  
                  
            this.loadAndShow = function(originalInvoice)
            {
               var optionList = ['Cash', 'Bank(HSBC)', 'Bank(BOC)', 'Cheque(HSBC)', 'Cheque(BOC)'];
                  
               if (typeof originalInvoice === 'undefined') originalInvoice = null;
               this.data('originalInvoice', originalInvoice);
               
               var invoice = (originalInvoice !== null) ? originalInvoice : Invoice_empty();

               this.find('number').value((originalInvoice !== null) ? invoice.number : '');
               this.find('datetime').value((originalInvoice !== null) ? invoice.createDatetime : '');
               
               this.setCustomer(null);
               this.find('customerName').value(invoice.customerName);
               this.find('telephone').value(invoice.telephone);
               this.find('address').value(invoice.address);
               
               this.find('itemList').removeAll();
               
               var qty = 0;
               for (var item = eachOF(Invoice_getItemList(invoice)); item.next(); )
               {
                  this.addInvoiceItem(item.val);
                  qty += item.val.ordered;
               }
               
               var shippingPerUnit = (qty > 0) ? invoice.shipping / qty : 0;

               this.find('shippingPerUnit').value(shippingPerUnit.toFixed(1));
               this.find('refund').value(invoice.refund.toFixed(1));

               this.find('paymentType').clearOption().addOption(optionList).value(invoice.paymentType);
               this.find('paymentDetail').value(invoice.paymentDetail);
               this.find('remark').value(invoice.remark);

               if (originalInvoice === null)
               {
                  this.find('user').value('');
               }
               else
                  createUserTable().data(this).findByIdOrNull(invoice.user, function(user, f)
                  {
                     f.find('user').value((user !== null) ? user.name : '');
                  });
               
               var isShowButton = false;
               if (g_currentUser != null)
                  isShowButton = (g_currentUser.permission == 'admin' || g_currentUser.id == invoice.user);
                              
               this.find('submit').show(isShowButton || originalInvoice == null);
               this.find('delete').show(isShowButton && originalInvoice != null);
               
               this.updateAmountAndTotal();
               this.showMe();
            };
            
            
            this.updateAmountAndTotal = function()
            {      
               var total = 0.0;
               var totalQty = 0;

               var childList = this.find('itemList').childList;
               for (var i = 0; i < childList.length; i++)
               {
                  var ordered = childList[i].find('ordered').value().parseInt(0);
                  var price = childList[i].data('invoiceItem').price;
                  childList[i].find('pricePerUnit').value(price.toFixed(1));
                  childList[i].find('amount').value((ordered * price).toFixed(1));
                  
                  childList[i].data('invoiceItem').ordered = ordered;
                  total += ordered * price;
                  totalQty += ordered;
               }
               
               var shippingPerUnit = this.find('shippingPerUnit').value().parseFloat(0.0);
               var refund = this.find('refund').value().parseFloat(0.0);
               var invoiceTotal = total + (totalQty * shippingPerUnit) - refund;
               
               this.find('refund').value(refund.toFixed(1));
               this.find('shippingPerUnit').value(shippingPerUnit.toFixed(1));
               
               this.find('itemTotal'   ).value(total.toFixed(1));
               this.find('totalQty'    ).value(totalQty);
               this.find('shipping'    ).value((totalQty * shippingPerUnit).toFixed(1));
               this.find('invoiceTotal').value(invoiceTotal.toFixed(1));
               return this;
            };
            
            this.addInvoiceItem = function(item)
            {
               var queue = this.find('itemList').addQueue().call(function(panel)
               {
                  panel.addTextBox('stockType');
                  panel.addTextBox('pricePerUnit').width(80).align('right');
                  panel.addSpace().length(10);
                  panel.addButton('ordered').width(60).align('right').click(function(b) { b.value(b.value().parseInt(0) + 1); b.form().updateAmountAndTotal(); });
                  panel.addTextBox('amount').width(80).align('right');
                  panel.addSpace().length(10);
                  panel.addButton('detail').click(function(b) { b.form('invoiceItem').loadAndShow(b.parent.id, b.parent.data('invoiceItem')); });
               });
               
               return this.updateInvoiceItem(queue.id, item);
            };
            
            this.updateInvoiceItem = function(panelId, item)
            {
               this.find(panelId).data('invoiceItem', item);
               this.find(panelId).find('stockType').value(StockTypeList_findByID(g_stockTypeList, item.stockType).name);
               this.find(panelId).find('ordered'  ).value(item.ordered);               
               return this.updateAmountAndTotal();
            };
            
            this.removeInvoiceItem = function(panelId)
            {
               this.find(panelId).remove();
               return this.updateAmountAndTotal();
            };
            
            this.setCustomer = function(customer)
            {
               if (typeof customer === 'undefined') customer = null;
               if (customer === null) customer = { id:null, name:'', telephone:'', addressList:[] };
               this.data('customer', customer);
               
               this.find('customerName' ).value(customer.name);
               this.find('telephone'    ).value(customer.telephone);
               this.find('address'      ).value((customer.addressList.length > 0) ? customer.addressList[0] : '');
               this.find('selectAddress').show(customer.addressList.length > 1);
               return this;
            };
            
            this.setAddressIndex = function(index)
            {
               return this.find('address').value(this.data('customer').addressList[index]);
            };
            
            this.getInvoice = function()
            {
               var invoice = Invoice_empty();
               
               if (this.data('originalInvoice') !== null)
               {
                  invoice.id             = this.data('originalInvoice').id;
                  invoice.createDatetime = this.data('originalInvoice').createDatetime;
                  invoice.modifyDatetime = this.data('originalInvoice').modifyDatetime;
               }
                  
               invoice.number         = this.find('number').value();
               invoice.customerName   = this.find('customerName').value();
               invoice.telephone      = this.find('telephone').value();
               invoice.address        = this.find('address').value();
               
               var itemListFrame = this.find('itemList');
               for (var i = 0; i < itemListFrame.childList.length; i++)
                  Invoice_addItem(invoice, itemListFrame.childList[i].data('invoiceItem'));
               
               invoice.shipping = this.find('shipping').value().parseFloat(0.0).toFixed(1);
               invoice.refund   = this.find('refund').value().parseFloat(0.0).toFixed(1);
               
               invoice.paymentType   = this.find('paymentType').value();
               invoice.paymentDetail = this.find('paymentDetail').value();
               invoice.remark        = this.find('remark').value();
          
               return invoice;
            };
            
            this.onDelete = function()
            {
               if (this.data('originalInvoice') !== null)
                  if (confirm('這項記錄將會被移除。') === false)
                     return;
                  
               this.blackOut(true, function()
               {
                  var invoice = this.data('originalInvoice');
                  if (invoice !== null)
                  {
                     createInvoiceTable().data(this).deLete(invoice, function(n, f)
                     {  
                        alert('已移除。(' + invoice.number + ')');
                        f.blackOut(false, function() { this.form('invoiceList').loadAndShow(); });
                     });
                  }
               });
            };
            
            this.onPrint = function()
            {            
               var invoice = this.data('originalInvoice');               
               if (invoice !== null && Invoice_equal(invoice, this.getInvoice()))
               {
                  var period = invoice.createDatetime.front(4) + invoice.createDatetime.front(2,5);
                  window.open('page-invoice-print.php?period=' + period + '&id=' + invoice.id);
               }
               else
                  alert('請先儲存！');
            };
            
            this.onSubmit = function()
            {
               this.blackOut(true, function()
               {
                  var invoice = this.getInvoice();
                  createInvoiceTable().data(this).write(invoice, function(invoice, f)
                  {
                     alert('已儲存。(' + invoice.number + ')');
                     
                     f.blackOut(false, function() { this.loadAndShow(invoice); });
                     
                     // f.form('invoiceList').loadAndShow();
                  });
               });
            };
         });
         
         
         
         
         createForm('invoiceItem').width(640).call(function()
         {
            this.addQueue('top').vpad(25).hpad(25).spacing(20).bgColor('CornflowerBlue').call(function(queue)
            {
               queue.addButton('cancel').width(80).value('取消').click(function(b) { b.form('invoiceDetail').showMe(); });
               queue.addButton('remove').width(80).value('移除').click(function(b) { b.form('invoiceDetail').removeInvoiceItem(b.data('panelId')).showMe(); });
               queue.addButton('ok'    ).width(80).value('確定').click(function(b) { b.form().invoiceItemOk(); });
            });
            
            this.addStack('main').vpad(25).hpad(25).spacing(20).call(function(stack)
            {
               stack.addQueue().spacing(20).call(function(panel) 
               {
                  panel.addTextInput('ordered'    ).width(100).label('訂購');
                  panel.addTextInput('undelivered').width(100).label('欠貨');
                  panel.addTextInput('price'      ).width(100).label('售價');
               });
               
               stack.addStack('stockTypeList').spacing(10);
            });
            
            this.loadAndShow = function(panelId, item)
            {
               if (typeof panelId === 'undefined') panelId = null;
               if (typeof item === 'undefined') item = null;
               if (item === null) item = { stockType:null, ordered:1, undelivered:0, price:null };
               
               this.data('invoiceItem', item).data('panelId', panelId);
               this.find('ordered'    ).value(item.ordered);
               this.find('undelivered').value(item.undelivered);
               this.find('price'      ).value(item.price !== null ? item.price.toFixed(1) : '');
               
               var stockTypeListFrame = this.find('stockTypeList').removeAll();
               
               stockTypeListFrame.addQueue().call(function()
               {
                  this.addTextBox().value('貨品');
                  this.addTextBox().align('right').width(70).value('售價');
                  this.addTextBox().align('right').width(70).value('數量');
                  this.addSpace().length(10);
                  this.addButton().show(false);                  
               });
               
               for (var stockType = eachOF(g_stockTypeList); stockType.next(); )
               {
                  stockTypeListFrame.addQueue().data('stockType', stockType.val).call(function(panel)
                  {
                     var qty = panel.data('stockType').currentQty;
                     var color = (qty <= STOCKTYPE_QTY_THRESHOLD) ? 'red' : 'black';
                  
                     panel.addTextBox('name').color(color).value(panel.data('stockType').name);
                     panel.addTextBox(      ).color(color).align('right').width(70).value(panel.data('stockType').defPrice.toFixed(1));
                     panel.addTextBox(      ).color(color).align('right').width(70).value(qty);
                     panel.addSpace().length(10);
                     panel.addButton('select').click(function(b) { b.form().invoiceItemOk(b.parent.data('stockType').id); });
                  });
               }
               
               return this.showMe();
            };
            
            this.invoiceItemOk = function(stockType)
            {
               if (typeof stockType === 'undefined' || stockType === null)
               {
                  stockType = this.data('invoiceItem').stockType;
                  if (stockType === null)
                     return;
               }
               
               var invoiceItem = 
               {
                  stockType: stockType,
                  ordered: this.find('ordered').value(),
                  undelivered: this.find('undelivered').value(),
                  price: 0
               };
               
               if (this.find('price').value().length > 0)
                  invoiceItem.price = this.find('price').value().parseFloat(0.0);
               else
                  invoiceItem.price = StockTypeList_findByID(g_stockTypeList, stockType).defPrice;

               invoiceItem.price = invoiceItem.price.toFixed(1).parseFloat();
                  
               if (this.data('panelId') === null)
                  this.form('invoiceDetail').addInvoiceItem(invoiceItem);
               else
                  this.form('invoiceDetail').updateInvoiceItem(this.data('panelId'), invoiceItem);
               
               this.form('invoiceDetail').showMe();
            };
         });
         
         
           
         createForm('customerList').width(640).call(function(customerListForm)
         {
            this.addQueue('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(queue)
            {
               queue.addButton('cancel').width(80).value('取消').click(function(b) { b.form('invoiceDetail').showMe(); });
            });
            
            this.addStack('list').vpad(25).hpad(25).spacing(10);
            
            
            this.loadAndShow = function(customerList)
            {
               var listPanel = this.find('list').removeAll();
               
               for (var i = 0; i < customerList.length; i++)
               {
                  listPanel.addQueue().data('customer', customerList[i]).call(function(panel)
                  {
                     panel.addTextBox('name').value(panel.data('customer').name);
                     panel.addTextBox('telephone').width(130).value(panel.data('customer').telephone);
                     panel.addButton().click(function(b) { b.form('invoiceDetail').setCustomer(b.data('customer')).showMe(); });  
                  });
               }
               
               return this.showMe();
            };
         });
         

         createForm('addressList').width(640).call(function()
         {
            this.addQueue('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(queue)
            {
               queue.addButton('cancel').width(80).value('取消').click(function(b) { b.form('invoiceDetail').showMe(); });
            });
            
            this.addStack('list').vpad(25).hpad(25);
            
            this.loadAndShow = function(addressList)
            {
               var listPanel = this.find('list').removeAll();
               
               for (var i = 0; i < addressList.length; i++)
               {
                  listPanel.addQueue().data('index', i).data('address', addressList[i]).call(function(panel)
                  {
                     panel.addTextBox('address').value(panel.data('address'));
                     panel.addButton().click(function(b) { b.form('invoiceDetail').setAddressIndex(b.data('index')).show(true); });
                  });
               }
               
               return this.showMe();
            };
         });
         
         
         
         
         firstForm.loadAndShow();
         
         
         
      
         createSystem().getCurrentUser(function(user)
         {
            g_currentUser = user;
         });
      
         
      });
   
      
   </script>

</body>

</html>


         






