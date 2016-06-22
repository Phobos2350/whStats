$(document).ready(function () {
  $.getJSON('getStats.php', function(json) {
    console.log(json);
    updateCharts(json);
  }).error(function(error){console.log(error);});
});

setInterval(function() {
  var minutes = 60 - new Date().getMinutes();
  var seconds = 60 - new Date().getSeconds();
  if(seconds == "60") {
    seconds = "0";
  }
  minutes = pad(minutes);
  seconds = pad(seconds);
  if(minutes == "60") {
    $('#refreshText').text("Retrieving new kills...Standby ");
    $('#countdown').text("00:" + seconds);
  } else {
    $('#refreshText').text("Time until next refresh ");
    $('#countdown').text(minutes + ":" + seconds);
  }

  if(seconds == "00") {
    seconds = "59";
  }

  if(minutes == "60" && seconds == "59") {
    setTimeout(function(){
      $.getJSON('getStats.php', function(json) {
        console.log(json);
        updateCharts(json);
      }).error(function(error){console.log(error);});
    },30000);
  }

  if(minutes == "50" && seconds == "59" || minutes == "40" && seconds == "59" || minutes == "30" && seconds == "59" || minutes == "20" && seconds == "59" || minutes == "10" && seconds == "59") {
    setTimeout(function(){
      $.getJSON('getStats.php', function(json) {
        console.log(json);
        updateCharts(json);
      }).error(function(error){console.log(error);});
    },10000);
  }

}, 1000);

function pad(n){return n<10 ? '0'+n : n}

function addCommas(nStr)
{
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

function updateCharts(data) {

  $('canvas').parent().each(function () {
      //get child canvas id
      childCanvasId = $(this).children().attr('id');
      //remove canvas
      $('#'+childCanvasId).remove();
      // append new canvas to the parent again
      $(this).append('<canvas id="'+childCanvasId+'" width="300" height="300"></canvas>');
  });

  var dataHour = {
    type: 'bar',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          label: 'Pod Kills',
          backgroundColor: "rgba(255,0,0,0.5)",
          data: [data[0]['podKillsHour'], data[1]['podKillsHour'], data[2]['podKillsHour'], data[3]['podKillsHour'], data[4]['podKillsHour'], data[5]['podKillsHour']]
        },
        {
          label: 'Faction Kills',
          backgroundColor: "rgba(51,153,102,0.5)",
          data: [data[0]['factionKillsHour'], data[1]['factionKillsHour'], data[2]['factionKillsHour'], data[3]['factionKillsHour'], data[4]['factionKillsHour'], data[5]['factionKillsHour']]
        },
        {
          label: 'T1 Kills',
          backgroundColor: "rgba(102,153,153,0.5)",
          data: [data[0]['t1KillsHour'], data[1]['t1KillsHour'], data[2]['t1KillsHour'], data[3]['t1KillsHour'], data[4]['t1KillsHour'], data[5]['t1KillsHour']]
        },
        {
          label: 'T2 Kills',
          backgroundColor: "rgba(255,204,0,0.5)",
          data: [data[0]['t2KillsHour'], data[1]['t2KillsHour'], data[2]['t2KillsHour'], data[3]['t2KillsHour'], data[4]['t2KillsHour'], data[5]['t2KillsHour']]
        },
        {
          label: 'T3 Kills',
          backgroundColor: "rgba(255,102,0,0.5)",
          data: [data[0]['t3KillsHour'], data[1]['t3KillsHour'], data[2]['t3KillsHour'], data[3]['t3KillsHour'], data[4]['t3KillsHour'], data[5]['t3KillsHour']]
        },
        {
          label: 'Cap Kills',
          backgroundColor: "rgba(204,51,255,0.8)",
          data: [data[0]['capKillsHour'], data[1]['capKillsHour'], data[2]['capKillsHour'], data[3]['capKillsHour'], data[4]['capKillsHour'], data[5]['capKillsHour']]
        },
        {
          label: 'Citadel Kills',
          backgroundColor: "rgba(51,102,255,0.8)",
          data: [data[0]['citadelKillsHour'], data[1]['citadelKillsHour'], data[2]['citadelKillsHour'], data[3]['citadelKillsHour'], data[4]['citadelKillsHour'], data[5]['citadelKillsHour']]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        yAxes: [{
          ticks: {
            suggestedMax: 5,
            min: 0,
            stepSize: 2
          }
        }]
      }
    }
  };
  var ctxHour = new Chart($('#chartHour'), dataHour);

  var dataHourC1 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[0]['industrialKillsHour'], data[0]['frigateKillsHour'], data[0]['cruiserKillsHour'], data[0]['battleshipKillsHour'], data[0]['capKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataHourC2 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[1]['industrialKillsHour'], data[1]['frigateKillsHour'], data[1]['cruiserKillsHour'], data[1]['battleshipKillsHour'], data[1]['capKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataHourC3 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[2]['industrialKillsHour'], data[2]['frigateKillsHour'], data[2]['cruiserKillsHour'], data[2]['battleshipKillsHour'], data[2]['capKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataHourC4 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[3]['industrialKillsHour'], data[3]['frigateKillsHour'], data[3]['cruiserKillsHour'], data[3]['battleshipKillsHour'], data[3]['capKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataHourC5 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[4]['industrialKillsHour'], data[4]['frigateKillsHour'], data[4]['cruiserKillsHour'], data[4]['battleshipKillsHour'], data[4]['capKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataHourC6 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[5]['industrialKillsHour'], data[5]['frigateKillsHour'], data[5]['cruiserKillsHour'], data[5]['battleshipKillsHour'], data[5]['capKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxHourC1 = (data[0]['hourKills'] - data[0]['citadelKillsHour'] - data[0]['structureKillsHour']) == 0 ?
    $('#chartHourC1').parent().html('No Ship Kills!') : new Chart($('#chartHourC1'), dataHourC1);
  var ctxHourC2 = (data[1]['hourKills'] - data[1]['citadelKillsHour'] - data[1]['structureKillsHour']) == 0 ?
    $('#chartHourC2').parent().html('No Ship Kills!') : new Chart($('#chartHourC2'), dataHourC2);
  var ctxHourC3 = (data[2]['hourKills'] - data[2]['citadelKillsHour'] - data[2]['structureKillsHour']) == 0 ?
    $('#chartHourC3').parent().html('No Ship Kills!') : new Chart($('#chartHourC3'), dataHourC3);
  var ctxHourC4 = (data[3]['hourKills'] - data[3]['citadelKillsHour'] - data[3]['structureKillsHour']) == 0 ?
    $('#chartHourC4').parent().html('No Ship Kills!') : new Chart($('#chartHourC4'), dataHourC4);
  var ctxHourC5 = (data[4]['hourKills'] - data[4]['citadelKillsHour'] - data[4]['structureKillsHour']) == 0 ?
    $('#chartHourC5').parent().html('No Ship Kills!') : new Chart($('#chartHourC5'), dataHourC5);
  var ctxHourC6 = (data[5]['hourKills'] - data[5]['citadelKillsHour'] - data[5]['structureKillsHour']) == 0 ?
    $('#chartHourC6').parent().html('No Ship Kills!') : new Chart($('#chartHourC6'), dataHourC6);

  // Fleet Fights
  var dataFleetC1 = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[0]['smallKillsHour'], data[0]['fleetKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      },
    }
  };
  var dataFleetC2 = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[1]['smallKillsHour'], data[1]['fleetKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC3 = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[2]['smallKillsHour'], data[2]['fleetKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC4 = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[3]['smallKillsHour'], data[3]['fleetKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC5 = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[4]['smallKillsHour'], data[4]['fleetKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC6 = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[5]['smallKillsHour'], data[5]['fleetKillsHour']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxFleetC1 = (data[0]['hourKills'] - data[0]['citadelKillsHour'] - data[0]['structureKillsHour']) == 0 ?
    $('#chartFleetC1').parent().css('height', $('#chartFleetC1').parent().parent().next().children('.small-chart-holder').css('height')).html('No Ship Kills!') : new Chart($('#chartFleetC1'), dataFleetC1);
  var ctxFleetC2 = (data[1]['hourKills'] - data[1]['citadelKillsHour'] - data[1]['structureKillsHour']) == 0 ?
    $('#chartFleetC2').parent().css('height', $('#chartFleetC2').parent().parent().next().children('.small-chart-holder').css('height')).html('No Ship Kills!') : new Chart($('#chartFleetC2'), dataFleetC2);
  var ctxFleetC3 = (data[2]['hourKills'] - data[2]['citadelKillsHour'] - data[2]['structureKillsHour']) == 0 ?
    $('#chartFleetC3').parent().css('height', $('#chartFleetC3').parent().parent().next().children('.small-chart-holder').css('height')).html('No Ship Kills!') : new Chart($('#chartFleetC3'), dataFleetC3);
  var ctxFleetC4 = (data[3]['hourKills'] - data[3]['citadelKillsHour'] - data[3]['structureKillsHour']) == 0 ?
    $('#chartFleetC4').parent().css('height', $('#chartFleetC4').parent().parent().next().children('.small-chart-holder').css('height')).html('No Ship Kills!') : new Chart($('#chartFleetC4'), dataFleetC4);
  var ctxFleetC5 = (data[4]['hourKills'] - data[4]['citadelKillsHour'] - data[4]['structureKillsHour']) == 0 ?
    $('#chartFleetC5').parent().css('height', $('#chartFleetC5').parent().parent().next().children('.small-chart-holder').css('height')).html('No Ship Kills!') : new Chart($('#chartFleetC5'), dataFleetC5);
  var ctxFleetC6 = (data[5]['hourKills'] - data[5]['citadelKillsHour'] - data[5]['structureKillsHour']) == 0 ?
    $('#chartFleetC6').parent().css('height', $('#chartFleetC6').parent().parent().next().children('.small-chart-holder').css('height')).html('No Ship Kills!') : new Chart($('#chartFleetC6'), dataFleetC6);

  // ISK CHART

  var dataTotalBillionsC1Hour = data[0]['hourISK'];
  dataTotalBillionsC1Hour /= Math.pow(10, 3);
  var dataTotalBillionsC2Hour = data[1]['hourISK'];
  dataTotalBillionsC2Hour /= Math.pow(10, 3);
  var dataTotalBillionsC3Hour = data[2]['hourISK'];
  dataTotalBillionsC3Hour /= Math.pow(10, 3);
  var dataTotalBillionsC4Hour = data[3]['hourISK'];
  dataTotalBillionsC4Hour /= Math.pow(10, 3);
  var dataTotalBillionsC5Hour = data[4]['hourISK'];
  dataTotalBillionsC5Hour /= Math.pow(10, 3);
  var dataTotalBillionsC6Hour = data[5]['hourISK'];
  dataTotalBillionsC6Hour /= Math.pow(10, 3);

  var dataISKHour = {
    type: 'line',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          label: 'Total ISK Killed',
          backgroundColor: "rgba(255,107,107,0.5)",
          data: [dataTotalBillionsC1Hour, dataTotalBillionsC2Hour, dataTotalBillionsC3Hour, dataTotalBillionsC4Hour, dataTotalBillionsC5Hour, dataTotalBillionsC6Hour]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  };

  var ctxISKHour = new Chart($('#chartISKHour'), dataISKHour);

  var c1Avg = data[0]['hourISK']/data[0]['hourKills'];
  var c2Avg = data[1]['hourISK']/data[1]['hourKills'];
  var c3Avg = data[2]['hourISK']/data[2]['hourKills'];
  var c4Avg = data[3]['hourISK']/data[3]['hourKills'];
  var c5Avg = data[4]['hourISK']/data[4]['hourKills'];
  var c6Avg = data[5]['hourISK']/data[5]['hourKills'];

  $("#c1Avg").html( isNaN(c1Avg) ? " No Data" : c1Avg.toFixed(1) + " Million ISK");
  $("#c2Avg").html( isNaN(c2Avg) ? " No Data" : c2Avg.toFixed(1) + " Million ISK");
  $("#c3Avg").html( isNaN(c3Avg) ? " No Data" : c3Avg.toFixed(1) + " Million ISK");
  $("#c4Avg").html( isNaN(c4Avg) ? " No Data" : c4Avg.toFixed(1) + " Million ISK");
  $("#c5Avg").html( isNaN(c5Avg) ? " No Data" : c5Avg.toFixed(1) + " Million ISK");
  $("#c6Avg").html( isNaN(c6Avg) ? " No Data" : c6Avg.toFixed(1) + " Million ISK");

  // TOTALS

  var dataTotal = {
    type: 'bar',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          label: 'Pod Kills',
          backgroundColor: "rgba(255,0,0,0.5)",
          data: [data[0]['podKillsTotal'], data[1]['podKillsTotal'], data[2]['podKillsTotal'], data[3]['podKillsTotal'], data[4]['podKillsTotal'], data[5]['podKillsTotal']]
        },
        {
          label: 'Faction Kills',
          backgroundColor: "rgba(51,153,102,0.5)",
          data: [data[0]['factionKillsTotal'], data[1]['factionKillsTotal'], data[2]['factionKillsTotal'], data[3]['factionKillsTotal'], data[4]['factionKillsTotal'], data[5]['factionKillsTotal']]
        },
        {
          label: 'T1 Kills',
          backgroundColor: "rgba(102,153,153,0.5)",
          data: [data[0]['t1KillsTotal'], data[1]['t1KillsTotal'], data[2]['t1KillsTotal'], data[3]['t1KillsTotal'], data[4]['t1KillsTotal'], data[5]['t1KillsTotal']]
        },
        {
          label: 'T2 Kills',
          backgroundColor: "rgba(255,204,0,0.5)",
          data: [data[0]['t2KillsTotal'], data[1]['t2KillsTotal'], data[2]['t2KillsTotal'], data[3]['t2KillsTotal'], data[4]['t2KillsTotal'], data[5]['t2KillsTotal']]
        },
        {
          label: 'T3 Kills',
          backgroundColor: "rgba(255,102,0,0.5)",
          data: [data[0]['t3KillsTotal'], data[1]['t3KillsTotal'], data[2]['t3KillsTotal'], data[3]['t3KillsTotal'], data[4]['t3KillsTotal'], data[5]['t3KillsTotal']]
        },
        {
          label: 'Cap Kills',
          backgroundColor: "rgba(204,51,255,0.8)",
          data: [data[0]['capKillsTotal'], data[1]['capKillsTotal'], data[2]['capKillsTotal'], data[3]['capKillsTotal'], data[4]['capKillsTotal'], data[5]['capKillsTotal']]
        },
        {
          label: 'Citadel Kills',
          backgroundColor: "rgba(51,102,255,0.8)",
          data: [data[0]['citadelKillsTotal'], data[1]['citadelKillsTotal'], data[2]['citadelKillsTotal'], data[3]['citadelKillsTotal'], data[4]['citadelKillsTotal'], data[5]['citadelKillsTotal']]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
    }
  };
  var ctxTotal = new Chart($('#chartTotal'), dataTotal);

  var dataTotalC1 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[0]['industrialKillsTotal'], data[0]['frigateKillsTotal'], data[0]['cruiserKillsTotal'], data[0]['battleshipKillsTotal'], data[0]['capKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalC2 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[1]['industrialKillsTotal'], data[1]['frigateKillsTotal'], data[1]['cruiserKillsTotal'], data[1]['battleshipKillsTotal'], data[1]['capKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalC3 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[2]['industrialKillsTotal'], data[2]['frigateKillsTotal'], data[2]['cruiserKillsTotal'], data[2]['battleshipKillsTotal'], data[2]['capKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalC4 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[3]['industrialKillsTotal'], data[3]['frigateKillsTotal'], data[3]['cruiserKillsTotal'], data[3]['battleshipKillsTotal'], data[3]['capKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalC5 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[4]['industrialKillsTotal'], data[4]['frigateKillsTotal'], data[4]['cruiserKillsTotal'], data[4]['battleshipKillsTotal'], data[4]['capKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalC6 = {
    type: 'doughnut',
    data: {
      labels: ['Industrial', 'Frigate/Destroyer', 'Cruiser/BC', 'Battleship', 'Caps'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
            "rgba(78,186,186,0.5)",
            "rgba(92,221,92,0.5)",
            "rgba(233,37,37,0.8)",
          ],
          data: [data[5]['industrialKillsTotal'], data[5]['frigateKillsTotal'], data[5]['cruiserKillsTotal'], data[5]['battleshipKillsTotal'], data[5]['capKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxTotalC1 = (data[0]['totalKills'] - data[0]['citadelKillsTotal'] - data[0]['structureKillsTotal']) == 0 ? $('#chartTotalC1').parent().html("No Ship Kills!") : new Chart($('#chartTotalC1'), dataTotalC1);
  var ctxTotalC2 = (data[1]['totalKills'] - data[1]['citadelKillsTotal'] - data[1]['structureKillsTotal']) == 0 ? $('#chartTotalC2').parent().html("No Ship Kills!") : new Chart($('#chartTotalC2'), dataTotalC2);
  var ctxTotalC3 = (data[2]['totalKills'] - data[2]['citadelKillsTotal'] - data[2]['structureKillsTotal']) == 0 ? $('#chartTotalC3').parent().html("No Ship Kills!") : new Chart($('#chartTotalC3'), dataTotalC3);
  var ctxTotalC4 = (data[3]['totalKills'] - data[3]['citadelKillsTotal'] - data[3]['structureKillsTotal']) == 0 ? $('#chartTotalC4').parent().html("No Ship Kills!") : new Chart($('#chartTotalC4'), dataTotalC4);
  var ctxTotalC5 = (data[4]['totalKills'] - data[4]['citadelKillsTotal'] - data[4]['structureKillsTotal']) == 0 ? $('#chartTotalC5').parent().html("No Ship Kills!") : new Chart($('#chartTotalC5'), dataTotalC5);
  var ctxTotalC6 = (data[5]['totalKills'] - data[5]['citadelKillsTotal'] - data[5]['structureKillsTotal']) == 0 ? $('#chartTotalC6').parent().html("No Ship Kills!") : new Chart($('#chartTotalC6'), dataTotalC6);

  // Fleet Fights
  var dataFleetC1Total = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[0]['smallKillsTotal'], data[0]['fleetKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC2Total = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[1]['smallKillsTotal'], data[1]['fleetKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC3Total = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[2]['smallKillsTotal'], data[2]['fleetKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC4Total = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[3]['smallKillsTotal'], data[3]['fleetKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC5Total = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[4]['smallKillsTotal'], data[4]['fleetKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataFleetC6Total = {
    type: 'pie',
    data: {
      labels: ['Small Fight/Gank', 'Large Fight/Fleet'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,107,107,0.5)",
            "rgba(255,174,107,0.5)",
          ],
          data: [data[5]['smallKillsTotal'], data[5]['fleetKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxFleetC1Total = (data[0]['totalKills'] - data[0]['citadelKillsTotal'] - data[0]['structureKillsTotal']) == 0 ? $('#chartFleetC1Total').parent().html("No Ship Kills!") : new Chart($('#chartFleetC1Total'), dataFleetC1Total);
  var ctxFleetC2Total = (data[1]['totalKills'] - data[1]['citadelKillsTotal'] - data[1]['structureKillsTotal']) == 0 ? $('#chartFleetC2Total').parent().html("No Ship Kills!") : new Chart($('#chartFleetC2Total'), dataFleetC2Total);
  var ctxFleetC3Total = (data[2]['totalKills'] - data[2]['citadelKillsTotal'] - data[2]['structureKillsTotal']) == 0 ? $('#chartFleetC3Total').parent().html("No Ship Kills!") : new Chart($('#chartFleetC3Total'), dataFleetC3Total);
  var ctxFleetC4Total = (data[3]['totalKills'] - data[3]['citadelKillsTotal'] - data[3]['structureKillsTotal']) == 0 ? $('#chartFleetC4Total').parent().html("No Ship Kills!") : new Chart($('#chartFleetC4Total'), dataFleetC4Total);
  var ctxFleetC5Total = (data[4]['totalKills'] - data[4]['citadelKillsTotal'] - data[4]['structureKillsTotal']) == 0 ? $('#chartFleetC5Total').parent().html("No Ship Kills!") : new Chart($('#chartFleetC5Total'), dataFleetC5Total);
  var ctxFleetC6Total = (data[5]['totalKills'] - data[5]['citadelKillsTotal'] - data[5]['structureKillsTotal']) == 0 ? $('#chartFleetC6Total').parent().html("No Ship Kills!") : new Chart($('#chartFleetC6Total'), dataFleetC6Total);

  // ISK CHART
  var dataTotalBillionsC1 = data[0]['totalISK'];
  dataTotalBillionsC1 /= Math.pow(10, 3);
  var dataTotalBillionsC2 = data[1]['totalISK'];
  dataTotalBillionsC2 /= Math.pow(10, 3);
  var dataTotalBillionsC3 = data[2]['totalISK'];
  dataTotalBillionsC3 /= Math.pow(10, 3);
  var dataTotalBillionsC4 = data[3]['totalISK'];
  dataTotalBillionsC4 /= Math.pow(10, 3);
  var dataTotalBillionsC5 = data[4]['totalISK'];
  dataTotalBillionsC5 /= Math.pow(10, 3);
  var dataTotalBillionsC6 = data[5]['totalISK'];
  dataTotalBillionsC6 /= Math.pow(10, 3);

  var dataISKTotal = {
    type: 'line',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          label: 'Total ISK Killed',
          backgroundColor: "rgba(255,107,107,0.5)",
          data: [dataTotalBillionsC1, dataTotalBillionsC2, dataTotalBillionsC3, dataTotalBillionsC4, dataTotalBillionsC5, dataTotalBillionsC6]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
    }
  };

  var ctxISKTotal = new Chart($('#chartISKTotal'), dataISKTotal);

  var c1AvgTotal = data[0]['totalISK']/data[0]['totalKills'];
  var c2AvgTotal = data[1]['totalISK']/data[1]['totalKills'];
  var c3AvgTotal = data[2]['totalISK']/data[2]['totalKills'];
  var c4AvgTotal = data[3]['totalISK']/data[3]['totalKills'];
  var c5AvgTotal = data[4]['totalISK']/data[4]['totalKills'];
  var c6AvgTotal = data[5]['totalISK']/data[5]['totalKills'];

  $("#c1AvgTotal").html( c1AvgTotal.toFixed(1) + " Million ISK");
  $("#c2AvgTotal").html( c2AvgTotal.toFixed(1) + " Million ISK");
  $("#c3AvgTotal").html( c3AvgTotal.toFixed(1) + " Million ISK");
  $("#c4AvgTotal").html( c4AvgTotal.toFixed(1) + " Million ISK");
  $("#c5AvgTotal").html( c5AvgTotal.toFixed(1) + " Million ISK");
  $("#c6AvgTotal").html( c6AvgTotal.toFixed(1) + " Million ISK");

  var dataTotalCarrier = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          backgroundColor: [
            "rgba(102,255,204,0.5)",
            "rgba(153,204,255,0.5)",
            "rgba(0,51,204,0.5)",
            "rgba(102,153,0,0.5)",
            "rgba(255,102,0,0.5)",
            "rgba(204,0,0,0.5)",
          ],
          data: [data[0]['carrierKillsTotal'], data[1]['carrierKillsTotal'], data[2]['carrierKillsTotal'], data[3]['carrierKillsTotal'], data[4]['carrierKillsTotal'], data[5]['carrierKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalDread = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          backgroundColor: [
            "rgba(102,255,204,0.5)",
            "rgba(153,204,255,0.5)",
            "rgba(0,51,204,0.5)",
            "rgba(102,153,0,0.5)",
            "rgba(255,102,0,0.5)",
            "rgba(204,0,0,0.5)",
          ],
          data: [data[0]['dreadKillsTotal'], data[1]['dreadKillsTotal'], data[2]['dreadKillsTotal'], data[3]['dreadKillsTotal'], data[4]['dreadKillsTotal'], data[5]['dreadKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalFAX = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          backgroundColor: [
            "rgba(102,255,204,0.5)",
            "rgba(153,204,255,0.5)",
            "rgba(0,51,204,0.5)",
            "rgba(102,153,0,0.5)",
            "rgba(255,102,0,0.5)",
            "rgba(204,0,0,0.5)",
          ],
          data: [data[0]['forceAuxKillsTotal'], data[1]['forceAuxKillsTotal'], data[2]['forceAuxKillsTotal'], data[3]['forceAuxKillsTotal'], data[4]['forceAuxKillsTotal'], data[5]['forceAuxKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var dataTotalLogi = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [
        {
          backgroundColor: [
            "rgba(102,255,204,0.5)",
            "rgba(153,204,255,0.5)",
            "rgba(0,51,204,0.5)",
            "rgba(102,153,0,0.5)",
            "rgba(255,102,0,0.5)",
            "rgba(204,0,0,0.5)",
          ],
          data: [data[0]['logiKillsTotal'], data[1]['logiKillsTotal'], data[2]['logiKillsTotal'], data[3]['logiKillsTotal'], data[4]['logiKillsTotal'], data[5]['logiKillsTotal']]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };

  var ctxTotalCarrier = (data[0]['carrierKillsTotal'] + data[1]['carrierKillsTotal'] + data[2]['carrierKillsTotal'] + data[3]['carrierKillsTotal'] + data[4]['carrierKillsTotal'] + data[5]['carrierKillsTotal']) == 0 ? $('#chartTotalCarrier').parent().html("No Ship Kills!") : new Chart($('#chartTotalCarrier'), dataTotalCarrier);
  var ctxTotalDread = (data[0]['dreadKillsTotal'] + data[1]['dreadKillsTotal'] + data[2]['dreadKillsTotal'] + data[3]['dreadKillsTotal'] + data[4]['dreadKillsTotal'] + data[5]['dreadKillsTotal']) == 0 ? $('#chartTotalDread').parent().html("No Ship Kills!") : new Chart($('#chartTotalDread'), dataTotalDread);
  var ctxTotalFAX = (data[0]['forceAuxKillsTotal'] + data[1]['forceAuxKillsTotal'] + data[2]['forceAuxKillsTotal'] + data[3]['forceAuxKillsTotal'] + data[4]['forceAuxKillsTotal'] + data[5]['forceAuxKillsTotal']) == 0 ? $('#chartTotalFAX').parent().html("No Ship Kills!") : new Chart($('#chartTotalFAX'), dataTotalFAX);
  var ctxTotalLogi = (data[0]['logiKillsTotal'] + data[1]['logiKillsTotal'] + data[2]['logiKillsTotal'] + data[3]['logiKillsTotal'] + data[4]['logiKillsTotal'] + data[5]['logiKillsTotal']) == 0 ? $('#chartTotalLogi').parent().html("No Ship Kills!") : new Chart($('#chartTotalLogi'), dataTotalLogi);

  var r = new Array(), j = 0;
  for (var key=0, size=data.length; key<size; key++){
      var type = 'C' + data[key]['class'];
      if(key == '6') { type = "Thera" }
      if(key == '7') { type = "Shattered"}
      if(key == '8') { type = "Frig-Hole"}
      r[++j] ='<tr><td>';
      r[++j] = type;
      r[++j] = '</td><td>';
      r[++j] = data[key]['hourKills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['totalKills'];
      r[++j] = '</td></tr>';
  }
  $('#dataTable').html(r.join(''));
}
