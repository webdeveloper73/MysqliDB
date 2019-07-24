# MysqliDB
This mysql database class makes it easy to build queries and interact with your mysql databases with ease which also helps with development time. 

### Connecting to your database
```php
<?php
$db = new MysqliDB(["mysql_host","mysql_user","mysql_password","mysql_database"]);
```
### rawQuery($query)
To simply run your own query instead of the query builder methods you would use the method rawQuery
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
