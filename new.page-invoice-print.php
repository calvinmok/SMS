<?php session_start(); ?><!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>
         
</head>

<style>
</style>

<body>
   <script>
   
      
   
   
   
      g_username = <?php echo "'" . $_SESSION['username'] . "'"; ?>;
      
      g_stockTypeList = [];
      
      $(document).ready(function()
      {
         var invoiceNumber = <?php echo (isset($_GET['number']) ? '"'.$_GET['number'].'"' : null); ?>;
         if (invoiceNumber == null)
            return;
            
         createStockTypeTable().findAll(function(stockTypeList)
         {
            g_stockTypeList = stockTypeList;
            
            createInvoiceTable().findByNumber(invoiceNumber, function(invoice)
            {
               printInvoice(invoice, g_stockTypeList);
            });
         });
      });      
      

      function printInvoice(argInvoice, argStockTypeList)
      {
         return createForm('invoice', 'mm').width(180).data('invoice', argInvoice).data('stockTypeList', argStockTypeList).call(function()
         {
            var invoice = this.data('invoice');
            var stockTypeList = this.data('stockTypeList');
            
            this.addQueue().call(function(queue)
            {
               queue.addImage().src('logo.small.png').width(60).height(40);

               queue.addPanel().call(function(panel)
               {
                  panel.addTextBox().width(120).height(12).align('center').value('珀斯迪香港有限公司');
                  panel.addTextBox().width(120).height(12).align('center').value('Plasti Dip Hong Kong Ltd.');
                  panel.addTextBox().width(120).height( 8).align('center').value('香港葵涌葵昌路8號萬泰中心403室');
                  panel.addTextBox().width(120).height( 8).align('center').value('Tel:852-2419 8649　Fax:852-2614 3788');
               });
            });

            this.addQueue().call(function(panel)
            {
               var invoice  = panel.data('invoice');
               var datetime = invoice.datetime.length > 16 ? invoice.datetime.substr(0, 16) : invoice.datetime
               panel.addTextBox().width(55).height(6).align('left'  ).value(invoice.number);
               panel.addTextBox().width(70).height(8).align('center').value('發 Invoice 票');
               panel.addTextBox().width(55).height(6).align('right' ).value(datetime);
            });

            this.addQueue().call(function(panel)
            {
               panel.addTextBox(           ).width(25).height(5).align('right').value('客戶名稱︰');
               panel.addTextBox('name'     ).width(50).height(6).align('left' ).value(panel.data('invoice').customerName);
               panel.addTextBox(           ).width(20).height(5).align('right').value('電話︰');
               panel.addTextBox('telephone').width(60).height(6).align('left' ).value(panel.data('invoice').telephone);
            });
            
            this.addQueue().call(function(panel)
            {
               panel.addTextBox(         ).height(5).width(25).align('right').value('　　地址︰');
               panel.addTextBox('address').height(6).value(panel.data('invoice').address);
            });
               
            this.addQueue().call(function(panel)
            {
               panel.addTextBox().width(20);
               panel.addTextBox().width(50).height(5).align('right').value('產品');
               panel.addTextBox().width(30).height(5).align('left' ).value('數量');
               panel.addTextBox().width(10).height(5).align('right').value('');
               panel.addTextBox().width(30).height(5).align('right').value('金額');
            });
            
            this.data('total', 0);
            
            for (var invoiceItem = eachOF(Invoice_getItemList(invoice)); invoiceItem.next(); )
            {         
               this.addPanel().data('invoiceItem', invoiceItem.val).call(function(panel)
               {
                  var item = panel.data(invoiceItem);
                  var name = StockTypeList_findByID(panel.doc().data('stockTypeList'), item.stockType).name;
                  
                  panel.addTextBox().width(20);
                  panel.addTextBox().width(50).height(5).align('left' ).value(name);
                  panel.addTextBox().width(30).height(5).align('right').value(item.ordered);
                  panel.addTextBox().width(10).height(5).align('right').value('');
                  panel.addTextBox().width(30).height(5).align('right').value(item.ordered * item.price);
                  
                  doc.data('total', doc.data('total') + (item.ordered * item.price));
               });
            }
            
            doc.addPanel().margin(0).call(function(panel)
            {
               panel.addText().width(20);
               panel.addText().width(50).height(5).align('left' );
               panel.addText().width(30).height(5).align('right');
               panel.addText().width(10).height(5).align('right');
               panel.addText().width(30).height(5).align('right').border(1, 2).value(doc.data('total'));
            });
                     
            doc.addPanel().margin(0).call(function(panel) { panel.addText(); });
            
            doc.addPanel().margin(5).call(function(panel)
            {         
               panel.addText().width(25).align('>').size(4).value('付款方式︰');
               panel.addText('paymentType').width(30).value(invoice.paymentType);
               panel.addText('paymentDetail').value(invoice.paymentDetail);
               panel.fillBy('paymentDetail');
            });      

            doc.addPanel().margin(5).call(function(panel)
            {         
               panel.addText().width(25).align('>').size(4).value('　　備註︰');
               panel.addText('remark').width(100).value(invoice.remark);
            });      


            doc.addPanel().margin(5).call(function(panel)
            {         
               panel.addText().width(25).align('<').size(4).value('　經手人︰');
               panel.addText().width(55).align('<').size(5).value(g_username);

               panel.addText().width(25).align('>').size(4).value('簽收︰');
               panel.addText().width(55).align('<').size(5).value('_______________________');
            });      
            
            
         });

      };
      
   </script>

</body>

</html>


         






