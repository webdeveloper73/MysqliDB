# MysqliDB
This mysql database class makes it easy to build queries and interact with your mysql databases with ease which also helps with development time. 

### Example 1 
```php
<?php
//Create instance of object and connect with your database
$db = new MysqliDB(["mysql_host","mysql_username","mysql_password","mysql_database"]);

//SELECT * FROM `users` WHERE `id` = 2
$db->select()->from("users")->where("id",2);
```
### Example 2
```php
<?php
//SELECT * FROM `users` WHERE id = 2 OR id > 5
$db->select()->from("users")->whereByArray(["id" => 2,"id > 5"],"OR",false);
```
