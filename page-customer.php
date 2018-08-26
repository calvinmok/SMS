<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>

   <title>存貨管理﹣顧客</title>   
   <meta name="viewport" content="width=640">

</head>

<body style="background-color:gray;">

   <script>
   
      $(document).ready(function()
      {
         var firstForm = createForm('customerList').width(640).call(function()
         {
            this.addQueue('top').spacing(20).vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(panel)
            {
               panel.addButton('back').width(80).value('選單').click(function(b) { backToMenu(); });
               panel.addButton('new' ).width(80).value('新增').click(function(b) { b.form('customerDetail').showCustomer(null); });
               
               panel.addSpace().length(30);

               panel.addButton('salesReport').width(80).value('銷售').show(false).click(function(b) { b.form('customerSalesReport').loadAndShow(); });
            });

            this.addStack('list').vpad(25).hpad(25).spacing(15);
            
            
            createSystem().data(this).getCurrentUser(function(user, f)
            {
               f.find('salesReport').show(user.permission === 'admin');
            });
            
            
            this.loadAndShow = function()
            {
               createCustomerTable().data(this).findAll(function(customerList, form)
               {
                  var listPanel = form.find('list').removeAll();

                  listPanel.addQueue().call(function(panel)
                  {
                     panel.addTextBox('name').value('顧客');
                     panel.addTextBox('telephone').width(120).value('電話');
                     panel.addButton('detail').show(false);
                  });
                  
                  for (var customer = eachOF(customerList); customer.next(); )
                  {
                     listPanel.addQueue().data('customer', customer.val).call(function(panel)
                     {
                        panel.addTextBox('name'     ).value(panel.data('customer').name);
                        panel.addTextBox('telephone').value(panel.data('customer').telephone).width(120);
                        panel.addButton ('detail'   ).click(function(b) { b.form('customerDetail').showCustomer(b.data('customer')); });
                     });
                  }
                  
                  form.showMe();         
               });
            };
            
         });
         
         createForm('customerDetail').width(640).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(stack)
            {
               stack.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('customerList').loadAndShow(); });
                  panel.addButton('delete').width(80).value('移除').click(function(b) { b.form().onDelete(); });
                  panel.addButton('submit').width(80).value('儲存').click(function(b) { b.form().onSubmit(); });
               });
            });

            this.addStack('main').spacing(20).vpad(25).hpad(25).call(function(mainPanel)
            {
               mainPanel.addQueue().call(function(panel)
               {
                  panel.addTextInput('name').label('名稱');                  
               });

               mainPanel.addQueue().call(function(panel)
               {
                  panel.addTextInput('telephone').label('電話');                  
               });
               
               mainPanel.addStack().spacing(10).label('地址').frame(true).call(function(stack)
               {
                  stack.addStack('addressList').spacing(10);      
                  stack.addQueue().call(function(panel)
                  {
                     panel.addButton('addAddress').value('+').click(function(b) { b.form().addAddress('').showMe(); });
                  });
               });
            });
            
            
            this.showCustomer = function(customer)
            {
               if (customer === null)
               {
                  this.data('customer.id', null);
                  this.find('name').value('');
                  this.find('telephone').value('');
                  this.find('addressList').removeAll();               
               }
               else
               {
                  this.data('customer.id', customer.id);
                  this.find('name').value(customer.name);
                  this.find('telephone').value(customer.telephone);

                  this.find('addressList').removeAll();
                  for (var i = 0; i < customer.addressList.length; i++)
                     this.addAddress(customer.addressList[i]);
               }
               
               return this.showMe();
            }
            
            this.onDelete = function()
            {            
               if (confirm('這項記錄將會被移除。') === false)
                  return;
               
               this.blackOut(true, function()
               {
                  createCustomerTable().data(this).deLete(this.data('customer.id'), function(id, f)
                  {
                     f.blackOut(false, function() { this.form('customerList').loadAndShow(); });
                  });
               });
            };
            
            this.addAddress = function(address)
            {
               this.find('addressList').addQueue().data('a', address).call(function(panel)
               {
                  panel.addButton().value('-').click(function(b) { b.parent.remove(); } );
                  panel.addSpace().width(10);
                  panel.addTextInput('address').value(panel.data('a'));
               });
               
               return this;
            };
            
            this.onSubmit = function()
            {
               this.blackOut(true, function()
               {
                  var customer = {};
                  if (this.data('customer.id') !== null) 
                     customer.id = this.data('customer.id');
                  
                  customer.name = this.find('name').value();
                  customer.telephone = this.find('telephone').value();
                  customer.addressList = [];
                  
                  for (var addressPanel = eachOF(this.find('addressList').childList); addressPanel.next(); )
                  {
                     var address = addressPanel.val.find('address').value();
                     if (address.length > 0)
                        customer.addressList.push(address);
                  }
                  
                  createCustomerTable().data(this).write(customer, function(id, f)
                  {
                     alert('已儲存。');
                     f.blackOut(false, function() { this.form('customerList').loadAndShow(); });
                  });
               });
            }
         });
         
         

         
         
         
         createForm('customerSalesReport').width(640).call(function()
         {
            this.addStack('top').width(640).vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(stack)
            {
               stack.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('customerList').loadAndShow(); });
                  
                  var yearSelect = panel.addSelect('year').width(100).change(function(s)
                  {
                     s.form().loadAndShowWithYear(s.value());
                  });
                  for (var y = 2013; y < 2033; y++)
                     yearSelect.addOption(''+y);
               });
            });
            
            this.addStack('main').spacing(30).vpad(20).hpad(20).call(function(mainPanel)
            {
            });
            
            
            this.loadAndShow = function()
            {
               var year = <?php echo Period::current()->year(); ?>;
               this.loadAndShowWithYear(year);
            };
            
            this.loadAndShowWithYear = function(year)
            {
               this.form('customerList').blackOut(true, function()
               {
                  createCustomerTable().data(this).salesReport(year, function(salesReport, form)
                  {
                     var customerListForm = form.form('customerSalesReport');
                     
                     customerListForm.find('main').removeAll();
                  
                     for (var customer = eachOF(salesReport.customer); customer.next(); )
                     {
                        var stack = customerListForm.find('main').addStack();
                        
                        var queue1 = stack.addQueue();
                        queue1.addTextBox('telephone').width(150).value(customer.val.telephone);
                        queue1.addTextBox('name').width(450).value(customer.val.name);
                                             
                        var queue2 = stack.addQueue().width(600).bgColor('LightGreen');
                        for (var month = eachOF(['1', '2', '3', '4', '5', '6']); month.next(); )
                        {
                           var sales = salesReport.sales[month.val.parseInt()][customer.val.telephone];
                           queue2.addTextBox(month.val).align('right').width(95).value(sales);
                        }
                        queue2.addSpace().length(30);
                        
                        var queue3 = stack.addQueue().width(600).bgColor('LightPink');
                        for (var month = eachOF(['7', '8', '9', '10', '11', '12']); month.next(); )
                        {
                           var sales = salesReport.sales[month.val.parseInt()][customer.val.telephone];
                           queue3.addTextBox(month.val).align('right').width(95).value(sales);
                        }
                        queue3.addSpace().length(30);

                     }
                     
                     
                     form.form('customerList').blackOut(false, function()
                     {
                        this.form('customerSalesReport').showMe();
                     });
                     
                     
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


         






