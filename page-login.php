<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>

   <title>存貨管理</title>   
   <meta name="viewport" content="width=450">

</head>


<body style="background-color:gray;">


   <?php
   
   String_test();
   Lizt_test();


   if (session_id() === '')
   {
      require_once("lib-dataDirectory.php");
      
      $dataDir = DataDirectory::createDef();
      
      $currentDatetime = Datetine::current();      
      $currentPeriod = $currentDatetime->period();



      $invocieFile1 = $dataDir->getInvoice($currentPeriod);
      $invocieFile2 = $dataDir->getInvoice($currentPeriod->prev());
      $stockTypeFile = $dataDir->getStockType();
      $customerFile = $dataDir->getCustomer();
      
      $cnt = 0;
      $cnt += $invocieFile1->all()->count();
      $cnt += $invocieFile2->all()->count();
      $cnt += $stockTypeFile->all()->count();
      $cnt += $customerFile->all()->count();
      
      
      $dataDir->close();
   }


   
   
   ?>


   <script>
   
      var cccccccc45436456 = <?php echo $cnt; ?>;

      $(document).ready(function()
      {
      
         createForm('login').width(450).bgColor('LightBlue').call(function()
         {
            this.addSpace().length(50);
            
            this.addStack().vpad(25).hpad(25).spacing(10).bgColor('CornflowerBlue').call(function(stack)
            {
               stack.addQueue().call(function(panel)
               {
                  panel.addSpace().length(50);
                  panel.addTextBox().value('名稱').width(100);
                  panel.addTextInput('username').width(100);
               });
               stack.addQueue().call(function(panel)
               {
                  panel.addSpace().length(50);
                  panel.addTextBox().value('密碼').width(100);
                  panel.addPassword('password').width(100);
               });
               stack.addQueue().call(function(queue)
               {               
                  queue.addSpace().length(250 - 35);
                  queue.addButton().click(function(b)
                  {
                     var username = b.form().find('username').value();
                     var password = b.form().find('password').value();
                     
                     createSystem().login(username, password, function(msg)
                     {
                        if (msg == 'Success!') backToMenu();
                        if (msg == 'Wrong Password!') alert('密碼錯誤!');
                        if (msg == 'Unknown User Name!') alert('名稱錯誤');
                     });
                  });
               });
            });
            
            this.addSpace().length(50);
            
            this.showMe();            
         });
         
      });

   </script>

</body>

</html>


         






