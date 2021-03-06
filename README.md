zend-doctrine-test project
==================

Built with Zend Framework 1.12, Doctrine 2.5, AngularJS 1.4


How to Deploy

1) With Vagrant

If you have vagrant and virtualbox, all you have to do is clone the repository to any directory, and type 

vagrant up.


2) With Unix Box or MAMP

This app uses php 5.5 & Mysql 5.5. Please check your software versions.

First, clone the files.

run composer to install doctrine and phpexcel


Set the apache (or nginx) document root to the /public folder

create database 'zend_test', with user 'zend_test' and password 'password'

dump the sql at /data/zend_test.sql onto the zend_test database. 



How to run
   At vagrant machine, it will run at localhost:8080. If it is running at apache on custom setup, please enter the virtual server name to access the app.

There are 3 routes under a single page

upload 
   uploads an excel file, set bulk password and bulk status

mapping 
   map the columns of the data

preview
   visitor can go back to the previous page (#mapping), or save the record to database (apply)



Backend

   The backend stack is PHP 5.5, MySQL 5.5, Zend Framework 1.12, Doctrine 2.5

    Doctrine is loaded at /application/Bootstrap.php
    There is one model/entity file (model/Users.php), and one repository file (model/UsersRepository.php)
    Repository is not used in this project.

    There are 3 controllers. 
    ErrorController is included for debugging purpose.
    IndexController loads javascript files and fire a single page app.
    Api controller has two routes being used.
    
    api/upload parse excel files and reconstruct the excel file into a json array.
    api/save process json data into database.

Front End
   Front end is using angularjs with angular-router and angular-upload(for file upload)

   No other library is used. No JQuery or Twitter Bootstrap at all.


Miscellaneous:



There is no css 


    

