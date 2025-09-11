// client_error_reporter.js
// Lightweight client-side error reporter that posts JS errors to the server.
(function(){
  if (window.__PT_CLIENT_ERROR_INSTALLED) return;
  window.__PT_CLIENT_ERROR_INSTALLED = true;

  const endpoint = '/log_client_error.php';
  const send = (payload) => {
    try {
      // Fire-and-forget, don't block the page
      navigator.sendBeacon && typeof navigator.sendBeacon === 'function'
        ? navigator.sendBeacon(endpoint, JSON.stringify(payload))
        : fetch(endpoint, {method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload), keepalive: true}).catch(()=>{});
    } catch(e) {
      // swallow errors
    }
  };

  const buildBase = () => ({
    url: location.href,
    ua: navigator.userAgent || '',
    ts: Date.now(),
    ref: document.referrer || ''
  });

  window.addEventListener('error', function(ev){
    try{
      const err = ev.error || {};
      const payload = Object.assign(buildBase(), {
        type: 'error',
        message: ev.message || (err && err.message) || '',
        filename: ev.filename || '',
        lineno: ev.lineno || 0,
        colno: ev.colno || 0,
        stack: err && err.stack ? String(err.stack) : ''
      });
      send(payload);
    }catch(e){}
  }, true);

  window.addEventListener('unhandledrejection', function(ev){
    try{
      const reason = ev.reason || {};
      const payload = Object.assign(buildBase(), {
        type: 'unhandledrejection',
        message: typeof reason === 'string' ? reason : (reason && reason.message) || '',
        stack: reason && reason.stack ? String(reason.stack) : JSON.stringify(reason)
      });
      send(payload);
    }catch(e){}
  }, true);

  // optional: capture console.error calls
  try{
    const origConsoleError = console.error.bind(console);
    console.error = function(...args){
      try{
        const payload = Object.assign(buildBase(), {
          type: 'console.error',
          message: args.map(a => (typeof a === 'string' ? a : (a && a.message) || JSON.stringify(a))).join(' '),
          args: args
        });
        send(payload);
      }catch(e){}
      origConsoleError(...args);
    };
  }catch(e){}

})();
