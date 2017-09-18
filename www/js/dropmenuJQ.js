$(document).ready (function(){
  if ($(window).width()>719)//iba vo vodorovnom menu
  {
    $( ".nav-item a.dropdown-toggle" ).on( "click", function(event)//ak sa klikne na polozku menu 
    {  
      var curElement=$( event.target ).next();//aktualne kliknuta polozka
      var heightTopmenu=$( "#topmenu" ).height()+21;//vyska horneho topmenu 
      $( ".dropdown-menu" ).css("top", heightTopmenu);//pozicia dropdowmenu sa nastavi podla vysky topmenu 
      if (curElement.is(':visible'))//ak je aktualne vysunute stiahne iba jedno
      {
        $( curElement ).slideUp(100);
      }
      else
      {
        $( ".dropdown-menu" ).slideUp(100);//inak stiahne vsetky submenu a vytiahne aktualne
        $( curElement ).slideDown(400);
      }
    });
    $( "header" ).on( "mouseout", function()//ak kurzor opusti topmenu 
    {  
      $( ".dropdown-menu" ).slideUp(100);//inak stiahne vsetky submenu a vytiahne aktualne
    });
}
});