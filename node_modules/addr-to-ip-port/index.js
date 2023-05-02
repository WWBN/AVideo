const ADDR_RE = /^\[?([^\]]+)]?:(\d+)$/ // ipv4/ipv6/hostname + port

let cache = new Map()

// reset cache when it gets to 100,000 elements (~ 600KB of ipv4 addresses)
// so it will not grow to consume all memory in long-running processes
module.exports = function addrToIPPort (addr) {
  if (cache.size === 100000) cache.clear()
  if (!cache.has(addr)) {
    const m = ADDR_RE.exec(addr)
    if (!m) throw new Error(`invalid addr: ${addr}`)
    cache.set(addr, [ m[1], Number(m[2]) ])
  }
  return cache.get(addr)
}
