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
function AjaxFormRequest(result_id,formMain,url) {
	jQuery.ajax({
		url: url,
		type: "POST",
		dataType:"html",
		data: jQuery("#"+formMain).serialize(),
		success: function(response) {
			document.getElementById(result_id).innerHTML = response;
		},
		error: function(response) {
			document.getElementById(result_id).innerHTML = "Возникла ошибка при отправке формы. Попробуйте еще раз";
		}
	});
};
$(document).ready(function(){
	var select =  $('#formInsert').find('select'),
	report = $('form[action="handler.php"]').find('select'),
	label_act =  $('#labelAction'),
	label_count = $('#labelCount'),
	count = $('#count'),
	send_button = $('button.btn-primary');
	$('#insertResult')[0].style.visibility='hidden';
			//$send_button.attr("disabled", true);
			$('#create').click(function(){
				let requests = $('#requests').children();
				for(var i=2,l=requests.length;i<l;i++){
					requests[i].remove();
				};
				for(var i =1,l=count[0].value;i<l;i++){
					$('<div class="input-group"><input type="text" id="action" name = "flat'+i+'" class="form-control" placeholder="Flat"><input type="text" id="action" name = "cash'+i+'" class="form-control" placeholder="Cash"></div>')
					.appendTo('#requests');
				};

			});
			select.bind('change',function(){
				label_act.text('Input ' + select[0].value);
				label_count.text('Count of ' + select[0].value);
			});
			$('#formInsert').submit(function(event){
				var flat = $('input[id = "action" placeholder="Flat"]');
				var cash = $('input[id = "action" placeholder="Cash"]');
				if ((find_repeat(flat)==-1) && (find_empty(cash)==-1)){
					alert("Successful!");//$send_button.attr("disabled", false);
				}
				else{
					if (find_repeat(flat)==-1){
						alert("'Cash' must be positive!!!");
					}
					else
					{
						alert("'Flat' must be positive and do not repeat!!!");
					}
					event.preventDefault();
				};
			});
			/*$('#send').click(function(){
				let flat = $('input[id = "action" placeholder="Flat"]');
				let cash = $('input[id = "action" placeholder="Cash"]');
				if((find_repeat(flat)==-1) && (find_empty(cash)==-1)){
					$('#insertResult')[0].style.visibility='visible';
					AjaxFormRequest("insertResult", "formInsert", "../insert_data.php");
				}
				else{
					alert("Error, field of flat and cash must be positive");
				};
				return false;
			});*/
			$('#choose').click(function()
			{
				let param = $('#param').children();
				for (var i=0,l = param.length;i<l;i++)
				{
					param[i].remove();
				};
				$("#show").attr("disabled",false);
				switch(report[0].value)
				{
					case '1':
					$('<label for = "report">Input Year</label><div class="input-group"><input type="year" id="report" name = "year" class="form-control date"></div>')
					.appendTo('#param');
					break;
					case '2':
					$('<div class="input-group"><input type="year" id="report" name = "year" class="form-control" placeholder="Year"><input type="number" id="report" name = "flat" class = "form-control" placeholder = "Flat"></div>')
					.appendTo('#param');
					break;
				};	
			});
			$('form[action="handler.php"]').submit(function(event){
				let flat = $('input[name = "flat"]');
				if (flat[0].value!=null && (flat[0].value<0)){
					alert("'Flat' must be positive!!!");
					event.preventDefault();
				};
			});
		});
