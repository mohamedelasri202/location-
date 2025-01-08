<?php

/*

// blog system
variable inside a calss = [properties]
variable inside a class =variable 
-> = this is an object operator
new = new object keybord
$this= pseaudo variable refers to object properties 
constant constantename = 'value';
[::]=scope resolution operatot it always follow the sel
f thing 

self =refeers to current class 
acces static  members
not use $ because it doasnt represent a varialbe but it represent class construction
****************
$this  referce to the current object that the opposite of self that referese to class
acces non static members
use the dollar sign cus it represnet the variable
*************************************************** 
encapsulation 
IN  INHERTENCE RELATIONSHIPS IN POO = WHEN U INHERIT A METHODE U CAN'T MAKE MODIFECATION TO THAT METHODE INSIDE THE ONE WHO INHERTED THE METHPODE INSTED U SHOULD MODIFY IT IN THE MAIN CLASS 
IF YOU MAKE MODIFECATION TO THE ONE WHO INHERTED THE METODE IT WOULD LOOK LIKE YOU JUST USED THE SAME METHODE WITH THE SAME NAME SO THAT WOULD RESULT IN AN ERROR ;

super class = the class that u inherit from 



in the inhertince logic thers something called the override and what this baseclly means is that ;you can inherit a methode and can make your own modefecation on that methode 
so you dont need to stick to the origianl methode
and the [FINAL function function name ] it prevents you from doing that as it wont let you add any modefecation to the origenal methode 
and same thing fo

final class you can't inherit from that calss 



class abstraction = he doasnt do anything spesific
you can't instence object from it  means that you wont find something like 
[new the name of the abstracted class ] 
made for other classes to inherit prop&mehtodes from 
you can inherit from it.
 

*/

abstract class makedevice{

public $ram;
public function sayhello(){

}

}
 class appledevice extends makedevice{

 }


class appledevices {
    // properties
  

    // constant 



 
}






 
 $iphone6plus= new appledevices;
echo '<pre>';
print_r($iphone6plus);

echo '</pre>';

