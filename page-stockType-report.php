<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>
   
   <title>存貨管理﹣貨物</title>
   <meta name="viewport" content="width=450">

</head>


<body style="background-color:gray;">
   
   <script>
   
      $(document).ready(function()
      {
      
      
      
         var firstForm = createForm('invoiceList').width(450).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).spacing(10).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('back').width(80).value('選單').click(function(b) { backToMenu(); });
                  panel.addButton('new' ).width(80).value('新增').click(function(b) { b.form('invoiceDetail').loadAndShow(); });
               });

               topPanel.addQueue().spacing(20).call(function(panel)
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
               });
            });

            this.addStack('list');
            
            
            
            this.showInvoiceList = function(invoiceList)
            {
               var amountMap = {};

               for (var invoice = eachOF(invoiceList); invoice.next(); )
               {
                  for (var item = eachOF(Invoice_getItemList(invoice)); item.next(); )
                  {
                     if (item.stockType in amountMap)
                        amountMap[item.stockType] += item.ordered * item.price;
                     else
                        amountMap[item.stockType] = item.ordered * item.price;
                  }
               }
               
               var listPanel = this.find('list').removeAll();
                              
               for (var stockType = eachOF(g_stockTypeList); stockType.next(); )
               {
                  amountMap[stockType.val.id] = 0;
               }
               
               
               this.showMe();
            };
            
            this.loadAndShow = function()
            {
               this.find('list').removeAll();

               createStockTypeTable().data(this).findAll(function(stockTypeList, form)
               {
                  g_stockTypeList = stockTypeList;
                  var year = form.find('year').value();
                  var month = form.find('month').value();    
                  
                  if (month.length > 0)
                     createInvoiceTable().data(form).findAll(year + month, function(invoiceList, f) { f.showInvoiceList(invoiceList); });
                  else
                     createInvoiceTable().data(form).findAllUndelivered(year, function(invoiceList, f) { f.showInvoiceList(invoiceList); });
               });
            }
            
         });      
      
      
      
         var firstForm = createForm('stockTypeList').width(450).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('back').width(80).value('選單').click(function(b) { backToMenu(); });
                  panel.addButton('new' ).width(80).value('新增').click(function(b) { b.form('stockTypeDetail').loadAndShow(); });
               });
            });

            this.addStack('list').vpad(10);
            
            
            this.loadAndShow = function()
            {
               createStockTypeTable().data(this).findAll(function(stockTypeList, form)
               {
                  var listPanel = form.find('list').removeAll();
                  
                  listPanel.addStack().hpad(25).vpad(10).call(function(stack)
                  {
                     stack.addQueue().call(function(panel)
                     {
                        panel.addTextBox('name').align('left' ).value('名稱');
                        panel.addTextBox(      ).align('right').value('價格').width(70);
                        panel.addTextBox(      ).align('right').value('數量').width(70);
                        panel.addSpace().width(10);
                        panel.addButton().show(false);
                     });
                  });
                  
                  for (var stockType = eachOF(stockTypeList); stockType.next(); )
                  {
                     listPanel.addQueue().hpad(25).vpad(10).data('stockType', stockType.val).call(function(panel)
                     {
                        var stockType = panel.data('stockType');
                        var color = (stockType.currentQty <= STOCKTYPE_QTY_THRESHOLD) ? 'red' : 'black';
                        panel.addTextBox('name'      ).color(color).value(stockType.name);
                        panel.addTextBox('defPrice'  ).color(color).align('right').width(70).value(stockType.defPrice);
                        panel.addTextBox('currentQty').color(color).align('right').width(70).value(stockType.currentQty);
                        panel.addSpace().width(10);
                        panel.addButton().click(function(b) { b.form('stockTypeDetail').loadAndShow(b.data('stockType')); } );
                     });
                  }
                  
                  form.showMe();
               });
               
               return this;               
            }
         });
         



         createForm('stockTypeDetail').width(450).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('stockTypeList').loadAndShow(); });
                  panel.addButton('submit').width(80).value('儲存').click(function(b) { b.form().onSubmit(); });
               });
            });
            
            this.addStack('main').vpad(25).hpad(25).spacing(20).call(function(mainPanel)
            {
               mainPanel.addQueue().call(function(panel) { panel.addTextInput('name'    ).label('名稱'   ); });
               mainPanel.addQueue().call(function(panel) { panel.addTextInput('defPrice').label('預計價格'); });
               
               mainPanel.addStack().label('出入').frame(true).call(function(ioPanel)
               {
                  ioPanel.addQueue().call(function(panel)
                  {
                     panel.addTextBox().width(150).align('left' ).value('月期');
                     panel.addTextBox().width(100).align('right').value('入');
                     panel.addTextBox().width(100).align('right').value('出');
                  });
               
                  ioPanel.addStack('ioList');
               });
            });
                  
                  
            this.loadAndShow = function(stockType)
            {
               if (typeof stockType === 'undefined') stockType = null;
               if (stockType === null) stockType = { id:null, name:'', defPrice:0, ioPeriodList:[] };
               
               this.data('stockType', stockType);
               this.find('name').value(stockType.name);
               this.find('defPrice').value(stockType.defPrice);
               
               var itemList = StockType_getItemList(stockType);
               this.find('ioList').removeAll();

               for (var i = itemList.length - 1; i >= 0; --i)
               {
                  this.find('ioList').addQueue().data('item', itemList[i]).call(function(panel)
                  {
                     var item = panel.data('item');
                     panel.addTextBox().width(150).align('left' ).value(item.period);
                     panel.addTextBox().width(100).align('right').value(item.invoiceIn + item.restockIn);
                     panel.addTextBox().width(100).align('right').value(item.invoiceOut + item.restockOut);
                  });
               }
               
               return this.showMe();
            };
            
            this.onSubmit = function()
            {
               var stockType = {};
               
               if (this.data('stockType').id !== null)
                  stockType.id = this.data('stockType').id;
                  
               stockType.name = this.find('name').value();
               stockType.defPrice = this.find('defPrice').value().parseInt(0);

               createStockTypeTable().data(this).write(stockType, function(id, f)
               {
                  alert('已儲存。');
                  f.form('stockTypeList').loadAndShow();               
               });
               
               return this;
            };
            
            
         });
         
         
         
                  
         
         firstForm.loadAndShow();
      });
   
      
   </script>

</body>

</html>


         






