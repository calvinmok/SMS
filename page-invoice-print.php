<?php session_start(); ?><!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>
         
</head>

<style>
</style>

<body ">
   <script>
   
   
   
   
   
   
   


var DocComponent_seq = 0;

function DocComponent_newNameSeq()
{
   return DocComponent_seq++;
}


function DocComponent_makeDivID(parent, name)
{
   if (parent === null)
      return name;
      
   return parent.id + '__' + name;
}




function DocComponent_extend(component)
{
   component.prototype.call = function(func)
   {
      func(this);
      return this;
   };

   component.prototype.DocComponent_init = function(parent, name, type)
   {
      this.parent = parent;
      this.type = type;
      this.name = name;
      this.id = DocComponent_makeDivID(parent, (name !== null) ? 'n'+ name : 'i' + DocComponent_newNameSeq());
      
      this.childList = [];
      this.__data = {};
   };

   component.prototype.form = function()
   {
      return (this.parent === null) ? this : this.parent.form();
   };
   component.prototype.doc = function()
   {
      return (this.parent === null) ? this : this.parent.doc();
   };
   
   component.prototype.__addChild = function(child)
   {
      this.childList.push(child);
      return child;
   };
   
   component.prototype.findChild = function(name)
   {
      for (var i = 0; i < this.childList.length; i++)
      {
         if (this.childList[i].name === name)
            return this.childList[i];
      }
      
      return null;
   };
   
   component.prototype.find = function(name)
   {
      for (var i = 0; i < this.childList.length; i++)
      {
         if (this.childList[i].name === name)
            return this.childList[i];
      }

      for (var i = 0; i < this.childList.length; i++)
      {
         var descendant = this.childList[i].find(name);
         if (descendant !== null)
            return descendant;
      }
      
      return null;
   };
   
   component.prototype.removeAll = function(name)
   {
      for (var i = this.childList.length - 1; i >= 0; --i)
         this.childList[i].remove();
         
      return this;
   };
   
   component.prototype.data = function(name, value)
   {   
      if (typeof value === 'undefined')
      {
         if (name in this.__data)
            return this.__data[name];
         
         if (this.parent !== null)
            return this.parent.data(name);
            
         return null;
      }

      this.__data[name] = value;
      return this;
   };
   

   
}




function DocSpace() { } DocComponent_extend(Space);

DocSpace.prototype.init = function(parent, name)
{
   this.DocComponent_init(parent, name, 'Space');

   parent.__addChildElement($('<div id="'+this.id+'" />'));
   $('#'+this.id).css('display', 'inline-block');
   $('#'+this.id).css('margin', '0');
   $('#'+this.id).css('padding', '0');
   $('#'+this.id).css('height', '1px');
   
   return this.width(0);
};

DocSpace.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   
   this.__width = width;   
   $('#'+this.id).css('width', width + 'px');
   return this;
};





function Document(name) { return this.init(name); } DocComponent_extend(Document);

Document.prototype.init = function(name)
{
   this.DocComponent_init(null, name, 'Document');

   $('body').append($('<div id="'+this.id+'" />'));

   return this.width(180);
};

Document.prototype.width = function(width)
{
   if (typeof width === 'undefined')
      return this.__width;
      
   this.__width = width;
   $('#'+this.id).css('width', width + 'mm');
   $('#'+this.id).css('margin', 'auto');
   $('#'+this.id).css('border', '0');
   return this;
};

Document.prototype.innerWidth = function() { return this.width(); };

Document.prototype.bgColor = function(color)
{
   $('#' + this.id).css('background-color', color);
   return this;
};

Document.prototype.__addChildElement = function(element)
{
   $('#'+this.id).append(element);
};

Document.prototype.addFlowPanel = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocFlowPanel().init(this, name));
};

Document.prototype.addPanel = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocPanel().init(this, name));
};




function DocFlowPanel() { } DocComponent_extend(DocFlowPanel);

DocFlowPanel.prototype.init = function(parent, name)
{
   this.DocComponent_init(parent, name, 'DocFlowPanel');

   parent.__addChildElement($('<div id="'+this.id+'" />'));
      
   $('#'+this.id).css('margin', '0');      
   $('#'+this.id).css('border', '0');      
   $('#'+this.id).css('padding', '0');      

   return this;
};

DocFlowPanel.prototype.__addChildElement = function(element)
{
   element.css('display', 'inline-block');
   element.css('vertical-align', 'bottom');   
   $('#'+this.id).append(element);
};   

DocFlowPanel.prototype.addPanel = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocPanel().init(this, name));
};

DocFlowPanel.prototype.innerWidth = function() { return this.parent.innerWidth(); };






function DocPanel() { } DocComponent_extend(DocPanel);

DocPanel.prototype.init = function(parent, name)
{
   this.DocComponent_init(parent, name, 'DocPanel');

   parent.__addChildElement($('<div id="'+this.id+'" />'));
      
   return this;
};

DocPanel.prototype.remove = function()
{
   $('#'+this.id).remove();

   for (var i = 0; i < this.parent.childList.length; i++)
   {
      if (this.parent.childList[i] === this)
      {
         this.parent.childList.splice(i, 1);
         break;
      }
   }
};

DocPanel.prototype.margin = function(value)
{
   $('#' + this.id).css('margin-bottom', value + 'mm');
   return this;
};

DocPanel.prototype.bgColor = function(color)
{
   $('#'+this.id).css('background-color', color);
   return this;
};

DocPanel.prototype.__addChildElement = function(element)
{
   $('#'+this.id).append(element);
};   

DocPanel.prototype.addPanel = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocPanel().init(this, name));
};

DocPanel.prototype.addSpace = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocSpace().init(this, name));
};

DocPanel.prototype.addText = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocText().init(this, name));
};

DocPanel.prototype.addImage = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new DocImage().init(this, name));
};

DocPanel.prototype.fillBy = function(name)
{
   var width = this.parent.innerWidth();
   var fillBy = null;
   
   for (var i = 0; i < this.childList.length; i++)
   {
      var child = this.childList[i];
      if (child.name === name)
         fillBy = child;
      else
         width -= child.width();
   }   

   if (fillBy !== null)
      fillBy.width(width);
   
   return this;
};

DocPanel.prototype.widthAvailable = function()
{
   var width = this.parent.innerWidth();
   
   for (var i = 0; i < this.childList.length; i++)
      width -= this.childList[i].width();
   
   return width;
};

DocPanel.prototype.innerWidth = function() { return this.parent.innerWidth(); };









function DocText() { } DocComponent_extend(DocText);

DocText.prototype.init = function(parent, name)
{
   this.DocComponent_init(parent, name, 'Text');
   
   this.__size = 5;
   this.__width = 100;

   parent.__addChildElement($('<div id="'+this.id+'" />'));
   
   $('#'+this.id).css('display', 'inline-block');
   $('#'+this.id).css('vertical-align', 'bottom');   
   $('#'+this.id).css('font-family', 'sans-serif');   

   return this.width(parent.widthAvailable()).size(5).align('<');
};
   
DocText.prototype.onWidthAndSize = function()
{
   if ('__size' in this === false) this.__size = 5;
   if ('__width' in this === false) this.__width = 10;
   if ('__borderTop' in this === false) this.__borderTop = 0;
   if ('__borderBottom' in this === false) this.__borderBottom = 0;

   if (this.__size === 4)
   {
      $('#'+this.id).css('width', (this.__width - 4) + 'mm');
      $('#'+this.id).css('font-size', '4mm');   
      $('#'+this.id).css('height', '4mm');
      $('#'+this.id).css('padding-top', '2mm');
      $('#'+this.id).css('padding-bottom', '2mm');
      $('#'+this.id).css('padding-left', '2mm');
      $('#'+this.id).css('padding-right', '2mm');
   }
   if (this.__size === 5)
   {
      $('#'+this.id).css('width', (this.__width - 4) + 'mm');
      $('#'+this.id).css('font-size', '6mm');   
      $('#'+this.id).css('height', '6mm');
      $('#'+this.id).css('padding-top', '2mm');
      $('#'+this.id).css('padding-bottom', '2mm');
      $('#'+this.id).css('padding-left', '2mm');
      $('#'+this.id).css('padding-right', '2mm');
   }
   if (this.__size === 6)
   {
      $('#'+this.id).css('width', (this.__width - 4) + 'mm');
      $('#'+this.id).css('font-size', '10mm');   
      $('#'+this.id).css('height', '10mm');
      $('#'+this.id).css('padding-top', '2mm');
      $('#'+this.id).css('padding-bottom', '2mm');
      $('#'+this.id).css('padding-left', '2mm');
      $('#'+this.id).css('padding-right', '2mm');
   }
   
   if (this.__borderTop == 1)
   {
      $('#'+this.id).css('padding-top', '1.5mm');
      $('#'+this.id).css('border-top', '0.5mm solid black');
   }
   if (this.__borderTop == 2)
   {
      $('#'+this.id).css('padding-top', '1mm');
      $('#'+this.id).css('border-top', '1mm double black');
   }
   if (this.__borderTop == 3)
   {
      $('#'+this.id).css('padding-top', '1mm');
      $('#'+this.id).css('border-top', '1mm solid black');
   }
   

   if (this.__borderBottom == 1)
   {
      $('#'+this.id).css('padding-bottom', '1.5mm');
      $('#'+this.id).css('border-bottom', '0.5mm solid black');
   }
   if (this.__borderBottom == 2)
   {
      $('#'+this.id).css('padding-bottom', '1mm');
      $('#'+this.id).css('border-bottom', '1mm double black');
   }
   if (this.__borderBottom == 3)
   {
      $('#'+this.id).css('padding-bottom', '1mm');
      $('#'+this.id).css('border-bottom', '1mm solid black');
   }
   
         
   return this;
};


DocText.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   this.__width = width;   
   return this.onWidthAndSize();
};


DocText.prototype.size = function(size)
{
   if (typeof size === 'undefined') return this.__size;
   this.__size = size;   
   return this.onWidthAndSize();
};

DocText.prototype.align = function(align)
{
   if      (align === '<') $('#' + this.id).css('text-align', 'left');
   else if (align === '>') $('#' + this.id).css('text-align', 'right');
   else $('#'+this.id).css('text-align', 'center');
   return this;
};

DocText.prototype.border = function(top, bottom)
{
   this.__borderTop = top;   
   this.__borderBottom = bottom;      
   return this.onWidthAndSize();
};


DocText.prototype.underline = function(underline)
{
   if (underline)
      $('#'+this.id).css('text-decoration', 'underline');
   else
      $('#'+this.id).css('text-decoration', 'none');

   return this;
};

DocText.prototype.color = function(color)
{
   $('#'+this.id).css('color', color);
   return this;
};

DocText.prototype.bgColor = function(color)
{
   $('#'+this.id).css('background-color', color);
   return this;
};

DocText.prototype.value = function(value)
{
   if (typeof value === 'undefined')
      return $('#' + this.id).text();

   $('#' + this.id).text(value);
   return this;
};

DocText.prototype.multiLine = function(multiLine)
{
   $('#' + this.id).html(multiLine.join('<br/>'));
   return this;
};

DocText.prototype.left = function()
{
   var result = 0;
   for (var i = 0; i < this.parent.childList.length; i++)
   {
      if (this.parent.childList[i] === this)
         return result;
      
      result += this.parent.childList[i].width();
   }

   return null;
};




function DocImage() { } DocComponent_extend(DocImage);

DocImage.prototype.outerID = function() { return DocComponent_makeDivID(this, 'cOuter'); },
DocImage.prototype.innerID = function() { return DocComponent_makeDivID(this, 'cInner'); },

DocImage.prototype.init = function(parent, name)
{
   this.DocComponent_init(parent, name, 'DocImage');

   var outerDiv = $('<div id="'+this.outerID()+'" />'); 
   outerDiv.append($('<img id="'+this.innerID()+'" />'));      
   parent.__addChildElement(outerDiv);
   
   return this.__resize();
};

DocImage.prototype.src = function(src)
{
   if (typeof src === 'undefined') return this.__src;
   this.__src = src;   
   $('#' + this.innerID()).attr('src', src);
   
   return this;
};

DocImage.prototype.__resize = function()
{ 
   if ('__width' in this === false) this.__width = 100;
   if ('__height' in this === false) this.__height = 100;
   if ('__topPadding' in this === false) this.__topPadding = 0;
   if ('__bottomPadding' in this === false) this.__bottomPadding = 0;
   
   
   $('#'+this.innerID()).css('width', this.__width + 'mm');
   $('#'+this.innerID()).css('height', this.__height + 'mm');
   $('#'+this.innerID()).css('margin', '0');
   $('#'+this.innerID()).css('padding', '0');
   $('#'+this.innerID()).css('border', '0');

   $('#'+this.outerID()).css('padding-top', this.__topPadding + 'mm');
   $('#'+this.outerID()).css('padding-bottom', this.__bottomPadding + 'mm');
   $('#'+this.outerID()).css('padding-left', '0');
   $('#'+this.outerID()).css('padding-right', '0');
   $('#'+this.outerID()).css('margin', '0');
   $('#'+this.outerID()).css('border', '0');
   
   return this;
}
   
DocImage.prototype.width = function(width)
{ 
   if (typeof width === 'undefined') return this.__width;
   this.__width = width;   
   return this.__resize();
};

DocImage.prototype.height = function(height)
{
   if (typeof height === 'undefined') return this.__height;
   this.__height = height;   
   return this.__resize();
};

DocImage.prototype.padding = function(top, bottom)
{
   this.__topPadding = top;   
   this.__bottomPadding = bottom;   
   return this.__resize();
};





   
   
   
   
   
   
   
   
   
   
      g_username = null;
      
      g_stockTypeList = [];
      
      $(document).ready(function()
      {
         var period = <?php echo (isset($_GET['period']) ? $_GET['period'] : null); ?>;
         if (period == null)
            return;

         var invoiceID = <?php echo (isset($_GET['id']) ? $_GET['id'] : null); ?>;
         if (invoiceID == null)
            return;
         
         systemGetCurrentUser(function(user)
         {  
            g_username = user;
            
            createStockTypeTable().findAll(function(stockTypeList)
            {
               g_stockTypeList = stockTypeList;
               
               createInvoiceTable().findByID(period, invoiceID, function(invoice)
               {
                  printInvoice(invoice, g_stockTypeList);
               });
            });
         });
      });      
      

      function printInvoice(invoice1, stockTypeList1)
      {
         new Document('invoice').data(0, invoice1).data('stockTypeList', stockTypeList1).call(function(doc)
         {
            var invoice = doc.data(0);
            var stockTypeList = doc.data('stockTypeList');
            
            
            doc.addFlowPanel().call(function(flowPanel)
            {
               flowPanel.addPanel().addText().width(15).value('');
               flowPanel.addPanel().call(function(panel)
               {
                  panel.addImage().src('logo.small.png').width(51).height(34);
               
               });
               flowPanel.addPanel().call(function(panel)
               {
                  panel.addPanel().addText().width(80).size(5).align('=').value('珀斯迪香港有限公司');
                  panel.addPanel().addText().width(80).size(5).align('=').value('Plasti Dip Hong Kong Ltd.');
                  panel.addPanel().addText().width(80).size(4).align('=').value('香港葵涌葵昌路8號萬泰中心403室');
                  panel.addPanel().addText().width(80).size(4).align('=').value('Tel:852-2419 8649　Fax:852-2614 3788');
               });
            });

            doc.addPanel().margin(0).call(function(panel) { panel.addText(); });

            doc.addPanel().margin(0).call(function(panel)
            {
               panel.addText().width(55).size(5).align('<').value('');
               panel.addText().width(70).size(6).align('=').value('發 Invoice 票');
               panel.addText().width(55).size(5).align('>').value('');
            });
            
            doc.addPanel().margin(10).call(function(panel)
            {
               var datetime = invoice.createDatetime.length > 16 ? invoice.createDatetime.substr(0, 16) : invoice.createDatetime
            
               panel.addText().width(25).align('>').size(4).value('　　編號︰');
               panel.addText('number').width(50).value(invoice.number);

               panel.addText().width(30).align('>').size(4).value('日期︰');
               panel.addText().width(55).value(datetime);

               panel.fillBy('number');
            });

            doc.addPanel().margin(0).call(function(panel)
            {
               panel.addText().width(25).align('>').size(4).value('客戶名稱︰');
               panel.addText('name').width(50).value(invoice.customerName);

               panel.addText().width(20).align('>').size(4).value('電話︰');
               panel.addText().width(55).value(invoice.telephone);

               panel.fillBy('name');
            });
            doc.addPanel().margin(10).call(function(panel)
            {
               panel.addText().width(25).align('>').size(4).value('　　地址︰');
               panel.addText('address').value(invoice.address);
               panel.fillBy('address');
            });
         
      
            doc.addPanel().margin(0).call(function(panel)
            {
               panel.addText().width(20);
               panel.addText().size(4).align('<').width(90).value('產品');
               panel.addText().size(4).align('>').width(30).value('數量');
               panel.addText().size(4).align('>').width(5).value('');
               panel.addText().size(4).align('>').width(30).value('金額');
            });
            
            doc.data('total', 0);
            var invoiceItemList = Invoice_getItemList(invoice);
            for (var i = 0; i < invoiceItemList.length; i++)
            {         
               doc.addPanel().margin(0).data(0, invoiceItemList[i]).call(function(panel)
               {
                  var item = panel.data(0);
                  var name = StockTypeList_findByID(panel.doc().data('stockTypeList'), item.stockType).name;
                  var amount = item.ordered * item.price;
                  
                  panel.addText().width(20);
                  panel.addText().align('<').width(90).value(name);
                  panel.addText().align('>').width(30).value(item.ordered);
                  panel.addText().align('>').width(5).value('');
                  panel.addText().align('>').width(30).value(amount.toFixed(1) );
                  
                  doc.data('total', doc.data('total') + amount);
               });
            }
            
            if (invoice.shipping > 0)
            {
               doc.addPanel().margin(0).data(0, invoice.shipping).call(function(panel)
               {
                  panel.addText().width(20);
                  panel.addText().align('<').width(90).value('運費');
                  panel.addText().align('>').width(30);
                  panel.addText().align('>').width(5);
                  panel.addText().align('>').width(30).value(panel.data(0).toFixed(1));
               });
               
               doc.data('total', doc.data('total') + invoice.shipping);
            }

            if (invoice.refund > 0)
            {
               doc.addPanel().margin(0).data(0, invoice.refund).call(function(panel)
               {
                  panel.addText().width(20);
                  panel.addText().align('<').width(90).value('回款');
                  panel.addText().align('>').width(30);
                  panel.addText().align('>').width(5);
                  panel.addText().align('>').width(30).value('(' + panel.data(0).toFixed(1) + ')');
               });
               
               doc.data('total', doc.data('total') - invoice.refund);
            }            
            
            doc.addPanel().margin(0).call(function(panel)
            {
               panel.addText().width(20);
               panel.addText().align('<').width(90);
               panel.addText().align('>').width(30);
               panel.addText().align('>').width(5);
               panel.addText().align('>').width(30).border(1, 2).value(doc.data('total').toFixed(1));
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
               panel.addText().width(55).align('<').size(5).value(g_username.name);

               panel.addText().width(25).align('>').size(4).value('簽收︰');
               panel.addText().width(55).align('<').size(5).value('_______________________');
            });      
            
            
         });

      };


$(document).ready(function()
{
   $('body').css('margin', '0');
   $('body').css('padding', '0');
   $('body').css('background-color', 'white');
});

      
   </script>

</body>

</html>


         






