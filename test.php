<!DOCTYPE html>
<html>


<head>



   <script src="jquery.min.js"></script>
   <script src="js-utility.js"></script>
   <script src="js-form.js"></script>
   <script src="js-table.js"></script>
   

   <?php include("html-head.php"); ?>
   
   <title> SMS </title>
   
   

</head>





<script>


   $(document).ready(function()
   {
   
      var invoice = 
      {
         customerName: 'JJF',
         telephone: '22222222',
         remark: 'fds dfsag dsag',
      };
      
      
      Invoice_addItem(invoice, 
      {
         stockType: 1,
         ordered: 3,
         undelivered: 0,
         price: 180
      });

      Invoice_addItem(invoice, 
      {
         stockType: 2,
         ordered: 5,
         undelivered: 3,
         price: 180
      });
      
      var stockType = 
      {
         id:1,
         name: 'Red',
         defPrice: 180
      };
      
      var restock =
      {
         remark: 'fdsfs',
         itemStockTypeList: [1],
         itemQtyList: [1]
          
      };
      
      Restock_addItem(restock, { stockType: 1, qty:3 });
      
      var customer = 
      {
         name:'john',
         telephone:'32432432'
      };
            
      $.ajax(
      {
         url: 'lib-ajax.php',   
         dataType: 'text',
         type: 'POST',      
               
         data :
         {
            table: '',
            action : 'login',
            
            value:
            {
               username: 'f',
               password: 'f'
            }
         },
         success : function(t)
         {
            $('pre').text(t);
         }
      });      

   
      
   });
   
   
   
   
   
</script>



<body >


<pre style="font-size:10px;">
</pre>



</body>

</html>


         






