
<br />
<p align="center">
  <a href="https://dev-league.com">
    <img src="https://raw.githubusercontent.com/dev-league/RESTful-API-PHP/master/assets/dist/img/logo.jpeg" alt="Logo">
  </a>

  <h1 align="center"> RESTful standard structure with PHP</h3>
</p>

This repository contains the basic structure for the development of RESTful API in the php language.

## Structure

* /assets
* /class
* /config
* /controllers
* /database
* /tools
    * Returns.php
* .htaccess
* index.php

### /assets
Folder where your public files will be, such as css, js, imgs, etc.

### /class
Folder where your classes will be, usually with the same name as the database

### /config
Folder with application startup settings, route control and ORM call

### /controllers
Application controllers, responsible for managing the pages and assigning the respective functions
### /database
Orm of the application, all calls to the database are made by it, with no direct link between the code and the database

### /tools
Folder with some tools for help such as masks treatments and etc.
   #### Retuns.php 
   Class responsible for the return in json of api data, standardizing the sending for the possibility of applying response reading models in the Front End
### .htaccess
File that controls access and directs them to the index, making you responsible for manipulating routes, in addition to blocking access to system folders.

### index.php
Responsible for initializing routes and calling system settings

## License
[MIT] (https://choosealicense.com/licenses/mit/)
