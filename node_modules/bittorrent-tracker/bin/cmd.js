#!/usr/bin/env node

const minimist = require('minimist')
const Server = require('../').Server

const argv = minimist(process.argv.slice(2), {
  alias: {
    h: 'help',
    p: 'port',
    q: 'quiet',
    s: 'silent',
    v: 'version'
  },
  boolean: [
    'help',
    'http',
    'quiet',
    'silent',
    'trust-proxy',
    'udp',
    'version',
    'ws',
    'stats'
  ],
  string: [
    'http-hostname',
    'udp-hostname',
    'udp6-hostname'
  ],
  default: {
    port: 8000,
    stats: true
  }
})

if (argv.version) {
  console.log(require('../package.json').version)
  process.exit(0)
}

if (argv.help) {
  console.log((() => {
  /*
  bittorrent-tracker - Start a bittorrent tracker server

  Usage:
    bittorrent-tracker [OPTIONS]

  If no --http, --udp, or --ws option is supplied, all tracker types will be started.

  Options:
    -p, --port [number]           change the port [default: 8000]
        --http-hostname [string]  change the http server hostname [default: '::']
        --udp-hostname [string]   change the udp hostname [default: '0.0.0.0']
        --udp6-hostname [string]  change the udp6 hostname [default: '::']
        --trust-proxy             trust 'x-forwarded-for' header from reverse proxy
        --interval                client announce interval (ms) [default: 600000]
        --http                    enable http server
        --udp                     enable udp server
        --ws                      enable websocket server
        --stats                   enable web-based statistics (default: true)
    -q, --quiet                   only show error output
    -s, --silent                  show no output
    -v, --version                 print the current version

  */
  }).toString().split(/\n/).slice(2, -2).join('\n'))
  process.exit(0)
}

if (argv.silent) argv.quiet = true

const allFalsy = !argv.http && !argv.udp && !argv.ws

argv.http = allFalsy || argv.http
argv.udp = allFalsy || argv.udp
argv.ws = allFalsy || argv.ws

const server = new Server({
  http: argv.http,
  interval: argv.interval,
  stats: argv.stats,
  trustProxy: argv['trust-proxy'],
  udp: argv.udp,
  ws: argv.ws
})

server.on('error', err => {
  if (!argv.silent) console.error(`ERROR: ${err.message}`)
})
server.on('warning', err => {
  if (!argv.quiet) console.log(`WARNING: ${err.message}`)
})
server.on('update', addr => {
  if (!argv.quiet) console.log(`update: ${addr}`)
})
server.on('complete', addr => {
  if (!argv.quiet) console.log(`complete: ${addr}`)
})
server.on('start', addr => {
  if (!argv.quiet) console.log(`start: ${addr}`)
})
server.on('stop', addr => {
  if (!argv.quiet) console.log(`stop: ${addr}`)
})

const hostname = {
  http: argv['http-hostname'],
  udp4: argv['udp-hostname'],
  udp6: argv['udp6-hostname']
}

server.listen(argv.port, hostname, () => {
  if (server.http && argv.http && !argv.quiet) {
    const httpAddr = server.http.address()
    const httpHost = httpAddr.address !== '::' ? httpAddr.address : 'localhost'
    const httpPort = httpAddr.port
    console.log(`HTTP tracker: http://${httpHost}:${httpPort}/announce`)
  }
  if (server.udp && !argv.quiet) {
    const udpAddr = server.udp.address()
    const udpHost = udpAddr.address
    const udpPort = udpAddr.port
    console.log(`UDP tracker: udp://${udpHost}:${udpPort}`)
  }
  if (server.udp6 && !argv.quiet) {
    const udp6Addr = server.udp6.address()
    const udp6Host = udp6Addr.address !== '::' ? udp6Addr.address : 'localhost'
    const udp6Port = udp6Addr.port
    console.log(`UDP6 tracker: udp://${udp6Host}:${udp6Port}`)
  }
  if (server.ws && !argv.quiet) {
    const wsAddr = server.http.address()
    const wsHost = wsAddr.address !== '::' ? wsAddr.address : 'localhost'
    const wsPort = wsAddr.port
    console.log(`WebSocket tracker: ws://${wsHost}:${wsPort}`)
  }
  if (server.http && argv.stats && !argv.quiet) {
    const statsAddr = server.http.address()
    const statsHost = statsAddr.address !== '::' ? statsAddr.address : 'localhost'
    const statsPort = statsAddr.port
    console.log(`Tracker stats: http://${statsHost}:${statsPort}/stats`)
  }
})
