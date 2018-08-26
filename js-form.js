

$(document).ready(function()
{
   $('body').css('margin', '0');
   $('body').css('padding', '0');
});



var Component_seq = 0;

function Component_newNameSeq()
{
   return Component_seq++;
}


function Component_makeDivID(parent, name)
{
   if (parent === null)
      return name;
      
   return parent.id + '__' + name;
}




function Component_extend(component)
{
   component.prototype.call = function(func)
   {
      func(this);
      return this;
   };

   component.prototype.Component_init = function(parent, name, type)
   {
      this.parent = parent;
      this.type = type;
      this.name = name;
      this.id = Component_makeDivID(parent, (name !== null) ? 'n'+ name : 'i' + Component_newNameSeq());
      
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





function Space() { } Component_extend(Space);

Space.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'Space');

   parent.__addChildElement($('<div id="'+this.id+'" />'));
   $('#'+this.id).css('display', 'inline-block');
   $('#'+this.id).css('margin', '0');
   $('#'+this.id).css('padding', '0');
   $('#'+this.id).css('height', '1px');
   
   return this.width(0);
};

Space.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   
   this.__width = width;   
   $('#'+this.id).css('width', width + 'px');
   return this;
};




function VSpace() { } Component_extend(VSpace);

VSpace.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'VSpace');

   parent.__addChildElement($('<div id="'+this.id+'" />'));
   $('#'+this.id).css('margin', '0');
   $('#'+this.id).css('padding', '0');
   $('#'+this.id).css('width', '1px');
   
   return this.height(0);
};

VSpace.prototype.height = function(height)
{
   if (typeof height === 'undefined') return this.__height;
   
   this.__height = height;   
   $('#'+this.id).css('height', height + 'px');
   return this;
};




function TextBox() { } Component_extend(TextBox);

TextBox.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'TextBox');

   parent.__addChildElement($('<div id="'+this.id+'" />'));
   
   $('#'+this.id).css('display', 'inline-block');
   $('#'+this.id).css('vertical-align', 'bottom');   
   $('#'+this.id).css('font-size', '20px');   
   $('#'+this.id).css('font-family', 'sans-serif');   

   return this.width(parent.widthAvailable()).align('<');
};
   
TextBox.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   
   this.__width = width;   
   $('#'+this.id).css('width', (width - 10) + 'px');
   $('#'+this.id).css('height', '25px');
   $('#'+this.id).css('padding', '5px');
   return this;
};
   
TextBox.prototype.align = function(align)
{
   if      (align === '<') $('#' + this.id).css('text-align', 'left');
   else if (align === '>') $('#' + this.id).css('text-align', 'right');
   else $('#'+this.id).css('text-align', 'center');
   return this;
};

TextBox.prototype.bgColor = function(color)
{
   $('#'+this.id).css('background-color', color);
   return this;
};

TextBox.prototype.value = function(value)
{
   if (typeof value === 'undefined')
      return $('#' + this.id).text();

   $('#' + this.id).text(value);
   return this;
};

TextBox.prototype.valueHTML = function(value)
{
   if (typeof value === 'undefined')
      return $('#' + this.id).html();

   $('#'+this.id).html(value);
   return this;
};

TextBox.prototype.multiLine = function(multiLine)
{
   $('#' + this.id).html(multiLine.join('<br/>'));
   return this;
};

TextBox.prototype.left = function()
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







function TextInput() { } Component_extend(TextInput);

TextInput.prototype.outerID = function() { return Component_makeDivID(this, 'cOuter'); },
TextInput.prototype.labelID = function() { return Component_makeDivID(this, 'cLabel'); },
TextInput.prototype.innerID = function() { return Component_makeDivID(this, 'cInner'); },

TextInput.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'TextInput');

   var outerDiv = $('<div class="IB" id="'+this.outerID()+'" />');
   outerDiv.append($('<div id="'+this.labelID()+'" />'));
   
   if (name === 'password')   
      outerDiv.append($('<input type="password" id="'+this.innerID()+'" />'));
   else
      outerDiv.append($('<input type="text" id="'+this.innerID()+'" />'));
   
   parent.__addChildElement(outerDiv);

   $('#'+this.innerID()).data('__TextInput', this);

   $('#'+this.outerID()).css('display', 'inline-block');
   $('#'+this.outerID()).css('vertical-align', 'bottom');   

   $('#'+this.labelID()).css('color', 'green');
   $('#'+this.labelID()).css('font-size', '15px');

   $('#'+this.innerID()).css('font-size', '20px');   
   $('#'+this.innerID()).css('font-family', 'sans-serif');   

   return this.label(null).width(parent.widthAvailable()).align('<').bgColor('white');
};

TextInput.prototype.label = function(value)
{
   if (typeof value === 'undefined')
      return this.__label;
   
   this.__label = value;
   
   if (value === null)
   {
      $('#'+this.labelID()).hide();
      $('#'+this.labelID()).text('');
   }
   else
   {
      $('#'+this.labelID()).show();
      $('#'+this.labelID()).text(value);
   }
   
   return this;
};
   
TextInput.prototype.bgColor = function(color)
{
   $('#' + this.innerID()).css('background-color', color);
   return this;
};
   
TextInput.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   
   this.__width = width;   
   
   $('#'+this.outerID()).css('padding', '0');

   $('#'+this.labelID()).css('width', width + 'px');
   $('#'+this.labelID()).css('padding', '0 0 3px 0');
   $('#'+this.labelID()).css('height', '15px');

   $('#'+this.innerID()).css('width', (width - 10) + 'px');
   $('#'+this.innerID()).css('height', '25px');
   $('#'+this.innerID()).css('margin', '0');
   $('#'+this.innerID()).css('border', '0');
   $('#'+this.innerID()).css('padding', '5px');

   return this;
};

TextInput.prototype.align = function(align)
{
   if      (align === '<') $('#'+this.innerID()).css('text-align', 'left');
   else if (align === '>') $('#'+this.innerID()).css('text-align', 'right');
   else $('#'+this.innerID()).css('text-align', 'center');
   return this;
};

TextInput.prototype.value = function(value)
{
   if (typeof value === 'undefined')
      return $('#'+this.innerID()).val();

   $('#'+this.innerID()).val(value);
   return this;
};

TextInput.prototype.enable = function(enable)
{
   if (typeof enable === 'undefined') return this.__enable;
   
   this.__enable = enable;  
       
   $('#'+this.innerID()).prop('disabled', enable === false);
   
   return this;
};

TextInput.prototype.left = function()
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

TextInput.prototype.change = function(change)
{
   $('#'+this.innerID()).change(function()
   {
      change($(this).data('__TextInput'));
   });
   return this;   
};




function Button() { } Component_extend(Button);

Button.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'Button');
   
   parent.__addChildElement($('<div class="IB" id="'+this.id+'" />'));
   $('#'+this.id).data('__Button', this);
   $('#'+this.id).css('display', 'inline-block');
   $('#'+this.id).css('vertical-align', 'bottom');   
   $('#'+this.id).css('font-size', '20px');   
   $('#'+this.id).css('font-family', 'sans-serif');   
   $('#'+this.id).css('cursor', 'pointer');   

   return this.width(35).align('=').bgColor('Khaki').valueHTML('&raquo;');
};

Button.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   
   this.__width = width;   
   $('#'+this.id).css('width', (width - 10) + 'px');
   $('#'+this.id).css('height', '25px');
   $('#'+this.id).css('margin', '0');
   $('#'+this.id).css('border', '2px solid black');
   $('#'+this.id).css('padding', '3px');
   $('#'+this.id).css('border-radius', '10px');
   return this;
};

Button.prototype.bgColor = function(color)
{
   $('#'+this.id).css('background-color', color);
   return this;
};

Button.prototype.align = function(align)
{
   if      (align === '<') $('#' + this.id).css('text-align', 'left');
   else if (align === '>') $('#' + this.id).css('text-align', 'right');
   else $('#' + this.id).css('text-align', 'center');
   return this;
};
   
Button.prototype.value = function(value)
{
   if (typeof value === 'undefined')
      return $('#' + this.id).text();
   
   $('#'+this.id).text(value);
   return this;
};

Button.prototype.valueHTML = function(value)
{
   if (typeof value === 'undefined')
      return $('#' + this.id).html();

   $('#'+this.id).html(value);
   return this;
};

Button.prototype.shown = function(shown)
{
   if (shown)
      $('#'+this.id).show();
   else
      $('#'+this.id).hide();
      
   return this;
}

Button.prototype.click = function(click)
{
   $('#'+this.id).click(function()
   {
      click($(this).data('__Button'));
   });
   return this;   
};

Button.prototype.left = function()
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






function Select() { } Component_extend(Select);

Select.prototype.outerID = function() { return Component_makeDivID(this, 'cOuter'); },
Select.prototype.labelID = function() { return Component_makeDivID(this, 'cLabel'); },
Select.prototype.innerID = function() { return Component_makeDivID(this, 'cInner'); },

Select.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'Select');
   
   var outerDiv = $('<div class="IB" id="'+this.outerID()+'" />');
   outerDiv.append($('<div id="'+this.labelID()+'" />'));
   outerDiv.append($('<select id="'+this.innerID()+'" />'));
   parent.__addChildElement(outerDiv);

   $('#'+this.innerID()).data('__Select', this);

   $('#'+this.outerID()).css('display', 'inline-block');
   $('#'+this.outerID()).css('vertical-align', 'bottom');   

   $('#'+this.labelID()).css('color', 'green');
   $('#'+this.labelID()).css('font-size', '15px');

   $('#'+this.innerID()).css('font-size', '20px');   
   $('#'+this.innerID()).css('font-family', 'sans-serif');   
   
   return this.label(null).width(parent.widthAvailable());
};

Select.prototype.label = function(value)
{
   if (typeof value === 'undefined')
      return this.__label;
   
   this.__label = value;
   
   if (value === null)
   {
      $('#'+this.labelID()).hide();
      $('#'+this.labelID()).text('');
   }
   else
   {
      $('#'+this.labelID()).show();
      $('#'+this.labelID()).text(value);
   }
   
   return this;
};

Select.prototype.add = function(value, name)
{
   if (typeof name === 'undefined') name = value;

   if ($.isArray(value))
   {
      for (var i = 0; i < value.length; i++)
         this.add(value[i], name[i]);
   }
   else
   {
      $('#'+this.innerID()).append('<option value="'+value+'" >'+name+'</option>');
   }
   
   return this;
};

Select.prototype.addList = function(objectList, nameKey, valueKey)
{
   for (var i = 0; i < objectList.length; i++)
      this.add(objectList[i][valueKey], objectList[i][nameKey]);
   return this;
};

Select.prototype.clearOption = function()
{
   $('#'+this.innerID()).children().remove();
   return this;
};

Select.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   
   this.__width = width;   
   
   $('#'+this.outerID()).css('padding', '0');

   $('#'+this.labelID()).css('width', width + 'px');
   $('#'+this.labelID()).css('padding', '0 0 3px 0');
   $('#'+this.labelID()).css('height', '15px');

   $('#'+this.innerID()).css('width', (width - 10) + 'px');
   $('#'+this.innerID()).css('height', '35px');
   $('#'+this.innerID()).css('margin', '0');
   $('#'+this.innerID()).css('border', '0');
   
   return this;
};

Select.prototype.value = function(value)
{
   if (typeof value === 'undefined')
      return $('#'+this.innerID()).val();
      
   var optionList = $.map($('#'+this.innerID()+' option'), function(option) { return option.value; });
   for (var i = 0; i < optionList.length; i++)
   {
      if (optionList[i] === value)
      {
         $('#'+this.innerID()).val(value);
         return this;      
      }
   }
   
   this.add(value);
   $('#'+this.innerID()).val(value);
   
   return this;
};

Select.prototype.change = function(change)
{
   $('#'+this.innerID()).change(function()
   {
      change($(this).data('__Select'));
   });
   return this;   
};





function Panel() { } Component_extend(Panel);

Panel.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'Panel');

   parent.__addChildElement($('<div class="IBC" id="'+this.id+'" />'));
      
   return this;
};

Panel.prototype.remove = function()
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

Panel.prototype.margin = function(value)
{
   $('#' + this.id).css('margin-bottom', value + 'px');
   return this;
};

Panel.prototype.bgColor = function(color)
{
   $('#'+this.id).css('background-color', color);
   return this;
};

Panel.prototype.__addChildElement = function(element)
{
   $('#'+this.id).append(element);
};   

Panel.prototype.addSpace = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Space().init(this, name));
};

Panel.prototype.addTextBox = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new TextBox().init(this, name));
};

Panel.prototype.addTextInput = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new TextInput().init(this, name));
};

Panel.prototype.addButton = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Button().init(this, name));
};

Panel.prototype.addSelect = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Select().init(this, name));
};

Panel.prototype.fillBy = function(name)
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

Panel.prototype.widthAvailable = function()
{
   var width = this.parent.innerWidth();
   
   for (var i = 0; i < this.childList.length; i++)
      width -= this.childList[i].width();
   
   return width;
};








function Frame() { } Component_extend(Frame);

Frame.prototype.outerID = function() { return Component_makeDivID(this, 'cOuter'); },
Frame.prototype.labelID = function() { return Component_makeDivID(this, 'cLabel'); },
Frame.prototype.innerID = function() { return Component_makeDivID(this, 'cInner'); },

Frame.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'Frame');

   var outerDiv = $('<div id="'+this.outerID()+'" />'); 
   outerDiv.append($('<div id="'+this.labelID()+'" />'));
   outerDiv.append($('<div id="'+this.innerID()+'" />'));      
   parent.__addChildElement(outerDiv);
   
   $('#'+this.labelID()).css('color', 'green');
   $('#'+this.labelID()).css('font-size', '15px');
  
   return this.margin(0).borderWidthPadding(false, parent.innerWidth(), 0, 0, 0, 0).label(null);
};

Frame.prototype.remove = function()
{
   $('#'+this.outerID()).remove();

   for (var i = 0; i < this.parent.childList.length; i++)
   {
      if (this.parent.childList[i] === this)
      {
         this.parent.childList.splice(i, 1);
         break;
      }
   }   
};

Frame.prototype.label = function(value)
{
   if (typeof value === 'undefined')
      return this.__label;
   
   this.__label = value;
   
   if (value === null)
   {
      $('#'+this.labelID()).hide();
      $('#'+this.labelID()).text('');
   }
   else
   {
      $('#'+this.labelID()).show();
      $('#'+this.labelID()).text(value);
   }
   
   return this;
};

Frame.prototype.border = function(border)
{
   if (typeof border === 'undefined') return this.__border;
   return this.borderWidthPadding(border, this.__width, null, null, null, null);
};

Frame.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   return this.borderWidthPadding(this.__border, width, null, null, null, null);
};

Frame.prototype.padding = function(vertical, horizontal)
{
   if (typeof horizontal === 'undefined') horizontal = vertical;
   return this.borderWidthPadding(this.__border, this.__width, vertical, vertical, horizontal, horizontal);
};

Frame.prototype.vpad = function(top, bottom)
{
   if (typeof bottom === 'undefined') bottom = top;
   return this.borderWidthPadding(this.__border, this.__width, top, bottom, null, null);
};

Frame.prototype.hpad = function(left, right)
{
   if (typeof right === 'undefined') right = left;
   return this.borderWidthPadding(this.__border, this.__width, null, null, left, right);
};

Frame.prototype.borderWidthPadding = function(border, width, pt, pb, pl, pr)
{
   this.__border = border;
   this.__width = width;
   if (pt !== null) this.__paddingTop = pt;
   if (pb !== null) this.__paddingBottom = pb;
   if (pl !== null) this.__paddingLeft = pl;
   if (pr !== null) this.__paddingRight = pr;
   
   var labelWidth = this.__width - this.__paddingLeft - this.__paddingRight;
   
   $('#'+this.outerID()).css('width', labelWidth + 'px');
   $('#'+this.outerID()).css('margin-top', '0');
   $('#'+this.outerID()).css('margin-bottom', this.__margin + 'px');
   $('#'+this.outerID()).css('margin-left', '0');
   $('#'+this.outerID()).css('margin-right', '0');
   
   $('#'+this.outerID()).css('border', '0');

   $('#'+this.outerID()).css('padding-top', this.__paddingTop + 'px');
   $('#'+this.outerID()).css('padding-bottom', this.__paddingBottom + 'px');
   $('#'+this.outerID()).css('padding-left', this.__paddingLeft + 'px');
   $('#'+this.outerID()).css('padding-right', this.__paddingRight + 'px');
   
   $('#'+this.labelID()).css('width', labelWidth + 'px');
   $('#'+this.labelID()).css('padding', '0 0 3px 0');

   if (this.__border === true)
   {
      $('#'+this.innerID()).css('border', '1px solid black');
      $('#'+this.innerID()).css('padding', '9px');
      $('#'+this.innerID()).css('border-radius', '10px');
      this.__innerWidth = labelWidth - 20;
   }
   else
   {
      $('#'+this.innerID()).css('border', '0');
      $('#'+this.innerID()).css('padding', '0');
      $('#'+this.innerID()).css('border-radius', '0');      
      this.__innerWidth = labelWidth;
   }
   
   $('#'+this.innerID()).css('width', this.__innerWidth + 'px');

   return this;
};

Frame.prototype.innerWidth = function() { return this.__innerWidth; };

Frame.prototype.margin = function(margin)
{
   if (typeof margin === 'undefined') return this.__margin;

   this.__margin = margin;
   $('#'+this.outerID()).css('margin-bottom', margin + 'px');
   return this;
};

Frame.prototype.bgColor = function(color)
{
   $('#'+this.outerID()).css('background-color', color);
   return this;
};

Frame.prototype.hide = function() { $('#'+this.outerID()).hide(); return this; };
Frame.prototype.show = function() { $('#'+this.outerID()).show(); return this; };

Frame.prototype.__addChildElement = function(element)
{
   $('#'+this.innerID()).append(element);
};
   
Frame.prototype.addPanel = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Panel().init(this, name));
};

Frame.prototype.addFrame = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Frame().init(this, name));
};

Frame.prototype.addVSpace = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new VSpace().init(this, name));
};







var Form_globalList = [];

function form(name)
{
   for (var i = 0; i < Form_globalList.length; i++)
      if (Form_globalList[i].name === name)
         return Form_globalList[i];
         
   return null;
}


function Form(name) { return this.init(name); } Component_extend(Form);

Form.prototype.init = function(name)
{
   Form_globalList.push(this);

   this.Component_init(null, name, 'Form');

   $('body').append($('<div id="'+this.id+'" />'));
   
   return this.width(450).bgColor('LightBlue').hide();
};

Form.prototype.width = function(width)
{
   if (typeof width === 'undefined')
      return this.__width;
      
   this.__width = width;
   $('#'+this.id).css('width', width + 'px');
   $('#'+this.id).css('margin', 'auto');
   $('#'+this.id).css('border', '0');
   return this;
};

Form.prototype.innerWidth = function() { return this.width(); };

Form.prototype.bgColor = function(color)
{
   $('#' + this.id).css('background-color', color);
   return this;
};

Form.prototype.hide = function() { $('#'+this.id).hide(); return this; };

Form.prototype.show = function() 
{
   for (var i = 0; i < Form_globalList.length; i++)
      Form_globalList[i].hide();
   
   $('#'+this.id).show();
   return this; 
};

Form.prototype.__addChildElement = function(element)
{
   $('#'+this.id).append(element);
};

Form.prototype.addFrame = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Frame().init(this, name));
};
   
Form.prototype.addPanel = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Panel().init(this, name));
};

Form.prototype.addVSpace = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new VSpace().init(this, name));
};


Form.prototype.invoiceTable = function()
{
   if ('__invoiceTable' in this === false)
      this.__invoiceTable = new InvoiceTable().init(this);
   return this.__invoiceTable;
}

Form.prototype.restockTable = function()
{
   if ('__restockTable' in this === false)
      this.__restockTable = new RestockTable().init(this);
   return this.__restockTable;
}

Form.prototype.stockTypeTable = function()
{
   if ('__stockTypeTable' in this === false)
      this.__stockTypeTable = new StockTypeTable().init(this);
   return this.__stockTypeTable;
}

Form.prototype.customerTable = function()
{
   if ('__customerTable' in this === false)
      this.__customerTable = new CustomerTable().init(this);
   return this.__customerTable;
}

Form.prototype.systemLogin = function(username, password, callback)
{
   $.ajax(
   {
      url: AJAX_URL,   
      dataType: 'json',
      type: 'POST',      
      
      context : { form: this, callback: callback },
      data : 
      {
         table: '',
         action: 'login',
         value: { username: username, password: password }
      },
      success : function(data)
      {
         this.callback(data, this.form);
      },
      error : function(jqXHR, textStatus, errorThrown)
      {
console.log(jqXHR);         
console.log(textStatus);         
      }
   });
}

Form.prototype.systemLogout = function(callback)
{
   $.ajax(
   {
      url: AJAX_URL,   
      dataType: 'json',
      type: 'POST',      
      
      context : { form: this, callback: callback },
      data : 
      {
         table: '',
         action: 'logout'
      },
      success : function(data)
      {
         this.callback(data, this.form);
      },
      error : function(jqXHR, textStatus, errorThrown)
      {
console.log(jqXHR);         
console.log(textStatus);         
      }
   });
}




































function Document(name) { return this.init(name); } Component_extend(Document);

Document.prototype.init = function(name)
{
   this.Component_init(null, name, 'Document');

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




function DocFlowPanel() { } Component_extend(DocFlowPanel);

DocFlowPanel.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'DocFlowPanel');

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






function DocPanel() { } Component_extend(DocPanel);

DocPanel.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'DocPanel');

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
   return this.__addChild(new Space().init(this, name));
};

DocPanel.prototype.addText = function(name)
{
   if (typeof name === 'undefined') name = null;
   return this.__addChild(new Text().init(this, name));
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









function Text() { } Component_extend(Text);

Text.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'Text');
   
   this.__size = 5;
   this.__width = 100;

   parent.__addChildElement($('<div id="'+this.id+'" />'));
   
   $('#'+this.id).css('display', 'inline-block');
   $('#'+this.id).css('vertical-align', 'bottom');   
   $('#'+this.id).css('font-family', 'sans-serif');   

   return this.width(parent.widthAvailable()).size(5).align('<');
};
   
Text.prototype.onWidthAndSize = function()
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


Text.prototype.width = function(width)
{
   if (typeof width === 'undefined') return this.__width;
   this.__width = width;   
   return this.onWidthAndSize();
};


Text.prototype.size = function(size)
{
   if (typeof size === 'undefined') return this.__size;
   this.__size = size;   
   return this.onWidthAndSize();
};

Text.prototype.align = function(align)
{
   if      (align === '<') $('#' + this.id).css('text-align', 'left');
   else if (align === '>') $('#' + this.id).css('text-align', 'right');
   else $('#'+this.id).css('text-align', 'center');
   return this;
};

Text.prototype.border = function(top, bottom)
{
   this.__borderTop = top;   
   this.__borderBottom = bottom;      
   return this.onWidthAndSize();
};


Text.prototype.underline = function(underline)
{
   if (underline)
      $('#'+this.id).css('text-decoration', 'underline');
   else
      $('#'+this.id).css('text-decoration', 'none');

   return this;
};

Text.prototype.color = function(color)
{
   $('#'+this.id).css('color', color);
   return this;
};

Text.prototype.bgColor = function(color)
{
   $('#'+this.id).css('background-color', color);
   return this;
};

Text.prototype.value = function(value)
{
   if (typeof value === 'undefined')
      return $('#' + this.id).text();

   $('#' + this.id).text(value);
   return this;
};

Text.prototype.multiLine = function(multiLine)
{
   $('#' + this.id).html(multiLine.join('<br/>'));
   return this;
};

Text.prototype.left = function()
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




function DocImage() { } Component_extend(DocImage);

DocImage.prototype.outerID = function() { return Component_makeDivID(this, 'cOuter'); },
DocImage.prototype.innerID = function() { return Component_makeDivID(this, 'cInner'); },

DocImage.prototype.init = function(parent, name)
{
   this.Component_init(parent, name, 'DocImage');

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

























