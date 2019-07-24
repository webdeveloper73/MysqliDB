# MysqliDB
This mysql database class makes it easy to build queries and interact with your mysql databases with ease which also helps with development time. 

### Connecting to your database
```php
<?php
$db = new MysqliDB(["mysql_host","mysql_user","mysql_password","mysql_database"]);
```
### rawQuery($query)
To simply run your own query instead of using the query builder methods you would use the method rawQuery
```php
<?php
$q = $db->rawQuery("SELECT * FROM `posts`");

if($db->num_rows() > 0)
{
foreach($db->result() as $row):
echo $row->postContent."<br />";
endforeach;
}
```
### insert($tbl,$data,$escape)
Insert data into your database table
```php
$data = [
"username" => "username",
"password" => "pass",
"about_me" => "some about me"
];

//Insert the data into users table
$db->insert("users",$data);
```
### insertID()
Gets id of last inserted record
```php
$data = [
"username" => "username",
"password" => "pass",
"about_me" => "some about me"
];

//Insert the data into users table
$db->insert("users",$data);

//Insert id
$insert_id = $db->insertID();
```
### update($tbl,$data,$escape)
Update data in a given table
```php
<?php
//UPDATE `users` SET `username` = 'newUsername',`bio` = 'hello world' WHERE `id` = 5
$db->update("users",["username" => "newUsername","bio" => "hello world"])->where("id",5);
```
you can pass false as a 3rd parameter in order to not escape data

### delete()
Starts off a DELETE query
```php
//DELETE FROM `users` WHERE `id` = 5
$db->delete()->from("users")->where("id",5);
```
### escape($str)
Makes data safe for passing to a mysql query
```php
$string = "this 's is an unsafe string 's";
$string = $db->escape($string);
//this \'s is an unsafe string \'s
$db->update("users",["bio",$string])->where("id",5);
```
