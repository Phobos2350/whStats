var dummyArray = {
  0: {
    entityID: 0,
    nullKills: 0,
    lowKills: 0,
    whKills: 0,
    t1FrigUse: 0,
    factionFrigUse: 0,
    t2FrigUse: 0,
    t1DestroyerUse: 0,
    factionDestroyerUse: 0,
    t2DestroyerUse: 0,
    t3DestroyerUse: 0,
    t1CruiserUse: 0,
    factionCruiserUse: 0,
    t2CruiserUse: 0,
    t3CruiserUse: 0,
    t1BCUse: 0,
    t2BCUse: 0,
    t1BattleshipUse: 0,
    t2BattleshipUse: 0,
    factionBattleshipUse: 0,
    carrierUse: 0,
    archonUse: 0,
    nidUse: 0,
    chimeraUse: 0,
    thanatosUse: 0,
    dreadUse: 0,
    nagUse: 0,
    morosUse: 0,
    phoenixUse: 0,
    revUse: 0,
    faxUse: 0,
    apostleUse: 0,
    lifUse: 0,
    minokawaUse: 0,
    ninazuUse: 0,
    frigLogiUse: 0,
    cruiserLogiUse: 0,
    neutsUse: 0,
    jamsUse: 0,
    dampsUse: 0,
    avgFleetSize: 0,
    largestFleetSize: 0,
    lastKill: 0,
    c1Kills: 0,
    c2Kills: 0,
    c3Kills: 0,
    c4Kills: 0,
    c5Kills: 0,
    c6Kills: 0,
    c7Kills: 0,
    c8Kills: 0,
    c9Kills: 0,
    armourTank: 0,
    shieldTank: 0,
    factionBCUse: 0,
    entityName: "",
    entityType: ""
  }
}

$(document).ready(function () {
  var url = window.location.pathname;
  var id = url.substring(url.lastIndexOf('/') + 1);
  console.log("ID - "+id);
  var statsEU = dummyArray;
  var statsUS = dummyArray;
  var statsAU = dummyArray;
  var statsRU = dummyArray;
  getAllStats(id);

});

function getAllStats(id) {
  $.getJSON('/api/entity/whKills/EU/'+id+'/', function(data) {
    if(data.length != 0) {
      statsEU = data;
      var entityName = statsEU[0]['entityName'];
      $('.span_h2').html(entityName);
      $('#euAvg').html("EU - "+statsEU[0]['avgFleetSize']+" ");
      $('#euLrg').html("EU - "+statsEU[0]['largestFleetSize']+" ");
      if (document.title != entityName+" Stats") {
        document.title = entityName+ " Stats";
      }
    }  else {
      statsEU = dummyArray;
    }
  });
  $.getJSON('/api/entity/whKills/US/'+id+'/', function(data) {
    if(data.length != 0) {
      statsUS = data;
      var entityName = statsUS[0]['entityName'];
      $('.span_h2').html(entityName);
      $('#usAvg').html("US - "+statsUS[0]['avgFleetSize']+" ");
      $('#usLrg').html("US - "+statsUS[0]['largestFleetSize']+" ");
      if (document.title != entityName+" Stats") {
        document.title = entityName+ " Stats";
      }
    } else {
      statsUS = dummyArray;
    }
  });
  $.getJSON('/api/entity/whKills/AU/'+id+'/', function(data) {
    if(data.length != 0) {
      statsAU = data;
      var entityName = statsAU[0]['entityName'];
      $('.span_h2').html(entityName);
      $('#auAvg').html("AU - "+statsAU[0]['avgFleetSize']+" ");
      $('#auLrg').html("AU - "+statsAU[0]['largestFleetSize']+" ");
      if (document.title != entityName+" Stats") {
        document.title = entityName+ " Stats";
      }
    } else {
      statsAU = dummyArray;
    }
  });
  $.getJSON('/api/entity/whKills/RU/'+id+'/', function(data) {
    if(data.length != 0) {
      statsRU = data;
      var entityName = statsRU[0]['entityName'];
      $('.span_h2').html(entityName);
      $('#ruAvg').html("RU - "+statsRU[0]['avgFleetSize']+" ");
      $('#ruLrg').html("RU - "+statsRU[0]['largestFleetSize']+" ");
      if (document.title != entityName+" Stats") {
        document.title = entityName+ " Stats";
      }
    } else {
      statsRU = dummyArray;
    }
  });

  setTimeout(function() {
    updateCharts(statsEU, statsUS, statsAU, statsRU);
    $('.progress').hide();
  }, 5000);
}

function getPercent(total, kills) {
  return Math.round(((kills / total) * 100) * 100) / 100;
}

function updateCharts(dataEU, dataUS, dataAU, dataRU) {

  $('canvas').parent().each(function () {
      //get child canvas id
      childCanvasId = $(this).children().attr('id');
      //remove canvas
      $('#'+childCanvasId).remove();
      // append new canvas to the parent again
      $(this).append('<canvas id="'+childCanvasId+'" width="300" height="300"></canvas>');
  });

  var totalKills = parseInt(dataEU[0]['whKills'], 10) + parseInt(dataUS[0]['whKills'], 10) + parseInt(dataAU[0]['whKills'], 10) + parseInt(dataRU[0]['whKills'], 10);
  var totalAvg = (parseInt(dataEU[0]['avgFleetSize'], 10) + parseInt(dataUS[0]['avgFleetSize'], 10) + parseInt(dataAU[0]['avgFleetSize'], 10) + parseInt(dataRU[0]['avgFleetSize'], 10)) / 4 ;
  // Logi Stats
  var euLogi = getPercent((parseInt(dataEU[0]['whKills'], 10) / parseInt(dataEU[0]['avgFleetSize'], 10)), (parseInt(dataEU[0]['cruiserLogiUse'], 10) + parseInt(dataEU[0]['frigLogiUse'], 10)));
  var usLogi = getPercent((parseInt(dataUS[0]['whKills'], 10) / parseInt(dataUS[0]['avgFleetSize'], 10)), (parseInt(dataUS[0]['cruiserLogiUse'], 10) + parseInt(dataUS[0]['frigLogiUse'], 10)));
  var auLogi = getPercent((parseInt(dataAU[0]['whKills'], 10) / parseInt(dataAU[0]['avgFleetSize'], 10)), (parseInt(dataAU[0]['cruiserLogiUse'], 10) + parseInt(dataAU[0]['frigLogiUse'], 10)));
  var ruLogi = getPercent((parseInt(dataRU[0]['whKills'], 10) / parseInt(dataRU[0]['avgFleetSize'], 10)), (parseInt(dataRU[0]['cruiserLogiUse'], 10) + parseInt(dataRU[0]['frigLogiUse'], 10)));

  $('#euLogi').html("EU - " + (euLogi/100).toFixed(2));
  $('#usLogi').html("US - " + (usLogi/100).toFixed(2));
  $('#auLogi').html("AU - " + (auLogi/100).toFixed(2));
  $('#ruLogi').html("RU - " + (ruLogi/100).toFixed(2));

  //EWAR
  var neutsTotal = parseInt(dataEU[0]['neutsUse'], 10) + parseInt(dataUS[0]['neutsUse'], 10) + parseInt(dataAU[0]['neutsUse'], 10) + parseInt(dataRU[0]['neutsUse'], 10);
  var jamsTotal = parseInt(dataEU[0]['jamsUse'], 10) + parseInt(dataUS[0]['jamsUse'], 10) + parseInt(dataAU[0]['jamsUse'], 10) + parseInt(dataRU[0]['jamsUse'], 10);
  var dampsTotal = parseInt(dataEU[0]['dampsUse'], 10) + parseInt(dataUS[0]['dampsUse'], 10) + parseInt(dataAU[0]['dampsUse'], 10) + parseInt(dataRU[0]['dampsUse'], 10);

  $('#neuts').html("Neuts - " + getPercent((totalKills / totalAvg), neutsTotal) + "%");
  $('#jams').html("Jams - " + getPercent((totalKills / totalAvg), jamsTotal) + "%");
  $('#damps').html("Damps - " + getPercent((totalKills / totalAvg), dampsTotal) + "%");

  // TOTALS
  var dataTotal = {
    type: 'bar',
    data: {
      labels: ['RU (1200-1800)', 'EU (1800-0000)', 'US (0000-0600)', 'AU (0600-1200)'],
      datasets: [
        {
          label: 'C1',
          backgroundColor: "rgba(51,153,102,0.4)",
          data: [dataRU[0]['c1Kills'],
                 dataEU[0]['c1Kills'],
                 dataUS[0]['c1Kills'],
                 dataAU[0]['c1Kills']]
        },
        {
          label: 'C2',
          backgroundColor: "rgba(0, 182, 223,0.4)",
          data: [dataRU[0]['c2Kills'],
                 dataEU[0]['c2Kills'],
                 dataUS[0]['c2Kills'],
                 dataAU[0]['c2Kills']]
        },
        {
          label: 'C3',
          backgroundColor: "rgba(102,153,153,0.4)",
          data: [dataRU[0]['c3Kills'],
                 dataEU[0]['c3Kills'],
                 dataUS[0]['c3Kills'],
                 dataAU[0]['c3Kills']]
        },
        {
          label: 'C4',
          backgroundColor: "rgba(153, 204, 0,0.4)",
          data: [dataRU[0]['c4Kills'],
                 dataEU[0]['c4Kills'],
                 dataUS[0]['c4Kills'],
                 dataAU[0]['c4Kills']]
        },
        {
          label: 'C5',
          backgroundColor: "rgba(255, 102, 0,0.4)",
          data: [dataRU[0]['c5Kills'],
                 dataEU[0]['c5Kills'],
                 dataUS[0]['c5Kills'],
                 dataAU[0]['c5Kills']]
        },
        {
          label: 'C6',
          backgroundColor: "rgba(255, 0, 0,0.4)",
          data: [dataRU[0]['c6Kills'],
                 dataEU[0]['c6Kills'],
                 dataUS[0]['c6Kills'],
                 dataAU[0]['c6Kills']]
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
          }
        }]
      }
    }
  };
  var ctxTotal = new Chart($('#chartActivity'), dataTotal);

  var totalFrigsEU = parseInt(dataEU[0]['t1FrigUse'], 10) + parseInt(dataEU[0]['factionFrigUse'], 10) + parseInt(dataEU[0]['t2FrigUse'], 10);
  var totalDestEU = parseInt(dataEU[0]['t1DestroyerUse'], 10) + parseInt(dataEU[0]['factionDestroyerUse'], 10) + parseInt(dataEU[0]['t2DestroyerUse'], 10) + parseInt(dataEU[0]['t3DestroyerUse'], 10);
  var totalCruiserEU = parseInt(dataEU[0]['t1CruiserUse'], 10) + parseInt(dataEU[0]['factionCruiserUse'], 10) + parseInt(dataEU[0]['t2CruiserUse'], 10) + parseInt(dataEU[0]['t3CruiserUse'], 10);
  var totalBCEU = parseInt(dataEU[0]['t1BCUse'], 10) + parseInt(dataEU[0]['t2BCUse'], 10);
  var totalBSEU = parseInt(dataEU[0]['t1BattleshipUse'], 10) + parseInt(dataEU[0]['factionBattleshipUse'], 10) + parseInt(dataEU[0]['t2BattleshipUse'], 10);
  var totalCapEU = parseInt(dataEU[0]['carrierUse'], 10) + parseInt(dataEU[0]['dreadUse'], 10) + parseInt(dataEU[0]['faxUse'], 10);
  var totalShipCountEU = totalFrigsEU + totalDestEU + totalCruiserEU + totalBCEU + totalBSEU + totalCapEU;

  var dataShipsEU = {
    type: 'pie',
    data: {
      labels: ['T1 Frig', 'Faction Frig', 'T2 Frig', 'T1 Destroyer', 'T2 Destroyer', 'T3 Destroyer', 'T1 Cruiser', 'Faction Cruiser', 'T2 Cruiser', 'T3 Cruiser', 'T1 Battlecruiser', 'T2 Battlecruiser', 'T1 Battleship', 'Faction Battleship', 'T2 Battleship', 'Capital'],
      datasets: [
        {
          backgroundColor: [
            "rgba(51,153,102,0.4)",   //T1Frig
            "rgba(0, 182, 223,0.4)",  //Faction Frig
            "rgba(102,153,153,0.4)",  //
            "rgba(153, 204, 0,0.4)",
            "rgba(255, 102, 0,0.4)",
            "rgba(255, 0, 0,0.4)",
            "rgba(141,211,199, 0.4)",
            "rgba(255,255,179, 0.4)",
            "rgba(190,186,218, 0.4)",
            "rgba(251,128,114, 0.4)",
            "rgba(128,177,211, 0.4)",
            "rgba(253,180,98, 0.4)",
            "rgba(179,222,105, 0.4)",
            "rgba(252,205,229, 0.4)",
            "rgba(188,128,189, 0.5)",
          ],
          data: [getPercent(totalShipCountEU, parseInt(dataEU[0]['t1FrigUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['factionFrigUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t2FrigUse'], 10)),
                 getPercent(totalShipCountEU, parseInt(dataEU[0]['t1DestroyerUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t2DestroyerUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t3DestroyerUse'], 10)),
                 getPercent(totalShipCountEU, parseInt(dataEU[0]['t1CruiserUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['factionCruiserUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t2CruiserUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t3CruiserUse'], 10)),
                 getPercent(totalShipCountEU, parseInt(dataEU[0]['t1BCUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t2BCUse'], 10)),
                 getPercent(totalShipCountEU, parseInt(dataEU[0]['t1BattleshipUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['factionBattleshipUse'], 10)), getPercent(totalShipCountEU, parseInt(dataEU[0]['t2BattleshipUse'], 10)),
                 getPercent(totalShipCountEU, parseInt(dataEU[0]['totalCapEU'], 10))
                ]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: {
        display: false
      }
    }
  };
  var ctxShipsEU = dataEU[0]['whKills'] != 0 ? new Chart($('#chartShipsEU'), dataShipsEU) : $('#chartShipsEU').parent().html("No Data!");

  var totalFrigsUS = parseInt(dataUS[0]['t1FrigUse'], 10) + parseInt(dataUS[0]['factionFrigUse'], 10) + parseInt(dataUS[0]['t2FrigUse'], 10);
  var totalDestUS = parseInt(dataUS[0]['t1DestroyerUse'], 10) + parseInt(dataUS[0]['factionDestroyerUse'], 10) + parseInt(dataUS[0]['t2DestroyerUse'], 10) + parseInt(dataUS[0]['t3DestroyerUse'], 10);
  var totalCruiserUS = parseInt(dataUS[0]['t1CruiserUse'], 10) + parseInt(dataUS[0]['factionCruiserUse'], 10) + parseInt(dataUS[0]['t2CruiserUse'], 10) + parseInt(dataUS[0]['t3CruiserUse'], 10);
  var totalBCUS = parseInt(dataUS[0]['t1BCUse'], 10) + parseInt(dataUS[0]['t2BCUse'], 10);
  var totalBSUS = parseInt(dataUS[0]['t1BattleshipUse'], 10) + parseInt(dataUS[0]['factionBattleshipUse'], 10) + parseInt(dataUS[0]['t2BattleshipUse'], 10);
  var totalCapUS = parseInt(dataUS[0]['carrierUse'], 10) + parseInt(dataUS[0]['dreadUse'], 10) + parseInt(dataUS[0]['faxUse'], 10);
  var totalShipCountUS = totalFrigsUS + totalDestUS + totalCruiserUS + totalBCUS + totalBSUS + totalCapUS;

  var dataShipsUS = {
    type: 'pie',
    data: {
      labels: ['T1 Frig', 'Faction Frig', 'T2 Frig', 'T1 Destroyer', 'T2 Destroyer', 'T3 Destroyer', 'T1 Cruiser', 'Faction Cruiser', 'T2 Cruiser', 'T3 Cruiser', 'T1 Battlecruiser', 'T2 Battlecruiser', 'T1 Battleship', 'Faction Battleship', 'T2 Battleship', 'Capital'],
      datasets: [
        {
          backgroundColor: [
            "rgba(51,153,102,0.4)",   //T1Frig
            "rgba(0, 182, 223,0.4)",  //Faction Frig
            "rgba(102,153,153,0.4)",  //
            "rgba(153, 204, 0,0.4)",
            "rgba(255, 102, 0,0.4)",
            "rgba(255, 0, 0,0.4)",
            "rgba(141,211,199, 0.4)",
            "rgba(255,255,179, 0.4)",
            "rgba(190,186,218, 0.4)",
            "rgba(251,128,114, 0.4)",
            "rgba(128,177,211, 0.4)",
            "rgba(253,180,98, 0.4)",
            "rgba(179,222,105, 0.4)",
            "rgba(252,205,229, 0.4)",
            "rgba(188,128,189, 0.5)",
          ],
          data: [getPercent(totalShipCountUS, parseInt(dataUS[0]['t1FrigUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['factionFrigUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t2FrigUse'], 10)),
                 getPercent(totalShipCountUS, parseInt(dataUS[0]['t1DestroyerUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t2DestroyerUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t3DestroyerUse'], 10)),
                 getPercent(totalShipCountUS, parseInt(dataUS[0]['t1CruiserUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['factionCruiserUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t2CruiserUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t3CruiserUse'], 10)),
                 getPercent(totalShipCountUS, parseInt(dataUS[0]['t1BCUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t2BCUse'], 10)),
                 getPercent(totalShipCountUS, parseInt(dataUS[0]['t1BattleshipUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['factionBattleshipUse'], 10)), getPercent(totalShipCountUS, parseInt(dataUS[0]['t2BattleshipUse'], 10)),
                 getPercent(totalShipCountUS, parseInt(dataUS[0]['totalCapEU'], 10))
                ]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: {
        display: false
      }
    }
  };
  var ctxShipsUS = dataUS[0]['whKills'] != 0 ? new Chart($('#chartShipsUS'), dataShipsUS) : $('#chartShipsUS').parent().html("No Data!");

  var totalFrigsAU = parseInt(dataUS[0]['t1FrigUse'], 10) + parseInt(dataUS[0]['factionFrigUse'], 10) + parseInt(dataUS[0]['t2FrigUse'], 10);
  var totalDestAU = parseInt(dataUS[0]['t1DestroyerUse'], 10) + parseInt(dataUS[0]['factionDestroyerUse'], 10) + parseInt(dataUS[0]['t2DestroyerUse'], 10) + parseInt(dataUS[0]['t3DestroyerUse'], 10);
  var totalCruiserAU = parseInt(dataUS[0]['t1CruiserUse'], 10) + parseInt(dataUS[0]['factionCruiserUse'], 10) + parseInt(dataUS[0]['t2CruiserUse'], 10) + parseInt(dataUS[0]['t3CruiserUse'], 10);
  var totalBCAU = parseInt(dataUS[0]['t1BCUse'], 10) + parseInt(dataUS[0]['t2BCUse'], 10);
  var totalBSAU = parseInt(dataUS[0]['t1BattleshipUse'], 10) + parseInt(dataUS[0]['factionBattleshipUse'], 10) + parseInt(dataUS[0]['t2BattleshipUse'], 10);
  var totalCapAU = parseInt(dataUS[0]['carrierUse'], 10) + parseInt(dataUS[0]['dreadUse'], 10) + parseInt(dataUS[0]['faxUse'], 10);
  var totalShipCountAU = totalFrigsAU + totalDestAU + totalCruiserAU + totalBCAU + totalBSUS + totalCapUS;

  var dataShipsAU = {
    type: 'pie',
    data: {
      labels: ['T1 Frig', 'Faction Frig', 'T2 Frig', 'T1 Destroyer', 'T2 Destroyer', 'T3 Destroyer', 'T1 Cruiser', 'Faction Cruiser', 'T2 Cruiser', 'T3 Cruiser', 'T1 Battlecruiser', 'T2 Battlecruiser', 'T1 Battleship', 'Faction Battleship', 'T2 Battleship', 'Capital'],
      datasets: [
        {
          backgroundColor: [
            "rgba(51,153,102,0.4)",   //T1Frig
            "rgba(0, 182, 223,0.4)",  //Faction Frig
            "rgba(102,153,153,0.4)",  //
            "rgba(153, 204, 0,0.4)",
            "rgba(255, 102, 0,0.4)",
            "rgba(255, 0, 0,0.4)",
            "rgba(141,211,199, 0.4)",
            "rgba(255,255,179, 0.4)",
            "rgba(190,186,218, 0.4)",
            "rgba(251,128,114, 0.4)",
            "rgba(128,177,211, 0.4)",
            "rgba(253,180,98, 0.4)",
            "rgba(179,222,105, 0.4)",
            "rgba(252,205,229, 0.4)",
            "rgba(188,128,189, 0.5)",
          ],
          data: [getPercent(totalShipCountAU, parseInt(dataAU[0]['t1FrigUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['factionFrigUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t2FrigUse'], 10)),
                 getPercent(totalShipCountAU, parseInt(dataAU[0]['t1DestroyerUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t2DestroyerUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t3DestroyerUse'], 10)),
                 getPercent(totalShipCountAU, parseInt(dataAU[0]['t1CruiserUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['factionCruiserUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t2CruiserUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t3CruiserUse'], 10)),
                 getPercent(totalShipCountAU, parseInt(dataAU[0]['t1BCUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t2BCUse'], 10)),
                 getPercent(totalShipCountAU, parseInt(dataAU[0]['t1BattleshipUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['factionBattleshipUse'], 10)), getPercent(totalShipCountAU, parseInt(dataAU[0]['t2BattleshipUse'], 10)),
                 getPercent(totalShipCountAU, parseInt(dataAU[0]['totalCapEU'], 10))
                ]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: {
        display: false
      }
    }
  };
  var ctxShipsAU = dataAU[0]['whKills'] != 0 ? new Chart($('#chartShipsAU'), dataShipsAU) : $('#chartShipsAU').parent().html("No Data!");

  var totalFrigsRU = parseInt(dataRU[0]['t1FrigUse'], 10) + parseInt(dataRU[0]['factionFrigUse'], 10) + parseInt(dataRU[0]['t2FrigUse'], 10);
  var totalDestRU = parseInt(dataRU[0]['t1DestroyerUse'], 10) + parseInt(dataRU[0]['factionDestroyerUse'], 10) + parseInt(dataRU[0]['t2DestroyerUse'], 10) + parseInt(dataRU[0]['t3DestroyerUse'], 10);
  var totalCruiserRU = parseInt(dataRU[0]['t1CruiserUse'], 10) + parseInt(dataRU[0]['factionCruiserUse'], 10) + parseInt(dataRU[0]['t2CruiserUse'], 10) + parseInt(dataRU[0]['t3CruiserUse'], 10);
  var totalBCRU = parseInt(dataRU[0]['t1BCUse'], 10) + parseInt(dataRU[0]['t2BCUse'], 10);
  var totalBSRU = parseInt(dataRU[0]['t1BattleshipUse'], 10) + parseInt(dataRU[0]['factionBattleshipUse'], 10) + parseInt(dataRU[0]['t2BattleshipUse'], 10);
  var totalCapRU = parseInt(dataRU[0]['carrierUse'], 10) + parseInt(dataRU[0]['dreadUse'], 10) + parseInt(dataRU[0]['faxUse'], 10);
  var totalShipCountRU = totalFrigsRU + totalDestRU + totalCruiserRU + totalBCRU + totalBSRU + totalCapRU;

  var dataShipsRU = {
    type: 'pie',
    data: {
      labels: ['T1 Frig', 'Faction Frig', 'T2 Frig', 'T1 Destroyer', 'T2 Destroyer', 'T3 Destroyer', 'T1 Cruiser', 'Faction Cruiser', 'T2 Cruiser', 'T3 Cruiser', 'T1 Battlecruiser', 'T2 Battlecruiser', 'T1 Battleship', 'Faction Battleship', 'T2 Battleship', 'Capital'],
      datasets: [
        {
          backgroundColor: [
            "rgba(51,153,102,0.4)",   //T1Frig
            "rgba(0, 182, 223,0.4)",  //Faction Frig
            "rgba(102,153,153,0.4)",  //
            "rgba(153, 204, 0,0.4)",
            "rgba(255, 102, 0,0.4)",
            "rgba(255, 0, 0,0.4)",
            "rgba(141,211,199, 0.4)",
            "rgba(255,255,179, 0.4)",
            "rgba(190,186,218, 0.4)",
            "rgba(251,128,114, 0.4)",
            "rgba(128,177,211, 0.4)",
            "rgba(253,180,98, 0.4)",
            "rgba(179,222,105, 0.4)",
            "rgba(252,205,229, 0.4)",
            "rgba(188,128,189, 0.5)",
          ],
          data: [getPercent(totalShipCountRU, parseInt(dataRU[0]['t1FrigUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['factionFrigUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t2FrigUse'], 10)),
                 getPercent(totalShipCountRU, parseInt(dataRU[0]['t1DestroyerUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t2DestroyerUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t3DestroyerUse'], 10)),
                 getPercent(totalShipCountRU, parseInt(dataRU[0]['t1CruiserUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['factionCruiserUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t2CruiserUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t3CruiserUse'], 10)),
                 getPercent(totalShipCountRU, parseInt(dataRU[0]['t1BCUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t2BCUse'], 10)),
                 getPercent(totalShipCountRU, parseInt(dataRU[0]['t1BattleshipUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['factionBattleshipUse'], 10)), getPercent(totalShipCountRU, parseInt(dataRU[0]['t2BattleshipUse'], 10)),
                 getPercent(totalShipCountRU, parseInt(dataRU[0]['totalCapEU'], 10))
                ]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      legend: {
        display: false
      }
    }
  };
  var ctxShipsRU = dataRU[0]['whKills'] != 0 ? new Chart($('#chartShipsRU'), dataShipsRU) : $('#chartShipsRU').parent().html("No Data!");

  var faxTotal = parseInt(dataEU[0]['faxUse'], 10) + parseInt(dataUS[0]['faxUse'], 10) + parseInt(dataAU[0]['faxUse'], 10) + parseInt(dataRU[0]['faxUse'], 10);
  var apostleTotal = parseInt(dataEU[0]['apostleUse'], 10) + parseInt(dataUS[0]['apostleUse'], 10) + parseInt(dataAU[0]['apostleUse'], 10) + parseInt(dataRU[0]['apostleUse'], 10);
  var ninazuTotal = parseInt(dataEU[0]['ninazuUse'], 10) + parseInt(dataUS[0]['ninazuUse'], 10) + parseInt(dataAU[0]['ninazuUse'], 10) + parseInt(dataRU[0]['ninazuUse'], 10);
  var lifTotal = parseInt(dataEU[0]['lifUse'], 10) + parseInt(dataUS[0]['lifUse'], 10) + parseInt(dataAU[0]['lifUse'], 10) + parseInt(dataRU[0]['lifUse'], 10);
  var minTotal = parseInt(dataEU[0]['minokawaUse'], 10) + parseInt(dataUS[0]['minokawaUse'], 10) + parseInt(dataAU[0]['minokawaUse'], 10) + parseInt(dataRU[0]['minokawaUse'], 10);

  var dataFAX = {
    type: 'pie',
    data: {
      labels: ['Apostle', 'Ninazu', 'Lif', 'Minokawa'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,255,179,0.4)",   //Amarr
            "rgba(141,211,199,0.4)",  //Gallente
            "rgba(251,128,114,0.4)",  //Minmatar
            "rgba(128,177,211,0.4)",  //Caldari
          ],
          data: [apostleTotal, ninazuTotal, lifTotal, minTotal]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxFAX = faxTotal != 0 ? new Chart($('#chartFAX'), dataFAX) : $('#chartFAX').parent().html("No Data!");

  var dreadTotal = parseInt(dataEU[0]['dreadUse'], 10) + parseInt(dataUS[0]['dreadUse'], 10) + parseInt(dataAU[0]['dreadUse'], 10) + parseInt(dataRU[0]['dreadUse'], 10);
  var revTotal = parseInt(dataEU[0]['revUse'], 10) + parseInt(dataUS[0]['revUse'], 10) + parseInt(dataAU[0]['revUse'], 10) + parseInt(dataRU[0]['revUse'], 10);
  var morosTotal = parseInt(dataEU[0]['morosUse'], 10) + parseInt(dataUS[0]['morosUse'], 10) + parseInt(dataAU[0]['morosUse'], 10) + parseInt(dataRU[0]['morosUse'], 10);
  var nagTotal = parseInt(dataEU[0]['nagUse'], 10) + parseInt(dataUS[0]['nagUse'], 10) + parseInt(dataAU[0]['nagUse'], 10) + parseInt(dataRU[0]['nagUse'], 10);
  var phoenixTotal = parseInt(dataEU[0]['phoenixUse'], 10) + parseInt(dataUS[0]['phoenixUse'], 10) + parseInt(dataAU[0]['phoenixUse'], 10) + parseInt(dataRU[0]['phoenixUse'], 10);

  var dataDread = {
    type: 'pie',
    data: {
      labels: ['Revelation', 'Moros', 'Naglfar', 'Phoenix'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,255,179,0.4)",   //Amarr
            "rgba(141,211,199,0.4)",  //Gallente
            "rgba(251,128,114,0.4)",  //Minmatar
            "rgba(128,177,211,0.4)",  //Caldari
          ],
          data: [revTotal, morosTotal, nagTotal, phoenixTotal]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxDread = dreadTotal != 0 ? new Chart($('#chartDreads'), dataDread) : $('#chartDreads').parent().html("No Data!");

  var carrierTotal = parseInt(dataEU[0]['carrierUse'], 10) + parseInt(dataUS[0]['carrierUse'], 10) + parseInt(dataAU[0]['carrierUse'], 10) + parseInt(dataRU[0]['carrierUse'], 10);
  var archonTotal = parseInt(dataEU[0]['archonUse'], 10) + parseInt(dataUS[0]['archonUse'], 10) + parseInt(dataAU[0]['archonUse'], 10) + parseInt(dataRU[0]['archonUse'], 10);
  var thanatosTotal = parseInt(dataEU[0]['thanatosUse'], 10) + parseInt(dataUS[0]['thanatosUse'], 10) + parseInt(dataAU[0]['thanatosUse'], 10) + parseInt(dataRU[0]['thanatosUse'], 10);
  var nidTotal = parseInt(dataEU[0]['nidUse'], 10) + parseInt(dataUS[0]['nidUse'], 10) + parseInt(dataAU[0]['nidUse'], 10) + parseInt(dataRU[0]['nidUse'], 10);
  var chimeraTotal = parseInt(dataEU[0]['chimeraUse'], 10) + parseInt(dataUS[0]['chimeraUse'], 10) + parseInt(dataAU[0]['chimeraUse'], 10) + parseInt(dataRU[0]['chimeraUse'], 10);

  var dataCarrier = {
    type: 'pie',
    data: {
      labels: ['Archon', 'Thanatos', 'Nidhoggur', 'Chimera'],
      datasets: [
        {
          backgroundColor: [
            "rgba(255,255,179,0.4)",   //Amarr
            "rgba(141,211,199,0.4)",  //Gallente
            "rgba(251,128,114,0.4)",  //Minmatar
            "rgba(128,177,211,0.4)",  //Caldari
          ],
          data: [archonTotal, thanatosTotal, nidTotal, chimeraTotal]
        }
      ]
    },
    options: {
      legend: {
        display: false
      }
    }
  };
  var ctxCarrier = carrierTotal != 0 ? new Chart($('#chartCarriers'), dataCarrier) : $('#chartCarriers').parent().html("No Data!");
}
