// Copyright 2018 The Go Authors. All rights reserved.
// Use of this source code is governed by a BSD-style
// license that can be found in the LICENSE file.
//
// This file has been modified for use by the TinyGo compiler.

(() => {
  // Map multiple JavaScript environments to a single common API,
  // preferring web standards over Node.js API.
  //
  // Environments considered:
  // - Browsers
  // - Node.js
  // - Electron
  // - Parcel

  if (typeof global !== 'undefined') {
    // global already exists
  } else if (typeof window !== 'undefined') {
    window.global = window
  } else if (typeof self !== 'undefined') {
    self.global = self
  } else {
    throw new Error('cannot export Go (neither global, window nor self is defined)')
  }


  const enosys = () => {
    const err = new Error('not implemented')
    err.code = 'ENOSYS'
    return err
  }



  // End of polyfills for common API.

  const encoder = new TextEncoder('utf-8');
  const decoder = new TextDecoder('utf-8');
  var logLine = [];

  global.Go = class {
    constructor () {
      this._callbackTimeouts = new Map()
      this._nextCallbackTimeoutID = 1

      const mem = () => {
        // The buffer may change when requesting more memory.
        return new DataView(this._inst.exports.memory.buffer)
      }

      const setInt64 = (addr, v) => {
        mem().setUint32(addr + 0, v, true)
        mem().setUint32(addr + 4, Math.floor(v / 4294967296), true)
      }

      const getInt64 = (addr) => {
        const low = mem().getUint32(addr + 0, true)
        const high = mem().getInt32(addr + 4, true)
        return low + high * 4294967296
      }

      const loadValue = (addr) => {
        const f = mem().getFloat64(addr, true)
        if (f === 0) {
          return undefined
        }
        if (!isNaN(f)) {
          return f
        }

        const id = mem().getUint32(addr, true)
        return this._values[id]
      }

      const storeValue = (addr, v) => {
        const nanHead = 0x7FF80000

        if (typeof v === 'number') {
          if (isNaN(v)) {
            mem().setUint32(addr + 4, nanHead, true)
            mem().setUint32(addr, 0, true)
            return
          }
          if (v === 0) {
            mem().setUint32(addr + 4, nanHead, true)
            mem().setUint32(addr, 1, true)
            return
          }
          mem().setFloat64(addr, v, true)
          return
        }

        switch (v) {
          case undefined:
            mem().setFloat64(addr, 0, true)
            return
          case null:
            mem().setUint32(addr + 4, nanHead, true)
            mem().setUint32(addr, 2, true)
            return
          case true:
            mem().setUint32(addr + 4, nanHead, true)
            mem().setUint32(addr, 3, true)
            return
          case false:
            mem().setUint32(addr + 4, nanHead, true)
            mem().setUint32(addr, 4, true)
            return
        }

        let id = this._ids.get(v)
        if (id === undefined) {
          id = this._idPool.pop()
          if (id === undefined) {
            id = this._values.length
          }
          this._values[id] = v
          this._goRefCounts[id] = 0
          this._ids.set(v, id)
        }
        this._goRefCounts[id]++
        let typeFlag = 1
        switch (typeof v) {
          case 'string':
            typeFlag = 2
            break
          case 'symbol':
            typeFlag = 3
            break
          case 'function':
            typeFlag = 4
            break
        }
        mem().setUint32(addr + 4, nanHead | typeFlag, true)
        mem().setUint32(addr, id, true)
      }

      const loadSlice = (array, len, cap) => {
        return new Uint8Array(this._inst.exports.memory.buffer, array, len)
      }

      const loadSliceOfValues = (array, len, cap) => {

        const a = new Array(len)
        for (let i = 0; i < len; i++) {
          a[i] = loadValue(array + i * 8)
        }
        return a
      }

      const loadString = (ptr, len) => {

        return decoder.decode(new DataView(this._inst.exports.memory.buffer, ptr, len))
      }

      const timeOrigin = Date.now() - performance.now()
      this.importObject = {
        wasi_unstable: {
          // https://github.com/bytecodealliance/wasmtime/blob/master/docs/WASI-api.md#__wasi_fd_write
          fd_write: function (fd, iovs_ptr, iovs_len, nwritten_ptr) {
            const nwritten = 0
            if (fd == 1) {
              for (let iovs_i = 0; iovs_i < iovs_len; iovs_i++) {
                const iov_ptr = iovs_ptr + iovs_i * 8 // assuming wasm32
                const ptr = mem().getUint32(iov_ptr + 0, true)
                const len = mem().getUint32(iov_ptr + 4, true)
                for (let i = 0; i < len; i++) {
                  const c = mem().getUint8(ptr + i)
                  if (c == 13) { // CR
                    // ignore
                  } else if (c == 10) { // LF
                    // write line
                    const line = decoder.decode(new Uint8Array(logLine))
                    logLine = []
                    console.log(line)
                  } else {
                    logLine.push(c)
                  }
                }
              }
            } else {
              console.error('invalid file descriptor:', fd)
            }
            mem().setUint32(nwritten_ptr, nwritten, true);
            
            return 0;
          }
        },
        env: {


          // func finalizeRef(v ref)
          'syscall/js.finalizeRef': (sp) => {
            // Note: TinyGo does not support finalizers so this should never be
            // called.
            // NOTE commented line below.
            // console.error('syscall/js.finalizeRef not implemented');
          },

          // func stringVal(value string) ref
          'syscall/js.stringVal': (ret_ptr, value_ptr, value_len) => {

          },

          // func valueGet(v ref, p string) ref
          'syscall/js.valueGet': (retval, v_addr, p_ptr, p_len) => {

            const prop = loadString(p_ptr, p_len)
            const value = loadValue(v_addr)
            const result = Reflect.get(value, prop)
            storeValue(retval, result)

          },

          // func valueSet(v ref, p string, x ref)
          'syscall/js.valueSet': (v_addr, p_ptr, p_len, x_addr) => {
            const v = loadValue(v_addr)
            const p = loadString(p_ptr, p_len)
            const x = loadValue(x_addr)
            Reflect.set(v, p, x)

          },



          // func valueIndex(v ref, i int) ref
          'syscall/js.valueIndex': (ret_addr, v_addr, i) => {
            storeValue(ret_addr, Reflect.get(loadValue(v_addr), i))
          },

          // valueSetIndex(v ref, i int, x ref)
          'syscall/js.valueSetIndex': (v_addr, i, x_addr) => {
            //console.log(i, loadValue(x_addr));
            Reflect.set(loadValue(v_addr), i, loadValue(x_addr))
          },

          // func valueCall(v ref, m string, args []ref) (ref, bool)
          'syscall/js.valueCall': (ret_addr, v_addr, m_ptr, m_len, args_ptr, args_len, args_cap) => {
            const v = loadValue(v_addr)
            const name = loadString(m_ptr, m_len)
            const args = loadSliceOfValues(args_ptr, args_len, args_cap)
            try {
              const m = Reflect.get(v, name)
              storeValue(ret_addr, Reflect.apply(m, v, args))
              mem().setUint8(ret_addr + 8, 1)
            } catch (err) {
              storeValue(ret_addr, err)
              mem().setUint8(ret_addr + 8, 0)
            }
          },



          // func valueNew(v ref, args []ref) (ref, bool)
          'syscall/js.valueNew': (ret_addr, v_addr, args_ptr, args_len, args_cap) => {
            const v = loadValue(v_addr)
            const args = loadSliceOfValues(args_ptr, args_len, args_cap)
            try {
              storeValue(ret_addr, Reflect.construct(v, args))
              mem().setUint8(ret_addr + 8, 1)
            } catch (err) {
              storeValue(ret_addr, err)
              mem().setUint8(ret_addr + 8, 0)
            }
          },

          // func valueLength(v ref) int
          'syscall/js.valueLength': (v_addr) => {
            return loadValue(v_addr).length
          },

          // valuePrepareString(v ref) (ref, int)
          'syscall/js.valuePrepareString': (ret_addr, v_addr) => {
            const s = String(loadValue(v_addr))
            const str = encoder.encode(s)
            storeValue(ret_addr, str)
            setInt64(ret_addr + 8, str.length)
          },

          // valueLoadString(v ref, b []byte)
          'syscall/js.valueLoadString': (v_addr, slice_ptr, slice_len, slice_cap) => {
            const str = loadValue(v_addr)
            loadSlice(slice_ptr, slice_len, slice_cap).set(str)
          }
        }
      }
    }

    async run (instance) {
      this._inst = instance
      this._values = [ // JS values that Go currently has references to, indexed by reference id
        NaN,
        0,
        null,
        true,
        false,
        global,
        this
      ]
      this._goRefCounts = [] // number of references that Go has to a JS value, indexed by reference id
      this._ids = new Map() // mapping from JS values to reference ids
      this._idPool = [] // unused ids that have been garbage collected
      this.exited = false // whether the Go program has exited

      //const mem = new DataView(this._inst.exports.memory.buffer)

      while (true) {
        const callbackPromise = new Promise((resolve) => {
          this._resolveCallbackPromise = () => {
            if (this.exited) {
              throw new Error('bad callback: Go program has already exited')
            }
            setTimeout(resolve, 0) // make sure it is asynchronous
          }
        })
        this._inst.exports._start()
        if (this.exited) {
          break
        }
        await callbackPromise
      }
    }

    _resume () {
      if (this.exited) {
        throw new Error('Go program has already exited')
      }
      this._inst.exports.resume()
      if (this.exited) {
        this._resolveExitPromise()
      }
    }

    _makeFuncWrapper (id) {
      const go = this
      return function () {
        const event = { id: id, this: this, args: arguments }
        go._pendingEvent = event
        go._resume();
        //console.log(event)
        return event.result
      }
    }
  }

})()
