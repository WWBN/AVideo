import test from 'ava'
import { mockPlay } from './common'
import canAutoplay from '../lib/index'

test.serial('returns true for available audio auto-playback', t => {
  mockPlay(() => Promise.resolve())
  return canAutoplay.audio().then(({result}) => t.true(result))
})

test.serial('returns false for unavailable audio auto-playback', t => {
  mockPlay(() => Promise.reject(new Error()))
  return canAutoplay.audio().then(({result}) => t.false(result))
})

test.serial('returns error object', t => {
  const message = 'Aborted.'
  mockPlay(() => Promise.reject(new Error(message)))
  return canAutoplay.audio().then(({error}) => t.is(error.message, message))
})
