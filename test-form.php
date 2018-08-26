<!DOCTYPE html>
<html>

   <link href="main.css" rel="stylesheet" type="text/css">
         
   <script src="jquery.min.js"></script>
   <script src="utility.js"></script>
   <script src="company.js"></script>
   <script src="form.js"></script>
   
   


   <script>
      $(document).ready(function()
      {
         var invoiceNumber = <?php echo (isset($_GET['number']) ? '"'.$_GET['number'].'"' : null); ?>;
         if (invoiceNumber == null)
            return;

         ajaxGetInvoiceByNumber(
         {
            number: invoiceNumber,
            success: function(invoice)
            {
               ajaxGetStockTypeList(
               {
                  context: invoice,
                  success: function(stockTypeList, context)
                  {
                     printInvoice(context, stockTypeList);
                  }
               });
            }
         });      
      });
   </script>

<head>

   

   <?php include("html-head.php"); ?>
   
   <title> SMS </title>
   
   

</head>

<script>   
   
</script>



<body >



</body>

</html>


         






