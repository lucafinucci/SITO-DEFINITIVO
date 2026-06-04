import { useEffect } from "react";
import { useLocation } from "react-router-dom";
import { track } from "@/lib/track";

// Invia un evento page_view a GA4 ad ogni cambio di route.
// Necessario perché in una SPA gtag('config') invia un solo page_view al primo load.
export default function RouteTracker() {
  const location = useLocation();

  useEffect(() => {
    track("page_view", {
      page_path: location.pathname + location.search,
      page_location: window.location.href,
      page_title: document.title,
    });
  }, [location]);

  return null;
}
