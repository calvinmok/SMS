<!DOCTYPE html>
<html>

<head>

   <?php include("pagehead.php"); ?>

   <title>存貨管理﹣顧客</title>   
   <meta name="viewport" content="width=450">

</head>

<body style="background-color:gray;">
   
   <script>
   
      $(document).ready(function()
      {
         var firstForm = createForm('userList').width(450).call(function()
         {
            this.addQueue('top').vpad(25).hpad(25).spacing(20).bgColor('CornflowerBlue').call(function(panel)
            {
               panel.addButton('back').width(80).value('選單').click(function(b) { backToMenu(); });
               panel.addButton('new' ).width(80).value('新增').click(function(b) { b.form('userDetail').showUser(null); });
            });

            this.addStack('list').vpad(25).hpad(25).spacing(15);
            
            
            this.loadAndShow = function()
            {
               createUserTable().data(this).findAll(function(userList, form)
               {
                  var listPanel = form.find('list').removeAll();

                  listPanel.addQueue().call(function(panel)
                  {
                     panel.addTextBox('code').width(100).value('代號');
                     panel.addTextBox('name').value('名稱');
                     panel.addTextBox('permission').width(100).value('權限');
                     panel.addButton('detail').show(false);
                  });
                  
                  for (var user = eachOF(userList); user.next(); )
                  {
                     listPanel.addQueue().data('user', user.val).call(function(panel)
                     {
                        panel.addTextBox('code').width(100).value(user.val.code);
                        panel.addTextBox('name').value(user.val.name);
                        panel.addTextBox('permission').width(100).value(user.val.permission);
                        panel.addButton('detail').click(function(b)
                        {
                           b.form('userDetail').showUser(b.data('user'));
                        });
                     });
                  }
                  
                  form.showMe();         
               });
            };
            
         });
         
         createForm('userDetail').width(450).call(function()
         {
            this.addStack('top').vpad(25).hpad(25).bgColor('CornflowerBlue').call(function(stack)
            {
               stack.addQueue().spacing(20).call(function(panel)
               {
                  panel.addButton('cancel').width(80).value('取消').click(function(b) { b.form('userList').loadAndShow(); });
                  panel.addButton('submit').width(80).value('儲存').click(function(b) { b.form().onSubmit(); });
               });
            });

            this.addStack('main').vpad(25).hpad(25).spacing(20).call(function(mainPanel)
            {
               mainPanel.addTextInput('code').label('代號');                  
               mainPanel.addPassword('password').label('密碼');                  
               mainPanel.addTextInput('name').label('名稱');                  
               mainPanel.addSelect('permission').label('權限').addOption('admin').addOption('user').value('user');                  
            });
            
            
            this.showUser = function(user)
            {
               if (user === null)
               {
                  this.data('user.id', null);
                  this.find('code'      ).value('');
                  this.find('password'  ).value('');
                  this.find('name'      ).value('');
                  this.find('permission').value('user');
               }
               else
               {
                  this.data('user.id', user.id);
                  this.find('code'      ).value(user.code);
                  this.find('password'  ).value(user.password);
                  this.find('name'      ).value(user.name);
                  this.find('permission').value(user.permission);
               }
               
               return this.showMe();
            }
                        
            this.onSubmit = function()
            {
               var user = {};
               if (this.data('user.id') !== null) 
                  user.id = this.data('user.id');
               
               user.code = this.find('code').value();
               user.password = this.find('password').value();
               user.name = this.find('name').value();
               user.permission = this.find('permission').value();

               createUserTable().data(this).write(user, function(user, f)
               {
                  if (user != null)
                     alert('已儲存。');
                  
                  f.form('userList').loadAndShow();
               });
            }
         });
         
         

         firstForm.loadAndShow();         
      });
            
   </script>

</body>

</html>


         






