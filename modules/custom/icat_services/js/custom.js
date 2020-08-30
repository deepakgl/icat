(function($, Drupal) {
  Drupal.behaviors.MarketBehavior = {
    attach: function(context, settings) {
      $('input.summable').keyup(function() {
        var sum = 0;
	var isNumber=true;
	$('input.summable').each(function() {
		if(isNaN(this.value)) {
		        this.value = '';
			isNumber=false;
			alert("Please enter numbers only.");
			return false;
		}
	}
        );
	if (isNumber) {
        $('input.summable').each(function() {
	if(!isNaN(this.value)) {
          if (this.value) {

            sum += parseFloat(this.value);
          }
}
        });
    }
        $('input.totalsum').val(sum);



      });
      $("#txtToDate").change(function() {
        var pickup = $("#txtFromDate").val();
        var eta = $("#txtToDate").val();
        if (pickup > eta) {
          $("#txtToDate").val("");
          alert("Please enter higher date than pickup date.");
        }
      });

      $("#quoteDate").change(function() {
        var pickup = $("#txtFromDate").val();
        var quote = $("#quoteDate").val();
        if (quote > pickup) {
          $("#quoteDate").val("");
          alert("Please enter lesser date than pickup date.");
        }
      });



    //$('input').keyup(function() {


          // $("#txtFromDate").datepicker({
          //     numberOfMonths: 2,
          //     onSelect: function(selected) {
          //       $("#txtToDate").datepicker("option","minDate", selected)
          //     }
          // });
          // $("#txtToDate").datepicker({
          //     numberOfMonths: 2,
          //     onSelect: function(selected) {
          //        $("#txtFromDate").datepicker("option","maxDate", selected)
          //     }
          // });
     // });
    }
  };
})(jQuery, Drupal);
