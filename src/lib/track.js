export function track(event, params={}){
  if(window.gtag){
    window.gtag('event', event, params)
  } else {
    // fallback log
    console.debug('[track]', event, params)
  }
}
