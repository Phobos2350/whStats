var seconds = 120
var refreshPeriod = 120
var refresh = false
var lastCached = 0
var setPeriod = 0
var renderedOnce = 0
var chartCarrierDrawn = false; var chartDreadDrawn = false; var chartFAXDrawn = false
var date = new Date()
var month = new Date().getMonth()
var year = new Date().getFullYear()
var weekday = new Array(7)
weekday[0] = 'Sun'
weekday[1] = 'Mon'
weekday[2] = 'Tue'
weekday[3] = 'Wed'
weekday[4] = 'Thu'
weekday[5] = 'Fri'
weekday[6] = 'Sat'
var ctxHour = null
var ctxISKHour = null
var ctxTotalCarrier = null
var ctxTotalDread = null
var ctxTotalFAX = null
var ctxBreakdowns = null

$(document).ready(function () {
  setPeriod = 'hour'
  $('.statsLink').parent().addClass('active')
  $('.periodLinks-hour').trigger('click')
  setTimeout(function () {
    Materialize.toast('Welcome to 2.0 - Reddit Stats Summary for September is Under Construction, AT Permitting!', 10000)
  }, 2000)
})

$('.info-text').click(function () {
  $('#modal2').openModal()
})

$('.periodLinks').click(function () {
  setPeriod = $(this).text().toLowerCase()
  $('.periodLinks').parent().removeClass('active')
  $(this).parent().addClass('active')
  seconds = 120
  date = new Date()
  $('.period').removeClass('hide')
  if (setPeriod === 'hour') {
    refreshPeriod = 120
    seconds = 120
    if (date.getHours() === 0) {
      date.setHours(23)
      date.setDate(date.getDate() - 1)
    } else {
      date.setHours(date.getHours() - 1)
    }
    $('.period').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    $('.periodStats').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
  }
  if (setPeriod === 'day') {
    refreshPeriod = 300
    seconds = 300
    if (date.getDay() === 0) {
      date.setDate(6)
    } else {
      date.setDate(date.getDate() - 1)
    }
    $('.period').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    $('.periodStats').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
  }
  if (setPeriod === 'week') {
    refreshPeriod = 900
    seconds = 900
    date.setDate(date.getDate() - 7)
    $('.period').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    $('.periodStats').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
  }
  if (setPeriod === 'month') {
    refreshPeriod = 1800
    seconds = 1800
    $('.period').addClass('hide')
    $('.monthLinks').removeClass('hide')
    setPeriod = 'year/' + year + '/month/' + (month + 1)
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
  } else {
    $('.monthLinks').addClass('hide')
  }
  changePeriod(setPeriod)
})

$('.prevMonth').click(function () {
  refreshPeriod = 1800
  seconds = 1800
  if (month === 0) {
    month = 11
    year -= 1
  } else {
    month -= 1
  }
  setPeriod = 'year/' + year + '/month/' + (month + 1)
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
  refreshPeriod = 1800
  seconds = 1800
  if (month === 13) {
    month = 1
    year += 1
  } else {
    month += 1
  }
  setPeriod = 'year/' + year + '/month/' + (month + 1)
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

setInterval(function () {
  if (refresh) {
    $('#refreshText').text('Retrieving new kills... ')
    $('#countdown').text('Standby')
    changePeriod(setPeriod)
    refresh = false
    seconds = refreshPeriod
  } else {
    var thisDate = new Date()
    $('#refreshText').text('Time until next refresh ')
    if (setPeriod === 'hour') {
      seconds = (refreshPeriod - ((thisDate.getMinutes() % 2) * 60)) - (thisDate.getSeconds() % 60)
      $('#countdown').text(fmtMSS(seconds))
      parseInt(seconds, 10) === 1 ? refresh = true : refresh = false
    }
    if (setPeriod === 'day') {
      seconds = (refreshPeriod - ((thisDate.getMinutes() % 5) * 60)) - (thisDate.getSeconds() % 60)
      $('#countdown').text(fmtMSS(seconds))
      parseInt(seconds, 10) === 1 ? refresh = true : refresh = false
    }
    if (setPeriod === 'week') {
      seconds  = (refreshPeriod - ((thisDate.getMinutes() % 15) * 60)) - (thisDate.getSeconds() % 60)
      $('#countdown').text(fmtMSS(seconds))
      parseInt(seconds, 10) === 1 ? refresh = true : refresh = false
    }
    if (setPeriod === 'month') {
      seconds = (refreshPeriod - ((thisDate.getMinutes() % 30) * 60)) - (thisDate.getSeconds() % 60)
      $('#countdown').text(fmtMSS(seconds))
      parseInt(seconds, 10) === 1 ? refresh = true : refresh = false
    }
  }
}, 1000)

function changePeriod (period) {
  $('#modal1').openModal()
  $.getJSON('./api/rethink/stats/' + period + '/', function (json) {
    updateStats(json)
    updateCharts(json)
    setTimeout(function () {
      $('#modal1').closeModal()
    }, 500)
  }).error(function (error) {
    console.log(error)
    window.location.replace('./api/rethink/stats/' + period + '/')
  })
}

function pad (n) {
  return n < 10 ? '0' + n : n
}

function getNum (val) {
  if (isNaN(val) || val == null) {
    return 0
  }
  return val
}

function fmtMSS (s) {
  return (s - (s %= 60)) / 60 + (9 < s ? ':' : ':0') + s
}

function truncateString (str, length) {
  if (str !== null) {
    return str.length > length ? str.substring(0, length - 3) + '...' : str
  } else {
    return '...'
  }
}

function updateStats (data) {
  lastCached = data['lastCached']
  $('.lastCached').text(lastCached)
  data = data['statsArray']['stats']
  var biggestNPCKill = 0
  var biggestSoloKill = 0
  var biggestTotalKill = 0
  var iskText = ''
  var killTotal = data[1]['totalKills'] + data[2]['totalKills'] + data[3]['totalKills'] + data[4]['totalKills'] + data[5]['totalKills'] + data[6]['totalKills']
  $('.totalKills').text(killTotal)
  var iskTotal = data[1]['totalISK'] + data[2]['totalISK'] + data[3]['totalISK'] + data[4]['totalISK'] + data[5]['totalISK'] + data[6]['totalISK']
  iskTotal /= Math.pow(10, 3)
  iskTotal > 1000 ? $('.totalISK').text((iskTotal / Math.pow(10, 3)).toFixed(2) + ' Trillion ISK') : $('.totalISK').text(iskTotal.toFixed(2) + ' Billion ISK')

  for (var i = 1; i < 7; i++) {
    if (data[i]['biggestKill']['killID'] == null) {
      $('.biggestC' + (i) + 'Img').attr('src', '../img/blank_symbol.png')
      $('.biggestC' + (i) + 'Kill').text('No Kills!')
    } else {
      $('.biggestC' + (i) + 'Img').attr('src', 'https://imageserver.eveonline.com/Type/' + data[i]['biggestKill']['typeID'] + '_64.png')
      var iskValue = data[i]['biggestKill']['value']
      if (iskValue >= 1000) {
        iskValue /= Math.pow(10, 3)
        iskText = iskValue.toFixed(2) + 'bil ISK'
      } else {
        iskText = iskValue.toFixed(0) + 'mil ISK'
      }
      $('.biggestC' + (i) + 'Kill').text(truncateString(data[i]['biggestKill']['shipName'], 16) + ' - ' + iskText)
      $('.biggestC' + (i) + 'Kill').attr('href', 'https://zkillboard.com/kill/' + data[i]['biggestKill']['killID'] + '/')

      if (parseInt(data[i]['biggestKill']['value'], 10) > biggestTotalKill) {
        biggestTotalKill = data[i]['biggestKill']['value']
        $('.biggestTotalImg').attr('src', 'https://imageserver.eveonline.com/Type/' + data[i]['biggestKill']['typeID'] + '_64.png')
        iskValue = data[i]['biggestKill']['value']
        if (iskValue >= 1000) {
          iskValue /= Math.pow(10, 3)
          iskText = iskValue.toFixed(2) + 'bil ISK'
        } else {
          iskText = iskValue.toFixed(0) + 'mil ISK'
        }
        $('.biggestTotalKill').text(truncateString(data[i]['biggestKill']['shipName'], 16) + ' - ' + iskText)
        $('.biggestTotalKill').attr('href', 'https://zkillboard.com/kill/' + data[i]['biggestKill']['killID'] + '/')
      }

      if (parseInt(data[i]['biggestSoloKill']['value'], 10) > biggestSoloKill) {
        biggestSoloKill = data[i]['biggestSoloKill']['value']
        $('.biggestSoloImg').attr('src', 'https://imageserver.eveonline.com/Type/' + data[i]['biggestSoloKill']['typeID'] + '_64.png')
        iskValue = data[i]['biggestSoloKill']['value']
        if (iskValue >= 1000) {
          iskValue /= Math.pow(10, 3)
          iskText = iskValue.toFixed(2) + 'bil ISK'
        } else {
          iskText = iskValue.toFixed(0) + 'mil ISK'
        }
        $('.biggestSoloKill').text(truncateString(data[i]['biggestSoloKill']['shipName'], 16) + ' - ' + iskText)
        $('.biggestSoloKill').attr('href', 'https://zkillboard.com/kill/' + data[i]['biggestSoloKill']['killID'] + '/')
      }

      if (parseInt(data[i]['biggestNPCKill']['value'], 10) > biggestNPCKill) {
        biggestNPCKill = data[i]['biggestNPCKill']['value']
        $('.biggestNPCImg').attr('src', 'https://imageserver.eveonline.com/Type/' + data[i]['biggestNPCKill']['typeID'] + '_64.png')
        iskValue = data[i]['biggestNPCKill']['value']
        if (iskValue >= 1000) {
          iskValue /= Math.pow(10, 3)
          iskText = iskValue.toFixed(2) + 'bil ISK'
        } else {
          iskText = iskValue.toFixed(0) + 'mil ISK'
        }
        $('.biggestNPCKill').text(truncateString(data[i]['biggestNPCKill']['shipName'], 16) + ' - ' + iskText)
        $('.biggestNPCKill').attr('href', 'https://zkillboard.com/kill/' + data[i]['biggestNPCKill']['killID'] + '/')
      }
    }
  }
}

function updateCharts (data) {
  data = data['statsArray']['stats']
  if (renderedOnce === 1) {
    ctxHour != null ? ctxHour.destroy() : ctxHour = null
    ctxISKHour != null ? ctxISKHour.destroy() : ctxISKHour = null
    ctxTotalCarrier != null && chartCarrierDrawn ? ctxTotalCarrier.destroy() : ctxTotalCarrier = null
    ctxTotalDread != null && chartDreadDrawn ? ctxTotalDread.destroy() : ctxTotalDread = null
    ctxTotalFAX != null && chartFAXDrawn ? ctxTotalFAX.destroy() : ctxTotalFAX = null
    ctxBreakdowns != null ? ctxBreakdowns.destroy() : ctxBreakdowns = null
  }

  var dataHour = {
    type: 'bar',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [{
        label: 'Pod Kills',
        backgroundColor: 'rgba(255,0,0,0.5)',
        data: [
          getNum(data[1]['kills']['shipTechs']['Capsule']),
          getNum(data[2]['kills']['shipTechs']['Capsule']),
          getNum(data[3]['kills']['shipTechs']['Capsule']),
          getNum(data[4]['kills']['shipTechs']['Capsule']),
          getNum(data[5]['kills']['shipTechs']['Capsule']),
          getNum(data[6]['kills']['shipTechs']['Capsule'])
        ]
      }, {
        label: 'T1 Kills',
        backgroundColor: 'rgba(102,153,153,0.5)',
        data: [
          getNum(data[1]['kills']['shipTechs']['T1']),
          getNum(data[2]['kills']['shipTechs']['T1']),
          getNum(data[3]['kills']['shipTechs']['T1']),
          getNum(data[4]['kills']['shipTechs']['T1']),
          getNum(data[5]['kills']['shipTechs']['T1']),
          getNum(data[6]['kills']['shipTechs']['T1'])
        ]
      }, {
        label: 'Faction Kills',
        backgroundColor: 'rgba(51,153,102,0.5)',
        data: [
          getNum(data[1]['kills']['shipTechs']['Faction']),
          getNum(data[2]['kills']['shipTechs']['Faction']),
          getNum(data[3]['kills']['shipTechs']['Faction']),
          getNum(data[4]['kills']['shipTechs']['Faction']),
          getNum(data[5]['kills']['shipTechs']['Faction']),
          getNum(data[6]['kills']['shipTechs']['Faction'])
        ]
      }, {
        label: 'T2 Kills',
        backgroundColor: 'rgba(255,204,0,0.5)',
        data: [
          getNum(data[1]['kills']['shipTechs']['T2']),
          getNum(data[2]['kills']['shipTechs']['T2']),
          getNum(data[3]['kills']['shipTechs']['T2']),
          getNum(data[4]['kills']['shipTechs']['T2']),
          getNum(data[5]['kills']['shipTechs']['T2']),
          getNum(data[6]['kills']['shipTechs']['T2'])
        ]
      }, {
        label: 'T3 Kills',
        backgroundColor: 'rgba(255,102,0,0.5)',
        data: [
          getNum(data[1]['kills']['shipTechs']['T3']),
          getNum(data[2]['kills']['shipTechs']['T3']),
          getNum(data[3]['kills']['shipTechs']['T3']),
          getNum(data[4]['kills']['shipTechs']['T3']),
          getNum(data[5]['kills']['shipTechs']['T3']),
          getNum(data[6]['kills']['shipTechs']['T3'])
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
          stacked: true
        }]
      }
    }
  }
  ctxHour = new Chart($('#chartHour'), dataHour)

  // ISK CHART
  var dataTotalBillionsC1Hour = getNum(data[1]['totalISK'])
  dataTotalBillionsC1Hour /= Math.pow(10, 3)
  var dataTotalBillionsC2Hour = getNum(data[2]['totalISK'])
  dataTotalBillionsC2Hour /= Math.pow(10, 3)
  var dataTotalBillionsC3Hour = getNum(data[3]['totalISK'])
  dataTotalBillionsC3Hour /= Math.pow(10, 3)
  var dataTotalBillionsC4Hour = getNum(data[4]['totalISK'])
  dataTotalBillionsC4Hour /= Math.pow(10, 3)
  var dataTotalBillionsC5Hour = getNum(data[5]['totalISK'])
  dataTotalBillionsC5Hour /= Math.pow(10, 3)
  var dataTotalBillionsC6Hour = getNum(data[6]['totalISK'])
  dataTotalBillionsC6Hour /= Math.pow(10, 3)

  var dataISKHour = {
    type: 'line',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [{
        label: 'Total ISK Killed',
        backgroundColor: 'rgba(255,107,107,0.5)',
        data: [dataTotalBillionsC1Hour.toFixed(2), dataTotalBillionsC2Hour.toFixed(2), dataTotalBillionsC3Hour.toFixed(2),
               dataTotalBillionsC4Hour.toFixed(2), dataTotalBillionsC5Hour.toFixed(2), dataTotalBillionsC6Hour.toFixed(2)]
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  }
  ctxISKHour = new Chart($('#chartISKHour'), dataISKHour)

  var c1Avg = data[1]['totalISK'] / data[1]['totalKills']
  var c2Avg = data[2]['totalISK'] / data[2]['totalKills']
  var c3Avg = data[3]['totalISK'] / data[3]['totalKills']
  var c4Avg = data[4]['totalISK'] / data[4]['totalKills']
  var c5Avg = data[5]['totalISK'] / data[5]['totalKills']
  var c6Avg = data[6]['totalISK'] / data[6]['totalKills']

  $('#c1Avg').html(isNaN(c1Avg) ? ' No Data' : c1Avg.toFixed(1) + ' Million ISK')
  $('#c2Avg').html(isNaN(c2Avg) ? ' No Data' : c2Avg.toFixed(1) + ' Million ISK')
  $('#c3Avg').html(isNaN(c3Avg) ? ' No Data' : c3Avg.toFixed(1) + ' Million ISK')
  $('#c4Avg').html(isNaN(c4Avg) ? ' No Data' : c4Avg.toFixed(1) + ' Million ISK')
  $('#c5Avg').html(isNaN(c5Avg) ? ' No Data' : c5Avg.toFixed(1) + ' Million ISK')
  $('#c6Avg').html(isNaN(c6Avg) ? ' No Data' : c6Avg.toFixed(1) + ' Million ISK')

  var dataTotalCarrier = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [{
        backgroundColor: [
          'rgba(102,255,204,0.5)',
          'rgba(153,204,255,0.5)',
          'rgba(0,51,204,0.5)',
          'rgba(102,153,0,0.5)',
          'rgba(255,102,0,0.5)',
          'rgba(204,0,0,0.5)'
        ],
        data: [
          getNum(data[1]['kills']['typeNames']['Carriers']),
          getNum(data[2]['kills']['typeNames']['Carriers']),
          getNum(data[3]['kills']['typeNames']['Carriers']),
          getNum(data[4]['kills']['typeNames']['Carriers']),
          getNum(data[5]['kills']['typeNames']['Carriers']),
          getNum(data[6]['kills']['typeNames']['Carriers'])
        ]
      }]
    },
    options: {
      legend: {
        display: false
      }
    }
  }
  var dataTotalDread = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [{
        backgroundColor: [
          'rgba(102,255,204,0.5)',
          'rgba(153,204,255,0.5)',
          'rgba(0,51,204,0.5)',
          'rgba(102,153,0,0.5)',
          'rgba(255,102,0,0.5)',
          'rgba(204,0,0,0.5)'
        ],
        data: [
          getNum(data[1]['kills']['typeNames']['Dreadnoughts']),
          getNum(data[2]['kills']['typeNames']['Dreadnoughts']),
          getNum(data[3]['kills']['typeNames']['Dreadnoughts']),
          getNum(data[4]['kills']['typeNames']['Dreadnoughts']),
          getNum(data[5]['kills']['typeNames']['Dreadnoughts']),
          getNum(data[6]['kills']['typeNames']['Dreadnoughts'])
        ]
      }]
    },
    options: {
      legend: {
        display: false
      }
    }
  }
  var dataTotalFAX = {
    type: 'doughnut',
    data: {
      labels: ['C1', 'C2', 'C3', 'C4', 'C5', 'C6'],
      datasets: [{
        backgroundColor: [
          'rgba(102,255,204,0.5)',
          'rgba(153,204,255,0.5)',
          'rgba(0,51,204,0.5)',
          'rgba(102,153,0,0.5)',
          'rgba(255,102,0,0.5)',
          'rgba(204,0,0,0.5)'
        ],
        data: [
          getNum(data[1]['kills']['typeNames']['Force Auxiliary']),
          getNum(data[2]['kills']['typeNames']['Force Auxiliary']),
          getNum(data[3]['kills']['typeNames']['Force Auxiliary']),
          getNum(data[4]['kills']['typeNames']['Force Auxiliary']),
          getNum(data[5]['kills']['typeNames']['Force Auxiliary']),
          getNum(data[6]['kills']['typeNames']['Force Auxiliary'])
        ]
      }]
    },
    options: {
      legend: {
        display: false
      }
    }
  }

  getNum(data[1]['kills']['typeNames']['Carriers']) + getNum(data[2]['kills']['typeNames']['Carriers']) + getNum(data[3]['kills']['typeNames']['Carriers']) +
  getNum(data[4]['kills']['typeNames']['Carriers']) + getNum(data[5]['kills']['typeNames']['Carriers']) + getNum(data[6]['kills']['typeNames']['Carriers']) > 0
  ? (ctxTotalCarrier = new Chart($('#chartTotalCarrier'), dataTotalCarrier), chartCarrierDrawn = true) : (ctxTotalCarrier = document.getElementById('chartTotalCarrier').getContext('2d'), ctxTotalCarrier.font = '20px Arial', ctxTotalCarrier.fillText('No kills', 10, 50), chartCarrierDrawn = false)
  getNum(data[1]['kills']['typeNames']['Dreadnoughts']) + getNum(data[2]['kills']['typeNames']['Dreadnoughts']) + getNum(data[3]['kills']['typeNames']['Dreadnoughts']) +
  getNum(data[4]['kills']['typeNames']['Dreadnoughts']) + getNum(data[5]['kills']['typeNames']['Dreadnoughts']) + getNum(data[6]['kills']['typeNames']['Dreadnoughts']) > 0
  ? (ctxTotalDread = new Chart($('#chartTotalDread'), dataTotalDread), chartDreadDrawn = true) : (ctxTotalDread = document.getElementById('chartTotalDread').getContext('2d'), ctxTotalDread.font = '20px Arial', ctxTotalDread.fillText('No kills', 10, 50), chartDreadDrawn = false)
  getNum(data[1]['kills']['typeNames']['Force Auxiliary']) + getNum(data[2]['kills']['typeNames']['Force Auxiliary']) + getNum(data[3]['kills']['typeNames']['Force Auxiliary']) +
  getNum(data[4]['kills']['typeNames']['Force Auxiliary']) + getNum(data[5]['kills']['typeNames']['Force Auxiliary']) + getNum(data[6]['kills']['typeNames']['Force Auxiliary']) > 0
  ? (ctxTotalFAX = new Chart($('#chartTotalFAX'), dataTotalFAX), chartFAXDrawn = true) : (ctxTotalFAX = document.getElementById('chartTotalFAX').getContext('2d'), ctxTotalFAX.font = '20px Arial', ctxTotalFAX.fillText('No kills', 10, 50), chartFAXDrawn = false)

  var dataBreakdowns = {
    type: 'horizontalBar',
    data: {
      labels: ['Shuttles', 'Rookie Ships',
          'T1 Battleships', 'Faction Battleships', 'Marauders', 'Black Ops',
          'T1 Battlecruisers', 'Faction Battlecruisers', 'Command Ships',
          'T1 Cruisers', 'Faction Cruisers', 'Recon Ships', 'Heavy Assault Cruisers', 'Heavy Interdictors', 'Logistics Cruisers', 'Strategic Cruisers',
          'T1 Destroyers', 'Interdictors', 'Command Destroyers', 'Tactical Destroyers',
          'T1 Frigates', 'Faction Frigates', 'Electronic Attack Frigates', 'Interceptors', 'Assault Frigates', 'Logistics Frigates', 'Covert Ops', 'Stealth Bombers',
          'Mining Frigate', 'Mining Barges', 'Exhumer Barges', 'Capital Industrial Ships', 'Industrial Ships', 'Transport Ships'
        ],
      datasets: [{
        label: 'C1',
        backgroundColor: 'rgba(102,255,204,0.5)',
        data: [
          getNum(data[1]['kills']['typeNames']['Shuttle']),
          getNum(data[1]['kills']['typeNames']['Rookie Ships']),
          getNum(data[1]['kills']['typeNames']['Battleships']),
          getNum(data[1]['kills']['typeNames']['Faction Battleships']),
          getNum(data[1]['kills']['typeNames']['Marauders']),
          getNum(data[1]['kills']['typeNames']['Black Ops']),
          getNum(data[1]['kills']['typeNames']['Battlecruisers']) + getNum(data[1]['kills']['typeNames']['Battlecruisers (Attack)']),
          getNum(data[1]['kills']['typeNames']['Faction Battlecruisers']),
          getNum(data[1]['kills']['typeNames']['Command Ships']),
          getNum(data[1]['kills']['typeNames']['Cruisers']),
          getNum(data[1]['kills']['typeNames']['Faction Cruisers']),
          getNum(data[1]['kills']['typeNames']['Recon Ships']),
          getNum(data[1]['kills']['typeNames']['Heavy Assault Cruisers']),
          getNum(data[1]['kills']['typeNames']['Heavy Interdictors']),
          getNum(data[1]['kills']['typeNames']['Logistics Cruisers']),
          getNum(data[1]['kills']['typeNames']['Strategic Cruisers']),
          getNum(data[1]['kills']['typeNames']['Destroyers']),
          getNum(data[1]['kills']['typeNames']['Interdictors']),
          getNum(data[1]['kills']['typeNames']['Command Destroyers']),
          getNum(data[1]['kills']['typeNames']['Tactical Destroyers']),
          getNum(data[1]['kills']['typeNames']['Frigates']),
          getNum(data[1]['kills']['typeNames']['Faction Frigates']),
          getNum(data[1]['kills']['typeNames']['Electronic Attack Frigates']),
          getNum(data[1]['kills']['typeNames']['Interceptors']),
          getNum(data[1]['kills']['typeNames']['Assault Frigates']),
          getNum(data[1]['kills']['typeNames']['Logistics Frigate']),
          getNum(data[1]['kills']['typeNames']['Covert Ops']),
          getNum(data[1]['kills']['typeNames']['Stealth Bombers']),
          getNum(data[1]['kills']['typeNames']['Mining Frigate']),
          getNum(data[1]['kills']['typeNames']['Mining Barges']),
          getNum(data[1]['kills']['typeNames']['Exhumer Barges']),
          getNum(data[1]['kills']['typeNames']['Capital Industrial Ships']),
          getNum(data[1]['kills']['typeNames']['Industrial Ships']),
          getNum(data[1]['kills']['typeNames']['Transport Ships'])
        ]
      }, {
        label: 'C2',
        backgroundColor: 'rgba(153,204,255,0.5)',
        data: [
          getNum(data[2]['kills']['typeNames']['Shuttle']),
          getNum(data[2]['kills']['typeNames']['Rookie Ships']),
          getNum(data[2]['kills']['typeNames']['Battleships']),
          getNum(data[2]['kills']['typeNames']['Faction Battleships']),
          getNum(data[2]['kills']['typeNames']['Marauders']),
          getNum(data[2]['kills']['typeNames']['Black Ops']),
          getNum(data[2]['kills']['typeNames']['Battlecruisers']) + getNum(data[2]['kills']['typeNames']['Battlecruisers (Attack)']),
          getNum(data[2]['kills']['typeNames']['Faction Battlecruisers']),
          getNum(data[2]['kills']['typeNames']['Command Ships']),
          getNum(data[2]['kills']['typeNames']['Cruisers']),
          getNum(data[2]['kills']['typeNames']['Faction Cruisers']),
          getNum(data[2]['kills']['typeNames']['Recon Ships']),
          getNum(data[2]['kills']['typeNames']['Heavy Assault Cruisers']),
          getNum(data[2]['kills']['typeNames']['Heavy Interdictors']),
          getNum(data[2]['kills']['typeNames']['Logistics Cruisers']),
          getNum(data[2]['kills']['typeNames']['Strategic Cruisers']),
          getNum(data[2]['kills']['typeNames']['Destroyers']),
          getNum(data[2]['kills']['typeNames']['Interdictors']),
          getNum(data[2]['kills']['typeNames']['Command Destroyers']),
          getNum(data[2]['kills']['typeNames']['Tactical Destroyers']),
          getNum(data[2]['kills']['typeNames']['Frigates']),
          getNum(data[2]['kills']['typeNames']['Faction Frigates']),
          getNum(data[2]['kills']['typeNames']['Electronic Attack Frigates']),
          getNum(data[2]['kills']['typeNames']['Interceptors']),
          getNum(data[2]['kills']['typeNames']['Assault Frigates']),
          getNum(data[2]['kills']['typeNames']['Logistics Frigate']),
          getNum(data[2]['kills']['typeNames']['Covert Ops']),
          getNum(data[2]['kills']['typeNames']['Stealth Bombers']),
          getNum(data[2]['kills']['typeNames']['Mining Frigate']),
          getNum(data[2]['kills']['typeNames']['Mining Barges']),
          getNum(data[2]['kills']['typeNames']['Exhumer Barges']),
          getNum(data[2]['kills']['typeNames']['Capital Industrial Ships']),
          getNum(data[2]['kills']['typeNames']['Industrial Ships']),
          getNum(data[2]['kills']['typeNames']['Transport Ships'])
        ]
      }, {
        label: 'C3',
        backgroundColor: 'rgba(0,51,204,0.5)',
        data: [
          getNum(data[3]['kills']['typeNames']['Shuttle']),
          getNum(data[3]['kills']['typeNames']['Rookie Ships']),
          getNum(data[3]['kills']['typeNames']['Battleships']),
          getNum(data[3]['kills']['typeNames']['Faction Battleships']),
          getNum(data[3]['kills']['typeNames']['Marauders']),
          getNum(data[3]['kills']['typeNames']['Black Ops']),
          getNum(data[3]['kills']['typeNames']['Battlecruisers']) + getNum(data[3]['kills']['typeNames']['Battlecruisers (Attack)']),
          getNum(data[3]['kills']['typeNames']['Faction Battlecruisers']),
          getNum(data[3]['kills']['typeNames']['Command Ships']),
          getNum(data[3]['kills']['typeNames']['Cruisers']),
          getNum(data[3]['kills']['typeNames']['Faction Cruisers']),
          getNum(data[3]['kills']['typeNames']['Recon Ships']),
          getNum(data[3]['kills']['typeNames']['Heavy Assault Cruisers']),
          getNum(data[3]['kills']['typeNames']['Heavy Interdictors']),
          getNum(data[3]['kills']['typeNames']['Logistics Cruisers']),
          getNum(data[3]['kills']['typeNames']['Strategic Cruisers']),
          getNum(data[3]['kills']['typeNames']['Destroyers']),
          getNum(data[3]['kills']['typeNames']['Interdictors']),
          getNum(data[3]['kills']['typeNames']['Command Destroyers']),
          getNum(data[3]['kills']['typeNames']['Tactical Destroyers']),
          getNum(data[3]['kills']['typeNames']['Frigates']),
          getNum(data[3]['kills']['typeNames']['Faction Frigates']),
          getNum(data[3]['kills']['typeNames']['Electronic Attack Frigates']),
          getNum(data[3]['kills']['typeNames']['Interceptors']),
          getNum(data[3]['kills']['typeNames']['Assault Frigates']),
          getNum(data[3]['kills']['typeNames']['Logistics Frigate']),
          getNum(data[3]['kills']['typeNames']['Covert Ops']),
          getNum(data[3]['kills']['typeNames']['Stealth Bombers']),
          getNum(data[3]['kills']['typeNames']['Mining Frigate']),
          getNum(data[3]['kills']['typeNames']['Mining Barges']),
          getNum(data[3]['kills']['typeNames']['Exhumer Barges']),
          getNum(data[3]['kills']['typeNames']['Capital Industrial Ships']),
          getNum(data[3]['kills']['typeNames']['Industrial Ships']),
          getNum(data[3]['kills']['typeNames']['Transport Ships'])
        ]
      }, {
        label: 'C4',
        backgroundColor: 'rgba(102,153,0,0.5)',
        data: [
          getNum(data[4]['kills']['typeNames']['Shuttle']),
          getNum(data[4]['kills']['typeNames']['Rookie Ships']),
          getNum(data[4]['kills']['typeNames']['Battleships']),
          getNum(data[4]['kills']['typeNames']['Faction Battleships']),
          getNum(data[4]['kills']['typeNames']['Marauders']),
          getNum(data[4]['kills']['typeNames']['Black Ops']),
          getNum(data[4]['kills']['typeNames']['Battlecruisers']) + getNum(data[4]['kills']['typeNames']['Battlecruisers (Attack)']),
          getNum(data[4]['kills']['typeNames']['Faction Battlecruisers']),
          getNum(data[4]['kills']['typeNames']['Command Ships']),
          getNum(data[4]['kills']['typeNames']['Cruisers']),
          getNum(data[4]['kills']['typeNames']['Faction Cruisers']),
          getNum(data[4]['kills']['typeNames']['Recon Ships']),
          getNum(data[4]['kills']['typeNames']['Heavy Assault Cruisers']),
          getNum(data[4]['kills']['typeNames']['Heavy Interdictors']),
          getNum(data[4]['kills']['typeNames']['Logistics Cruisers']),
          getNum(data[4]['kills']['typeNames']['Strategic Cruisers']),
          getNum(data[4]['kills']['typeNames']['Destroyers']),
          getNum(data[4]['kills']['typeNames']['Interdictors']),
          getNum(data[4]['kills']['typeNames']['Command Destroyers']),
          getNum(data[4]['kills']['typeNames']['Tactical Destroyers']),
          getNum(data[4]['kills']['typeNames']['Frigates']),
          getNum(data[4]['kills']['typeNames']['Faction Frigates']),
          getNum(data[4]['kills']['typeNames']['Electronic Attack Frigates']),
          getNum(data[4]['kills']['typeNames']['Interceptors']),
          getNum(data[4]['kills']['typeNames']['Assault Frigates']),
          getNum(data[4]['kills']['typeNames']['Logistics Frigate']),
          getNum(data[4]['kills']['typeNames']['Covert Ops']),
          getNum(data[4]['kills']['typeNames']['Stealth Bombers']),
          getNum(data[4]['kills']['typeNames']['Mining Frigate']),
          getNum(data[4]['kills']['typeNames']['Mining Barges']),
          getNum(data[4]['kills']['typeNames']['Exhumer Barges']),
          getNum(data[4]['kills']['typeNames']['Capital Industrial Ships']),
          getNum(data[4]['kills']['typeNames']['Industrial Ships']),
          getNum(data[4]['kills']['typeNames']['Transport Ships'])
        ]
      }, {
        label: 'C5',
        backgroundColor: 'rgba(255,102,0,0.5)',
        data: [
          getNum(data[5]['kills']['typeNames']['Shuttle']),
          getNum(data[5]['kills']['typeNames']['Rookie Ships']),
          getNum(data[5]['kills']['typeNames']['Battleships']),
          getNum(data[5]['kills']['typeNames']['Faction Battleships']),
          getNum(data[5]['kills']['typeNames']['Marauders']),
          getNum(data[5]['kills']['typeNames']['Black Ops']),
          getNum(data[5]['kills']['typeNames']['Battlecruisers']) + getNum(data[5]['kills']['typeNames']['Battlecruisers (Attack)']),
          getNum(data[5]['kills']['typeNames']['Faction Battlecruisers']),
          getNum(data[5]['kills']['typeNames']['Command Ships']),
          getNum(data[5]['kills']['typeNames']['Cruisers']),
          getNum(data[5]['kills']['typeNames']['Faction Cruisers']),
          getNum(data[5]['kills']['typeNames']['Recon Ships']),
          getNum(data[5]['kills']['typeNames']['Heavy Assault Cruisers']),
          getNum(data[5]['kills']['typeNames']['Heavy Interdictors']),
          getNum(data[5]['kills']['typeNames']['Logistics Cruisers']),
          getNum(data[5]['kills']['typeNames']['Strategic Cruisers']),
          getNum(data[5]['kills']['typeNames']['Destroyers']),
          getNum(data[5]['kills']['typeNames']['Interdictors']),
          getNum(data[5]['kills']['typeNames']['Command Destroyers']),
          getNum(data[5]['kills']['typeNames']['Tactical Destroyers']),
          getNum(data[5]['kills']['typeNames']['Frigates']),
          getNum(data[5]['kills']['typeNames']['Faction Frigates']),
          getNum(data[5]['kills']['typeNames']['Electronic Attack Frigates']),
          getNum(data[5]['kills']['typeNames']['Interceptors']),
          getNum(data[5]['kills']['typeNames']['Assault Frigates']),
          getNum(data[5]['kills']['typeNames']['Logistics Frigate']),
          getNum(data[5]['kills']['typeNames']['Covert Ops']),
          getNum(data[5]['kills']['typeNames']['Stealth Bombers']),
          getNum(data[5]['kills']['typeNames']['Mining Frigate']),
          getNum(data[5]['kills']['typeNames']['Mining Barges']),
          getNum(data[5]['kills']['typeNames']['Exhumer Barges']),
          getNum(data[5]['kills']['typeNames']['Capital Industrial Ships']),
          getNum(data[5]['kills']['typeNames']['Industrial Ships']),
          getNum(data[5]['kills']['typeNames']['Transport Ships'])
        ]
      }, {
        label: 'C6',
        backgroundColor: 'rgba(204,0,0,0.5)',
        data: [
          getNum(data[6]['kills']['typeNames']['Shuttle']),
          getNum(data[6]['kills']['typeNames']['Rookie Ships']),
          getNum(data[6]['kills']['typeNames']['Battleships']),
          getNum(data[6]['kills']['typeNames']['Faction Battleships']),
          getNum(data[6]['kills']['typeNames']['Marauders']),
          getNum(data[6]['kills']['typeNames']['Black Ops']),
          getNum(data[6]['kills']['typeNames']['Battlecruisers']) + getNum(data[6]['kills']['typeNames']['Battlecruisers (Attack)']),
          getNum(data[6]['kills']['typeNames']['Faction Battlecruisers']),
          getNum(data[6]['kills']['typeNames']['Command Ships']),
          getNum(data[6]['kills']['typeNames']['Cruisers']),
          getNum(data[6]['kills']['typeNames']['Faction Cruisers']),
          getNum(data[6]['kills']['typeNames']['Recon Ships']),
          getNum(data[6]['kills']['typeNames']['Heavy Assault Cruisers']),
          getNum(data[6]['kills']['typeNames']['Heavy Interdictors']),
          getNum(data[6]['kills']['typeNames']['Logistics Cruisers']),
          getNum(data[6]['kills']['typeNames']['Strategic Cruisers']),
          getNum(data[6]['kills']['typeNames']['Destroyers']),
          getNum(data[6]['kills']['typeNames']['Interdictors']),
          getNum(data[6]['kills']['typeNames']['Command Destroyers']),
          getNum(data[6]['kills']['typeNames']['Tactical Destroyers']),
          getNum(data[6]['kills']['typeNames']['Frigates']),
          getNum(data[6]['kills']['typeNames']['Faction Frigates']),
          getNum(data[6]['kills']['typeNames']['Electronic Attack Frigates']),
          getNum(data[6]['kills']['typeNames']['Interceptors']),
          getNum(data[6]['kills']['typeNames']['Assault Frigates']),
          getNum(data[6]['kills']['typeNames']['Logistics Frigate']),
          getNum(data[6]['kills']['typeNames']['Covert Ops']),
          getNum(data[6]['kills']['typeNames']['Stealth Bombers']),
          getNum(data[6]['kills']['typeNames']['Mining Frigate']),
          getNum(data[6]['kills']['typeNames']['Mining Barges']),
          getNum(data[6]['kills']['typeNames']['Exhumer Barges']),
          getNum(data[6]['kills']['typeNames']['Capital Industrial Ships']),
          getNum(data[6]['kills']['typeNames']['Industrial Ships']),
          getNum(data[6]['kills']['typeNames']['Transport Ships'])
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
