<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>
   
   <title>存貨管理﹣貨物</title>
   <meta name="viewport" content="width=640">

</head>


<body style="background-color:gray;">
   
   <script>
      
      
      g_currentUser = null;
      
            
      $(document).ready(function()
      {
         var firstForm = createForm('stockTypeList').width(640).call(function()
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
                        panel.addSpace().length(10);
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
                        panel.addTextBox('defPrice'  ).color(color).align('right').width(70).value(stockType.defPrice.toFixed(1));
                        panel.addTextBox('currentQty').color(color).align('right').width(70).value(stockType.currentQty);
                        panel.addSpace().length(10);
                        panel.addButton('detail').click(function(button)
                        {
                           if (g_currentUser.permission != 'admin')
                              return;
                              
                           createStockTypeTable().data(button).findByID(button.data('stockType').id, function(s, b)
                           {
                              b.form('stockTypeDetail').loadAndShow(s);
                           });
                        });
                     });
                  }
                  

                  form.showMe();
               });
            
               return this;               
            };
         });
         



         createForm('stockTypeDetail').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(topPanel)
            {
               topPanel.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('stockTypeList').loadAndShow(); });
                  panel.addButton('delete').width(80).value('移除').click(function(b) { b.form().onDelete(); });
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
               this.find('defPrice').value(stockType.defPrice.toFixed(1));
               
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
               
               var isShowButton = (g_currentUser.permission == 'admin');
               this.find('submit').show(isShowButton);
               this.find('delete').show(isShowButton && StockType_isAllInOutZero(stockType));
               
               return this.showMe();
            };
            
            this.onDelete = function()
            {
               if (this.data('stockType').id != null)
                  if (confirm('這項記錄將會被移除。') === false)
                     return;
                     
               this.blackOut(true, function() 
               {
                  var id = this.data('stockType').id;
                  if (id != null)
                  {
                     createStockTypeTable().data(this).deLete(id, function(_, f)
                     {
                        alert('已移除。');
                        f.blackOut(false, function() { this.form('stockTypeList').loadAndShow(); });
                     });
                  }
               });
            };            
            
            this.onSubmit = function()
            {
               this.blackOut(true, function() 
               {
                  var stockType = {};
                  
                  if (this.data('stockType').id !== null)
                     stockType.id = this.data('stockType').id;
                     
                  stockType.name = this.find('name').value();
                  stockType.defPrice = this.find('defPrice').value().parseFloat(0.0).toFixed(1);

                  createStockTypeTable().data(this).write(stockType, function(stockType, f)
                  {
                     alert('已儲存。');
                     f.blackOut(false, function() { this.form('stockTypeList').loadAndShow(); });         
                  });
               });
               
               return this;
            };
            
            
         });
                  
         
         
         createSystem().data(firstForm).getCurrentUser(function(user, f)
         {
            g_currentUser = user;
            
            var isShowButton = (g_currentUser.permission == 'admin');
            f.form('stockTypeList').find('new').show(isShowButton);
         });

         
         firstForm.loadAndShow();
         
         
         

               
      });
   
      
   </script>

</body>

</html>


         






