$(function() {
  $.nette.init();
	$( "#locale" ).change(function() {
			$( "#datepicker" ).datepicker( "option",
				$.datepicker.regional[ 'sk' ] );
	});
	$("input.date").each(function () { 
        var el = $(this);
        var value = el.val();
        var date = (value ? $.datepicker.parseDate($.datepicker.W3C, value) : null);

        var minDate = el.attr("min") || null;
        if (minDate) minDate = $.datepicker.parseDate($.datepicker.W3C, minDate);
        var maxDate = el.attr("max") || null;
        if (maxDate) maxDate = $.datepicker.parseDate($.datepicker.W3C, maxDate);

        if (el.attr("type") === "date") {
            var tmp = $("<input/>");
            $.each("class,disabled,id,maxlength,name,readonly,required,size,style,tabindex,title,value".split(","), function(i, attr)  {
                tmp.attr(attr, el.attr(attr));
            });
            el.replaceWith(tmp);
            el = tmp;
        }
        el.datepicker({
            minDate: minDate,
            maxDate: maxDate
        });
        el.val($.datepicker.formatDate(el.datepicker("option", "dateFormat"), date));
    });
  

	/*Pre zobrazenie celého článku*/
	var cely = $('.cely_clanok'); //Nájdem doplnok textu
	cely.next().hide();              //Skryjem ho
	cely.click(function() { //Pri kliku na článok
		$(this).fadeOut(200, function() {
			$(this).remove();	//Odstránim odkaz
		}).next().slideDown('slow');		  //Skryjem samotný odkaz
		return false; 					          //Zakážem odkaz
	});

	/*Pre zobrazenie celého oznamu*/
	var cely = $('.cely_oznam'); //Nájdem doplnok textu
	var textC = cely.next().html();		//Najdem cely text
	var textU = cely.prev();		//Najdem upraveny text
	cely.next().hide().remove();              //Skryjem ho
	cely.click(function() { //Pri kliku na článok
		textU.append('<span class="ost">' + textC + '</span>');
		var ost = $('.ost');
		ost.hide();
		$(this).fadeOut(200, function() {
			$(this).remove();	//Odstránim odkaz
  		ost.slideDown('slow');
		});			  //Skryjem samotný odkaz
		return false; 					  //Zakážem odkaz
	});
});