<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>  

   <title>存貨管理﹣入貨</title>   
   <meta name="viewport" content="width=640">

</head>


<body style="background-color:gray;">
   
   <script>
   
      g_stockTypeList = null;
      
      
      $(document).ready(function()
      {
         var firstForm = createForm('restockList').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).spacing(20).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('back').width(80).value('選單').click(function(b) { backToMenu(); });
                  panel.addButton('new' ).width(80).value('新增').click(function(b) { b.form('restockDetail').loadAndShow(); });
               });
               
               topPanel.addQueue().call(function(panel)
               {
                  var year = <?php echo Period::current()->year(); ?>;
                  var yearSelect = panel.addSelect('year').label('搜尋年份').width(100).change(function(b) { b.form('restockList').loadAndShow(); });
                  for (var i = 0; i < 20; i++)
                     yearSelect.addOption(''+(year - i));
               });
            });

            this.addStack('list').vpad(25).hpad(25).spacing(30);


            this.loadAndShow = function()
            {
               this.find('list').removeAll();
               
               if (g_stockTypeList == null)
                  createStockTypeTable().data(this).findAll(function(stockTypeList, listForm)
                  {
                     g_stockTypeList = stockTypeList;
                     listForm.loadAllRestockAndShow();
                  });
               else
                  this.loadAllRestockAndShow();
            };
            
            
            this.loadAllRestockAndShow = function()
            {
               if (g_stockTypeList == null)
                  return;
                  
               createRestockTable().data(this).findAll(this.find('year').value(), function(restockList, form)
               {               
                  var listPanel = form.find('list');
                  
                  listPanel.addQueue().call(function(panel)
                  {
                     panel.addTextBox().value('日期 - 貨物');
                     panel.addTextBox().value('數量').width(60).align('right');
                  });
                  
                  for (var restock = eachOF(restockList); restock.next(); )
                  {
                     var restockPanel = listPanel.addStack().data('restock', restock.val);
                     
                     var numberPanel = restockPanel.addQueue();
                     numberPanel.addTextBox().value(restock.val.createDatetime);
                     numberPanel.addButton().click(function(b) { b.form('restockDetail').loadAndShow(b.parent.data('restock')); } );
                     
                     for (var i = 0; i < restock.val.itemStockTypeList.length; i++)
                     {
                        var stockTypeId = restock.val.itemStockTypeList[i];
                        var stockType = StockTypeList_findByID(g_stockTypeList, stockTypeId);
                        var qty = restock.val.itemQtyList[i];
                        
                        var itemPanel = restockPanel.addQueue();
                        itemPanel.addTextBox().value('-').width(20);
                        itemPanel.addTextBox().value(stockType.name);
                        itemPanel.addTextBox().value(qty).width(60).align('right');
                        itemPanel.addSpace().length(10);
                     }
                     
                  }
                  
                  form.showMe();            
               });
            };
            
         });
         
         createForm('restockDetail').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('restockList').loadAndShow(); });
                  panel.addButton('delete').width(80).value('移除').click(function(b) { b.form().onDelete(); });
                  panel.addButton('submit').width(80).value('儲存').click(function(b) { b.form().onSubmit(); });
               });
            });

            this.addStack('main').vpad(25).hpad(25).spacing(20).call(function(mainPanel)
            {
               mainPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addTextInput('createDatetime').width(200).label('建立').enable(false);               
               });
               
               mainPanel.addStack().label('項目').frame(true).spacing(10).call(function(itemPanel)
               {
                  itemPanel.addStack('ioList').spacing(10);
                  itemPanel.addQueue().call(function(panel)
                  {
                     panel.addButton('add').value('+').click(function(b) { b.form('restockDetail').addItem('').showMe(); });
                  });
               });

               mainPanel.addQueue().call(function(panel)
               {
                  panel.addTextInput('remark').label('備註');                  
               });
            });
            

            this.loadAndShow = function(restock)
            {
               if (typeof restock === 'undefined') restock = null;
               
               if (restock === null)
                  restock = { id: null, createDatetime:'', modifyDatetime:'', itemStockTypeList:[], remark:'' };
               
               this.data('restockID', restock.id);
               this.data('createDatetime', restock.createDatetime);
               
               this.find('createDatetime').value(restock.createDatetime);

               this.find('ioList').removeAll();
               for (var i = 0; i < restock.itemStockTypeList.length; i++)
                  this.addItem(restock.itemStockTypeList[i], restock.itemQtyList[i]);

               this.find('remark').value(restock.remark);     
               
               return this.showMe();
            };

            this.addItem = function(stockType, qty)
            {
               if (typeof stockType === 'undefined') stockType = '';
               if (typeof qty === 'undefined') qty = 0;
            
               this.find('ioList').addQueue().spacing(10).data('s', stockType).data('q', qty).call(function(panel)
               {
                  panel.addButton(           ).value('-').click(function(b) { b.parent.remove(); } );
                  panel.addSelect('stockType').addOption('').addOptionByList(g_stockTypeList, 'id', 'name').value(panel.data('s'));
                  panel.addTextInput('qty'   ).width(60).align('right').value(panel.data('q'));
               });
               
               return this;
            };

            this.onDelete = function()
            {
               if (this.data('restockID') != null)
                  if (confirm('這項記錄將會被移除。') === false)
                     return;
                     
               this.blackOut(true, function()
               {
                  var id = this.data('restockID');
                  if (id != null)
                  {
                     var createDatetime = this.data('createDatetime');
                     createRestockTable().data(this).deLete(createDatetime, id, function(restock, f)
                     {
                        alert('已移除。(' + restock.createDatetime + ')');
                        f.blackOut(false, function() { this.form('restockList').loadAndShow(); });
                     });
                  }
               });
            };
            
            this.onSubmit = function()
            {
               this.blackOut(true, function()
               {            
                  var restock = {};
                  if (this.data('restockID') != null) 
                  {
                     restock.id = this.data('restockID');
                     restock.createDatetime = this.find('createDatetime').value();
                  }
                  
                  restock.itemStockTypeList = [];
                  restock.itemQtyList = [];
                  
                  for (var panel = eachOF(this.find('ioList').childList); panel.next(); )
                  {
                     restock.itemStockTypeList.push(panel.val.find('stockType').value().parseInt());
                     restock.itemQtyList      .push(panel.val.find('qty'      ).value().parseInt(0));
                  }
                  
                  restock.remark = this.find('remark').value();

                  createRestockTable().data(this).write(restock, function(restock, f)
                  {
                     alert('已儲存。(' + restock.createDatetime + ')');
                     f.blackOut(false, function() { this.form('restockList').loadAndShow(); });
                  });
               });
               
               return this;
            };
                       
            
         });


         firstForm.loadAndShow();
      });
   
      
   </script>

</body>

</html>


         






