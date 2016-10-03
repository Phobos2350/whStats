var url = window.location.pathname
var id = url.substring(url.lastIndexOf('/') + 1)
var setPeriod = 0
var renderedOnce = 0
var month = new Date().getMonth()
var year = new Date().getFullYear()
var ctxKills = null
var ctxISK = null
var ctxBreakdowns = null

$(document).ready(function () {
  $('.entitiesLink').parent().addClass('active')
  $('.period').addClass('hide')
  $('.periodLinks').addClass('disabled')
  $('.periodLinks-month').removeClass('disabled')
  $('.periodLinks-month').parent().addClass('active')
  $('.periodLinks-month').trigger('click')
})

$('.periodLinks').click(function () {
  setPeriod = 'month'
  $('.monthLinks').removeClass('hide')
  setPeriod = 'year/' + year + '/month/' + (month + 1) + '/entityStats/' + id
  var currMonth = new Date(year, month, 1, 0, 0, 0, 0)
  $('.currMonth').text(currMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  $('.periodStats').text('Kills During ' + currMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  var prevMonth = new Date(year, month - 1, 1, 0, 0, 0, 0)
  $('.prevMonth').text('<< ' + prevMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  changePeriod(setPeriod)
})

$('.prevMonth').click(function () {
  if (month === 0) {
    month = 11
    year -= 1
  } else {
    month -= 1
  }
  setPeriod = 'year/' + year + '/month/' + (month + 1) + '/entityStats/' + id
  changePeriod(setPeriod)

  var currMonth = new Date(year, month, 1, 0, 0, 0, 0)
  $('.currMonth').text(currMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  var prevMonth = new Date(year, month - 1, 1, 0, 0, 0, 0)
  $('.prevMonth').text('<< ' + prevMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  var nextMonth = new Date(year, month + 1, 1, 0, 0, 0, 0)
  $('.nextMonth').text(nextMonth.toLocaleString('en-gb', {
    month: 'long'
  }) + ' >>')
})

$('.nextMonth').click(function () {
  if (month === 13) {
    month = 1
    year += 1
  } else {
    month += 1
  }
  setPeriod = 'year/' + year + '/month/' + (month + 1) + '/entityStats/' + id
  changePeriod(setPeriod)
  var thisMonth = new Date().getMonth()
  var currMonth = new Date(year, month, 1, 0, 0, 0, 0)
  $('.currMonth').text(currMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  var prevMonth = new Date(year, month - 1, 1, 0, 0, 0, 0)
  $('.prevMonth').text('<< ' + prevMonth.toLocaleString('en-gb', {
    month: 'long'
  }))
  if (month !== thisMonth) {
    var nextMonth = new Date(year, month + 1, 1, 0, 0, 0, 0)
    $('.nextMonth').text(nextMonth.toLocaleString('en-gb', {
      month: 'long'
    }) + ' >>')
  } else {
    $('.nextMonth').text('')
  }
})

function changePeriod (period) {
  $('#modal1').openModal()
  $.getJSON('../api/rethink/' + period + '/', function (json) {
    if (json === null) {
      window.location.replace('../entity/noData/' + id + '/')
    }
    if (parseInt(id, 10) === 0) {
      $('#entityName').text('Sleepers/NPC')
    } else {
      $('#entityName').text(json['statsArray']['entityName'])
    }
    $('#entityName').attr('href', 'https://zkillboard.com/' + json['statsArray']['entityType'] + '/' + id + '/')
    updateStats(json)
    updateCharts(json)
    setTimeout(function () {
      $('#modal1').closeModal()
    }, 500)
  }).error(function (error) {
    console.log(error)
    window.location.replace('../entity/noData/' + id + '/')
  })
}

function getNum (val) {
  if (isNaN(val) || val == null) {
    return 0
  }
  return val
}

function updateStats (data) {
  $('.lastCached').text(data['lastCached'])
  data = data['statsArray']
  var killTotal = data['ALL']['totalKills']
  $('.totalKills').text(killTotal)
  var iskTotal = data['ALL']['totalISK']
  iskTotal /= Math.pow(10, 9)
  iskTotal > 1000 ? $('.totalISK').text((iskTotal / Math.pow(10, 3)).toFixed(2) + ' Trillion ISK') : $('.totalISK').text(iskTotal.toFixed(2) + ' Billion ISK')
  var averagePilots = (data['ALL']['totalPilotsOnKills'] / killTotal).toFixed(0)
  $('.avgPilots').text(averagePilots)
}

function updateCharts (data) {
  data = data['statsArray']
  if (renderedOnce === 1) {
    ctxKills != null ? ctxKills.destroy() : ctxKills = null
    ctxISK != null ? ctxISK.destroy() : ctxISK = null
    ctxBreakdowns != null ? ctxBreakdowns.destroy() : ctxBreakdowns = null
  }

  var dataKills = {
    type: 'bar',
    data: {
      labels: ['US (0000-0800)', 'AU (0800-1600)', 'EU (1600-0000)'],
      datasets: [{
        label: 'C1',
        backgroundColor: 'rgba(102,255,204,0.5)',
        data: [
          getNum(data['US']['c1Kills']),
          getNum(data['AU']['c1Kills']),
          getNum(data['EU']['c1Kills'])
        ]
      }, {
        label: 'C2',
        backgroundColor: 'rgba(153,204,255,0.5)',
        data: [
          getNum(data['US']['c2Kills']),
          getNum(data['AU']['c2Kills']),
          getNum(data['EU']['c2Kills'])
        ]
      }, {
        label: 'C3',
        backgroundColor: 'rgba(0,51,204,0.5)',
        data: [
          getNum(data['US']['c3Kills']),
          getNum(data['AU']['c3Kills']),
          getNum(data['EU']['c3Kills'])
        ]
      }, {
        label: 'C4',
        backgroundColor: 'rgba(102,153,0,0.5)',
        data: [
          getNum(data['US']['c4Kills']),
          getNum(data['AU']['c4Kills']),
          getNum(data['EU']['c4Kills'])
        ]
      }, {
        label: 'C5',
        backgroundColor: 'rgba(255,102,0,0.5)',
        data: [
          getNum(data['US']['c5Kills']),
          getNum(data['AU']['c5Kills']),
          getNum(data['EU']['c5Kills'])
        ]
      }, {
        label: 'C6',
        backgroundColor: 'rgba(204,0,0,0.5)',
        data: [
          getNum(data['US']['c6Kills']),
          getNum(data['AU']['c6Kills']),
          getNum(data['EU']['c6Kills'])
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [{
          ticks: {
            beginAtZero: true,
            fontFamily: "'Open Sans Bold', sans-serif",
            fontSize: 11
          },
          scaleLabel: {
            display: false
          },
          gridLines: {},
          stacked: false
        }],
        yAxes: [{
          gridLines: {
            display: false,
            color: '#fff',
            zeroLineColor: '#fff',
            zeroLineWidth: 0
          },
          ticks: {
            fontFamily: "'Open Sans Bold', sans-serif",
            fontSize: 11
          },
          stacked: false
        }]
      }
    }
  }
  ctxKills = new Chart($('#chartKills'), dataKills)

  // ISK CHART
  var dataTotalBillionsUSHour = getNum(data['US']['totalISK'])
  dataTotalBillionsUSHour /= Math.pow(10, 9)
  var dataTotalBillionsAUHour = getNum(data['AU']['totalISK'])
  dataTotalBillionsAUHour /= Math.pow(10, 9)
  var dataTotalBillionsEUHour = getNum(data['EU']['totalISK'])
  dataTotalBillionsEUHour /= Math.pow(10, 9)

  var dataISK = {
    type: 'bar',
    data: {
      labels: ['US (0000-0800)', 'AU (0800-1600)', 'EU (1600-0000)'],
      datasets: [{
        label: 'Total ISK Killed',
        backgroundColor: 'rgba(255,107,107,0.5)',
        data: [dataTotalBillionsUSHour.toFixed(2), dataTotalBillionsAUHour.toFixed(2), dataTotalBillionsEUHour.toFixed(2)]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  }
  ctxISK = new Chart($('#chartISK'), dataISK)

  var dataBreakdowns = {
    type: 'horizontalBar',
    data: {
      labels: [
        'Dreadnoughts', 'Force Auxiliary', 'Carriers',
        'T1 Battleships', 'Faction Battleships', 'Marauders', 'Black Ops',
        'T1 Battlecruisers', 'Faction Battlecruisers', 'Command Ships',
        'T1 Cruisers', 'Faction Cruisers', 'Recon Ships', 'Heavy Assault Cruisers', 'Heavy Interdictors', 'Logistics Cruisers', 'Strategic Cruisers',
        'T1 Destroyers', 'Interdictors', 'Command Destroyers', 'Tactical Destroyers',
        'T1 Frigates', 'Faction Frigates', 'Electronic Attack Frigates', 'Interceptors', 'Assault Frigates', 'Logistics Frigates', 'Covert Ops', 'Stealth Bombers'
      ],
      datasets: [{
        label: 'US',
        backgroundColor: 'rgba(153,204,255,0.5)',
        data: [
          typeof data['US']['shipsUsed']['Dreadnoughts'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Dreadnoughts']['totalUses']),
          typeof data['US']['shipsUsed']['Force Auxiliary'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Force Auxiliary']['totalUses']),
          typeof data['US']['shipsUsed']['Carriers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Carriers']['totalUses']),
          typeof data['US']['shipsUsed']['Battleships'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Battleships']['totalUses']),
          typeof data['US']['shipsUsed']['Faction Battleships'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Faction Battleships']['totalUses']),
          typeof data['US']['shipsUsed']['Marauders'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Marauders']['totalUses']),
          typeof data['US']['shipsUsed']['Black Ops'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Black Ops']['totalUses']),
          typeof data['US']['shipsUsed']['Battlecruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Battlecruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Faction Battlecruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Faction Battlecruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Command Ships'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Command Ships']['totalUses']),
          typeof data['US']['shipsUsed']['Cruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Cruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Faction Cruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Faction Cruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Recon Ships'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Recon Ships']['totalUses']),
          typeof data['US']['shipsUsed']['Heavy Assault Cruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Heavy Assault Cruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Heavy Interdictors'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Heavy Interdictors']['totalUses']),
          typeof data['US']['shipsUsed']['Logistics Cruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Logistics Cruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Strategic Cruisers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Strategic Cruisers']['totalUses']),
          typeof data['US']['shipsUsed']['Destroyers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Destroyers']['totalUses']),
          typeof data['US']['shipsUsed']['Interdictors'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Interdictors']['totalUses']),
          typeof data['US']['shipsUsed']['Command Destroyers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Command Destroyers']['totalUses']),
          typeof data['US']['shipsUsed']['Tactical Destroyers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Tactical Destroyers']['totalUses']),
          typeof data['US']['shipsUsed']['Frigates'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Frigates']['totalUses']),
          typeof data['US']['shipsUsed']['Faction Frigates'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Faction Frigates']['totalUses']),
          typeof data['US']['shipsUsed']['Electronic Attack Frigates'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Electronic Attack Frigates']['totalUses']),
          typeof data['US']['shipsUsed']['Interceptors'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Interceptors']['totalUses']),
          typeof data['US']['shipsUsed']['Assault Frigates'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Assault Frigates']['totalUses']),
          typeof data['US']['shipsUsed']['Logistics Frigate'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Logistics Frigate']['totalUses']),
          typeof data['US']['shipsUsed']['Covert Ops'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Covert Ops']['totalUses']),
          typeof data['US']['shipsUsed']['Stealth Bombers'] === 'undefined' ? 0 : getNum(data['US']['shipsUsed']['Stealth Bombers']['totalUses'])
        ]
      }, {
        label: 'AU',
        backgroundColor: 'rgba(0,51,204,0.5)',
        data: [
          typeof data['AU']['shipsUsed']['Dreadnoughts'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Dreadnoughts']['totalUses']),
          typeof data['AU']['shipsUsed']['Force Auxiliary'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Force Auxiliary']['totalUses']),
          typeof data['AU']['shipsUsed']['Carriers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Carriers']['totalUses']),
          typeof data['AU']['shipsUsed']['Battleships'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Battleships']['totalUses']),
          typeof data['AU']['shipsUsed']['Faction Battleships'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Faction Battleships']['totalUses']),
          typeof data['AU']['shipsUsed']['Marauders'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Marauders']['totalUses']),
          typeof data['AU']['shipsUsed']['Black Ops'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Black Ops']['totalUses']),
          typeof data['AU']['shipsUsed']['Battlecruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Battlecruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Faction Battlecruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Faction Battlecruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Command Ships'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Command Ships']['totalUses']),
          typeof data['AU']['shipsUsed']['Cruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Cruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Faction Cruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Faction Cruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Recon Ships'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Recon Ships']['totalUses']),
          typeof data['AU']['shipsUsed']['Heavy Assault Cruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Heavy Assault Cruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Heavy Interdictors'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Heavy Interdictors']['totalUses']),
          typeof data['AU']['shipsUsed']['Logistics Cruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Logistics Cruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Strategic Cruisers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Strategic Cruisers']['totalUses']),
          typeof data['AU']['shipsUsed']['Destroyers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Destroyers']['totalUses']),
          typeof data['AU']['shipsUsed']['Interdictors'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Interdictors']['totalUses']),
          typeof data['AU']['shipsUsed']['Command Destroyers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Command Destroyers']['totalUses']),
          typeof data['AU']['shipsUsed']['Tactical Destroyers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Tactical Destroyers']['totalUses']),
          typeof data['AU']['shipsUsed']['Frigates'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Frigates']['totalUses']),
          typeof data['AU']['shipsUsed']['Faction Frigates'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Faction Frigates']['totalUses']),
          typeof data['AU']['shipsUsed']['Electronic Attack Frigates'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Electronic Attack Frigates']['totalUses']),
          typeof data['AU']['shipsUsed']['Interceptors'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Interceptors']['totalUses']),
          typeof data['AU']['shipsUsed']['Assault Frigates'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Assault Frigates']['totalUses']),
          typeof data['AU']['shipsUsed']['Logistics Frigate'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Logistics Frigate']['totalUses']),
          typeof data['AU']['shipsUsed']['Covert Ops'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Covert Ops']['totalUses']),
          typeof data['AU']['shipsUsed']['Stealth Bombers'] === 'undefined' ? 0 : getNum(data['AU']['shipsUsed']['Stealth Bombers']['totalUses'])
        ]
      }, {
        label: 'EU',
        backgroundColor: 'rgba(102,153,0,0.5)',
        data: [
          typeof data['EU']['shipsUsed']['Dreadnoughts'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Dreadnoughts']['totalUses']),
          typeof data['EU']['shipsUsed']['Force Auxiliary'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Force Auxiliary']['totalUses']),
          typeof data['EU']['shipsUsed']['Carriers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Carriers']['totalUses']),
          typeof data['EU']['shipsUsed']['Battleships'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Battleships']['totalUses']),
          typeof data['EU']['shipsUsed']['Faction Battleships'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Faction Battleships']['totalUses']),
          typeof data['EU']['shipsUsed']['Marauders'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Marauders']['totalUses']),
          typeof data['EU']['shipsUsed']['Black Ops'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Black Ops']['totalUses']),
          typeof data['EU']['shipsUsed']['Battlecruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Battlecruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Faction Battlecruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Faction Battlecruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Command Ships'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Command Ships']['totalUses']),
          typeof data['EU']['shipsUsed']['Cruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Cruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Faction Cruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Faction Cruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Recon Ships'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Recon Ships']['totalUses']),
          typeof data['EU']['shipsUsed']['Heavy Assault Cruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Heavy Assault Cruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Heavy Interdictors'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Heavy Interdictors']['totalUses']),
          typeof data['EU']['shipsUsed']['Logistics Cruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Logistics Cruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Strategic Cruisers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Strategic Cruisers']['totalUses']),
          typeof data['EU']['shipsUsed']['Destroyers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Destroyers']['totalUses']),
          typeof data['EU']['shipsUsed']['Interdictors'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Interdictors']['totalUses']),
          typeof data['EU']['shipsUsed']['Command Destroyers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Command Destroyers']['totalUses']),
          typeof data['EU']['shipsUsed']['Tactical Destroyers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Tactical Destroyers']['totalUses']),
          typeof data['EU']['shipsUsed']['Frigates'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Frigates']['totalUses']),
          typeof data['EU']['shipsUsed']['Faction Frigates'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Faction Frigates']['totalUses']),
          typeof data['EU']['shipsUsed']['Electronic Attack Frigates'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Electronic Attack Frigates']['totalUses']),
          typeof data['EU']['shipsUsed']['Interceptors'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Interceptors']['totalUses']),
          typeof data['EU']['shipsUsed']['Assault Frigates'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Assault Frigates']['totalUses']),
          typeof data['EU']['shipsUsed']['Logistics Frigate'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Logistics Frigate']['totalUses']),
          typeof data['EU']['shipsUsed']['Covert Ops'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Covert Ops']['totalUses']),
          typeof data['EU']['shipsUsed']['Stealth Bombers'] === 'undefined' ? 0 : getNum(data['EU']['shipsUsed']['Stealth Bombers']['totalUses'])
        ]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        xAxes: [{
          ticks: {
            beginAtZero: true,
            fontFamily: "'Open Sans Bold', sans-serif",
            fontSize: 11
          },
          scaleLabel: {
            display: false
          },
          gridLines: {},
          stacked: true
        }],
        yAxes: [{
          gridLines: {
            display: false,
            color: '#fff',
            zeroLineColor: '#fff',
            zeroLineWidth: 0
          },
          ticks: {
            fontFamily: "'Open Sans Bold', sans-serif",
            fontSize: 11
          },
          stacked: true,
          barPercentage: 0.25
        }]
      }
    }
  }
  ctxBreakdowns = new Chart($('#chartBreakdowns'), dataBreakdowns)

  renderedOnce = 1
}
