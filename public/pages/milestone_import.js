async function saveMilestone() {
    const rows_len = $('.trr_1').length;
    if(parseInt(rows_len) <= 0){
      show_msgT(1, "No any proposal pending for submission.");
      return false;
    }
    const rows = Array.from($('.trr_1'));
    const link = $('.divMilstoneList').attr('data-action');
    const token = $('input[name="_token"]').val();
    for (const trr of rows) {
      const $trr = $(trr);
      const wc_id = $trr.find('.wc_ids').val();
      const prop_id = $trr.find('.prop_ids').val();
      const project_id = $trr.find('.project_ids').val();
      const milestones = $trr.find('.milestones').val();
  
      if (!prop_id || !project_id || !milestones) {
        console.error('One or more values are undefined:');
        continue; // Skip this iteration if any value is undefined
      }
  
      try {
        show_msg(3, '', '<b>Please wait...', 4);
        const response = await fetch(link, {
          method: 'POST',
          body: JSON.stringify({ wc_id, prop_id, project_id, milestones }),
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
          }
        }).then(async response => {
          var idd = $trr.attr('data-idd');
          $('.'+idd).find('.respMsg').removeClass('text-primary');
          $('.'+idd).find('.respMsg').removeClass('text-danger');
  
          const result = await response.json();
          if (response.ok) {
            var type = result.type;
            var message = result.message;
            //console.log('uploaded successfully:', message);
            show_msgT(type, message);
            if(type == '1'){
              var addClass = 'text-primary';
              var txt = 'Saved';
              $trr.addClass('trrSaved').removeClass('trr_1');
            }else{
              var addClass = 'text-danger';
              var txt = 'Failed';
            }
            $('.'+idd).find('.respMsg').addClass(addClass).html(txt);
          } else {
            show_msgT(2, "Something error");
            $('.'+idd).find('.respMsg').addClass('text-danger').html("Error");
          }
          Swal.close();
        });
      } catch (error) {
        console.error('Error uploading:', error);
      }
    }
}
  
  // Pie chart js start
function submitPieForm() {
	var wc_code = $('.wc_code').val();
	var fin_year = $('.fin_year').val();
	var csrfToken = $('input[name="_token"]').val();
	var url = $('#pieContainer').attr('data-link');
	$.ajax({
		type: 'POST',
		url: url, 
		data: {_token:csrfToken, wc_code:wc_code, fin_year:fin_year},
		
		success: function(response) {
			//console.log(response);
			var status = response.status;
			var result = response.result;
			var agencyCount = response.agencyCount;
			Highcharts.chart('pieContainer', {
				chart: {
					type: 'pie',
				},
				title: {
					text: ''
				},
				credits: {
					enabled: false
				},
				plotOptions: {
					pie: {
						size: '80%',
						dataLabels: {
							enabled: true,
							formatter: function () {
								return this.point.name + ': ' + this.y.toLocaleString();
							}
						}
					}
				},
				tooltip: {
					useHTML: true, 
					style: {
						width: '300px',  // Set a specific width
						whiteSpace: 'normal'  // Ensure text wraps if needed
					},
					formatter: function () {
						return this.point.name + ': ' + this.y.toLocaleString() + '<br>('+price_in_words(this.y.toFixed(2))+')';
					}
				},
				series: [{
					name: response.labelName,
					data: result
				}]
			});
			
		},
		error: function(error) {
		console.error(error);
		var status = error.status;
		}
	});
}
//submitPieForm();
///////////////////////////////////////////////////////////////
colors = ['#EE82EE', '#FF6347', '#9ACD32', '#008080', '#4169E1', '#663399'];
	var lineArr = {
	//'proposed_cost': 'Proposed Amount',
	//'processed_cost': 'Processed Amount',
	//'fuc_amount': 'FUC Amount',
	'final_cost': 'Approved Amount',
	'released_cost': 'Released Amount'
	};
//// enable above commented line if these required to print.
var barChart;
var lineChart;
///////////////////////////////////////////////////////////////////////////////

// Line chart js start
function submitBarForm() {
	if (barChart) {
		barChart.destroy();
	}
	if (lineChart) {
		lineChart.destroy();
	}
	var wc_code = $('.wc_code').val();
	var fin_year = $('.fin_year').val();
	var csrfToken = $('input[name="_token"]').val();
	var url = $('#barChart').attr('data-link');
	$.ajax({
		type: 'POST',
		url: url, 
		data: {_token:csrfToken, wc_code:wc_code, fin_year:fin_year},
		
		success: function(response) {
		var status = response.status;
		var result = response.result;
		console.log(result);

		var labelsFull = [];
		var labels = [];
		var datasets = [];
		var datasetsLine = [];
		
		var clrCnt = 0;
		$.each(lineArr, function(key, value) {
			datasets.push({
				label: lineArr[key], // Use the property name as label
				backgroundColor: colors[clrCnt],
				borderColor: colors[clrCnt],
				pointRadius: false,
				pointColor: '#ffffff',
				pointStrokeColor: colors[clrCnt], 
				pointHighlightFill: '#fff',
				pointHighlightStroke: colors[clrCnt],
				data: []
			});
			datasetsLine.push({
				type: 'line',
				label: lineArr[key],
				data: [],
				backgroundColor: 'transparent',
				borderColor: colors[clrCnt],
				pointBorderColor: colors[clrCnt],
				pointBackgroundColor: colors[clrCnt],
				fill: false
			});
			clrCnt++;
		});
		$(result).each(function(index){
			var wc_full_name = result[index].addressto;
			var wc_short_name = result[index].wc_sname;
			var disha_amt = result[index].final_cost;
			var fuc_amt = result[index].fuc_amount;
			var processed_amt = result[index].processed_cost;
			var proposed_amt = result[index].proposed_cost;
			var released_amt = result[index].released_cost;
			//console.log('processed_cost = ',processed_amt);

			labelsFull.push(wc_full_name);
			labels.push(wc_short_name);

			for (var key in result[index]) {
			//if (key !== 'addressto' && key !== 'wc_sname') {
			if (key in lineArr) {
				// Find existing dataset or create a new one if it doesn't exist
				var datasetIndex = datasets.findIndex(function(dataset) {
					return dataset.label === lineArr[key];
				});
				// Push data to the corresponding dataset
				var amt = result[index][key];
				if(parseFloat(amt) > 0){
					amt = parseFloat(amt).toFixed(2);
				}
				var amount = result[index][key];
				datasets[datasetIndex].data.push(amount);

				var datasetIndex2 = datasetsLine.findIndex(function(datasetsLine) {
					return datasetsLine.label === lineArr[key];
				});
				datasetsLine[datasetIndex2].data.push(amt);
			}
			}
		});
		var areaChartData = {
			labelsFull: labelsFull,
			labels: labels,
			datasets: datasets
		};
		var barChartCanvas = $('#barChart').get(0).getContext('2d')
		var barChartData = $.extend(true, {}, areaChartData);
		
		var line_len = areaChartData.datasets.length;
		for(var i = 0; i < line_len; i++){
			var temp = areaChartData.datasets[i];
			barChartData.datasets[i] = temp;
		}

		var barChartOptions = {
			responsive              : true,
			maintainAspectRatio     : false,
			datasetFill             : false,
			tooltips: {
				enabled: true,
				callbacks: {
					title: function(tooltipItems, data) {
						// Modify this function to change the tooltip header
						var tooltipItem = tooltipItems[0];
						var label = areaChartData.labelsFull[tooltipItem.index];
						return label;
					},
					label: function(tooltipItem, data) {
						var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
						var dataPoint = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
						var formattedValue = parseFloat(dataPoint).toLocaleString(); // Format with comma separators
						var wordsValue = price_in_words(parseFloat(dataPoint).toFixed(2));
						return [
							datasetLabel + ' : ' + formattedValue,
							'(' + wordsValue + ')'
						];
					}
				}
			},
			scales: {
			yAxes: [{
				ticks: {
				beginAtZero: true,
				maxTicksLimit: 15 // Maximum number of ticks to display
				}
			}]
			}
		}

		barChart = new Chart(barChartCanvas, {
			type: 'bar',
			data: barChartData,
			options: barChartOptions
		});
		//////  start line chart
		var ticksStyle = {fontColor:'#495057', fontStyle:'bold'}
		var mode='index';
		var intersect=true;

		var $lineChart = $('#lineChart');
		lineChart = new Chart($lineChart, {
			data: {
			labels: labels,
			datasets: datasetsLine
			},
			options: {
			maintainAspectRatio: false,
			tooltips: {
				mode: mode,
				intersect: intersect,
				callbacks: {
					title: function(tooltipItems, data) {
						var tooltipItem = tooltipItems[0];
						var label = areaChartData.labelsFull[tooltipItem.index];
						return label;
					},
					label: function(tooltipItem, data) {
						var datasetLabel = data.datasets[tooltipItem.datasetIndex].label || '';
						var dataPoint = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
						var formattedValue = parseFloat(dataPoint).toLocaleString(); // Format with comma separators
						var wordsValue = price_in_words(parseFloat(dataPoint).toFixed(2));
						return [
							datasetLabel + ' : ' + formattedValue,
							'(' + wordsValue + ')'
						];
					}
				}
			},
			hover: {
				mode: mode,
				intersect: intersect
			},
			legend: {
				display: true
			},
			scales: {
				yAxes: [{
				// display: false,
				gridLines: {
					display: true,
					lineWidth: '4px',
					color: 'rgba(0, 0, 0, .2)',
					zeroLineColor: 'transparent'
				},
				ticks: $.extend({
					beginAtZero: true,
					suggestedMax: 200
				}, ticksStyle)
				}],
				xAxes: [{
				display: true,
				gridLines: {
					display: true
				},
				ticks: ticksStyle
				}]
			}
			}
		});
		///// end line chart
		},
		error: function(error) {
		console.error(error);
		var status = error.status;
		}
	});
}
//submitBarForm();
/////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
function submitBtn(){
    submitPieForm();
    submitBarForm();
}
//submitBtn();
/////////////////////////////////////////////////////
function selectGraph(thiss){
	submitBtn();
	var chart_type = $(thiss).val();
	$('.chartDiv').css('display', 'none');
	$('.chartDiv_'+chart_type).css('display', 'block');
}
selectGraph('#graph_type');