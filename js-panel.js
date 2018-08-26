

$(document).ready(function()
{
   $('body').css('margin', '0');
   $('body').css('padding', '0');
   $('body').css('background-color', 'gray');
});













function defineClass(cLass, superClass, prototype)
{
   if (superClass !== null)
      for (var key in superClass.prototype)
         cLass.prototype[key] = superClass.prototype[key];
         
   for (var key in prototype)
      cLass.prototype[key] = prototype[key];
}








g_Component_seq = 1;

function Component(){}
defineClass(Component, null, 
{
   nextSeq: function() { return g_Component_seq++; },
   
   makeID: function(name, parent) 
   {
      if (typeof parent === 'undefined')
         parent = this;
      return (parent === null) ? name : parent.id + '__' + name;
   },
   

   __initComponent: function(parent, name, type)
   {
      if (typeof name === 'undefined') name = null;
      if (typeof type === 'undefined') type = '';

      this.parent = parent;
      this.name = name;
      this.id = this.makeID((name !== null) ? 'n'+ name : 'i' + this.nextSeq() + type, parent);
      this.__data = {};
      this.unit = this.form().formUnit;
   },
   
   __afterInit: function()
   {
      this.valign(this.valign());
      this.__updateProperty('');
      return this;
   },
   
   __updateDimension: function() {},
   __updateProperty: function(name) {},
   

   $Q: function(name)
   {
      if (typeof name === 'undefined')
         return $('#' + this.id);
      else
         return $('#' + this.makeID('c' + name));
   },
   
   $Create: function(str, extraCSS)
   {
      if (typeof extraCSS === 'undefined') extraCSS = {};
   
      var element = $(str);
      element.css({ border:'0', margin:'0', padding:'0', color:'black', fontSize:'20px', fontFamily:'sans-serif' });
      element.css(extraCSS);
      return element;         
   },

   call: function(func) { func.call(this, this); return this; },
   callEach: function(list, func) 
   {
      for (var i = 0; i < list.length; i++) func.call(this, list[i]);
      return this; 
   },
   
   form: function(name) { return (typeof name !== 'undefined') ? ((name in g___formMap) ? g___formMap[name] : null) : ((this.parent === null) ? this : this.parent.form()); },
   find: function(value) { return (value === this.name || value === this.id) ? this : null; },
   remove: function() { this.parent.removeChild(this); },

   
   __prop: function(name, value, defValue, afterChanged)
   {
      if ('__'+name in this === false)
         this['__'+name] = defValue;
   
      if (typeof value === 'undefined')
         return this['__'+name];
            
      oldValue = this['__'+name];
      this['__'+name] = value;
      
      if (typeof afterChanged !== 'undefined')      
         afterChanged.call(this, name, value, oldValue);
      
      return this;
   },

   __getset: function(value, getter, setter)
   {
      if (typeof value === 'undefined')
         return getter.call(this);

      setter.call(this, value);
      return this;
   },
   
   show: function(value) 
   {
      if (value === -1)
         return this.show(this.show() === false);
      
      return this.__prop('show', value, true, function(n, value)
      {
         if (value) this.$Q().show(); else this.$Q().hide();
      });
   },
   
   width: function(value) { return this.__prop('width', value, null); },
   height: function(value) { return this.__prop('height', value, null); },
   
   actualWidth: function()
   {
      if (this.width() !== null)
         return this.width();
         
      if (this.parent === null)
         return null;
      
      var length = this.parent.innerWidth();
      if (length === null)
         return null;
         
      if (this.parent.direction === 'down')
         return length
         
      var nullCount = 0;
         
      for (var i = 0; i < this.parent.childList.length; i++)
      {
         length -= (i != 0) ? this.parent.spacing() : 0;
            
         var w = this.parent.childList[i].width();
         if (w !== null)
            length -= w;
         else
            nullCount++;
      }

      return length / nullCount;
   },      
   

   valign: function(value) { return this.__prop('valign', value, 'bottom', function(n, value)
   {
      this.$Q().css('vertical-align', value);
   }); },

   
   
   
   data: function(name, value)
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
   },
   
   
   
   
   cssValue: function(value) { return ($.isNumeric(value)) ? value + this.unit : value; },
   css: function(v1, v2, v3, v4)
   {
      if (typeof v2 === 'undefined') return this.cssValue(v1);
      if (typeof v3 === 'undefined') return this.cssValue(v1) + ' ' + this.cssValue(v2);
      if (typeof v4 === 'undefined') return this.cssValue(v1) + ' ' + this.cssValue(v2) + ' ' + this.cssValue(v3);
                                     return this.cssValue(v1) + ' ' + this.cssValue(v2) + ' ' + this.cssValue(v3) + ' ' + this.cssValue(v4);
   },
   
   
});





function Space() { }
defineClass(Space, Component, 
{
   __init: function(parent, name)
   {
      this.__initComponent(parent, name, 'Space');
      
      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block' });
      parent.__appendElement(this, element);

      return this.__afterInit();
   },   
   
   length: function(value) { return this.__prop('length', value, 0, this.__updateDimension); },
   width: function() { return (this.parent.direction === 'right') ? this.length() : 0; },
   height: function() { return (this.parent.direction === 'down') ? this.length() : 0; },

   __updateDimension: function()
   {
      var w = (this.parent.direction === 'right') ? this.length() : 1;
      var h = (this.parent.direction === 'down') ? this.length() : 1;

      this.$Q().css(
      {  
         margin: this.parent.__getChildMargin(this),
         width: w+this.unit,
         height: h+this.unit
      });
   },
});


function TextBox() { }
defineClass(TextBox, Component, 
{
   __init: function(parent, name)
   {
      this.__initComponent(parent, name);
      
      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block' });
      element.append(this.$Create('<div id="'+this.makeID('cinner')+'" />', {  }));
      parent.__appendElement(this, element);      

      return this.__afterInit().height(30).value('');
   },   
   
   
   border: function(value) 
   {
      return this.__prop('border', value, { top: { width:0, style:0 }, bottom: { width:0, style:0 } });
   },

   borderTop: function(width, style)
   {
      var border = $.extend({}, this.border());
      if (typeof style == 'undefined') style = border.top.style;
      border.top = { width: width, style: style };
      return this.border(border);
   },
   borderBottom: function(width, style)
   {
      var border = $.extend({}, this.border());
      if (typeof style == 'undefined') style = border.bottom.style;
      border.bottom = { width: width, style: style };
      return this.border(border);
   },
   
   
   

   __updateDimension: function()
   {   
      this.$Q().css(
      {
         margin: this.parent.__getChildMargin(this),
         width: this.actualWidth()+this.unit,
         height: (this.height()) + this.unit
      });
      
      var innerHeight = this.height() * 0.75;
      this.$Q('inner').css(
      {
         marginTop: ((this.height()-innerHeight)*0.5) + this.unit,
         marginBottom: ((this.height()-innerHeight)*0.5) + this.unit,
         fontSize: (innerHeight) + this.unit,
         height: (innerHeight) + this.unit
      });

      var btw = this.border().top.width;      
      if (this.border().top.style === 0) this.$Q().css({ paddingTop: this.css(btw) });
      if (this.border().top.style === 1) this.$Q().css({ borderTop: this.css(btw/2.0, 'solid', 'black'), paddingTop: this.css(btw/2.0) });
      if (this.border().top.style === 2) this.$Q().css({ borderTop: this.css(btw, 'double', 'black') });
      if (this.border().top.style === 3) this.$Q().css({ borderTop: this.css(btw, 'solid', 'black') });

      var bbw = this.border().bottom.width;
      if (this.border().bottom.style === 0) this.$Q().css({ paddingBottom: this.css(bbw) });
      if (this.border().bottom.style === 1) this.$Q().css({ borderBottom: this.css(bbw/2.0, 'solid', 'black'), paddingBottom: this.css(bbw/2.0) });
      if (this.border().bottom.style === 2) this.$Q().css({ borderBottom: this.css(bbw, 'double', 'black') });
      if (this.border().bottom.style === 3) this.$Q().css({ borderBottom: this.css(bbw, 'solid', 'black') });      
   },
   

   value: function(value) { return this.__getset(value, function() { return this.$Q('inner').text(); }, function(v) { this.$Q('inner').text(v); }); },

   align: function(value) { return this.__prop('align', value, 'left', this.__updateProperty); },
   bgColor: function(value) { return this.__prop('bgColor', value, 'transparent', this.__updateProperty); },
   color: function(value)   { return this.__prop('color', value, 'black', this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'align'  ) this.$Q().css('text-align', this.align());
      if (name == '' || name === 'bgColor') this.$Q().css('background-color', this.bgColor());
      if (name == '' || name === 'color'  ) this.$Q('inner').css('color', this.color());
   }
});




function TextInput() { }
defineClass(TextInput, Component, 
{
   __init: function(parent, name, inputType)
   {
      this.__initComponent(parent, name);

      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block' });
      element.append(this.$Create('<div id="'+this.makeID('clabel')+'" />', { color:'green', fontSize:'15px' }));
      element.append(this.$Create('<input type="'+inputType+'" id="'+this.makeID('cinner')+'" />'));
      parent.__appendElement(this, element);
      
      return this.__afterInit().value('');
   },
   
   __updateDimension: function(name, value)
   {   
      this.$Q().css({ margin: this.parent.__getChildMargin(this) });

      this.$Q('label').css(
      {
         padding: '0 0 3px 0',
         width: this.actualWidth()+'px',
         height: '15px' 
      });
      
      this.$Q('inner').css(
      {
         padding: '5px', 
         width: (this.actualWidth() - 10)+'px', 
         height: '25px'
      });
   },
   
   value: function(value) { return this.__getset(value, function() { return this.$Q('inner').val(); }, function(v) { this.$Q('inner').val(v); }); },
   html: function(value) { return this.__getset(value, function() { return this.$Q('inner').html(); }, function(v) { this.$Q('inner').html(v); }); },
   
   label: function(value) { return this.__prop('label', value, null, this.__updateProperty); },
   align: function(value) { return this.__prop('align', value, 'left', this.__updateProperty); },
   bgColor: function(value) { return this.__prop('bgColor', value, 'white', this.__updateProperty); },
   color: function(value) { return this.__prop('color', value, 'black', this.__updateProperty); },
   enable: function(value) { return this.__prop('enable', value, true, this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'align'  ) this.$Q('inner').css('text-align', this.align());
      if (name == '' || name === 'bgColor') this.$Q('inner').css('background-color', this.bgColor());
      if (name == '' || name === 'color'  ) this.$Q('inner').css('color', this.color());
      if (name == '' || name === 'enable' ) this.$Q('inner').prop('disabled', !!!this.enable());
      if (name == '' || name === 'label'  ) if (this.label() === null) this.$Q('label').text('').hide(); else this.$Q('label').text(this.label()).show();
   },
   
   change: function(change)
   {
      this.__change = change;
      this.$Q('inner').data('__this', this);
      this.$Q('inner').change(function() { var t = $(this).data('__this'); t.__change(t); });
      return this;
   }
});


function Button() { }
defineClass(Button, Component, 
{
   __init: function(parent, name)
   {
      this.__initComponent(parent, name);
      
      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block', cursor:'pointer' });
      parent.__appendElement(this, element);

      return this.__afterInit().width(35).html('&raquo;');
   },

   __updateDimension: function()
   {   
      this.$Q().css(
      {
         margin: this.parent.__getChildMargin(this),
         width: (this.width()-10)+'px',
         height: '25px',         
         border: '2px solid black',
         padding: '3px',
         borderRadius: '10px'
      });
   },   

   value: function(value) { return this.__getset(value, function() { return this.$Q().text(); }, function(v) { this.$Q().text(v); }); },
   html: function(value) { return this.__getset(value, function() { return this.$Q().html(); }, function(v) { this.$Q().html(v); }); },

   align: function(value) { return this.__prop('align', value, 'center', this.__updateProperty); },
   bgColor: function(value) { return this.__prop('bgColor', value, 'khaki', this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'align') this.$Q().css('text-align', this.align());
      if (name == '' || name === 'bgColor') this.$Q().css('background-color', this.bgColor());
   },
   
   click: function(click)
   {
      this.__click = click;
      this.$Q().data('__this', this);
      this.$Q().click(function() { var t = $(this).data('__this'); t.__click(t); });
      return this;
   }
});



function Select() { }
defineClass(Select, Component, 
{
   __init: function(parent, name)
   {
      this.__initComponent(parent, name);

      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block' });
      element.append(this.$Create('<div id="'+this.makeID('clabel')+'" />', { color:'green', fontSize:'15px' }));
      element.append(this.$Create('<select id="'+this.makeID('cinner')+'" />'));
      parent.__appendElement(this, element);
      
      return this.__afterInit();
   },

   __updateDimension: function(name, value)
   {
      this.$Q().css({ margin: this.parent.__getChildMargin(this) });
   
      this.$Q('label').css(
      {
         padding: '0 0 3px 0',
         width: this.actualWidth()+'px',
         height: '15px' 
      });

      this.$Q('inner').css(
      {
         width: (this.actualWidth())+'px', 
         height: '35px'
      });
   },
   
   label: function(value) { return this.__prop('label', value, null, this.__updateProperty); },
   align: function(value) { return this.__prop('align', value, 'left', this.__updateProperty); },
   enable: function(value) { return this.__prop('enable', value, true, this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'align') this.$Q('inner').css('text-align', this.align());
      if (name == '' || name === 'enable') this.$Q('inner').prop('disabled', !!!this.enable());
      if (name == '' || name === 'label') if (this.label() === null) this.$Q('label').text('').hide(); else this.$Q('label').text(this.label()).show();
   },
         
   addOption: function(value, name)
   {
      if (typeof name === 'undefined') name = value;

      if ($.isArray(value))
         for (var i = 0; i < value.length; i++)
            this.addOption(value[i], name[i]);
      else
         this.$Q('inner').append('<option value="'+value+'" >'+name+'</option>');

      return this;   
   },

   addOptionByList: function(objectList, valueKey, nameKey)
   {
      if (typeof nameKey === 'undefined') nameKey = valueKey;
      for (var i = 0; i < objectList.length; i++)
         this.addOption(objectList[i][valueKey], objectList[i][nameKey]);
      return this;
   },
   
   clearOption: function()
   {
      this.$Q('inner').children().remove();
      return this;
   },
   
   value: function(value)
   {
      if (typeof value === 'undefined')
         return this.$Q('inner').val();

      var optionList = $.map(this.$Q('inner').children(), function(option) { return option.value; });
      for (var i = 0; i < optionList.length; i++)
         if (optionList[i] == value)
         {
            this.$Q('inner').val(value);
            return this;
         }

      this.addOption(value);
      this.$Q('inner').val(value);
      return this;
   },
   
   change: function(change)
   {
      this.__change = change;
      this.$Q('inner').data('__this', this);
      this.$Q('inner').change(function() { var t = $(this).data('__this'); t.__change(t); });
      return this;
   }
});


function Image() { }
defineClass(Image, Component, 
{
   __init: function(parent, name)
   {
      this.__initComponent(parent, name);

      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block' });
      element.append(this.$Create('<div id="'+this.makeID('clabel')+'" />', { color:'green', fontSize:'15px' }));
      element.append(this.$Create('<img id="'+this.makeID('cinner')+'" />'));
      parent.__appendElement(this, element);
      
      return this.__afterInit();
   },
   
   src: function(value) { return this.__prop('src', value, '', function(n, value)
   {
      this.$Q('inner').attr('src', value);
   }); },
   
   
   padding: function(valueOrTop, bottom)
   {
      var value = (typeof bottom !== 'undefined') ? { top:valueOrTop, bottom:bottom } : valueOrTop;
      return this.__prop('padding', value, {top:0, bottom:0});
   },

   __updateDimension: function(name, value)
   {
      this.$Q().css(
      {
         margin: this.parent.__getChildMargin(this),
         paddingTop: this.padding().top + this.unit,
         paddingBottom: this.padding().bottom + this.unit,
         paddingLeft: '0',
         paddingRight: '0'
      });
   
      this.$Q('label').css(
      {
         padding: '0 0 3px 0',
         width: this.width()+'px',
         height: '15px' 
      });
      
      this.$Q('inner').css(
      {
         width: this.width() + this.unit, 
         height: this.height() + this.unit
      });
   },
   
   label: function(value) { return this.__prop('label', value, null, this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'label') if (this.label() === null) this.$Q('label').text('').hide(); else this.$Q('label').text(this.label()).show();
   },
   
});




function ComponentContatiner(){}
defineClass(ComponentContatiner, Component, 
{

   __initComponentContatiner: function(parent, name, type, direction)
   {
      this.__initComponent(parent, name, type);  
      this.direction = direction;
      this.childList = [];
   },
   
   __updateAllComponentDimension: function()
   {
      this.traverse(function(c) { c.__updateDimension(); });
   },
   


   
   innerWidth: function() { return null; },
   innerHeight: function() { return null; },
   spacing: function(value) { return this.__prop('spacing', value, 0); },
   
   __getChildMargin: function(child)
   {
      if (child !== this.lastChild())
      {
         var s = this.spacing() + this.unit;
         if (this.direction === 'down') return '0 0 '+s+' 0';
         if (this.direction === 'right') return '0 '+s+' 0 0';
      }
      
      return '0 0 0 0';
   },
   
   
   
   
   
   __addChild: function(child) 
   {
      this.childList.push(child);
      return child; 
   },

   firstChild: function() { return (this.childList.length > 0) ? this.childList[0] : null; },
   lastChild: function() { return (this.childList.length > 0) ? this.childList[this.childList.length - 1] : null; },
   
   traverse: function(callback)
   {
      for (var queue = [this]; queue.length > 0; )
      {
         var item = queue.shift();
         callback(item);
         
         if ('childList' in item)   
            for (var i = 0; i < item.childList.length; i++)
               queue.push(item.childList[i]);
      }
   },

   find: function(value)
   {
      for (var queue = [this]; queue.length > 0; )
      {
         var item = queue.shift();
         if (item.name === value || item.id === value)
            return item;
         
         if ('childList' in item)   
            for (var i = 0; i < item.childList.length; i++)
               queue.push(item.childList[i]);
      }
      
      return null;
   },   
   




   __Panel_UpdateDimension: function()
   {
      if (this.direction === 'down')
         for (var i = 0; i < this.childList.length; i++)
         {
            var child = this.childList[i];
            if (child.show())
               child.$Q('br').show();
            else
               child.$Q('br').hide();
         }
         
      return this;
   },
   

   __appendElement: function(child, element)
   {
      if (this.direction === 'down' && this.childList.length > 0)
      {
         var id = child.makeID('cbr');
         this.$Q('inner').append($('<br id='+id+' />'));
      }

      this.$Q('inner').append(element);
   },      

   removeChild: function(child)
   {
      for (var i = 0; i < this.childList.length; i++)
      {
         if (this.childList[i] === child)
         {
            if (i != 0)
               child.$Q().prev().remove();
            else if (this.childList.length > 1)
               child.$Q().next().remove();
            
            child.$Q().remove();
            this.childList.splice(i, 1);
            break;
         }
      }
   },      
   
   removeAll: function(name)
   {
      for (var i = this.childList.length - 1; i >= 0; --i)
         this.childList[i].remove();
         
      return this;
   },
   
   
   addStack: function(name) { return this.__addChild(new Panel().__init(this, name, 'down')); },
   addQueue: function(name) { return this.__addChild(new Panel().__init(this, name, 'right')); },

   addSpace: function(name) { return this.__addChild(new Space().__init(this, name)); },
   addTextBox: function(name) { return this.__addChild(new TextBox().__init(this, name)); },
   addTextInput: function(name) { return this.__addChild(new TextInput().__init(this, name, 'text')); },
   addPassword: function(name) { return this.__addChild(new TextInput().__init(this, name, 'password')); },
   addButton: function(name) { return this.__addChild(new Button().__init(this, name)); },
   addSelect: function(name) { return this.__addChild(new Select().__init(this, name)); }

});


function Panel() { }
defineClass(Panel, ComponentContatiner, 
{
   __init: function(parent, name, direction)
   {
      this.__initComponentContatiner(parent, name, 'Panel', direction);
   
      var element = this.$Create('<div id="'+this.id+'"></div>', { display:'inline-block', verticalAlign:'bottom' });
      element.append(this.$Create('<div id="'+this.makeID('clabel')+'" />', { color:'green', fontSize:'15px' }));
      element.append(this.$Create('<div id="'+this.makeID('cinner')+'" />'), {  whiteSpace:'nowrap'  }  );
      parent.__appendElement(this, element);
      
      return this.__afterInit();
   },
   

   frameWidth: function() { var w = this.actualWidth(); return (w === null) ? null : w - this.padding().left - this.padding().right; },
   innerWidth: function() { var w = this.frameWidth(); return (w === null) ? null : w - (this.frame() ? 20 : 0); },
   
   frame: function(value) { return this.__prop('frame', value, false); },
   padding: function(value) { return this.__prop('padding', value, {top:0, bottom:0, left:0, right:0}); },
   vpad: function(top, bottom)
   {
      if (typeof bottom === 'undefined') bottom = top;
      return this.padding({ top:top, bottom:bottom, left:this.padding().left, right:this.padding().right });
   },
   hpad: function(left, right)
   {
      if (typeof right === 'undefined') right = left;
      return this.padding({ top:this.padding().top, bottom:this.padding().bottom, left:left, right:right });
   },
   
   
   __updateDimension: function()
   {
      this.$Q(       ).css('width', this.frameWidth()+this.unit);
      this.$Q('label').css('width', this.frameWidth()+this.unit);
      this.$Q('inner').css('width', this.innerWidth()+this.unit);
      
      this.$Q().css(
      {
         margin: this.parent.__getChildMargin(this),
         paddingTop: this.padding().top+'px',
         paddingBottom: this.padding().bottom+'px',
         paddingLeft: this.padding().left+'px',
         paddingRight: this.padding().right+'px'
      });

      this.$Q('label').css(
      {
         padding: '0 0 3px 0',
         height: '15px' 
      });
      
      if (this.frame())
         this.$Q('inner').css({ border:'1px solid black', padding:'9px', borderRadius:'10px' });
      else
         this.$Q('inner').css({ border:'0', padding:'0', borderRadius:'0' });
         
      return this.__Panel_UpdateDimension();
   },
   
   
   label: function(value) { return this.__prop('label', value, null, this.__updateProperty); },
   bgColor: function(value) { return this.__prop('bgColor', value, 'transparent', this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'bgColor') this.$Q().css('background-color', this.bgColor());
      if (name == '' || name === 'label') if (this.label() === null) this.$Q('label').text('').hide(); else this.$Q('label').text(this.label()).show();
   }

});








g___formMap = [];

function form(name)
{
   if (name in g___formMap)
      return g___formMap[name];

   return null;
}

function createForm(name, unit)
{
   if (typeof unit === 'undefined') unit = 'px';
      
   var form = new Form().__init(name, unit);
   g___formMap[name] = form;
   return form;
}

function Form(){}
defineClass(Form, ComponentContatiner, 
{
   __init: function(name, formUnit)
   {
      this.formUnit = formUnit;
   
      this.__initComponentContatiner(null, name, 'Form', 'down');

      var element = this.$Create('<div id="'+this.id+'"></div>', { width:'100%', textAlign:'center' });
      element.append(this.$Create('<div id="'+this.makeID('cinner')+'" />', { display:'inline-block', margin:'auto', width:'auto', textAlign:'left' }));      
      $('body').append(element);
      
      this.__blackOut = false;
      this.__blackOutElement = $('<div style="background:rgba(0,0,0,0.3); display:none; width:100%; height:100%; position:fixed; top:0; left:0; z-index:99998;" ></div>');
      $('body').append(this.__blackOutElement);


      return this.__afterInit().show(false);
   },
      

   width: function(value) 
   {
      return this.__prop('width', value, null, function(n,v) 
      {
         this.$Q('inner').css('width', (v === null) ? 'auto' : v + this.unit); 
      }); 
   },
   innerWidth: function() { return this.width(); },
   
   
   bgColor: function(value) { return this.__prop('bgColor', value, 'LightBlue', this.__updateProperty); },

   __updateProperty: function(name)
   {
      if (name == '' || name === 'bgColor') this.$Q('inner').css('background-color', this.bgColor());
   },
   
   
   
   showMe: function()
   {
      for (var name in g___formMap)
         if (name !== this.name)
            g___formMap[name].show(false);
      
      this.__updateAllComponentDimension();
      
      return this.show(true);
   },
   

   __updateDimension: function()
   {
      return this.__Panel_UpdateDimension();
   },   
      
      
      
   blackOut: function(value, callback)
   {      
      if (typeof value === 'undefined')
         return this.__blackOut;

      if (value === -1)
         this.blackOut(this.__blackOut === false, callback);

      var complete = null;

      if (typeof callback !== 'undefined')
      {
         this.__blackOutElement.data('__form', this);
         this.__blackOutElement.data('__callback', callback);
         complete = function()
         {
            var f = $(this).data('__form');
            $(this).data('__callback').call(f, f);
         };
      }

      if (this.__blackOut === false && value === true)
      {
         this.__blackOut = true;
         
         if (complete === null)
            this.__blackOutElement.fadeIn(150);
         else
            this.__blackOutElement.fadeIn(150, complete);
      }

      if (this.__blackOut === true && value === false)
      {
         this.__blackOut = false;

         if (complete === null)
            this.__blackOutElement.fadeOut(150);
         else
            this.__blackOutElement.fadeOut(150, complete);
      }
   }      
      
      
});
















