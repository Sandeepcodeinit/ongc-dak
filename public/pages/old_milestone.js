// // Pie chart js start
// function submitPieForm() 
// {
//     var wc_code = $('.wc_code').val();
//     var fin_year = $('.fin_year').val();
//     var csrfToken = $('input[name="_token"]').val();
//     var url = $('#pieContainer').attr('data-link');
//     $.ajax({
//         type: 'POST',
//         url: url, 
//         data: {_token:csrfToken, fin_year:fin_year, wc_code:wc_code},
//         beforeSend: function() {
//             show_msg(3, '', 'Please wait...', 4);
//         },
//         success: function(response) 
//         {
//             var result = response.result;

//             var formattedResult = result.map(function(item) {
//                 return {
//                     name: item.name,
//                     y: parseFloat(item.y), 
//                     formattedAmount: item.y.toLocaleString('en-IN')
//                 };
//             });
//             Highcharts.chart('pieContainer', {
//                 chart: {
//                     type: 'pie'
//                 },
//                 title: {
//                     text: ''
//                 },
//                 credits: {
//                     enabled: false
//                 },
//                 tooltip: {
//                     formatter: function() {
//                         return '<b>' + this.point.name + '</b>: ' + this.point.formattedAmount;
//                     }
//                 },
//                 plotOptions: {
//                     pie: {
//                         dataLabels: {
//                             enabled: true,
//                             style: {
//                                 fontSize: '14px'
//                             }
//                         }
//                     }
//                 },
//                 series: [{
//                     name: response.labelName,
//                     data: formattedResult
//                 }]
//             });
//             Swal.close();
//         },
//         error: function(error) {
//             console.error(error);
//             var status = error.status;
//         }
//     });
// }


function submitPieForm() {
    Highcharts.chart('pieContainer', {
        chart: {
            type: 'pie',
            backgroundColor: 'transparent'
        },
        title: {
            text: 'Expenditure amount incurred on CSR projects (FY 24-25) <br> Total : 950cr',
            style: {
                fontWeight: 'bold'
            }
        },
        credits: {
            enabled: false
        },
        tooltip: {
            pointFormat: '<b>Amount</b>: {point.y}' // Show name and value on hover
        },
        plotOptions: {
            pie: {
                showInLegend: true,
                dataLabels: {
                    enabled: false // Disable labels on the slices
                },
                allowPointSelect: true,
                cursor: 'pointer'
            }
        },
        series: [
            {
                name: 'Amount',
                colorByPoint: true,
                data: [
                    {
                        name: 'Expenditure on projects',
                        y: 895
                    },
                    {
                        name: 'Admin Cost',
                        sliced: true,
                        selected: true,
                        y: 25
                    },
                    {
                        name: 'Set-off',
                        y: 30
                    },
                   
                ]
            }
        ]
    });
    
}

// Pie chart js end
 
// Bar chart js start
// function submitBarForm() {
//     var wc_code = $('.wc_code').val();
//     var fin_year = $('.fin_year').val();
//     var csrfToken = $('input[name="_token"]').val();
//     var url = $('#barChart').attr('data-link');

//     $.ajax({
//         type: 'POST',
//         url: url,
//         data: {
//             _token: csrfToken,
//             wc_code: wc_code,
//             fin_year: fin_year
//         },
//         beforeSend: function() {
//             show_msg(3, '', 'Please wait...', 4);
//         },
//         success: function (response) {
//             // if (response.status === 1) {
//                 var data = response.result;

//                 // Modify work center names to remove "work center" prefix
//                 var categories = data.map(item => item.work_center_name.replace(/work center\s*/i, '').trim());
//                 var approvedAmounts = data.map(item => item.total_approved_amount);
//                 var releasedAmounts = data.map(item => item.total_released_amount); 
                

//                 // Create the Highcharts column chart
//                 Highcharts.chart('barChart', {
//                     chart: {
//                         type: 'column',
//                         height: Math.max(800, data.length * 50)
//                     },
//                     title: {
//                         text: 'Approved and Released Amounts by Work Center'
//                     },
//                     credits: {
//                         enabled: false
//                     },
//                     xAxis: {
//                         categories: categories,
//                         title: {
//                             text: ''
//                         },
//                         labels: {
//                             rotation: -45, 
//                             style: {
//                                 color: '#0d0d0d',
//                                 fontSize: '14px',
//                             }
//                         }
//                     },
//                     yAxis: {
//                         min: 0,
//                         title: {
//                             text: 'Amount'
//                         },
//                         labels: {
//                             formatter: function() {
//                                 return Highcharts.numberFormat(this.value, 0, '', ',');
//                             },
//                             style: {
//                                 color: '#0d0d0d',
//                                 fontSize: '14px',
//                             }
//                         }
//                     },
//                     plotOptions: {
//                         column: {
//                             pointPadding: 0.1,  
//                             borderWidth: 0,
//                             groupPadding: 0.1,
//                             dataLabels: {
//                                 enabled: true,
//                                 formatter: function() {
//                                     return Highcharts.numberFormat(this.y, 0, '', ',');
//                                 },
//                                 style: {
//                                     fontSize: '12px',
//                                     color: '#0d0d0d'
//                                 }
//                             }
//                         }
//                     },
//                     tooltip: {
//                         shared: true,
//                         formatter: function() {
//                             return `<b>${this.x}</b><br/>Total Approved Amount: ${Highcharts.numberFormat(this.points[0].y, 0, '', ',')}<br/>Total Released Amount: ${Highcharts.numberFormat(this.points[1].y, 0, '', ',')}`;
//                         }
//                     },
//                     series: [{
//                         name: 'Total Approved Amount',
//                         data: approvedAmounts,
//                         color: '#F86A35'
//                     }, {
//                         name: 'Total Released Amount',
//                         data: releasedAmounts,
//                         color: '#00e272'
//                     }]
//                 });
//                 Swal.close();
//             // } else {
//             //     console.error('Unexpected response status:', response.status);
//             // }
//         },
//         error: function (xhr, status, error) {
//             console.error('Error fetching data:', error, xhr.responseText);
//         }
//     });
// }

// Bar chart js end
 
// Line chart js start
// function submitLineForm() {
//     var wc_code = $('.wc_code').val();
//     var fin_year = $('.fin_year').val();
//     var csrfToken = $('input[name="_token"]').val();
//     var url = $('#barChart').attr('data-link');

//     $.ajax({
//         type: 'POST',
//         url: url,
//         data: {
//             _token: csrfToken,
//             wc_code: wc_code,
//             fin_year: fin_year
//         },
//         beforeSend: function() {
//             show_msg(3, '', 'Please wait...', 4);
//         },
//         success: function (response) {
//             if (response.status === 1) {
//                 var data = response.result;
//                 var categories = data.map(item => item.work_center_name);
//                 var releasedAmounts = data.map(item => item.total_released_amount);
//                 var fucAmounts = data.map(item => item.total_fuc_amount);

//                 // Create the Highcharts line chart
//                 Highcharts.chart('lineChart', {
//                     chart: {
//                         type: 'line',
//                     },
//                     title: {
//                         text: 'Released Amounts and FUC Received by Work Center'
//                     },
//                     credits: {
//                         enabled: false
//                     },
//                     xAxis: {
//                         categories: categories,
//                         title: {
//                             text: ''
//                         },
//                         labels: {
//                             style: {
//                                 color: '#0d0d0d',
//                                 fontSize: '14px',
//                             }
//                         }
//                     },
//                     yAxis: {
//                         min: 0,
//                         title: {
//                             text: 'Amount'
//                         },
//                         labels: {
//                             formatter: function () {
//                                 return Highcharts.numberFormat(this.value, 0, '', ',');
//                             },
//                             style: {
//                                 color: '#0d0d0d',
//                                 fontSize: '14px',
//                             }
//                         }
//                     },
//                     tooltip: {
//                         formatter: function () {
//                             return `<b>${this.x}</b><br/>Total Released Amount: ${Highcharts.numberFormat(this.points[0].y, 0, '', ',')}<br/>Total FUC Amount: ${Highcharts.numberFormat(this.points[1].y, 0, '', ',')}`;
//                         },
//                         shared: true
//                     },
//                     series: [{
//                         name: 'Total Released Amount',
//                         data: releasedAmounts,
//                         color: '#00e272',
//                         dataLabels: {
//                             enabled: true,
//                             formatter: function () {
//                                 return Highcharts.numberFormat(this.y, 0, '', ',');
//                             }
//                         }
//                     }, {
//                         name: 'Total FUC Amount',
//                         data: fucAmounts,
//                         color: '#FFB300',
//                         dataLabels: {
//                             enabled: true,
//                             formatter: function () {
//                                 return Highcharts.numberFormat(this.y, 0, '', ',');
//                             }
//                         }
//                     }]
//                 });
//                 Swal.close();
//             } else {
//                 console.error('Unexpected response status:', response.status);
//             }
//         },
//         error: function (xhr, status, error) {
//             console.error('Error fetching data:', error, xhr.responseText);
//         }
//     });
// }

function submitLineForm() {

    Highcharts.chart('lineChart', {
        chart: {
            type: 'line' // Set the chart type to line
        },
        title: {
            text: 'Expenditure trend line (FY 24-25)'
        },
        xAxis: {
            categories: ['11-Sep', '20-Sep', '27-Sep', '1-Oct', '14-Oct', '25-Sep', '1-Nov','11-Nov','18-Nov','22-Nov','27-Nov','5-Dec', '12-Dec','26-Dec','2-Jan', '9-Jan'] // Example categories (months)
        },
        yAxis: {
            title: {
                text: 'Values'
            }
        },
        tooltip: {
            shared: true, // Show the tooltip for all series when hovering over a point
            valueSuffix: ' units' // Optional: add a suffix to the values
        },
        plotOptions: {
            line: {
                dataLabels: {
                    enabled: false // Hide data labels by default
                },
                enableMouseTracking: true // Enable mouse tracking for tooltips
            }
        },
        series: [
            {
                name: 'Amount',
                data: [167.99, 172.51, 186, 191.18, 226.10, 254, 288, 292, 332, 336, 340, 361, 478, 497, 501, 597] // Data for Series 1
            }
        ]
    });
    
}

function submitBarForm()
{
    Highcharts.chart('barContainer', {
        chart: {
            type: 'bar',
            backgroundColor: 'transparent'
        },
        title: {
            text: 'Action Plan for (FY 2025-26)<br> Total : 1212cr',
            style: {
                fontWeight: 'bold'
            },
            align: 'center'
        },
        xAxis: {
            categories: [
                'Set-off from FY 24-25', 
                'PMIS',
                'Expenditure on KVs by WorkCentres', 
                'Panchayati Raj & MOTA', 
                'Admin Cost', 
                'Ongoing Projects',
                'New Medical Projects',
                'Other New Projects',
                'Allocated To Work-Centers'                
            ],
            labels: {
                style: {
                    fontSize: '16px', 
                }
            },
        },
        
        yAxis: {
            min: 0,
            title: {
                text: ''
            }
        },
        legend: {
            reversed: true
        },
        plotOptions: {
            series: {
                stacking: 'normal',
                dataLabels: {
                    enabled: true,
                    formatter: function () {
                        return this.y !== 0 ? this.y : null; // Display only non-zero values
                    }
                }
            }
        },
        series: [
            {
                name: 'Action Plan',
                data: [42,80,80,70, 40, 350, 200, 250, 100]
            },
           
        ]
    });    
}


// Line chart js end

function submitAgencyPieForm() {
    var csrfToken = $('input[name="_token"]').val();
    var url = $('#agency-pie-container').attr('data-link');

    $.ajax({
        type: 'POST',
        url: url,
        data: {
            _token: csrfToken,
        },
        beforeSend: function() {
            show_msg(3, '', 'Please wait...', 4);
        },
        success: function(response) {
            var result = response.result;

            var formattedResult = result.map(function(item) {
                return {
                    name: item.name,
                    y: parseFloat(item.y),
                    formattedAmount: item.y.toLocaleString('en-IN')
                };
            });

            Highcharts.chart('agency-pie-container', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: ''
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.point.name + '</b>: ' + this.point.formattedAmount;
                    }
                },
                plotOptions: {
                    pie: {
                        size: '50%',
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '14px'
                            }
                        }
                    }
                },
                
                series: [{
                    name: response.labelName,
                    data: formattedResult
                }]
            });

            Swal.close();
        },
        error: function(error) {
            console.error(error);
        }
    });
}

function submitProposalPieForm() {
    var csrfToken = $('input[name="_token"]').val();
    var url = $('#proposal-pie-container').attr('data-link');

    $.ajax({
        type: 'POST',
        url: url,
        data: {
            _token: csrfToken,
        },
        beforeSend: function() {
            show_msg(3, '', 'Please wait...', 4);
        },
        success: function(response) {
            var result = response.result;

            var formattedResult = result.map(function(item) {
                return {
                    name: item.name,
                    y: parseFloat(item.y),
                    formattedAmount: item.y.toLocaleString('en-IN')
                };
            });

            Highcharts.chart('proposal-pie-container', {
                chart: {
                    type: 'pie',
                    backgroundColor: 'transparent'
                },
                title: {
                    text: ''
                },
                credits: {
                    enabled: false
                },
                tooltip: {
                    formatter: function() {
                        return '<b>' + this.point.name + '</b>: ' + this.point.formattedAmount;
                    }
                },
                plotOptions: {
                    pie: {
                        size: '50%',
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '14px'
                            }
                        }
                    }
                },
                
                series: [{
                    name: response.labelName,
                    data: formattedResult
                }]
            });

            Swal.close();
        },
        error: function(error) {
            console.error(error);
        }
    });
}
 
function submitBtn(){
    submitPieForm();
    // submitBarForm();
    // submitLineForm();
    submitBarForm();

    submitAgencyPieForm();
    submitProposalPieForm();
    
}
submitBtn();
 
// if any work center selected then do not diaplay line chart
$(document).ready(function() {
    $('.wc_code').on('change', function() {
        var selectedValue = $(this).val();
        if(selectedValue != '')
        {
            $('.line_chart').hide();
        }
        else{
            $('.line_chart').show();
        }
    });
});
