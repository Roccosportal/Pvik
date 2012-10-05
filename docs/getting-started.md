## Install
To install Pvik you just need to copy the files from this repository to the webserver (/var/www/) folder. If you use logging you need to set write permissions for the folder '/logs/'.
Access the folder via a browser, you should get a message like 'It works!'.

## Naming conventions (important)
Folder and files are in small letters and words are separated through a dash. Code, like variables, classes and methods begins with a capital letter and uses camel casing.

    # file and folders
    /controllers/first-page.php
    /views/first-page/add-comment.php
    /some-folder/test.php
     
    # variables, classes methods and more
    MyNewClass #class
    FullName #variable
    ConvertSomething #method

## Create hello world
### First steps
The folder /application/ is the working environment for our php code. At first we should set up the php namespace for our project. Therefore we change the content of /application/configs/config.php to:
```php5
<?php
// Default php namespace is \HelloWorld
self::$Config['DefaultNamespace'] = '\\HelloWorld'; 
//The class autoloader will search for a class with the namespace \HelloWorld in the folder /application/
self::$Config['NamespaceAssociations']['\\HelloWorld'] = '~/application/'; 

// matches Class HelloWorld->IndexAction to url '/'   
self::$Config['Routes'] = array(
array ('Url' => '/', 'Controller' => 'HelloWorld', 'Action' => 'Index'),
);
```
### Create a controller
Create the file 'hello-world.php' in the folder '/application/controllers/' with following content:
```php5
<?php

namespace HelloWorld\Controllers;

class HelloWorld extends \Pvik\Web\Controller {
    // action
    public function IndexAction(){
        // set a variable for the view
        $this->ViewData->Set('text' , 'this is a text');
        // place here the program logic
        // execute the view
        $this->ExecuteView();
    }
}
```
### Create a view
Create the folder 'hello-world' in the folder '/application/views/'. Create the file 'index.php' in the folder '/views/hello-world/' with follwing content:
```html+php
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
       "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Hello World</title>
</head>
<body>
<h1>Hello World</h1>
<?php
    echo $this->ViewData->Get('text');
    // place php code here
?>
</body>
</html>
```
