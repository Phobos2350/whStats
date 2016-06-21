$(document).ready(function () {
  var url = window.location.pathname;
  var tz = url.substring(url.lastIndexOf('/') + 1);
  console.log("TZ - "+tz);

  if (document.title != tz.toUpperCase() +" Stats") {
    document.title = tz.toUpperCase() + " Stats";
  }

  if(tz == "eu") {
    $('#pageTitle').html("EU Stats (1800-0000)");
  }
  if(tz == "us") {
    $('#pageTitle').html("US Stats (0000-0600)");
  }
  if(tz == "au") {
    $('#pageTitle').html("AU Stats (0600-1200)");
  }
  if(tz == "ru") {
    $('#pageTitle').html("RU Stats (1200-1800)");
  }

  $.getJSON('/api/entity/whkills/'+tz+'/', function(json) {
    console.log(json);
    updateTable(json);
    $('#stats').dataTable({
      "oLanguage": {
        "sStripClasses": "",
        "sSearch": "",
        "sSearchPlaceholder": "Enter Keywords Here",
        "sInfo": "_START_ -_END_ of _TOTAL_",
        "sLengthMenu": '<span>Rows per page:</span><select class="browser-default">' +
          '<option value="10">10</option>' +
          '<option value="20">20</option>' +
          '<option value="30">30</option>' +
          '<option value="40">40</option>' +
          '<option value="50">50</option>' +
          '<option value="-1">All</option>' +
          '</select></div>'
      },
      bAutoWidth: false,
      bProcessing: true,
      bDeferRender: true,
      scrollY: "650px",
      scrollX: true,
      scrollCollapse: true,
      fixedColumns:   {
            leftColumns: 2
        }
    });
  }).error(function(error){console.log(error);});

  setInterval(function() {
    $.getJSON('/api/entity/whkills/'+tz+'/', function(json) {
      console.log(json);
      updateTable(json);
    }).error(function(error){console.log(error);});
  }, 600000)
});

function updateTable(data) {
  var r = new Array(), j = 0;
  for (var key=0, size=data.length; key<size; key++){
      r[++j] ='<tr><td>';
      r[++j] = key+1;
      r[++j] = '</td><td>';
      if(data[key]['entityType'] == "Alliance") {
        r[++j] = '<a target="_blank" href="http://stats.limited-power.co.uk/entity/' + data[key]['entityID'] + '">'+data[key]['entityName']+'</a>';
      } else {
        r[++j] = '<a target="_blank" href="http://stats.limited-power.co.uk/entity/' + data[key]['entityID'] + '">'+data[key]['entityName']+'</a>';
      }
      r[++j] = '</td><td>';
      r[++j] = data[key]['entityType'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['whKills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['avgFleetSize'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['largestFleetSize'];
      r[++j] = '</td><td>';
      r[++j] = '<a target="_blank" href="https://zkillboard.com/kill/' + data[key]['lastKill'] + '/">'+data[key]['lastKill']+'</a>';
      r[++j] = '</td><td>';
      r[++j] = data[key]['c1Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c2Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c3Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c4Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c5Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c6Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c7Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c8Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['c9Kills'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t1FrigUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['factionFrigUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t2FrigUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t1DestroyerUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t2DestroyerUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t3DestroyerUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t1CruiserUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['factionCruiserUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t2CruiserUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t3CruiserUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t1BCUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['factionBCUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t2BCUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t1BattleshipUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['factionBattleshipUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['t2BattleshipUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['carrierUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['archonUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['nidUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['chimeraUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['thanatosUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['dreadUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['nagUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['morosUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['revUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['phoenixUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['faxUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['apostleUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['lifUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['minokawaUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['ninazuUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['frigLogiUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['cruiserLogiUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['neutsUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['jamsUse'];
      r[++j] = '</td><td>';
      r[++j] = data[key]['dampsUse'];
      r[++j] = '</td></tr>';
  }
  $('#dataTable').html(r.join(''));
  $('.progress').hide();
}
