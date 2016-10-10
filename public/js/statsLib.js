function pad (n) {
  return n < 10 ? '0' + n : n
}

function getNum (val) {
  if (isNaN(val) || val === null) {
    return 0
  }
  return val
}

function fmtMSS (s) {
  return (s - (s %= 60)) / 60 + (s > 9 ? ':' : ':0') + s
}

function truncateString (str, length) {
  if (str !== null) {
    return str.length > length ? str.substring(0, length - 3) + '...' : str
  } else {
    return '...'
  }
}
