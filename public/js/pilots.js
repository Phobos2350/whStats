var setPeriod = 0
var renderedOnce = 0
var setTz = 'ALL'
var date = new Date()
var month = new Date().getMonth()
var year = new Date().getFullYear()
var weekday = new Array(7)
var table
var killData
weekday[0] = 'Sun'
weekday[1] = 'Mon'
weekday[2] = 'Tue'
weekday[3] = 'Wed'
weekday[4] = 'Thu'
weekday[5] = 'Fri'
weekday[6] = 'Sat'

$(document).ready(function () {
  setPeriod = 'hour'
  setTz = 'ALL'
  $('.entitiesLink').parent().addClass('active')
  $('.periodLinks-hour').trigger('click')
  $('.tzLinks-all').addClass('tzLinks-active')
})

$('.tzLinks').click(function () {
  setTz = $(this).text().toLowerCase()
  $('.tzLinks').removeClass('tzLinks-active')
  $(this).addClass('tzLinks-active')
  changePeriod(setTz, setPeriod)
})

$('.periodLinks').click(function () {
  killData = null
  setPeriod = $(this).text().toLowerCase()
  $('.periodLinks').parent().removeClass('active')
  $(this).parent().addClass('active')
  date = new Date()
  $('.period').removeClass('hide')
  $('#tzEU').removeClass('disabled')
  $('#tzUS').removeClass('disabled')
  $('#tzAU').removeClass('disabled')
  if (setPeriod === 'hour') {
    setTz = 'all'
    $('.tzLinks').removeClass('tzLinks-active')
    $('.tzLinks-all').addClass('tzLinks-active')
    if (date.getHours() === 0) {
      date.setHours(23)
      date.setDate(date.getDate() - 1)
    } else {
      date.setHours(date.getHours() - 1)
    }
    $('.period').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    $('.periodStats').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    if (date.getHours() >= 0 && date.getHours() < 8) {
      $('#tzEU').removeClass('active')
      $('#tzAU').removeClass('active')
      $('#tzEU').addClass('disabled')
      $('#tzAU').addClass('disabled')
    }
    if (date.getHours() >= 8 && date.getHours() < 16) {
      $('#tzEU').removeClass('active')
      $('#tzUS').removeClass('active')
      $('#tzEU').addClass('disabled')
      $('#tzUS').addClass('disabled')
    }
    if (date.getHours() >= 16 && date.getHours() < 24) {
      $('#tzAU').removeClass('active')
      $('#tzUS').removeClass('active')
      $('#tzAU').addClass('disabled')
      $('#tzUS').addClass('disabled')
    }
  }
  if (setPeriod === 'day') {
    if (date.getDay() === 0) {
      date.setDate(6)
    } else {
      date.setDate(date.getDate() - 1)
    }
    $('.period').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    $('.periodStats').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
  }
  if (setPeriod === 'week') {
    date.setDate(date.getDate() - 7)
    $('.period').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
    $('.periodStats').text('Kills Since - ' + weekday[date.getDay()] + ' ' + date.getDate() + ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()))
  }
  if (setPeriod === 'month') {
    $('.period').addClass('hide')
    $('.monthLinks').removeClass('hide')
    setPeriod = 'year/' + year + '/month/' + pad(month + 1)
    var currMonth = new Date(year, month, 1, 0, 0, 0, 0)
    $('.currMonth').text(currMonth.toLocaleString('en-gb', {
      month: 'long'
    }) + ' ' + currMonth.getFullYear())
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
  changePeriod(setTz, setPeriod)
})

$('.prevMonth').click(function () {
  killData = null
  if (month === 0) {
    month = 11
    year -= 1
  } else {
    month -= 1
  }
  setPeriod = 'year/' + year + '/month/' + pad(month + 1)
  changePeriod(setTz, setPeriod)

  var currMonth = new Date(year, month, 1, 0, 0, 0, 0)
  $('.currMonth').text(currMonth.toLocaleString('en-gb', {
    month: 'long'
  }) + ' ' + currMonth.getFullYear())
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
  killData = null
  if (month === 12) {
    month = 1
    year += 1
  } else {
    month += 1
  }
  setPeriod = 'year/' + year + '/month/' + pad(month + 1)
  changePeriod(setTz, setPeriod)
  var thisMonth = new Date().getMonth()
  var currMonth = new Date(year, month, 1, 0, 0, 0, 0)
  $('.currMonth').text(currMonth.toLocaleString('en-gb', {
    month: 'long'
  }) + ' ' + currMonth.getFullYear())
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

function changePeriod (tz, period) {
  $('.modal-content-text').text('Loading Stats')
  $('#modal1').openModal()
  if (killData !== null) {
    updateTable(killData)
    setTimeout(function () {
      $('#modal1').closeModal()
    }, 500)
  } else {
    $.getJSON('/api/rethink/pilots/period/' + period + '/', function (json) {
      killData = json
      // console.log(json);
      if (json['stats'] === null) {
        $('.modal-content-text').text('No Data! A Task has been despatched! Try Again in a few moments')
        setTimeout(function () {
          $('#modal1').closeModal()
        }, 1500)
        return null
      }
      if (renderedOnce !== 0) {
        table.destroy()
      }
      updateTable(killData)
      table = $('#stats').DataTable({
        'oLanguage': {
          'sStripClasses': '',
          'sSearch': '',
          'sSearchPlaceholder': 'Enter Keywords Here',
          'sInfo': '_START_ -_END_ of _TOTAL_',
          'sLengthMenu': '<span>Rows per page:</span><select class="browser-default">' +
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
        scrollY: '650px',
        scrollX: true,
        scrollCollapse: true,
        fixedColumns: {
          leftColumns: 2
        }
      })
      renderedOnce = 1
      setTimeout(function () {
        $('#modal1').closeModal()
      }, 500)
    }).error(function (error) {
      console.log(error)
      $('#modal1').openModal()
      $('.modal-content-text').text('No Data! A Task has been despatched! Try Again in a few moments')
      setTimeout(function () {
        $('#modal1').closeModal()
      }, 1500)
    })
  }
}

function pad (n) {
  return n < 10 ? '0' + n : n
}

function updateTable (data) {
  $('.lastCached').text(data['lastCached'])
  data = data['statsArray']['stats'][setTz.toUpperCase()]
  var r = []
  var j = 0
  for (var key = 0, size = data.length; key < size; key++) {
    r[++j] = '<tr><td>'
    r[++j] = key + 1
    r[++j] = '</td><td>'
    r[++j] = '<a target="_blank" href="http://stats.limited-power.co.uk/pilot/' + data[key]['entityID'] + '">' + data[key]['entityName'] + '</a>'
    r[++j] = '</td><td>'
    if (data[key]['allianceID'] != 0) {
      r[++j] = '<a target="_blank" href="http://stats.limited-power.co.uk/entity/' + data[key]['allianceID'] + '">' + data[key]['allianceName'] + '</a>'
      r[++j] = '</td><td>'
    } else {
      r[++j] = '<a target="_blank" href="http://stats.limited-power.co.uk/entity/' + data[key]['corporationID'] + '">' + data[key]['corporationName'] + '</a>'
      r[++j] = '</td><td>'
    }
    var iskVal = (data[key]['totalISK'] / 1000000000).toFixed(1, 10)
    var isk = ''
    if (parseInt(iskVal, 10) >= 1000) {
      iskVal = (iskVal / 1000).toFixed(1, 10)
      isk = iskVal + 'T'
    } else {
      isk = iskVal + 'B'
    }
    r[++j] = isk
    r[++j] = '</td><td>'
    r[++j] = data[key]['totalKills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c1Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c2Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c3Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c4Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c5Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c6Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c7Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c8Kills']
    r[++j] = '</td><td>'
    r[++j] = data[key]['c9Kills']
    r[++j] = '</td><td>'
    if ('Frigates' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Frigates']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Faction Frigates' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Faction Frigates']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Electronic Attack Frigates' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Electronic Attack Frigates']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Interceptors' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Interceptors']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Assault Frigates' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Assault Frigates']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Logistics Frigate' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Logistics Frigate']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Covert Ops' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Covert Ops']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Stealth Bombers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Stealth Bombers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Destroyers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Destroyers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Interdictors' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Interdictors']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Command Destroyers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Command Destroyers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Tactical Destroyers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Tactical Destroyers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Cruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Cruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Faction Cruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Faction Cruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Recon Ships' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Recon Ships']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Heavy Assault Cruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Heavy Assault Cruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Heavy Interdictors' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Heavy Interdictors']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Logistics Cruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Logistics Cruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Strategic Cruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Strategic Cruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Battlecruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Battlecruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Faction Battlecruisers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Faction Battlecruisers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Command Ships' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Command Ships']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Battleships' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Battleships']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Faction Battleships' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Faction Battleships']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Marauders' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Marauders']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Black Ops' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Black Ops']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Carriers' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Carriers']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Dreadnoughts' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Dreadnoughts']['totalUses']
      r[++j] = '</td><td>'
    } else {
      r[++j] = 0
      r[++j] = '</td><td>'
    }
    if ('Force Auxiliary' in data[key]['shipsUsed']) {
      r[++j] = data[key]['shipsUsed']['Force Auxiliary']['totalUses']
      r[++j] = '</td></tr>'
    } else {
      r[++j] = 0
      r[++j] = '</td></tr>'
    }
  }
  $('#dataTable').html(r.join(''))
}
