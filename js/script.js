function find_repeat(mass)
{
	var buff = []
	for (var i = 0; 100 > i; i++) {
		buff[i] = false;
	}
	for (var i = 0; mass.length - 1 >= i; i++) {
		if (((mass[i].value)<0) || buff[mass[i].value]){
			return i;
		}
		else{
			buff[mass[i].value]=true;
		};
	};
	return -1;
};
function find_empty(mass)
{
	for (var i =0;mass.length - 1>= i; i++) {
		if ((mass[i].value)<0)
			return i;
	};
	return -1;
};
$(document).ready(function(){
	var select =  $('form[action="insert_data.php"]').find('select'),
	label_act =  $('#labelAction'),
	label_count = $('#labelCount'),
	count = $('#count'),
	send_button = $('button.btn-primary');
			//$send_button.attr("disabled", true);
			$('#create').click(function(){
				requests = $('#requests').children();
				for(var i=1,l=requests.length;i<l;i++){
					requests[i].remove();	
				};
				for(var i =0,l=count[0].value;i<l;i++){
					$('<div class="input-group"><input type="text" id="action" name = "flat'+l+1+'" class="form-control" placeholder="Flat"><input type="text" id="action" name = "cash'+l+1+'" class="form-control" placeholder="Cash"></div>')
					.appendTo('#requests');
				};
			});
			select.bind('change',function(){
				label_act.text('Input ' + select[0].value);
				label_count.text('Count of ' + select[0].value);
			});
			$('form[action="insert_data.php"]').submit(function(event){
				var flat = $('input[placeholder="Flat"]');
				var cash = $('input[placeholder="Cash"]');
				if ((find_repeat(flat)==-1) && (find_empty(cash)==-1)){
					alert("Successful!");//$send_button.attr("disabled", false);
				}
				else{
					if (find_repeat(flat)==-1){
						alert("Cash must be positive!!!");
					}
					else
					{
						alert("Flat must be positive and do not repeat!!!");
					}
					event.preventDefault();
				};
			});
		});
