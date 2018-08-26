<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>

   <title>存貨管理</title>   
   <meta name="viewport" content="width=450">
   
</head>


<body style="background-color:gray;">

   <script>
      $(document).ready(function()
      {
         createForm('menu').width(450).bgColor('CornflowerBlue').call(function()
         {
            createSystem().data(this).getCurrentUser(function(user, f)
            {
               f.find('restockPanel').show(user.permission === 'admin');
               f.find('userPanel').show(user.permission === 'admin');
               f.find('currentUser').addTextBox().align('left').value(user.code + ' : ' + user.name);
               f.find('currentUser').addButton().width(80).value('登出').click(function(b)
               {
                  createSystem().logout(function(msg)
                  {
                     if (msg == 'Success!') window.location.href = 'page-login.php';
                     else alert(msg);
                  });
               });
               
               f.showMe();            
            });

            this.addQueue('currentUser').vpad(25).hpad(25).bgColor('LightBlue');            
            this.addSpace().length(10);
                                                
            this.addQueue().vpad(25).hpad(25).call(function(panel)
            {
               panel.addSpace().length(100);
               panel.addButton('invoice').width(200).value('發票').click(function(b)
               {
                  window.location.href = 'page-invoice.php';
               });
            });

            this.addQueue('restockPanel').vpad(25).hpad(25).call(function(panel)
            {
               panel.addSpace().length(100);
               panel.addButton('restock').width(200).value('入貨').click(function(b)
               {
                  window.location.href = 'page-restock.php';
               });
            });

            this.addQueue().vpad(25).hpad(25).call(function(panel)
            {
               panel.addSpace().length(100);
               panel.addButton('stockType').width(200).value('貨品種類').click(function(b)
               {
                  window.location.href = 'page-stockType.php';
               });
            });

            this.addQueue().vpad(25).hpad(25).call(function(panel)
            {
               panel.addSpace().length(100);
               panel.addButton('customer').width(200).value('顧客').click(function(b)
               {
                  window.location.href = 'page-customer.php';
               });
            });
            
            this.addQueue('userPanel').show(false).vpad(25).hpad(25).call(function(panel)
            {
               panel.addSpace().length(100);
               panel.addButton('user').width(200).value('用戶').click(function(b)
               {
                  window.location.href = 'page-user.php';
               });
            });
                        

            
            this.showMe();
         });
      });

   </script>

</body>

</html>


         






